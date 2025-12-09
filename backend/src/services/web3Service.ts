import { ethers, Contract, Wallet, JsonRpcProvider, formatEther, parseEther } from "ethers";
import config from "../config";
import { SUPPLY_CHAIN_ABI } from "../config/abi";

// Role and Status enums matching the contract
export enum Role {
  None = 0,
  Producer = 1,
  Supplier = 2,
  Consumer = 3
}

export enum ProductStatus {
  Created = 0,
  InTransit = 1,
  WithSupplier = 2,
  Sold = 3,
  Delivered = 4
}

// Type definitions
export interface Product {
  productId: string;
  productHash: string;
  name: string;
  description: string;
  producer: string;
  currentOwner: string;
  price: string;
  priceWei: string;
  createdAt: number;
  status: ProductStatus;
  statusName: string;
}

export interface TransferRecord {
  from: string;
  to: string;
  timestamp: number;
  status: ProductStatus;
  statusName: string;
  location: string;
  transactionHash: string;
}

export interface User {
  address: string;
  role: Role;
  roleName: string;
  name: string;
  isRegistered: boolean;
}

// Helper function to get status name
function getStatusName(status: ProductStatus): string {
  const statusNames: Record<ProductStatus, string> = {
    [ProductStatus.Created]: "Created",
    [ProductStatus.InTransit]: "In Transit",
    [ProductStatus.WithSupplier]: "With Supplier",
    [ProductStatus.Sold]: "Sold",
    [ProductStatus.Delivered]: "Delivered"
  };
  return statusNames[status] || "Unknown";
}

// Helper function to get role name
function getRoleName(role: Role): string {
  const roleNames: Record<Role, string> = {
    [Role.None]: "None",
    [Role.Producer]: "Producer",
    [Role.Supplier]: "Supplier",
    [Role.Consumer]: "Consumer"
  };
  return roleNames[role] || "Unknown";
}

class Web3Service {
  private provider: JsonRpcProvider;
  private contract: Contract | null = null;
  private wallet: Wallet | null = null;

  constructor() {
    this.provider = new JsonRpcProvider(config.blockchain.rpcUrl);
    
    // Initialize wallet if private key is provided
    if (config.blockchain.privateKey) {
      this.wallet = new Wallet(config.blockchain.privateKey, this.provider);
    }

    // Only initialize contract if address is set
    if (config.blockchain.contractAddress) {
      const signer = this.wallet || this.provider;
      this.contract = new Contract(
        config.blockchain.contractAddress,
        SUPPLY_CHAIN_ABI,
        signer
      );
    }
  }

  // Check if contract is initialized
  private ensureContract(): Contract {
    if (!this.contract) {
      throw new Error("Contract not initialized. Set CONTRACT_ADDRESS in .env and restart the server.");
    }
    return this.contract;
  }

  // Get a contract instance connected to a specific wallet
  getContractWithSigner(privateKey: string): Contract {
    if (!config.blockchain.contractAddress) {
      throw new Error("Contract not initialized. Set CONTRACT_ADDRESS in .env and restart the server.");
    }
    const wallet = new Wallet(privateKey, this.provider);
    return new Contract(config.blockchain.contractAddress, SUPPLY_CHAIN_ABI, wallet);
  }

  // ============ User Functions ============

  async registerUser(privateKey: string, role: Role, name: string): Promise<string> {
    const contract = this.getContractWithSigner(privateKey);
    const tx = await contract.registerUser(role, name);
    const receipt = await tx.wait();
    return receipt.hash;
  }

  async getUser(address: string): Promise<User> {
    const contract = this.ensureContract();
    const [role, name, isRegistered] = await contract.getUser(address);
    return {
      address,
      role: Number(role) as Role,
      roleName: getRoleName(Number(role) as Role),
      name,
      isRegistered
    };
  }

  // ============ Product Functions ============

  async registerProduct(
    privateKey: string,
    name: string,
    description: string,
    priceEth: string,
    location: string
  ): Promise<{ transactionHash: string; productId: string }> {
    const contract = this.getContractWithSigner(privateKey);
    const priceWei = parseEther(priceEth);
    
    const tx = await contract.registerProduct(name, description, priceWei, location);
    const receipt = await tx.wait();

    // Extract product ID from event
    const event = receipt.logs.find(
      (log: any) => log.fragment?.name === "ProductRegistered"
    );
    const productId = event?.args?.[0] || "";

    return {
      transactionHash: receipt.hash,
      productId
    };
  }

  async transferProduct(
    privateKey: string,
    productId: string,
    toAddress: string,
    location: string
  ): Promise<string> {
    const contract = this.getContractWithSigner(privateKey);
    const tx = await contract.transferProduct(productId, toAddress, location);
    const receipt = await tx.wait();
    return receipt.hash;
  }

  async transferProductWithPayment(
    privateKey: string,
    productId: string,
    location: string,
    paymentEth: string
  ): Promise<string> {
    const contract = this.getContractWithSigner(privateKey);
    const paymentWei = parseEther(paymentEth);
    
    const tx = await contract.transferProductWithPayment(productId, location, {
      value: paymentWei
    });
    const receipt = await tx.wait();
    return receipt.hash;
  }

  async confirmDelivery(
    privateKey: string,
    productId: string,
    location: string
  ): Promise<string> {
    const contract = this.getContractWithSigner(privateKey);
    const tx = await contract.confirmDelivery(productId, location);
    const receipt = await tx.wait();
    return receipt.hash;
  }

  // ============ Query Functions ============

  async getProduct(productId: string): Promise<Product> {
    const contract = this.ensureContract();
    const result = await contract.getProduct(productId);
    const status = Number(result.status) as ProductStatus;
    
    return {
      productId,
      productHash: result.productHash,
      name: result.name,
      description: result.description,
      producer: result.producer,
      currentOwner: result.currentOwner,
      price: formatEther(result.price),
      priceWei: result.price.toString(),
      createdAt: Number(result.createdAt),
      status,
      statusName: getStatusName(status)
    };
  }

  async getProductHistory(productId: string): Promise<TransferRecord[]> {
    const contract = this.ensureContract();
    const history = await contract.getProductHistory(productId);
    
    return history.map((record: any) => {
      const status = Number(record.status) as ProductStatus;
      return {
        from: record.from,
        to: record.to,
        timestamp: Number(record.timestamp),
        status,
        statusName: getStatusName(status),
        location: record.location,
        transactionHash: record.transactionHash
      };
    });
  }

  async verifyProduct(
    productId: string,
    name: string,
    description: string,
    priceEth: string,
    producer: string
  ): Promise<boolean> {
    const contract = this.ensureContract();
    const priceWei = parseEther(priceEth);
    return await contract.verifyProduct(
      productId,
      name,
      description,
      priceWei,
      producer
    );
  }

  async getUserProducts(address: string): Promise<string[]> {
    const contract = this.ensureContract();
    return await contract.getUserProducts(address);
  }

  async getTotalProducts(): Promise<number> {
    const contract = this.ensureContract();
    const count = await contract.getTotalProducts();
    return Number(count);
  }

  async getAllProductIds(): Promise<string[]> {
    const contract = this.ensureContract();
    return await contract.getAllProductIds();
  }

  async getProductHistoryLength(productId: string): Promise<number> {
    const contract = this.ensureContract();
    const length = await contract.getProductHistoryLength(productId);
    return Number(length);
  }

  // ============ Utility Functions ============

  async getBalance(address: string): Promise<string> {
    const balance = await this.provider.getBalance(address);
    return formatEther(balance);
  }

  async getWalletAddress(privateKey: string): Promise<string> {
    const wallet = new Wallet(privateKey);
    return wallet.address;
  }

  isValidAddress(address: string): boolean {
    return ethers.isAddress(address);
  }

  isValidBytes32(hash: string): boolean {
    return /^0x[a-fA-F0-9]{64}$/.test(hash);
  }
}

// Export singleton instance
export const web3Service = new Web3Service();
export default web3Service;
