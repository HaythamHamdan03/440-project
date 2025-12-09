# Supply Chain Tracking Backend

A blockchain-based supply chain tracking system built with **Express.js**, **Ethers.js v6**, and **Hardhat**.

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Variables](#environment-variables)
  - [Deploying the Smart Contract](#deploying-the-smart-contract)
  - [Running the Server](#running-the-server)
- [API Reference](#api-reference)
  - [User Endpoints](#user-endpoints)
  - [Product Endpoints](#product-endpoints)
  - [Utility Endpoints](#utility-endpoints)
- [Smart Contract](#smart-contract)
- [Testing](#testing)
- [Development Notes](#development-notes)

---

## Overview

This backend provides a REST API for tracking products through a supply chain using Ethereum smart contracts. It supports:

- **User Management**: Register users as Producer, Supplier, or Consumer
- **Product Registration**: Producers can register products on the blockchain
- **Product Transfer**: Transfer products through the supply chain
- **Payment Integration**: Transfer products with ETH payment
- **Product Verification**: Verify product authenticity using cryptographic hashes
- **History Tracking**: Complete transaction history for each product

---

## Tech Stack

| Component         | Technology                |
| ----------------- | ------------------------- |
| Runtime           | Node.js + TypeScript      |
| API Framework     | Express.js v5             |
| Blockchain        | Ethereum (Hardhat/Sepolia)|
| Smart Contracts   | Solidity 0.8.24           |
| Ethereum Library  | Ethers.js v6              |

---

## Project Structure

```
backend/
├── contracts/              # Solidity smart contracts
│   └── SupplyChain.sol
├── scripts/                # Deployment scripts
│   └── deploy.ts
├── src/
│   ├── app.ts              # Express server entry point
│   ├── config/             # Configuration
│   │   ├── index.ts        # Environment config
│   │   └── abi.ts          # Contract ABI
│   ├── controllers/        # Route handlers
│   │   └── productController.ts
│   ├── routes/             # API routes
│   │   └── productRoutes.ts
│   ├── services/           # Business logic
│   │   └── web3Service.ts  # Blockchain interactions
│   └── utils/
│       └── hashUtils.ts    # Hashing utilities
├── test/                   # Contract tests
│   └── SupplyChain.test.ts
├── typechain-types/        # Generated TypeScript types
├── .env.example            # Environment template
├── hardhat.config.ts       # Hardhat configuration
├── package.json
└── tsconfig.json
```

---

## Getting Started

### Prerequisites

- **Node.js** v18+ (v20 recommended)
- **npm** or **yarn**
- (Optional) **MetaMask** wallet for Sepolia testnet

### Installation

```bash
cd backend
npm install
```

### Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

| Variable           | Required | Description                                      | Where to Get It                                                                 |
| ------------------ | -------- | ------------------------------------------------ | ------------------------------------------------------------------------------- |
| `PORT`             | No       | Server port (default: `3000`)                    | Set any available port                                                          |
| `NODE_ENV`         | No       | Environment (`development` or `production`)      | Set based on your environment                                                   |
| `RPC_URL`          | Yes      | Ethereum RPC endpoint                            | `http://127.0.0.1:8545` for local, or Infura/Alchemy URL for testnet            |
| `CHAIN_ID`         | Yes      | Network chain ID                                 | `31337` for Hardhat local, `11155111` for Sepolia                               |
| `CONTRACT_ADDRESS` | Yes      | Deployed smart contract address                  | Output from `npm run deploy:local` or `npm run deploy:sepolia`                  |
| `PRIVATE_KEY`      | No       | Server wallet private key (for server-side txs)  | From MetaMask or Hardhat test accounts                                          |
| `SEPOLIA_RPC_URL`  | No       | Sepolia RPC URL (for deployment only)            | [Infura](https://infura.io), [Alchemy](https://alchemy.com), or public RPC      |

#### Example `.env` for Local Development

```env
PORT=3000
NODE_ENV=development
RPC_URL=http://127.0.0.1:8545
CHAIN_ID=31337
CONTRACT_ADDRESS=0x5FbDB2315678afecb367f032d93F642f64180aa3
```

#### Hardhat Test Accounts (for local development)

When running `npm run node`, Hardhat provides test accounts with 10,000 ETH each:

| Account | Address                                      | Private Key                                                          |
| ------- | -------------------------------------------- | -------------------------------------------------------------------- |
| #0      | `0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266` | `0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80` |
| #1      | `0x70997970C51812dc3A010C7d01b50e0d17dc79C8` | `0x59c6995e998f97a5a0044966f0945389dc9e86dae88c7a8412f4603b6b78690d` |
| #2      | `0x3C44CdDdB6a900fa2b585dd299e03d12FA4293BC` | `0x5de4111afa1a4b94908f83103eb1f1706367c2e68ca870fc3fb9a804cdab365a` |

---

### Deploying the Smart Contract

#### 1. Compile the Contract

```bash
npm run compile
```

#### 2. Start Local Blockchain (Terminal 1)

```bash
npm run node
```

Keep this terminal running.

#### 3. Deploy Contract (Terminal 2)

**Local Development:**

```bash
npm run deploy:local
```

**Sepolia Testnet:**

```bash
npm run deploy:sepolia
```

Copy the deployed contract address from the output and set it in your `.env` file:

```
CONTRACT_ADDRESS=0x5FbDB2315678afecb367f032d93F642f64180aa3
```

---

### Running the Server

```bash
# Development mode (with hot reload)
npm run dev

# Production mode
npm run build
npm start
```

The server will start at `http://localhost:3000`.

---

## API Reference

All endpoints return JSON responses in this format:

```json
{
  "success": true,
  "data": { ... }
}
```

Or on error:

```json
{
  "success": false,
  "error": "Error message"
}
```

---

### User Endpoints

#### `POST /api/users/register`

Register a new user on the blockchain.

**Request Body:**

| Field        | Type     | Required | Description                                         |
| ------------ | -------- | -------- | --------------------------------------------------- |
| `privateKey` | `string` | Yes      | User's Ethereum private key                         |
| `role`       | `number` | Yes      | Role: `1` = Producer, `2` = Supplier, `3` = Consumer |
| `name`       | `string` | Yes      | User's display name                                 |

**Example:**

```bash
curl -X POST http://localhost:3000/api/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "privateKey": "0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80",
    "role": 1,
    "name": "Apple Farm"
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "transactionHash": "0x...",
    "address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "role": 1,
    "name": "Apple Farm"
  }
}
```

---

#### `GET /api/users/:address`

Get user information by Ethereum address.

**URL Parameters:**

| Parameter | Type     | Description              |
| --------- | -------- | ------------------------ |
| `address` | `string` | User's Ethereum address  |

**Example:**

```bash
curl http://localhost:3000/api/users/0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
```

**Response:**

```json
{
  "success": true,
  "data": {
    "address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "role": 1,
    "roleName": "Producer",
    "name": "Apple Farm",
    "isRegistered": true
  }
}
```

---

#### `GET /api/users/:address/products`

Get all product IDs owned by a user.

**Response:**

```json
{
  "success": true,
  "data": {
    "address": "0x...",
    "productCount": 2,
    "productIds": ["0x123...", "0x456..."]
  }
}
```

---

#### `GET /api/users/:address/balance`

Get ETH balance of an address.

**Response:**

```json
{
  "success": true,
  "data": {
    "address": "0x...",
    "balance": "9999.99",
    "unit": "ETH"
  }
}
```

---

### Product Endpoints

#### `POST /api/products`

Register a new product (Producer only).

**Request Body:**

| Field         | Type     | Required | Description                        |
| ------------- | -------- | -------- | ---------------------------------- |
| `privateKey`  | `string` | Yes      | Producer's private key             |
| `name`        | `string` | Yes      | Product name                       |
| `description` | `string` | Yes      | Product description                |
| `price`       | `string` | Yes      | Price in ETH (e.g., `"0.5"`)       |
| `location`    | `string` | Yes      | Initial location                   |

**Example:**

```bash
curl -X POST http://localhost:3000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "privateKey": "0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80",
    "name": "Organic Apples",
    "description": "Fresh organic apples from local farm",
    "price": "0.5",
    "location": "Farm Warehouse A"
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "productId": "0x1234567890abcdef...",
    "transactionHash": "0x...",
    "name": "Organic Apples",
    "description": "Fresh organic apples from local farm",
    "price": "0.5",
    "location": "Farm Warehouse A"
  }
}
```

---

#### `GET /api/products`

Get all product IDs.

**Response:**

```json
{
  "success": true,
  "data": {
    "totalCount": 5,
    "productIds": ["0x123...", "0x456...", "..."]
  }
}
```

---

#### `GET /api/products/:productId`

Get product details by ID.

**Response:**

```json
{
  "success": true,
  "data": {
    "productId": "0x123...",
    "productHash": "0xabc...",
    "name": "Organic Apples",
    "description": "Fresh organic apples from local farm",
    "producer": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "currentOwner": "0x70997970C51812dc3A010C7d01b50e0d17dc79C8",
    "price": "0.5",
    "priceWei": "500000000000000000",
    "createdAt": 1702234567,
    "status": 2,
    "statusName": "With Supplier"
  }
}
```

**Product Status Values:**

| Status | Name           | Description                          |
| ------ | -------------- | ------------------------------------ |
| `0`    | Created        | Product just registered              |
| `1`    | In Transit     | Product being transferred            |
| `2`    | With Supplier  | Product received by supplier         |
| `3`    | Sold           | Product sold to consumer             |
| `4`    | Delivered      | Consumer confirmed delivery          |

---

#### `PUT /api/products/:productId/transfer`

Transfer product to another user.

**Request Body:**

| Field        | Type     | Required | Description                 |
| ------------ | -------- | -------- | --------------------------- |
| `privateKey` | `string` | Yes      | Current owner's private key |
| `toAddress`  | `string` | Yes      | Recipient's Ethereum address|
| `location`   | `string` | Yes      | New location                |

**Example:**

```bash
curl -X PUT http://localhost:3000/api/products/0x123.../transfer \
  -H "Content-Type: application/json" \
  -d '{
    "privateKey": "0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80",
    "toAddress": "0x70997970C51812dc3A010C7d01b50e0d17dc79C8",
    "location": "Distribution Center B"
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "transactionHash": "0x...",
    "productId": "0x123...",
    "toAddress": "0x70997970C51812dc3A010C7d01b50e0d17dc79C8",
    "location": "Distribution Center B"
  }
}
```

---

#### `PUT /api/products/:productId/purchase`

Purchase a product with ETH payment.

**Request Body:**

| Field           | Type     | Required | Description                    |
| --------------- | -------- | -------- | ------------------------------ |
| `privateKey`    | `string` | Yes      | Buyer's private key            |
| `location`      | `string` | Yes      | Delivery location              |
| `paymentAmount` | `string` | Yes      | Payment amount in ETH          |

**Response:**

```json
{
  "success": true,
  "data": {
    "transactionHash": "0x...",
    "productId": "0x123...",
    "location": "Customer Address",
    "paymentAmount": "0.5"
  }
}
```

---

#### `PUT /api/products/:productId/deliver`

Confirm product delivery (Consumer only).

**Request Body:**

| Field        | Type     | Required | Description              |
| ------------ | -------- | -------- | ------------------------ |
| `privateKey` | `string` | Yes      | Consumer's private key   |
| `location`   | `string` | Yes      | Final delivery location  |

**Response:**

```json
{
  "success": true,
  "data": {
    "transactionHash": "0x...",
    "productId": "0x123...",
    "location": "Customer Home",
    "status": "Delivered"
  }
}
```

---

#### `GET /api/products/:productId/history`

Get complete transfer history of a product.

**Response:**

```json
{
  "success": true,
  "data": {
    "productId": "0x123...",
    "historyCount": 3,
    "history": [
      {
        "from": "0x0000000000000000000000000000000000000000",
        "to": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
        "timestamp": 1702234567,
        "status": 0,
        "statusName": "Created",
        "location": "Farm Warehouse A",
        "transactionHash": "0x..."
      },
      {
        "from": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
        "to": "0x70997970C51812dc3A010C7d01b50e0d17dc79C8",
        "timestamp": 1702234600,
        "status": 2,
        "statusName": "With Supplier",
        "location": "Distribution Center B",
        "transactionHash": "0x..."
      }
    ]
  }
}
```

---

#### `POST /api/products/:productId/verify`

Verify product authenticity by checking stored hash.

**Request Body:**

| Field         | Type     | Required | Description                     |
| ------------- | -------- | -------- | ------------------------------- |
| `name`        | `string` | Yes      | Expected product name           |
| `description` | `string` | Yes      | Expected product description    |
| `price`       | `string` | Yes      | Expected price in ETH           |
| `producer`    | `string` | Yes      | Expected producer address       |

**Example:**

```bash
curl -X POST http://localhost:3000/api/products/0x123.../verify \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Organic Apples",
    "description": "Fresh organic apples from local farm",
    "price": "0.5",
    "producer": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266"
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "productId": "0x123...",
    "isAuthentic": true,
    "verifiedAt": "2024-12-10T12:00:00.000Z"
  }
}
```

---

### Utility Endpoints

#### `POST /api/wallet/address`

Get Ethereum address from private key.

**Request Body:**

| Field        | Type     | Required | Description       |
| ------------ | -------- | -------- | ----------------- |
| `privateKey` | `string` | Yes      | Ethereum private key |

**Response:**

```json
{
  "success": true,
  "data": {
    "address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266"
  }
}
```

---

#### `GET /health`

Health check endpoint.

**Response:**

```json
{
  "status": "ok",
  "timestamp": "2024-12-10T12:00:00.000Z",
  "environment": "development"
}
```

---

## Smart Contract

The `SupplyChain.sol` contract includes:

| Function                        | Description                            |
| ------------------------------- | -------------------------------------- |
| `registerUser(role, name)`      | Register as Producer/Supplier/Consumer |
| `registerProduct(...)`          | Register a new product                 |
| `transferProduct(...)`          | Transfer product ownership             |
| `transferProductWithPayment(...)` | Purchase with ETH payment            |
| `confirmDelivery(...)`          | Consumer confirms receipt              |
| `getProduct(productId)`         | Get product details                    |
| `getProductHistory(productId)`  | Get transfer history                   |
| `verifyProduct(...)`            | Verify product authenticity            |

---

## Testing

Run smart contract tests:

```bash
npm run test:contract
```

---

## Development Notes

### Security Considerations

- **Never commit `.env` files** with real private keys
- Private keys in API requests are for **demo purposes only**
- In production, use proper wallet integration (MetaMask, WalletConnect)
- All blockchain transactions are signed server-side in this demo

### Getting Sepolia ETH

For Sepolia testnet deployment:

1. Get Sepolia ETH from a faucet: https://sepoliafaucet.com/
2. Set `SEPOLIA_RPC_URL` in `.env` (get from [Infura](https://infura.io) or [Alchemy](https://alchemy.com))
3. Set your `PRIVATE_KEY` in `.env`
4. Deploy with `npm run deploy:sepolia`

---

## License

ISC
