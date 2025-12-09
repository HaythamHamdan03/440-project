import { Request, Response, NextFunction } from "express";
import web3Service, { Role } from "../services/web3Service";

// Helper for async error handling
const asyncHandler = (fn: Function) => (req: Request, res: Response, next: NextFunction) => {
  Promise.resolve(fn(req, res, next)).catch(next);
};

// ============ User Controllers ============

export const registerUser = asyncHandler(async (req: Request, res: Response) => {
  const { privateKey, role, name } = req.body;

  if (!privateKey || role === undefined || !name) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: privateKey, role, name"
    });
  }

  // Validate role
  if (![Role.Producer, Role.Supplier, Role.Consumer].includes(role)) {
    return res.status(400).json({
      success: false,
      error: "Invalid role. Must be 1 (Producer), 2 (Supplier), or 3 (Consumer)"
    });
  }

  const txHash = await web3Service.registerUser(privateKey, role, name);
  const address = await web3Service.getWalletAddress(privateKey);

  res.status(201).json({
    success: true,
    data: {
      transactionHash: txHash,
      address,
      role,
      name
    }
  });
});

export const getUser = asyncHandler(async (req: Request, res: Response) => {
  const { address } = req.params;

  if (!web3Service.isValidAddress(address)) {
    return res.status(400).json({
      success: false,
      error: "Invalid Ethereum address"
    });
  }

  const user = await web3Service.getUser(address);

  if (!user.isRegistered) {
    return res.status(404).json({
      success: false,
      error: "User not found"
    });
  }

  res.json({
    success: true,
    data: user
  });
});

// ============ Product Controllers ============

export const registerProduct = asyncHandler(async (req: Request, res: Response) => {
  const { privateKey, name, description, price, location } = req.body;

  if (!privateKey || !name || !description || !price || !location) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: privateKey, name, description, price, location"
    });
  }

  const result = await web3Service.registerProduct(
    privateKey,
    name,
    description,
    price,
    location
  );

  res.status(201).json({
    success: true,
    data: {
      productId: result.productId,
      transactionHash: result.transactionHash,
      name,
      description,
      price,
      location
    }
  });
});

export const getProduct = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format. Must be a bytes32 hash."
    });
  }

  const product = await web3Service.getProduct(productId);

  res.json({
    success: true,
    data: product
  });
});

export const transferProduct = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;
  const { privateKey, toAddress, location } = req.body;

  if (!privateKey || !toAddress || !location) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: privateKey, toAddress, location"
    });
  }

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format"
    });
  }

  if (!web3Service.isValidAddress(toAddress)) {
    return res.status(400).json({
      success: false,
      error: "Invalid recipient address"
    });
  }

  const txHash = await web3Service.transferProduct(
    privateKey,
    productId,
    toAddress,
    location
  );

  res.json({
    success: true,
    data: {
      transactionHash: txHash,
      productId,
      toAddress,
      location
    }
  });
});

export const transferProductWithPayment = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;
  const { privateKey, location, paymentAmount } = req.body;

  if (!privateKey || !location || !paymentAmount) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: privateKey, location, paymentAmount"
    });
  }

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format"
    });
  }

  const txHash = await web3Service.transferProductWithPayment(
    privateKey,
    productId,
    location,
    paymentAmount
  );

  res.json({
    success: true,
    data: {
      transactionHash: txHash,
      productId,
      location,
      paymentAmount
    }
  });
});

export const confirmDelivery = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;
  const { privateKey, location } = req.body;

  if (!privateKey || !location) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: privateKey, location"
    });
  }

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format"
    });
  }

  const txHash = await web3Service.confirmDelivery(privateKey, productId, location);

  res.json({
    success: true,
    data: {
      transactionHash: txHash,
      productId,
      location,
      status: "Delivered"
    }
  });
});

export const getProductHistory = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format"
    });
  }

  const history = await web3Service.getProductHistory(productId);

  res.json({
    success: true,
    data: {
      productId,
      historyCount: history.length,
      history
    }
  });
});

export const verifyProduct = asyncHandler(async (req: Request, res: Response) => {
  const { productId } = req.params;
  const { name, description, price, producer } = req.body;

  if (!name || !description || !price || !producer) {
    return res.status(400).json({
      success: false,
      error: "Missing required fields: name, description, price, producer"
    });
  }

  if (!web3Service.isValidBytes32(productId)) {
    return res.status(400).json({
      success: false,
      error: "Invalid product ID format"
    });
  }

  const isValid = await web3Service.verifyProduct(
    productId,
    name,
    description,
    price,
    producer
  );

  res.json({
    success: true,
    data: {
      productId,
      isAuthentic: isValid,
      verifiedAt: new Date().toISOString()
    }
  });
});

export const getUserProducts = asyncHandler(async (req: Request, res: Response) => {
  const { address } = req.params;

  if (!web3Service.isValidAddress(address)) {
    return res.status(400).json({
      success: false,
      error: "Invalid Ethereum address"
    });
  }

  const productIds = await web3Service.getUserProducts(address);

  res.json({
    success: true,
    data: {
      address,
      productCount: productIds.length,
      productIds
    }
  });
});

export const getAllProducts = asyncHandler(async (req: Request, res: Response) => {
  const productIds = await web3Service.getAllProductIds();
  const totalCount = await web3Service.getTotalProducts();

  res.json({
    success: true,
    data: {
      totalCount,
      productIds
    }
  });
});

// ============ Utility Controllers ============

export const getBalance = asyncHandler(async (req: Request, res: Response) => {
  const { address } = req.params;

  if (!web3Service.isValidAddress(address)) {
    return res.status(400).json({
      success: false,
      error: "Invalid Ethereum address"
    });
  }

  const balance = await web3Service.getBalance(address);

  res.json({
    success: true,
    data: {
      address,
      balance,
      unit: "ETH"
    }
  });
});

export const getWalletAddress = asyncHandler(async (req: Request, res: Response) => {
  const { privateKey } = req.body;

  if (!privateKey) {
    return res.status(400).json({
      success: false,
      error: "Missing required field: privateKey"
    });
  }

  const address = await web3Service.getWalletAddress(privateKey);

  res.json({
    success: true,
    data: {
      address
    }
  });
});
