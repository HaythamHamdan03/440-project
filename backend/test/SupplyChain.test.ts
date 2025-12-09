import { expect } from "chai";
import { ethers } from "hardhat";
import { SupplyChain } from "../typechain-types";
import { HardhatEthersSigner } from "@nomicfoundation/hardhat-ethers/signers";

describe("SupplyChain", function () {
  let supplyChain: SupplyChain;
  let owner: HardhatEthersSigner;
  let producer: HardhatEthersSigner;
  let supplier: HardhatEthersSigner;
  let consumer: HardhatEthersSigner;

  // Role enum values
  const Role = {
    None: 0,
    Producer: 1,
    Supplier: 2,
    Consumer: 3
  };

  // Status enum values
  const ProductStatus = {
    Created: 0,
    InTransit: 1,
    WithSupplier: 2,
    Sold: 3,
    Delivered: 4
  };

  beforeEach(async function () {
    [owner, producer, supplier, consumer] = await ethers.getSigners();

    const SupplyChainFactory = await ethers.getContractFactory("SupplyChain");
    supplyChain = await SupplyChainFactory.deploy();
    await supplyChain.waitForDeployment();
  });

  describe("User Registration", function () {
    it("should register a producer", async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
      const [role, name, isRegistered] = await supplyChain.getUser(producer.address);
      
      expect(role).to.equal(Role.Producer);
      expect(name).to.equal("Test Producer");
      expect(isRegistered).to.be.true;
    });

    it("should register a supplier", async function () {
      await supplyChain.connect(supplier).registerUser(Role.Supplier, "Test Supplier");
      const [role, name, isRegistered] = await supplyChain.getUser(supplier.address);
      
      expect(role).to.equal(Role.Supplier);
      expect(name).to.equal("Test Supplier");
      expect(isRegistered).to.be.true;
    });

    it("should register a consumer", async function () {
      await supplyChain.connect(consumer).registerUser(Role.Consumer, "Test Consumer");
      const [role, name, isRegistered] = await supplyChain.getUser(consumer.address);
      
      expect(role).to.equal(Role.Consumer);
      expect(name).to.equal("Test Consumer");
      expect(isRegistered).to.be.true;
    });

    it("should not allow registering with None role", async function () {
      await expect(
        supplyChain.connect(producer).registerUser(Role.None, "Invalid User")
      ).to.be.revertedWith("Invalid role");
    });

    it("should not allow double registration", async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
      await expect(
        supplyChain.connect(producer).registerUser(Role.Supplier, "Another Name")
      ).to.be.revertedWith("User already registered");
    });
  });

  describe("Product Registration", function () {
    beforeEach(async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
    });

    it("should register a product", async function () {
      const tx = await supplyChain.connect(producer).registerProduct(
        "Test Product",
        "A test product description",
        ethers.parseEther("1"),
        "Factory A"
      );

      const receipt = await tx.wait();
      expect(receipt).to.not.be.null;

      const productCount = await supplyChain.getTotalProducts();
      expect(productCount).to.equal(1);
    });

    it("should emit ProductRegistered event", async function () {
      await expect(
        supplyChain.connect(producer).registerProduct(
          "Test Product",
          "A test product description",
          ethers.parseEther("1"),
          "Factory A"
        )
      ).to.emit(supplyChain, "ProductRegistered");
    });

    it("should not allow non-producers to register products", async function () {
      await supplyChain.connect(supplier).registerUser(Role.Supplier, "Test Supplier");
      
      await expect(
        supplyChain.connect(supplier).registerProduct(
          "Test Product",
          "Description",
          ethers.parseEther("1"),
          "Location"
        )
      ).to.be.revertedWith("Only producers can call this");
    });

    it("should not allow unregistered users to register products", async function () {
      await expect(
        supplyChain.connect(supplier).registerProduct(
          "Test Product",
          "Description",
          ethers.parseEther("1"),
          "Location"
        )
      ).to.be.revertedWith("User not registered");
    });
  });

  describe("Product Transfer", function () {
    let productId: string;

    beforeEach(async function () {
      // Register users
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
      await supplyChain.connect(supplier).registerUser(Role.Supplier, "Test Supplier");
      await supplyChain.connect(consumer).registerUser(Role.Consumer, "Test Consumer");

      // Register product
      const tx = await supplyChain.connect(producer).registerProduct(
        "Test Product",
        "A test product description",
        ethers.parseEther("1"),
        "Factory A"
      );

      const receipt = await tx.wait();
      const event = receipt?.logs.find(
        (log: any) => log.fragment?.name === "ProductRegistered"
      );
      productId = (event as any)?.args?.[0];
    });

    it("should transfer product from producer to supplier", async function () {
      await supplyChain.connect(producer).transferProduct(
        productId,
        supplier.address,
        "Warehouse B"
      );

      const product = await supplyChain.getProduct(productId);
      expect(product.currentOwner).to.equal(supplier.address);
      expect(product.status).to.equal(ProductStatus.WithSupplier);
    });

    it("should transfer product from supplier to consumer", async function () {
      // First transfer to supplier
      await supplyChain.connect(producer).transferProduct(
        productId,
        supplier.address,
        "Warehouse B"
      );

      // Then transfer to consumer
      await supplyChain.connect(supplier).transferProduct(
        productId,
        consumer.address,
        "Consumer Location"
      );

      const product = await supplyChain.getProduct(productId);
      expect(product.currentOwner).to.equal(consumer.address);
      expect(product.status).to.equal(ProductStatus.Sold);
    });

    it("should record transfer history", async function () {
      await supplyChain.connect(producer).transferProduct(
        productId,
        supplier.address,
        "Warehouse B"
      );

      const history = await supplyChain.getProductHistory(productId);
      expect(history.length).to.equal(2); // Initial creation + transfer
      expect(history[1].from).to.equal(producer.address);
      expect(history[1].to).to.equal(supplier.address);
    });

    it("should not allow non-owners to transfer", async function () {
      await expect(
        supplyChain.connect(supplier).transferProduct(
          productId,
          consumer.address,
          "Some Location"
        )
      ).to.be.revertedWith("Not the product owner");
    });
  });

  describe("Product Transfer with Payment", function () {
    let productId: string;
    const productPrice = ethers.parseEther("1");

    beforeEach(async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
      await supplyChain.connect(supplier).registerUser(Role.Supplier, "Test Supplier");

      const tx = await supplyChain.connect(producer).registerProduct(
        "Test Product",
        "A test product description",
        productPrice,
        "Factory A"
      );

      const receipt = await tx.wait();
      const event = receipt?.logs.find(
        (log: any) => log.fragment?.name === "ProductRegistered"
      );
      productId = (event as any)?.args?.[0];
    });

    it("should transfer product with payment", async function () {
      const producerBalanceBefore = await ethers.provider.getBalance(producer.address);

      await supplyChain.connect(supplier).transferProductWithPayment(
        productId,
        "Warehouse B",
        { value: productPrice }
      );

      const producerBalanceAfter = await ethers.provider.getBalance(producer.address);
      expect(producerBalanceAfter - producerBalanceBefore).to.equal(productPrice);

      const product = await supplyChain.getProduct(productId);
      expect(product.currentOwner).to.equal(supplier.address);
    });

    it("should reject insufficient payment", async function () {
      await expect(
        supplyChain.connect(supplier).transferProductWithPayment(
          productId,
          "Warehouse B",
          { value: ethers.parseEther("0.5") }
        )
      ).to.be.revertedWith("Insufficient payment");
    });
  });

  describe("Product Verification", function () {
    let productId: string;
    const productName = "Test Product";
    const productDescription = "A test product description";
    const productPrice = ethers.parseEther("1");

    beforeEach(async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");

      const tx = await supplyChain.connect(producer).registerProduct(
        productName,
        productDescription,
        productPrice,
        "Factory A"
      );

      const receipt = await tx.wait();
      const event = receipt?.logs.find(
        (log: any) => log.fragment?.name === "ProductRegistered"
      );
      productId = (event as any)?.args?.[0];
    });

    it("should verify authentic product", async function () {
      const isValid = await supplyChain.verifyProduct(
        productId,
        productName,
        productDescription,
        productPrice,
        producer.address
      );

      expect(isValid).to.be.true;
    });

    it("should reject tampered product details", async function () {
      const isValid = await supplyChain.verifyProduct(
        productId,
        "Fake Product",
        productDescription,
        productPrice,
        producer.address
      );

      expect(isValid).to.be.false;
    });
  });

  describe("Delivery Confirmation", function () {
    let productId: string;

    beforeEach(async function () {
      await supplyChain.connect(producer).registerUser(Role.Producer, "Test Producer");
      await supplyChain.connect(supplier).registerUser(Role.Supplier, "Test Supplier");
      await supplyChain.connect(consumer).registerUser(Role.Consumer, "Test Consumer");

      const tx = await supplyChain.connect(producer).registerProduct(
        "Test Product",
        "A test product description",
        ethers.parseEther("1"),
        "Factory A"
      );

      const receipt = await tx.wait();
      const event = receipt?.logs.find(
        (log: any) => log.fragment?.name === "ProductRegistered"
      );
      productId = (event as any)?.args?.[0];

      // Transfer through supply chain
      await supplyChain.connect(producer).transferProduct(productId, supplier.address, "Warehouse");
      await supplyChain.connect(supplier).transferProduct(productId, consumer.address, "Delivery");
    });

    it("should allow consumer to confirm delivery", async function () {
      await supplyChain.connect(consumer).confirmDelivery(productId, "Home Address");

      const product = await supplyChain.getProduct(productId);
      expect(product.status).to.equal(ProductStatus.Delivered);
    });

    it("should not allow non-consumers to confirm delivery", async function () {
      // Transfer back to supplier for this test
      await supplyChain.connect(consumer).transferProduct(productId, supplier.address, "Return");

      await expect(
        supplyChain.connect(supplier).confirmDelivery(productId, "Location")
      ).to.be.revertedWith("Only consumers can confirm delivery");
    });
  });
});
