# System Architecture - Blockchain Supply Chain Tracking

## High-Level Overview

This is a **hybrid blockchain supply chain tracking system** that combines:
- **PHP Frontend** (Server-side rendered dashboards)
- **Node.js Backend API** (Blockchain interaction layer)
- **Ethereum Smart Contract** (Sepolia testnet or local Hardhat)
- **MetaMask Integration** (User wallet connection)

The system allows three user roles (Producer, Supplier, Consumer) to track products through a supply chain using blockchain technology.

---

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    User Browser (MetaMask)                   │
│  • Connects wallet via MetaMask extension                   │
│  • Signs transactions directly in browser                    │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│              PHP Frontend (Server-Side Rendering)            │
│  • dashboard_producer.php                                   │
│  • dashboard_supplier.php                                   │
│  • dashboard_consumer.php                                   │
│  • login.php, config.php                                    │
│  • File-based storage (products.txt, users.txt)             │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓ (HTTP Requests)
┌─────────────────────────────────────────────────────────────┐
│          JavaScript Client (Direct Blockchain)               │
│  • js/wallet.js - MetaMask connection                       │
│  • js/blockchain.js - Direct contract interaction          │
│  • Uses ethers.js v5 for blockchain calls                   │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓ (MetaMask Transaction Signing)
┌─────────────────────────────────────────────────────────────┐
│              Ethereum Blockchain (Sepolia/Local)             │
│  • SupplyChain.sol smart contract                           │
│  • Product registration, transfers, history                │
└─────────────────────────────────────────────────────────────┘
```

---

## Core Components

### 1. PHP Frontend Layer

**Purpose**: Server-side rendered dashboards for different user roles

**Key Files**:
- `dashboard_producer.php` - Producer dashboard (register products)
- `dashboard_supplier.php` - Supplier dashboard (purchase/transfer products)
- `dashboard_consumer.php` - Consumer dashboard (purchase/view history)
- `config.php` - Core configuration, authentication, file-based storage
- `login.php` - User authentication
- `partials/header.php` - Shared navigation with MetaMask buttons

**Responsibilities**:
- User authentication and session management
- Role-based access control (Producer, Supplier, Consumer, Admin)
- File-based data storage (`products.txt`, `users.txt`) as fallback
- Rendering HTML dashboards with product tables and forms
- Displaying transaction hashes and product status

**Data Flow**:
- Reads/writes to `products.txt` and `users.txt` for local data
- Stores MetaMask wallet address in PHP session
- Displays blockchain transaction results

---

### 2. JavaScript Client Layer

**Purpose**: Direct blockchain interaction via MetaMask

**Key Files**:
- `js/wallet.js` - MetaMask wallet connection and management
- `js/blockchain.js` - Smart contract interaction functions

#### `js/wallet.js`
**Responsibilities**:
- Connect/disconnect MetaMask wallet
- Store wallet address in PHP session via `store_wallet.php`
- Check network (Sepolia testnet or local Hardhat)
- Handle account and network changes
- Update UI buttons (Connect/Disconnect)

**Key Functions**:
- `connectWallet()` - Request MetaMask connection
- `disconnectWallet()` - Clear wallet connection
- `checkNetwork()` - Verify correct network (Sepolia or local)
- `updateWalletButton()` - Toggle Connect/Disconnect buttons

#### `js/blockchain.js`
**Responsibilities**:
- Initialize ethers.js provider and contract instance
- Direct smart contract function calls via MetaMask
- Handle transaction signing and confirmation

**Key Functions**:
- `initBlockchain()` - Initialize contract connection
- `approveProductOnBlockchain()` - Producer: Register product
- `purchaseProductOnBlockchain()` - Supplier/Consumer: Purchase with payment
- `transferProductOnBlockchain()` - Supplier: Transfer product

**Configuration**:
- `CONTRACT_ADDRESS` - Deployed smart contract address
- `CONTRACT_ABI` - Contract function definitions

---

### 3. Smart Contract Layer

**Purpose**: Blockchain-based product tracking

**Location**: `backend/contracts/SupplyChain.sol`

**Key Functions**:
- `registerProduct()` - Register new product (Producer)
- `transferProduct()` - Transfer product ownership
- `transferProductWithPayment()` - Transfer with ETH payment
- `getProduct()` - Get product details
- `getProductHistory()` - Get complete transfer history

**Deployment**:
- **Sepolia Testnet**: `npm run deploy:sepolia` (requires Sepolia ETH)
- **Local Hardhat**: `npm run deploy:local` (requires local node running)

**Network Options**:
1. **Sepolia Testnet** (Recommended for course projects)
   - Free testnet ETH from faucets
   - Viewable on https://sepolia.etherscan.io
   - Requires valid RPC URL

2. **Local Hardhat** (For testing)
   - Runs on `http://127.0.0.1:8545`
   - Chain ID: 31337
   - Pre-funded test accounts

---

### 4. Backend API Layer (Optional)

**Purpose**: Server-side blockchain operations (alternative to direct MetaMask)

**Location**: `backend/` directory

**Key Components**:
- `src/app.ts` - Express.js server
- `src/services/web3Service.ts` - Blockchain service
- `src/controllers/productController.ts` - API endpoints
- `api_client.php` - PHP client for backend API

**Note**: The current implementation uses **direct MetaMask interaction** (via `js/blockchain.js`), so the backend API is optional. The `api_client.php` exists for potential future use.

---

## Component Interactions

### Flow 1: User Connects Wallet

```
1. User clicks "Connect Wallet" button
   ↓
2. js/wallet.js → connectWallet()
   ↓
3. MetaMask popup appears → User approves
   ↓
4. Wallet address stored in PHP session (store_wallet.php)
   ↓
5. js/blockchain.js → initBlockchain()
   ↓
6. Contract instance created with ethers.js
   ↓
7. UI updates: "Disconnect" button shows, address displayed
```

### Flow 2: Producer Registers Product

```
1. Producer fills form in dashboard_producer.php
   ↓
2. Clicks "Approve" button
   ↓
3. js/blockchain.js → approveProductOnBlockchain()
   ↓
4. Checks: Wallet connected? Contract initialized?
   ↓
5. Calls contract.registerProduct() via ethers.js
   ↓
6. MetaMask popup → User confirms transaction
   ↓
7. Transaction sent to blockchain
   ↓
8. Wait for confirmation (tx.wait())
   ↓
9. Transaction hash saved to PHP (save_transaction.php)
   ↓
10. PHP updates products.txt with transaction hash
    ↓
11. Page reloads, shows updated status
```

### Flow 3: Supplier/Consumer Purchases Product

```
1. Supplier/Consumer clicks "Approve" on product
   ↓
2. js/blockchain.js → purchaseProductOnBlockchain()
   ↓
3. Calls contract.transferProductWithPayment() with ETH value
   ↓
4. MetaMask popup → User confirms (includes payment)
   ↓
5. Transaction sent with ETH payment
   ↓
6. Transaction hash saved to PHP
   ↓
7. PHP updates products.txt (owner, status, tx_hash)
   ↓
8. Page reloads, product shows as purchased
```

### Flow 4: Network Verification

```
1. User connects wallet
   ↓
2. js/wallet.js → checkNetwork()
   ↓
3. Gets current chain ID from MetaMask
   ↓
4. Compares with Sepolia (0xaa36a7) or Local (0x7a69)
   ↓
5. If wrong network → Prompts to switch
   ↓
6. If network doesn't exist → Adds it to MetaMask
```

---

## Data Storage

### File-Based Storage (PHP)

**Files**:
- `products.txt` - Product data (ID, name, creator, price, status, owner, tx_hash)
- `users.txt` - User accounts (username, password, role)

**Format**: Pipe-delimited (`|`) text files

**Purpose**: 
- Local data cache
- Fallback when blockchain unavailable
- Quick access for dashboard display

### Blockchain Storage (Ethereum)

**Storage**: On-chain in smart contract

**Data**:
- Product details (name, description, price, producer)
- Transfer history (from, to, timestamp, location, status)
- Ownership records
- Transaction hashes

**Purpose**:
- Immutable product tracking
- Complete audit trail
- Decentralized verification

---

## Configuration Files

### Frontend Configuration

**`js/blockchain.js`**:
```javascript
let CONTRACT_ADDRESS = "0x5FbDB2315678afecb367f032d93F642f64180aa3";
const CONTRACT_ABI = [...]; // Contract function definitions
```

**`js/wallet.js`**:
- Network checking (Sepolia or local)
- Wallet connection management

### Backend Configuration

**`backend/.env`**:
```env
SEPOLIA_RPC_URL=https://rpc.sepolia.org
PRIVATE_KEY=0x...
CONTRACT_ADDRESS=0x...
CHAIN_ID=11155111
```

**`backend/hardhat.config.ts`**:
- Network configurations (Sepolia, localhost)
- Solidity compiler settings

---

## User Roles & Permissions

### Producer
- **Can**: Register new products, approve products for blockchain
- **Dashboard**: `dashboard_producer.php`
- **Blockchain**: Calls `registerProduct()` function

### Supplier
- **Can**: Purchase products from producers, transfer products
- **Dashboard**: `dashboard_supplier.php`
- **Blockchain**: Calls `transferProductWithPayment()` function

### Consumer
- **Can**: Purchase products from suppliers, view product history
- **Dashboard**: `dashboard_consumer.php`
- **Blockchain**: Calls `transferProductWithPayment()` and `getProductHistory()`

### Admin
- **Can**: View all products, manage users
- **Dashboard**: `dashboard_admin.php`

---

## Network Setup

### Option 1: Sepolia Testnet (Recommended)

**Advantages**:
- Free testnet ETH
- Viewable on Etherscan
- Realistic testing environment
- No cost for course projects

**Setup**:
1. Get Sepolia RPC URL (Infura/Alchemy or public)
2. Get free Sepolia ETH from faucet
3. Deploy contract: `npm run deploy:sepolia`
4. Update `CONTRACT_ADDRESS` in `js/blockchain.js`
5. Connect MetaMask to Sepolia network

### Option 2: Local Hardhat

**Advantages**:
- Instant transactions
- No external dependencies
- Pre-funded accounts
- Good for development

**Setup**:
1. Start local node: `npm run node`
2. Deploy contract: `npm run deploy:local`
3. Add local network to MetaMask (Chain ID: 31337)
4. Import test account to MetaMask

---

## Security Considerations

### Current Implementation
- **Private Keys**: Not used in current direct MetaMask flow
- **Wallet Address**: Stored in PHP session (non-sensitive)
- **Transactions**: Signed directly by MetaMask (user controls)

### MetaMask Security
- Users control their own keys
- Transactions require explicit approval
- Network verification prevents wrong network usage

### File Storage
- Plain text files (`products.txt`, `users.txt`)
- Suitable for development/testing
- Not encrypted (for production, use database)

---

## Key Technologies

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Frontend | PHP | Server-side rendering, authentication |
| Blockchain Client | ethers.js v5 | Smart contract interaction |
| Wallet | MetaMask | Transaction signing |
| Smart Contract | Solidity 0.8.24 | On-chain logic |
| Development | Hardhat | Contract compilation, deployment |
| Network | Sepolia Testnet | Free testing environment |

---

## File Structure

```
Project440/
├── PHP Frontend
│   ├── dashboard_producer.php      # Producer dashboard
│   ├── dashboard_supplier.php      # Supplier dashboard
│   ├── dashboard_consumer.php      # Consumer dashboard
│   ├── config.php                  # Core config & functions
│   ├── login.php                   # Authentication
│   └── partials/
│       ├── header.php              # Navigation + MetaMask buttons
│       └── footer.php              # Footer
│
├── JavaScript Client
│   ├── js/
│   │   ├── wallet.js               # MetaMask connection
│   │   └── blockchain.js           # Contract interaction
│
├── Backend (Optional)
│   └── backend/
│       ├── contracts/
│       │   └── SupplyChain.sol     # Smart contract
│       ├── scripts/
│       │   └── deploy.ts           # Deployment script
│       └── src/                    # Node.js API (optional)
│
├── Data Storage
│   ├── products.txt                # Product data (file-based)
│   └── users.txt                   # User accounts (file-based)
│
└── Configuration
    ├── js/blockchain.js            # Contract address & ABI
    └── backend/.env                 # Backend config (if used)
```

---

## Quick Start Guide

### 1. Setup Contract
```bash
cd backend
npm install
npm run compile
npm run deploy:sepolia  # or deploy:local
```

### 2. Configure Frontend
- Update `CONTRACT_ADDRESS` in `js/blockchain.js`
- Ensure MetaMask is installed

### 3. Get Testnet ETH (Sepolia)
- Visit https://sepoliafaucet.com
- Request testnet ETH for your wallet

### 4. Connect & Use
- Open PHP dashboard (e.g., `dashboard_producer.php`)
- Click "Connect Wallet"
- Approve MetaMask connection
- Use "Approve" buttons to interact with blockchain

---

## Troubleshooting

### MetaMask Security Warnings
- **Cause**: Contract address not recognized on network
- **Fix**: Deploy contract to correct network (Sepolia or local)

### "Contract not configured" Error
- **Cause**: `CONTRACT_ADDRESS` not set in `js/blockchain.js`
- **Fix**: Update with deployed contract address

### Network Mismatch
- **Cause**: MetaMask on wrong network
- **Fix**: Switch to Sepolia or local Hardhat network

### Transaction Failures
- **Cause**: Insufficient testnet ETH or wrong network
- **Fix**: Get Sepolia ETH from faucet, verify network

---

## Summary

This system provides a **complete blockchain supply chain tracking solution** using:
- **PHP** for user interfaces and data management
- **JavaScript/ethers.js** for direct blockchain interaction
- **MetaMask** for secure transaction signing
- **Ethereum (Sepolia)** for decentralized product tracking

The architecture is **modular** and **flexible**, allowing for easy extension and customization while maintaining security through MetaMask's transaction signing model.
