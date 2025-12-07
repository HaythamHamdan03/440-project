/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Web3 Contract Interaction Logic
 * ============================================
 * 
 * This file handles all blockchain interactions using ethers.js
 * 
 * IMPORTANT: Replace CONTRACT_ADDRESS and CONTRACT_ABI below with your
 * actual deployed contract information!
 * 
 * HOW TO GET YOUR CONTRACT ABI:
 * 1. If using Remix: Go to Compile tab → Click "ABI" button → Copy entire JSON
 * 2. If using Hardhat/Truffle: Copy "abi" array from contract JSON file
 * 
 * HOW TO GET YOUR CONTRACT ADDRESS:
 * 1. After deploying to Sepolia, copy the contract address
 * 2. Replace "0xYOUR_CONTRACT_ADDRESS_HERE" below
 * 3. Must be a valid Ethereum address (42 characters, starts with 0x)
 */

'use strict';

// ============================================
// CONTRACT CONFIGURATION
// ============================================
// REPLACE THESE WITH YOUR ACTUAL CONTRACT INFORMATION!

const CONTRACT_ADDRESS = "0xYOUR_CONTRACT_ADDRESS_HERE_ON_SEPOLIA";

// Minimal ABI matching the required functions
// REPLACE THIS WITH YOUR FULL CONTRACT ABI!
const CONTRACT_ABI = [
    // Function: registerProduct(uint256 productId, string memory name, string memory description, string memory batchId)
    {
        "inputs": [
            { "internalType": "uint256", "name": "productId", "type": "uint256" },
            { "internalType": "string", "name": "name", "type": "string" },
            { "internalType": "string", "name": "description", "type": "string" },
            { "internalType": "string", "name": "batchId", "type": "string" }
        ],
        "name": "registerProduct",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    // Function: transferProduct(uint256 productId, address to, string memory newStatus)
    {
        "inputs": [
            { "internalType": "uint256", "name": "productId", "type": "uint256" },
            { "internalType": "address", "name": "to", "type": "address" },
            { "internalType": "string", "name": "newStatus", "type": "string" }
        ],
        "name": "transferProduct",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    // Function: getProductHistory(uint256 productId) returns (tuple[] memory)
    {
        "inputs": [
            { "internalType": "uint256", "name": "productId", "type": "uint256" }
        ],
        "name": "getProductHistory",
        "outputs": [
            {
                "components": [
                    { "internalType": "address", "name": "owner", "type": "address" },
                    { "internalType": "string", "name": "status", "type": "string" },
                    { "internalType": "uint256", "name": "timestamp", "type": "uint256" }
                ],
                "internalType": "tuple[]",
                "name": "",
                "type": "tuple[]"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    // Optional: verifyProduct function (if available in contract)
    {
        "inputs": [
            { "internalType": "uint256", "name": "productId", "type": "uint256" }
        ],
        "name": "verifyProduct",
        "outputs": [
            { "internalType": "bool", "name": "", "type": "bool" }
        ],
        "stateMutability": "view",
        "type": "function"
    }
];

// ============================================
// GLOBAL VARIABLES
// ============================================
let provider = null;        // Connection to Ethereum network
let signer = null;          // User's wallet (can sign transactions)
let contract = null;        // Instance of smart contract
let currentAccount = null;  // Currently connected wallet address

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize Web3 connection when page loads
 * This function is called automatically
 */
async function initWeb3() {
    // Check if MetaMask is installed
    if (typeof window.ethereum === 'undefined') {
        showMetaMaskWarning();
        return false;
    }
    
    try {
        // Create provider using MetaMask
        provider = new ethers.providers.Web3Provider(window.ethereum);
        
        // Get signer (user's wallet)
        signer = provider.getSigner();
        
        // Create contract instance
        contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
        
        // Get current account
        const accounts = await provider.listAccounts();
        if (accounts.length > 0) {
            currentAccount = accounts[0];
            updateWalletStatus();
        }
        
        // Check network
        await checkNetwork();
        
        // Listen for account changes
        window.ethereum.on('accountsChanged', handleAccountsChanged);
        
        // Listen for network changes
        window.ethereum.on('chainChanged', handleChainChanged);
        
        return true;
    } catch (error) {
        console.error('Error initializing Web3:', error);
        showMessage('error', 'Failed to initialize Web3 connection');
        return false;
    }
}

/**
 * Show warning if MetaMask is not installed
 */
function showMetaMaskWarning() {
    const statusEl = document.getElementById('metamask-status');
    if (statusEl) {
        statusEl.innerHTML = '<span style="color: #EF4444;">⚠️ MetaMask not installed</span>';
    }
    console.error('MetaMask is not installed!');
}

/**
 * Connect wallet explicitly (called when user clicks "Connect Wallet")
 */
async function connectWallet() {
    if (typeof window.ethereum === 'undefined') {
        alert('MetaMask is not installed. Please install MetaMask extension.');
        return;
    }
    
    try {
        // Request account access
        await window.ethereum.request({ method: 'eth_requestAccounts' });
        
        // Initialize Web3 with new account
        await initWeb3();
        
        showMessage('success', 'Wallet connected successfully!');
    } catch (error) {
        console.error('Error connecting wallet:', error);
        if (error.code === 4001) {
            showMessage('error', 'Connection rejected by user');
        } else {
            showMessage('error', 'Failed to connect wallet: ' + error.message);
        }
    }
}

/**
 * Update wallet status in the header
 */
async function updateWalletStatus() {
    const statusEl = document.getElementById('wallet-status');
    if (!statusEl) return;
    
    if (!currentAccount) {
        statusEl.textContent = 'Not connected';
        statusEl.className = 'wallet-status';
        return;
    }
    
    try {
        const network = await provider.getNetwork();
        const networkName = network.chainId === 11155111 ? 'Sepolia' : `Chain ${network.chainId}`;
        
        statusEl.textContent = `${shortenAddress(currentAccount)} | ${networkName}`;
        statusEl.className = 'wallet-status connected';
    } catch (error) {
        statusEl.textContent = 'Error getting network';
        statusEl.className = 'wallet-status';
    }
}

/**
 * Check if we're on Sepolia network
 */
async function checkNetwork() {
    if (!provider) return;
    
    try {
        const network = await provider.getNetwork();
        if (network.chainId !== 11155111) {
            showMessage('warning', 'Please switch to Sepolia Test Network (Chain ID: 11155111)');
        }
    } catch (error) {
        console.error('Error checking network:', error);
    }
}

/**
 * Handle account changes in MetaMask
 */
function handleAccountsChanged(accounts) {
    if (accounts.length === 0) {
        // User disconnected
        currentAccount = null;
        provider = null;
        signer = null;
        contract = null;
        updateWalletStatus();
    } else {
        // User switched account
        currentAccount = accounts[0];
        initWeb3();
    }
}

/**
 * Handle network changes in MetaMask
 */
function handleChainChanged(chainId) {
    // Reload page when network changes
    window.location.reload();
}

// ============================================
// PRODUCER FUNCTIONS
// ============================================

/**
 * Register a product on the blockchain
 * Called from Producer dashboard form
 * 
 * @param {HTMLElement} formElement The form element containing product data
 */
async function registerProductFromForm(formElement) {
    // Check if wallet is connected
    if (!contract || !signer) {
        showMessage('error', 'Please connect MetaMask wallet first!');
        return;
    }
    
    try {
        // Get form values
        const productId = formElement.querySelector('[name="productId"]').value;
        const name = formElement.querySelector('[name="productName"]').value;
        const description = formElement.querySelector('[name="productDescription"]').value || '';
        const batchId = formElement.querySelector('[name="batchId"]').value;
        
        // Validate inputs
        if (!productId || isNaN(productId)) {
            showMessage('error', 'Please enter a valid Product ID (number)');
            return;
        }
        
        if (!name || name.trim() === '') {
            showMessage('error', 'Please enter a Product Name');
            return;
        }
        
        if (!batchId || batchId.trim() === '') {
            showMessage('error', 'Please enter a Batch ID');
            return;
        }
        
        // Show loading message
        showMessage('info', 'Transaction sent... Waiting for confirmation...');
        
        // Call smart contract function
        const tx = await contract.registerProduct(
            productId,
            name,
            description,
            batchId
        );
        
        console.log('Transaction sent:', tx.hash);
        
        // Wait for transaction to be mined
        showMessage('info', 'Transaction confirmed! Waiting for block confirmation...');
        const receipt = await tx.wait();
        
        console.log('Transaction confirmed:', receipt);
        
        // Show success message
        const etherscanUrl = `https://sepolia.etherscan.io/tx/${tx.hash}`;
        showMessage('success', 
            `Product registered successfully!<br>` +
            `Transaction: <a href="${etherscanUrl}" target="_blank" style="color: #00FF88;">${tx.hash}</a>`
        );
        
        // Submit form to PHP to save to products.txt
        // We'll use a hidden form submission
        submitProductToPHP(productId, name, batchId);
        
    } catch (error) {
        console.error('Error registering product:', error);
        
        let errorMessage = 'Failed to register product: ';
        if (error.code === 4001) {
            errorMessage = 'Transaction rejected by user';
        } else if (error.message.includes('revert')) {
            errorMessage = 'Transaction failed. Product ID may already exist.';
        } else {
            errorMessage += error.message;
        }
        
        showMessage('error', errorMessage);
    }
}

/**
 * Submit product to PHP backend (for products.txt)
 */
function submitProductToPHP(productId, name, batchId) {
    // Create a form and submit it to save product to products.txt
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    
    form.appendChild(createHiddenInput('action', 'save_product'));
    form.appendChild(createHiddenInput('productId', productId));
    form.appendChild(createHiddenInput('productName', name));
    form.appendChild(createHiddenInput('batchId', batchId));
    
    document.body.appendChild(form);
    form.submit();
}

function createHiddenInput(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
}

// ============================================
// SUPPLIER FUNCTIONS
// ============================================

/**
 * Transfer a product on the blockchain
 * Called from Supplier dashboard form
 * 
 * @param {HTMLElement} formElement The form element containing transfer data
 */
async function transferProductFromForm(formElement) {
    // Check if wallet is connected
    if (!contract || !signer) {
        showMessage('error', 'Please connect MetaMask wallet first!');
        return;
    }
    
    try {
        // Get form values
        const productId = formElement.querySelector('[name="productId"]').value;
        const toAddress = formElement.querySelector('[name="receiverAddress"]').value.trim();
        const newStatus = formElement.querySelector('[name="newStatus"]').value.trim();
        
        // Validate inputs
        if (!productId || isNaN(productId)) {
            showMessage('error', 'Please enter a valid Product ID (number)');
            return;
        }
        
        if (!toAddress || !ethers.utils.isAddress(toAddress)) {
            showMessage('error', 'Please enter a valid Ethereum address');
            return;
        }
        
        if (!newStatus || newStatus.length === 0) {
            showMessage('error', 'Please enter a New Status');
            return;
        }
        
        // Show loading message
        showMessage('info', 'Transaction sent... Waiting for confirmation...');
        
        // Call smart contract function
        const tx = await contract.transferProduct(
            productId,
            toAddress,
            newStatus
        );
        
        console.log('Transaction sent:', tx.hash);
        
        // Wait for transaction to be mined
        showMessage('info', 'Transaction confirmed! Waiting for block confirmation...');
        const receipt = await tx.wait();
        
        console.log('Transaction confirmed:', receipt);
        
        // Show success message
        const etherscanUrl = `https://sepolia.etherscan.io/tx/${tx.hash}`;
        showMessage('success', 
            `Product transferred successfully!<br>` +
            `Transaction: <a href="${etherscanUrl}" target="_blank" style="color: #00FF88;">${tx.hash}</a>`
        );
        
    } catch (error) {
        console.error('Error transferring product:', error);
        
        let errorMessage = 'Failed to transfer product: ';
        if (error.code === 4001) {
            errorMessage = 'Transaction rejected by user';
        } else if (error.message.includes('revert')) {
            errorMessage = 'Transaction failed. Check if product exists and you have permission.';
        } else {
            errorMessage += error.message;
        }
        
        showMessage('error', errorMessage);
    }
}

// ============================================
// CONSUMER FUNCTIONS
// ============================================

/**
 * Load and display product history
 * Called from Consumer dashboard
 * 
 * @param {string} productId Product ID to look up
 * @param {string} targetTableElementId ID of the table element to populate
 */
async function loadHistoryForProduct(productId, targetTableElementId) {
    // Check if contract is available (view functions don't need signer)
    if (!contract) {
        // Try to initialize if not already done
        if (typeof window.ethereum !== 'undefined') {
            provider = new ethers.providers.Web3Provider(window.ethereum);
            contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, provider);
        } else {
            showMessage('error', 'MetaMask is required to view product history');
            return;
        }
    }
    
    try {
        // Validate input
        if (!productId || isNaN(productId)) {
            showMessage('error', 'Please enter a valid Product ID');
            return;
        }
        
        // Show loading
        showMessage('info', 'Loading product history...');
        
        // Call smart contract view function
        const history = await contract.getProductHistory(productId);
        
        console.log('Product history:', history);
        
        // Check if history is empty
        if (!history || history.length === 0) {
            showMessage('info', 'No history found for this product ID');
            const tableEl = document.getElementById(targetTableElementId);
            if (tableEl) {
                tableEl.innerHTML = '<tr><td colspan="4" class="text-center">No history found</td></tr>';
            }
            return;
        }
        
        // Display history in table
        displayHistoryTable(history, targetTableElementId);
        
        showMessage('success', `Found ${history.length} history entry/entries`);
        
    } catch (error) {
        console.error('Error loading product history:', error);
        
        let errorMessage = 'Failed to load product history: ';
        if (error.message.includes('revert')) {
            errorMessage = 'Product not found or contract reverted';
        } else {
            errorMessage += error.message;
        }
        
        showMessage('error', errorMessage);
    }
}

/**
 * Display product history in a table
 * 
 * @param {Array} history Array of history entries from contract
 * @param {string} tableElementId ID of table tbody element
 */
function displayHistoryTable(history, tableElementId) {
    const tbody = document.getElementById(tableElementId);
    if (!tbody) return;
    
    // Clear previous content
    tbody.innerHTML = '';
    
    // Loop through history entries
    history.forEach((entry, index) => {
        const row = document.createElement('tr');
        
        // Entry number
        const numCell = document.createElement('td');
        numCell.textContent = index + 1;
        
        // Owner address (shortened)
        const ownerCell = document.createElement('td');
        ownerCell.textContent = shortenAddress(entry.owner);
        ownerCell.className = 'monospace';
        
        // Status
        const statusCell = document.createElement('td');
        statusCell.textContent = entry.status;
        
        // Date/Time (convert timestamp)
        const timestamp = entry.timestamp;
        let dateTime;
        if (timestamp.toNumber) {
            dateTime = new Date(timestamp.toNumber() * 1000).toLocaleString();
        } else {
            dateTime = new Date(timestamp * 1000).toLocaleString();
        }
        
        const dateCell = document.createElement('td');
        dateCell.textContent = dateTime;
        
        // Add cells to row
        row.appendChild(numCell);
        row.appendChild(ownerCell);
        row.appendChild(statusCell);
        row.appendChild(dateCell);
        
        // Add row to table
        tbody.appendChild(row);
    });
}

/**
 * Verify product authenticity (optional function)
 * 
 * @param {string} productId Product ID to verify
 * @returns {Promise<boolean|null>} True if verified, false if not, null if function not available
 */
async function verifyProductOnChain(productId) {
    if (!contract) {
        return null;
    }
    
    try {
        // Check if verifyProduct function exists in ABI
        if (contract.verifyProduct) {
            const result = await contract.verifyProduct(productId);
            return result;
        }
        return null;
    } catch (error) {
        console.error('Error verifying product:', error);
        return null;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Shorten an Ethereum address for display
 * Example: "0x1234...ABCD"
 * 
 * @param {string} address Full Ethereum address
 * @returns {string} Shortened address
 */
function shortenAddress(address) {
    if (!address) return '-';
    if (address.length < 10) return address;
    return address.substring(0, 6) + '...' + address.substring(address.length - 4);
}

/**
 * Show a message to the user
 * 
 * @param {string} type Message type: 'success', 'error', 'info', 'warning'
 * @param {string} message Message text (can include HTML)
 */
function showMessage(type, message) {
    // Try to find a message container on the page
    let messageEl = document.getElementById('status-message');
    
    if (!messageEl) {
        // Create message element if it doesn't exist
        messageEl = document.createElement('div');
        messageEl.id = 'status-message';
        messageEl.className = 'message';
        
        // Insert at top of main content or body
        const mainContent = document.querySelector('.main-content') || document.body;
        mainContent.insertBefore(messageEl, mainContent.firstChild);
    }
    
    // Set message class and content
    messageEl.className = `message message-${type}`;
    messageEl.innerHTML = message;
    
    // Auto-hide after 10 seconds for success/info messages
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            if (messageEl) {
                messageEl.style.display = 'none';
            }
        }, 10000);
    } else {
        messageEl.style.display = 'block';
    }
    
    // Scroll to message
    messageEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ============================================
// AUTO-INITIALIZE ON PAGE LOAD
// ============================================

// Initialize Web3 when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWeb3);
} else {
    initWeb3();
}

