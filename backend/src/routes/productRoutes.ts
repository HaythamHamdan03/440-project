import { Router } from "express";
import * as productController from "../controllers/productController";

const router = Router();

// ============ User Routes ============

/**
 * POST /api/users/register
 * Register a new user with a role
 * Body: { privateKey, role (1=Producer, 2=Supplier, 3=Consumer), name }
 */
router.post("/users/register", productController.registerUser);

/**
 * GET /api/users/:address
 * Get user information by address
 */
router.get("/users/:address", productController.getUser);

/**
 * GET /api/users/:address/products
 * Get all products owned by a user
 */
router.get("/users/:address/products", productController.getUserProducts);

/**
 * GET /api/users/:address/balance
 * Get ETH balance of a user
 */
router.get("/users/:address/balance", productController.getBalance);

// ============ Product Routes ============

/**
 * POST /api/products
 * Register a new product (Producer only)
 * Body: { privateKey, name, description, price (in ETH), location }
 */
router.post("/products", productController.registerProduct);

/**
 * GET /api/products
 * Get all product IDs
 */
router.get("/products", productController.getAllProducts);

/**
 * GET /api/products/:productId
 * Get product details by ID
 */
router.get("/products/:productId", productController.getProduct);

/**
 * GET /api/products/:productId/history
 * Get complete transfer history of a product
 */
router.get("/products/:productId/history", productController.getProductHistory);

/**
 * PUT /api/products/:productId/transfer
 * Transfer product to another user
 * Body: { privateKey, toAddress, location }
 */
router.put("/products/:productId/transfer", productController.transferProduct);

/**
 * PUT /api/products/:productId/purchase
 * Purchase product with payment (transfer with ETH)
 * Body: { privateKey, location, paymentAmount (in ETH) }
 */
router.put("/products/:productId/purchase", productController.transferProductWithPayment);

/**
 * PUT /api/products/:productId/deliver
 * Confirm product delivery (Consumer only)
 * Body: { privateKey, location }
 */
router.put("/products/:productId/deliver", productController.confirmDelivery);

/**
 * POST /api/products/:productId/verify
 * Verify product authenticity using hash comparison
 * Body: { name, description, price (in ETH), producer }
 */
router.post("/products/:productId/verify", productController.verifyProduct);

// ============ Utility Routes ============

/**
 * POST /api/wallet/address
 * Get wallet address from private key
 * Body: { privateKey }
 */
router.post("/wallet/address", productController.getWalletAddress);

export default router;
