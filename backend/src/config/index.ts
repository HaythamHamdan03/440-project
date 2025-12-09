import dotenv from "dotenv";

// Load environment variables
dotenv.config();

interface Config {
  port: number;
  nodeEnv: string;
  blockchain: {
    rpcUrl: string;
    privateKey: string;
    contractAddress: string;
    chainId: number;
  };
}

const config: Config = {
  port: parseInt(process.env.PORT || "3000", 10),
  nodeEnv: process.env.NODE_ENV || "development",
  blockchain: {
    rpcUrl: process.env.RPC_URL || "http://127.0.0.1:8545",
    privateKey: process.env.PRIVATE_KEY || "",
    contractAddress: process.env.CONTRACT_ADDRESS || "",
    chainId: parseInt(process.env.CHAIN_ID || "31337", 10),
  },
};

// Validate required config
export function validateConfig(): void {
  if (!config.blockchain.contractAddress) {
    console.warn("WARNING: CONTRACT_ADDRESS not set. Deploy the contract first.");
  }
  if (!config.blockchain.privateKey && config.nodeEnv === "production") {
    throw new Error("PRIVATE_KEY is required in production");
  }
}

export default config;
