import express, { Request, Response, NextFunction } from "express";
import config, { validateConfig } from "./config";
import productRoutes from "./routes/productRoutes";

// Validate configuration
validateConfig();

const app = express();

// ============ Middleware ============

// Parse JSON bodies
app.use(express.json());

// Parse URL-encoded bodies
app.use(express.urlencoded({ extended: true }));

// CORS middleware (allow all origins for development)
app.use((req: Request, res: Response, next: NextFunction) => {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
  res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization");
  
  if (req.method === "OPTIONS") {
    return res.sendStatus(200);
  }
  next();
});

// Request logging
app.use((req: Request, res: Response, next: NextFunction) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
  next();
});

// ============ Routes ============

// Health check
app.get("/health", (req: Request, res: Response) => {
  res.json({
    status: "ok",
    timestamp: new Date().toISOString(),
    environment: config.nodeEnv
  });
});

// API info
app.get("/", (req: Request, res: Response) => {
  res.json({
    name: "Supply Chain Tracking API",
    version: "1.0.0",
    description: "Blockchain-based supply chain tracking system API",
    endpoints: {
      health: "GET /health",
      users: {
        register: "POST /api/users/register",
        getUser: "GET /api/users/:address",
        getUserProducts: "GET /api/users/:address/products",
        getBalance: "GET /api/users/:address/balance"
      },
      products: {
        create: "POST /api/products",
        getAll: "GET /api/products",
        getOne: "GET /api/products/:productId",
        getHistory: "GET /api/products/:productId/history",
        transfer: "PUT /api/products/:productId/transfer",
        purchase: "PUT /api/products/:productId/purchase",
        deliver: "PUT /api/products/:productId/deliver",
        verify: "POST /api/products/:productId/verify"
      },
      wallet: {
        getAddress: "POST /api/wallet/address"
      }
    }
  });
});

// Mount API routes
app.use("/api", productRoutes);

// ============ Error Handling ============

// 404 handler
app.use((req: Request, res: Response) => {
  res.status(404).json({
    success: false,
    error: "Endpoint not found",
    path: req.path
  });
});

// Global error handler
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
  console.error("Error:", err.message);
  
  // Handle specific blockchain errors
  if (err.message.includes("reverted")) {
    const revertReason = err.message.match(/reverted with reason string '(.+)'/)?.[1] 
      || err.message.match(/reason="([^"]+)"/)?.[1]
      || "Transaction reverted";
    
    return res.status(400).json({
      success: false,
      error: revertReason,
      type: "ContractError"
    });
  }

  // Handle invalid private key
  if (err.message.includes("invalid private key")) {
    return res.status(400).json({
      success: false,
      error: "Invalid private key provided",
      type: "ValidationError"
    });
  }

  // Generic error response
  res.status(500).json({
    success: false,
    error: config.nodeEnv === "development" ? err.message : "Internal server error",
    type: "ServerError"
  });
});

// ============ Server Start ============

const PORT = config.port;

app.listen(PORT, () => {
  console.log(`
╔════════════════════════════════════════════════════════════╗
║          Supply Chain Tracking API Server                  ║
╠════════════════════════════════════════════════════════════╣
║  Server running on:  http://localhost:${PORT}                  ║
║  Environment:        ${config.nodeEnv.padEnd(35)}║
║  Contract Address:   ${(config.blockchain.contractAddress || "Not set").slice(0, 30).padEnd(35)}║
║  RPC URL:            ${config.blockchain.rpcUrl.slice(0, 30).padEnd(35)}║
╚════════════════════════════════════════════════════════════╝
  `);
});

export default app;
