# ICS 440 - Blockchain Supply Chain Tracking System (BSTS)
## Frontend Documentation

### Overview

This is a complete frontend application for a blockchain-based supply chain tracking system. The application allows three types of users (Producer, Supplier, and Consumer) to interact with a smart contract deployed on the Ethereum Sepolia testnet.

**What the frontend does:**
- Connects to MetaMask wallet
- Allows Producers to register new products on the blockchain
- Allows Suppliers to transfer products between parties and update status
- Allows Consumers to view the complete history and traceability of products

### Prerequisites

Before using this application, ensure you have:

1. **MetaMask Extension Installed**
   - Install MetaMask browser extension from [metamask.io](https://metamask.io)
   - Create or import a wallet account

2. **Sepolia Testnet Access**
   - Add Sepolia testnet to MetaMask (if not already added)
   - Network Name: Sepolia
   - RPC URL: https://sepolia.infura.io/v3/YOUR_INFURA_KEY (or use public RPC)
   - Chain ID: 11155111
   - Currency Symbol: ETH
   - Block Explorer: https://sepolia.etherscan.io

3. **Test ETH on Sepolia**
   - Get free Sepolia test ETH from a faucet:
     - [Sepolia Faucet](https://sepoliafaucet.com/)
     - [Alchemy Faucet](https://sepoliafaucet.com/)
   - You need test ETH to pay for gas fees when making transactions

4. **Deployed Smart Contract**
   - Your smart contract must be deployed on Sepolia testnet
   - You need the contract address and ABI (Application Binary Interface)

### Setup Steps

1. **Replace Contract Information**
   - Open `abi.js` file
   - Replace `CONTRACT_ADDRESS` with your deployed contract address:
     ```javascript
     const CONTRACT_ADDRESS = "0xYourActualContractAddressHere";
     ```
   - Replace `CONTRACT_ABI` with your contract's ABI:
     - If using Remix: Go to Compile tab → Click "ABI" button → Copy entire JSON array
     - If using Hardhat/Truffle: Copy the "abi" array from your contract JSON file
     - Paste it into `CONTRACT_ABI` in `abi.js`

2. **Open the Application**
   - Simply open `index.html` in a web browser (Chrome, Firefox, Edge, etc.)
   - Make sure MetaMask extension is installed and unlocked
   - No build tools or server required - it's a static HTML file!

3. **Connect MetaMask**
   - Click the "Connect MetaMask" button
   - Approve the connection in the MetaMask popup
   - Verify that your account address and network (Sepolia) are displayed

### How to Use

#### Producer Panel - Register a Product

1. Fill in the form:
   - **Product ID**: A unique number (e.g., 1001, 1002, etc.)
   - **Product Name**: Name of the product (e.g., "Organic Coffee Beans")
   - **Description**: Optional description of the product
   - **Batch ID**: Batch identifier (e.g., "BATCH-2024-001")

2. Click "Register Product"
3. Confirm the transaction in MetaMask
4. Wait for confirmation (10-30 seconds)
5. View the transaction hash link to see it on Etherscan

#### Supplier Panel - Transfer a Product

1. Fill in the form:
   - **Product ID**: The ID of the product to transfer
   - **Receiver Address**: Ethereum address of the receiver (must be valid address starting with 0x)
   - **New Status**: Status update (e.g., "Shipped", "In Warehouse", "Delivered to Store")

2. Click "Transfer Product"
3. Confirm the transaction in MetaMask
4. Wait for confirmation
5. View the transaction hash link

#### Consumer Panel - View Product History

1. Enter the **Product ID** you want to trace
2. Click "Get Product History"
3. View the history table showing:
   - Entry number
   - Owner address (who owned it at that point)
   - Status (what the status was)
   - Date/Time (when the change occurred)

### File Structure

```
Project440/
├── index.html          # Main HTML structure
├── styles.css          # All styling and layout
├── app.js              # Main JavaScript logic (wallet connection, contract calls)
├── abi.js              # Contract ABI and address configuration
└── README_FRONTEND.md  # This documentation file
```

### Understanding the Code

#### `index.html`
- Contains the HTML structure with three main panels
- Links to external CSS and JavaScript files
- Includes ethers.js library from CDN

#### `styles.css`
- Modern, clean styling
- Responsive design for mobile and desktop
- Color-coded message areas (success = green, error = red, info = blue)

#### `abi.js`
- Contains contract address and ABI
- **You must replace these with your actual contract information**

#### `app.js`
- Main application logic
- Heavily commented for educational purposes
- Key functions:
  - `connectWallet()`: Connects to MetaMask
  - `handleRegisterProduct()`: Registers a new product
  - `handleTransferProduct()`: Transfers a product
  - `handleGetHistory()`: Retrieves product history
  - Utility functions for address shortening and message display

### Common Issues and Solutions

**Problem: "MetaMask is not installed"**
- Solution: Install MetaMask browser extension

**Problem: "Wrong Network"**
- Solution: Switch MetaMask to Sepolia testnet (Chain ID: 11155111)

**Problem: "Transaction failed" or "Contract reverted"**
- Solution: Check if:
  - Product ID already exists (for registration)
  - Product exists (for transfer/history)
  - You have permission to perform the action
  - You have enough test ETH for gas fees

**Problem: "Invalid Ethereum address"**
- Solution: Ensure address starts with "0x" and is 42 characters long

**Problem: Contract functions not working**
- Solution: Verify that:
  - Contract address in `abi.js` is correct
  - ABI in `abi.js` matches your contract
  - Contract is deployed on Sepolia testnet

### Future Enhancements

The code is structured to allow easy additions:

1. **Adding More Product Fields**
   - Modify the Producer Panel form in `index.html`
   - Update `handleRegisterProduct()` in `app.js` to include new fields
   - Update the contract ABI if new fields are added to the contract

2. **QR Code Integration**
   - A placeholder area exists in the Consumer Panel
   - You can integrate a QR code scanner library (e.g., `html5-qrcode`)
   - Scan QR code to automatically fill in Product ID

3. **Event Listening**
   - Currently, the app doesn't listen to blockchain events
   - You can add event listeners in `app.js` to update UI in real-time:
     ```javascript
     contract.on("ProductRegistered", (productId, name) => {
         // Update UI when new product is registered
     });
     ```

4. **Additional Features**
   - Product search/filtering
   - Export history to CSV
   - Multiple product batch registration
   - User role management

### Technical Notes

- **ethers.js Version**: Using v5.7.2 from CDN
- **Network**: Ethereum Sepolia Testnet (Chain ID: 11155111)
- **No Build Tools**: Pure HTML/CSS/JavaScript - works by opening `index.html`
- **Browser Compatibility**: Works in all modern browsers with MetaMask support

### Support

For issues or questions:
1. Check browser console (F12) for error messages
2. Verify MetaMask connection and network
3. Ensure contract address and ABI are correct
4. Check that you have test ETH for gas fees

### License

This is a university course project for ICS 440 - Cryptography and Blockchain Applications.

