/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Direct Blockchain Interaction via MetaMask
 * ============================================
 * 
 * This file handles all blockchain interactions using MetaMask and ethers.js
 * No private keys required - uses MetaMask for signing
 */

// Contract configuration
// Contract address (deployed contract)
let CONTRACT_ADDRESS = "0x5FbDB2315678afecb367f032d93F642f64180aa3";

const SEPOLIA_CHAIN_ID = '0xaa36a7'; // 11155111 in hex
const LOCAL_CHAIN_ID = '0x7a69'; // 31337 in hex

// Contract ABI - from backend/src/config/abi.ts
const CONTRACT_ABI = [
    // User Management
    {
        "inputs": [
            { "internalType": "uint8", "name": "_role", "type": "uint8" },
            { "internalType": "string", "name": "_name", "type": "string" }
        ],
        "name": "registerUser",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            { "internalType": "address", "name": "_userAddress", "type": "address" }
        ],
        "name": "getUser",
        "outputs": [
            { "internalType": "uint8", "name": "", "type": "uint8" },
            { "internalType": "string", "name": "", "type": "string" },
            { "internalType": "bool", "name": "", "type": "bool" }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    // Product Management
    {
        "inputs": [
            { "internalType": "string", "name": "_name", "type": "string" },
            { "internalType": "string", "name": "_description", "type": "string" },
            { "internalType": "uint256", "name": "_price", "type": "uint256" },
            { "internalType": "string", "name": "_location", "type": "string" }
        ],
        "name": "registerProduct",
        "outputs": [
            { "internalType": "bytes32", "name": "", "type": "bytes32" }
        ],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            { "internalType": "bytes32", "name": "_productId", "type": "bytes32" },
            { "internalType": "address", "name": "_to", "type": "address" },
            { "internalType": "string", "name": "_location", "type": "string" }
        ],
        "name": "transferProduct",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            { "internalType": "bytes32", "name": "_productId", "type": "bytes32" },
            { "internalType": "string", "name": "_location", "type": "string" }
        ],
        "name": "transferProductWithPayment",
        "outputs": [],
        "stateMutability": "payable",
        "type": "function"
    },
    {
        "inputs": [
            { "internalType": "bytes32", "name": "_productId", "type": "bytes32" },
            { "internalType": "string", "name": "_location", "type": "string" }
        ],
        "name": "confirmDelivery",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    // Query Functions
    {
        "inputs": [
            { "internalType": "bytes32", "name": "_productId", "type": "bytes32" }
        ],
        "name": "getProduct",
        "outputs": [
            { "internalType": "bytes32", "name": "productHash", "type": "bytes32" },
            { "internalType": "string", "name": "name", "type": "string" },
            { "internalType": "string", "name": "description", "type": "string" },
            { "internalType": "address", "name": "producer", "type": "address" },
            { "internalType": "address", "name": "currentOwner", "type": "address" },
            { "internalType": "uint256", "name": "price", "type": "uint256" },
            { "internalType": "uint256", "name": "createdAt", "type": "uint256" },
            { "internalType": "uint8", "name": "status", "type": "uint8" }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            { "internalType": "bytes32", "name": "_productId", "type": "bytes32" }
        ],
        "name": "getProductHistory",
        "outputs": [
            {
                "components": [
                    { "internalType": "address", "name": "from", "type": "address" },
                    { "internalType": "address", "name": "to", "type": "address" },
                    { "internalType": "uint256", "name": "timestamp", "type": "uint256" },
                    { "internalType": "uint8", "name": "status", "type": "uint8" },
                    { "internalType": "string", "name": "location", "type": "string" },
                    { "internalType": "bytes32", "name": "transactionHash", "type": "bytes32" }
                ],
                "internalType": "struct SupplyChain.TransferRecord[]",
                "name": "",
                "type": "tuple[]"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    // Events
    {
        "anonymous": false,
        "inputs": [
            { "indexed": true, "internalType": "bytes32", "name": "productId", "type": "bytes32" },
            { "indexed": false, "internalType": "string", "name": "name", "type": "string" },
            { "indexed": true, "internalType": "address", "name": "producer", "type": "address" },
            { "indexed": false, "internalType": "uint256", "name": "price", "type": "uint256" },
            { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" }
        ],
        "name": "ProductRegistered",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            { "indexed": true, "internalType": "bytes32", "name": "productId", "type": "bytes32" },
            { "indexed": true, "internalType": "address", "name": "from", "type": "address" },
            { "indexed": true, "internalType": "address", "name": "to", "type": "address" },
            { "indexed": false, "internalType": "uint8", "name": "status", "type": "uint8" },
            { "indexed": false, "internalType": "string", "name": "location", "type": "string" },
            { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" }
        ],
        "name": "ProductTransferred",
        "type": "event"
    }
];

// Global variables
let provider = null;
let signer = null;
let contract = null;

/**
 * Initialize blockchain connection
 */
async function initBlockchain() {
    // Check MetaMask
    if (typeof window.ethereum === 'undefined') {
        console.warn('MetaMask not installed');
        return false;
    }
    
    // Check ethers.js - may need to wait for CDN to load
    if (typeof ethers === 'undefined') {
        console.log('Waiting for ethers.js to load...');
        // Wait up to 3 seconds for ethers to load
        for (let i = 0; i < 30; i++) {
            await new Promise(resolve => setTimeout(resolve, 100));
            if (typeof ethers !== 'undefined') break;
        }
        if (typeof ethers === 'undefined') {
            console.warn('ethers.js not loaded from CDN');
            return false;
        }
    }
    
    try {
        provider = new ethers.providers.Web3Provider(window.ethereum);
        signer = provider.getSigner();
        
        // Check if contract address is set
        if (CONTRACT_ADDRESS && CONTRACT_ADDRESS.length === 42) {
            // Create contract instance (skip code check to avoid MetaMask warnings)
            contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
            console.log('Contract initialized:', CONTRACT_ADDRESS);
            return true;
        } else {
            console.warn('CONTRACT_ADDRESS not set in js/blockchain.js');
            return false;
        }
    } catch (error) {
        console.error('Error initializing blockchain:', error);
        return false;
    }
}

/**
 * Approve product (Producer) - Register on blockchain
 */
async function approveProductOnBlockchain(productId, productName, description, price) {
    if (!isWalletConnected()) {
        alert('Please connect your MetaMask wallet first!');
        return false;
    }
    
    if (!contract) {
        alert('Contract not configured. Please set CONTRACT_ADDRESS in js/blockchain.js');
        return false;
    }
    
    try {
        // Convert price to wei (assuming price is in ETH)
        const priceWei = ethers.utils.parseEther(price || '0');
        
        // Generate bytes32 productId from string/number
        // For now, we'll use keccak256 hash of the productId string
        const productIdBytes32 = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(productId.toString()));
        
        // Show confirmation
        if (!confirm(`Register product "${productName}" on blockchain?\n\nThis will open MetaMask for transaction confirmation.`)) {
            return false;
        }
        
        // Call contract function - this will open MetaMask
        // The function returns bytes32 productId
        const txResponse = await contract.registerProduct(
            productName,
            description || productName,
            priceWei,
            'Initial Location'
        );
        
        console.log('Transaction sent:', txResponse.hash);
        
        // Wait for transaction to be mined
        const receipt = await txResponse.wait();
        console.log('Transaction confirmed:', receipt);
        
        // Get the productId (bytes32) from the event
        let blockchainProductId = null;
        if (receipt.events && receipt.events.length > 0) {
            // Find ProductRegistered event
            for (let event of receipt.events) {
                if (event.event === 'ProductRegistered' && event.args) {
                    blockchainProductId = event.args.productId;
                    break;
                }
            }
        }
        
        // If no event found, generate from productId string
        if (!blockchainProductId) {
            blockchainProductId = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(productId.toString()));
        }
        
        return {
            success: true,
            transactionHash: txResponse.hash,
            productId: blockchainProductId,
            receipt: receipt
        };
    } catch (error) {
        console.error('Error approving product:', error);
        throw error;
    }
}

/**
 * Purchase product (Supplier/Consumer) - Transfer with payment
 */
async function purchaseProductOnBlockchain(productIdBytes32, price) {
    if (!isWalletConnected()) {
        alert('Please connect your MetaMask wallet first!');
        return false;
    }
    
    if (!contract) {
        const initialized = await initBlockchain();
        if (!initialized || !contract) {
            alert('Contract not configured.\n\nPlease:\n1. Deploy your contract to Sepolia\n2. Set CONTRACT_ADDRESS in js/blockchain.js\n\nSee CONTRACT_SETUP.md for details.');
            return false;
        }
    }
    
    // Verify contract is properly initialized
    if (!contract || !contract.transferProductWithPayment) {
        alert('Contract not properly initialized. Please check CONTRACT_ADDRESS in js/blockchain.js');
        return false;
    }
    
    try {
        // Convert price to wei
        const priceWei = ethers.utils.parseEther(price || '0');
        
        // Show confirmation
        if (!confirm(`Purchase product on blockchain?\n\nPrice: ${price} ETH\n\nThis will open MetaMask for transaction confirmation.`)) {
            return false;
        }
        
        // Call contract function with payment
        const tx = await contract.transferProductWithPayment(
            productIdBytes32,
            'Purchase Location',
            { value: priceWei }
        );
        
        console.log('Transaction sent:', tx.hash);
        
        // Wait for transaction
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        
        return {
            success: true,
            transactionHash: tx.hash,
            receipt: receipt
        };
    } catch (error) {
        console.error('Error purchasing product:', error);
        throw error;
    }
}

/**
 * Transfer product (Supplier) - Simple transfer
 */
async function transferProductOnBlockchain(productIdBytes32, toAddress, location) {
    if (!isWalletConnected()) {
        alert('Please connect your MetaMask wallet first!');
        return false;
    }
    
    if (!contract) {
        const initialized = await initBlockchain();
        if (!initialized || !contract) {
            alert('Contract not configured.\n\nPlease:\n1. Deploy your contract to Sepolia\n2. Set CONTRACT_ADDRESS in js/blockchain.js\n\nSee CONTRACT_SETUP.md for details.');
            return false;
        }
    }
    
    // Verify contract is properly initialized
    if (!contract || !contract.transferProduct) {
        alert('Contract not properly initialized. Please check CONTRACT_ADDRESS in js/blockchain.js');
        return false;
    }
    
    try {
        // Show confirmation
        if (!confirm(`Transfer product to ${toAddress}?\n\nThis will open MetaMask for transaction confirmation.`)) {
            return false;
        }
        
        // Call contract function
        const tx = await contract.transferProduct(
            productIdBytes32,
            toAddress,
            location || 'Transfer Location'
        );
        
        // Wait for transaction
        const receipt = await tx.wait();
        
        return {
            success: true,
            transactionHash: tx.hash,
            receipt: receipt
        };
    } catch (error) {
        console.error('Error transferring product:', error);
        throw error;
    }
}

// Initialize when wallet connects
if (typeof window !== 'undefined') {
    window.addEventListener('walletConnected', async () => {
        await initBlockchain();
    });
}
