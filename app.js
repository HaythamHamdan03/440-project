/**
 * ============================================
 * ICS 440 - Blockchain Supply Chain (BSTS)
 * Main Application JavaScript
 * ============================================
 * 
 * This file contains all the logic for interacting with the blockchain
 * through MetaMask and ethers.js.
 * 
 * IMPORTANT CONCEPTS:
 * - Provider: Connects to the blockchain network (via MetaMask)
 * - Signer: Represents the user's wallet (can sign transactions)
 * - Contract: An instance of your smart contract that we can call functions on
 */

'use strict';

// ============================================
// GLOBAL VARIABLES
// ============================================
// These variables store our connection to the blockchain
let provider = null;        // Connection to Ethereum network
let signer = null;          // User's wallet (can sign transactions)
let contract = null;        // Instance of our smart contract
let currentAccount = null;  // Currently connected wallet address

// ============================================
// INITIALIZATION - Runs when page loads
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Initializing application...');
    
    // Check if MetaMask is installed
    if (typeof window.ethereum === 'undefined') {
        // MetaMask is not installed - show warning
        showMetaMaskWarning();
        return;
    }
    
    // MetaMask is installed - set up event listeners
    setupEventListeners();
    
    // Listen for account changes (user switches account in MetaMask)
    window.ethereum.on('accountsChanged', handleAccountsChanged);
    
    // Listen for network changes (user switches network in MetaMask)
    window.ethereum.on('chainChanged', handleChainChanged);
    
    console.log('Application initialized successfully.');
});

/**
 * Show warning message if MetaMask is not installed
 */
function showMetaMaskWarning() {
    const warningDiv = document.getElementById('metamaskWarning');
    if (warningDiv) {
        warningDiv.style.display = 'block';
    }
    console.error('MetaMask is not installed!');
}

/**
 * Set up all button click event listeners
 */
function setupEventListeners() {
    // Connect MetaMask button
    const connectBtn = document.getElementById('connectBtn');
    if (connectBtn) {
        connectBtn.addEventListener('click', connectWallet);
    }
    
    // Producer Panel - Register Product form
    const registerForm = document.getElementById('registerProductForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterProduct);
    }
    
    // Supplier Panel - Transfer Product form
    const transferForm = document.getElementById('transferProductForm');
    if (transferForm) {
        transferForm.addEventListener('submit', handleTransferProduct);
    }
    
    // Consumer Panel - Get History form
    const historyForm = document.getElementById('getHistoryForm');
    if (historyForm) {
        historyForm.addEventListener('submit', handleGetHistory);
    }
}

// ============================================
// WALLET CONNECTION
// ============================================

/**
 * Connect to MetaMask wallet
 * This function is called when user clicks "Connect MetaMask" button
 */
async function connectWallet() {
    try {
        console.log('Attempting to connect to MetaMask...');
        
        // Request access to user's accounts
        // This will show a MetaMask popup asking user to connect
        const accounts = await window.ethereum.request({
            method: 'eth_requestAccounts'
        });
        
        if (accounts.length === 0) {
            throw new Error('No accounts found. Please unlock MetaMask.');
        }
        
        // Get the first account (user's primary account)
        currentAccount = accounts[0];
        console.log('Connected account:', currentAccount);
        
        // Create a provider using ethers.js
        // Web3Provider wraps the MetaMask provider
        provider = new ethers.providers.Web3Provider(window.ethereum);
        
        // Get the signer (represents the user's wallet)
        signer = provider.getSigner();
        
        // Create a contract instance
        // This allows us to call functions on the smart contract
        contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
        
        // Update the UI to show connected account
        updateWalletUI();
        
        // Check if we're on the correct network (Sepolia)
        await checkNetwork();
        
        console.log('Wallet connected successfully!');
        
    } catch (error) {
        console.error('Error connecting wallet:', error);
        alert('Failed to connect wallet: ' + error.message);
    }
}

/**
 * Update the UI to show connected wallet information
 */
function updateWalletUI() {
    const walletInfo = document.getElementById('walletInfo');
    const accountAddress = document.getElementById('accountAddress');
    const networkName = document.getElementById('networkName');
    
    if (walletInfo && currentAccount) {
        walletInfo.style.display = 'block';
        
        if (accountAddress) {
            accountAddress.textContent = shortenAddress(currentAccount);
        }
        
        // Network name will be updated by checkNetwork()
    }
}

/**
 * Check if we're connected to Sepolia testnet
 * Sepolia Chain ID: 11155111
 */
async function checkNetwork() {
    if (!provider) return;
    
    try {
        // Get the current network
        const network = await provider.getNetwork();
        const networkNameEl = document.getElementById('networkName');
        
        // Sepolia Chain ID is 11155111
        if (network.chainId === 11155111) {
            if (networkNameEl) {
                networkNameEl.textContent = 'Sepolia Test Network ✓';
                networkNameEl.style.color = '#28a745';
            }
        } else {
            // Wrong network - show warning
            if (networkNameEl) {
                networkNameEl.textContent = 'Wrong Network! Please switch to Sepolia';
                networkNameEl.style.color = '#dc3545';
            }
            alert('Please switch MetaMask network to Sepolia Test Network (Chain ID: 11155111)');
        }
    } catch (error) {
        console.error('Error checking network:', error);
    }
}

/**
 * Handle when user switches accounts in MetaMask
 */
function handleAccountsChanged(accounts) {
    if (accounts.length === 0) {
        // User disconnected their wallet
        currentAccount = null;
        provider = null;
        signer = null;
        contract = null;
        
        // Hide wallet info
        const walletInfo = document.getElementById('walletInfo');
        if (walletInfo) {
            walletInfo.style.display = 'none';
        }
    } else {
        // User switched to a different account
        currentAccount = accounts[0];
        // Reconnect with new account
        connectWallet();
    }
}

/**
 * Handle when user switches networks in MetaMask
 * Reload the page to reset the connection
 */
function handleChainChanged(chainId) {
    // Reload the page when network changes
    window.location.reload();
}

// ============================================
// PRODUCER PANEL - Register Product
// ============================================

/**
 * Handle the "Register Product" form submission
 * This function is called when user clicks "Register Product" button
 */
async function handleRegisterProduct(event) {
    // Prevent form from submitting normally (which would reload the page)
    event.preventDefault();
    
    // Check if wallet is connected
    if (!contract || !signer) {
        setStatusMessage('producerMessage', 'error', 'Please connect MetaMask first!');
        return;
    }
    
    try {
        // Get values from the form inputs
        const productId = document.getElementById('productId').value;
        const productName = document.getElementById('productName').value;
        const productDescription = document.getElementById('productDescription').value;
        const batchId = document.getElementById('batchId').value;
        
        // Validate inputs
        if (!productId || isNaN(productId)) {
            setStatusMessage('producerMessage', 'error', 'Please enter a valid Product ID (number)');
            return;
        }
        
        if (!productName || productName.trim() === '') {
            setStatusMessage('producerMessage', 'error', 'Please enter a Product Name');
            return;
        }
        
        if (!batchId || batchId.trim() === '') {
            setStatusMessage('producerMessage', 'error', 'Please enter a Batch ID');
            return;
        }
        
        // Show "transaction pending" message
        setStatusMessage('producerMessage', 'info', 'Transaction sent... Waiting for confirmation...');
        
        // Call the smart contract function
        // This will show a MetaMask popup asking user to confirm the transaction
        const tx = await contract.registerProduct(
            productId,                    // uint256 productId
            productName,                 // string name
            productDescription || '',     // string description (empty if not provided)
            batchId                      // string batchId
        );
        
        console.log('Transaction sent:', tx.hash);
        
        // Wait for the transaction to be mined (confirmed on blockchain)
        // This can take 10-30 seconds
        setStatusMessage('producerMessage', 'info', 'Transaction confirmed! Waiting for block confirmation...');
        const receipt = await tx.wait();
        
        console.log('Transaction confirmed:', receipt);
        
        // Show success message with link to Etherscan
        const etherscanUrl = `https://sepolia.etherscan.io/tx/${tx.hash}`;
        setStatusMessage('producerMessage', 'success', 
            `Product registered successfully!<br>` +
            `Transaction Hash: <a href="${etherscanUrl}" target="_blank">${tx.hash}</a>`
        );
        
        // Clear the form
        document.getElementById('registerProductForm').reset();
        
    } catch (error) {
        console.error('Error registering product:', error);
        
        // Show user-friendly error message
        let errorMessage = 'Failed to register product: ';
        
        if (error.code === 4001) {
            errorMessage += 'Transaction rejected by user';
        } else if (error.message.includes('revert')) {
            errorMessage += 'Transaction failed. Check if product ID already exists or contract reverted.';
        } else {
            errorMessage += error.message;
        }
        
        setStatusMessage('producerMessage', 'error', errorMessage);
    }
}

// ============================================
// SUPPLIER PANEL - Transfer Product
// ============================================

/**
 * Handle the "Transfer Product" form submission
 * This function is called when user clicks "Transfer Product" button
 */
async function handleTransferProduct(event) {
    // Prevent form from submitting normally
    event.preventDefault();
    
    // Check if wallet is connected
    if (!contract || !signer) {
        setStatusMessage('supplierMessage', 'error', 'Please connect MetaMask first!');
        return;
    }
    
    try {
        // Get values from the form inputs
        const productId = document.getElementById('transferProductId').value;
        const receiverAddress = document.getElementById('receiverAddress').value.trim();
        const newStatus = document.getElementById('newStatus').value.trim();
        
        // Validate inputs
        if (!productId || isNaN(productId)) {
            setStatusMessage('supplierMessage', 'error', 'Please enter a valid Product ID (number)');
            return;
        }
        
        if (!receiverAddress || receiverAddress.length === 0) {
            setStatusMessage('supplierMessage', 'error', 'Please enter a Receiver Address');
            return;
        }
        
        // Check if address is valid Ethereum address
        if (!ethers.utils.isAddress(receiverAddress)) {
            setStatusMessage('supplierMessage', 'error', 'Invalid Ethereum address. Address must start with 0x and be 42 characters long.');
            return;
        }
        
        if (!newStatus || newStatus.length === 0) {
            setStatusMessage('supplierMessage', 'error', 'Please enter a New Status');
            return;
        }
        
        // Show "transaction pending" message
        setStatusMessage('supplierMessage', 'info', 'Transaction sent... Waiting for confirmation...');
        
        // Call the smart contract function
        const tx = await contract.transferProduct(
            productId,           // uint256 productId
            receiverAddress,     // address to
            newStatus           // string newStatus
        );
        
        console.log('Transaction sent:', tx.hash);
        
        // Wait for transaction to be mined
        setStatusMessage('supplierMessage', 'info', 'Transaction confirmed! Waiting for block confirmation...');
        const receipt = await tx.wait();
        
        console.log('Transaction confirmed:', receipt);
        
        // Show success message with link to Etherscan
        const etherscanUrl = `https://sepolia.etherscan.io/tx/${tx.hash}`;
        setStatusMessage('supplierMessage', 'success', 
            `Product transferred successfully!<br>` +
            `Transaction Hash: <a href="${etherscanUrl}" target="_blank">${tx.hash}</a>`
        );
        
        // Clear the form
        document.getElementById('transferProductForm').reset();
        
    } catch (error) {
        console.error('Error transferring product:', error);
        
        // Show user-friendly error message
        let errorMessage = 'Failed to transfer product: ';
        
        if (error.code === 4001) {
            errorMessage += 'Transaction rejected by user';
        } else if (error.message.includes('revert')) {
            errorMessage += 'Transaction failed. Check if product exists and you have permission to transfer it.';
        } else {
            errorMessage += error.message;
        }
        
        setStatusMessage('supplierMessage', 'error', errorMessage);
    }
}

// ============================================
// CONSUMER PANEL - Get Product History
// ============================================

/**
 * Handle the "Get Product History" form submission
 * This function is called when user clicks "Get Product History" button
 */
async function handleGetHistory(event) {
    // Prevent form from submitting normally
    event.preventDefault();
    
    // Check if wallet is connected
    if (!contract) {
        setStatusMessage('consumerMessage', 'error', 'Please connect MetaMask first!');
        return;
    }
    
    try {
        // Get product ID from form
        const productId = document.getElementById('historyProductId').value;
        
        // Validate input
        if (!productId || isNaN(productId)) {
            setStatusMessage('consumerMessage', 'error', 'Please enter a valid Product ID (number)');
            return;
        }
        
        // Show loading message
        setStatusMessage('consumerMessage', 'info', 'Fetching product history...');
        
        // Call the smart contract function
        // This is a "view" function, so it doesn't cost gas and doesn't require a transaction
        const history = await contract.getProductHistory(productId);
        
        console.log('Product history received:', history);
        
        // Check if history is empty
        if (!history || history.length === 0) {
            setStatusMessage('consumerMessage', 'info', 'No history found for this product ID.');
            document.getElementById('historyTableContainer').style.display = 'none';
            return;
        }
        
        // Display the history in the table
        displayHistory(history);
        
        // Show success message
        setStatusMessage('consumerMessage', 'success', `Found ${history.length} history entry/entries.`);
        
    } catch (error) {
        console.error('Error getting product history:', error);
        
        // Show user-friendly error message
        let errorMessage = 'Failed to get product history: ';
        
        if (error.message.includes('revert')) {
            errorMessage += 'Product not found or contract reverted.';
        } else {
            errorMessage += error.message;
        }
        
        setStatusMessage('consumerMessage', 'error', errorMessage);
        document.getElementById('historyTableContainer').style.display = 'none';
    }
}

/**
 * Display product history in the table
 * @param {Array} history - Array of history entries from the contract
 */
function displayHistory(history) {
    const tableBody = document.getElementById('historyTableBody');
    const tableContainer = document.getElementById('historyTableContainer');
    
    if (!tableBody || !tableContainer) return;
    
    // Clear previous table content
    tableBody.innerHTML = '';
    
    // Loop through each history entry and create a table row
    history.forEach((entry, index) => {
        // Create a new table row
        const row = document.createElement('tr');
        
        // Entry number (1, 2, 3, ...)
        const numCell = document.createElement('td');
        numCell.textContent = index + 1;
        
        // Owner address (shortened for display)
        const ownerCell = document.createElement('td');
        ownerCell.textContent = shortenAddress(entry.owner);
        ownerCell.style.fontFamily = 'monospace';
        
        // Status
        const statusCell = document.createElement('td');
        statusCell.textContent = entry.status;
        
        // Date/Time - Convert timestamp to readable date
        // Timestamp from blockchain is in seconds, JavaScript Date needs milliseconds
        const timestamp = entry.timestamp;
        let dateTime;
        
        // Handle both BigNumber (ethers v5) and regular numbers
        if (timestamp.toNumber) {
            dateTime = new Date(timestamp.toNumber() * 1000).toLocaleString();
        } else {
            dateTime = new Date(timestamp * 1000).toLocaleString();
        }
        
        const dateCell = document.createElement('td');
        dateCell.textContent = dateTime;
        
        // Add all cells to the row
        row.appendChild(numCell);
        row.appendChild(ownerCell);
        row.appendChild(statusCell);
        row.appendChild(dateCell);
        
        // Add row to table body
        tableBody.appendChild(row);
    });
    
    // Show the table
    tableContainer.style.display = 'block';
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Shorten an Ethereum address for display
 * Example: "0x1234567890123456789012345678901234567890" -> "0x1234...7890"
 * @param {string} address - Full Ethereum address
 * @returns {string} Shortened address
 */
function shortenAddress(address) {
    if (!address) return '-';
    if (address.length < 10) return address;
    return address.substring(0, 6) + '...' + address.substring(address.length - 4);
}

/**
 * Display a status message in a panel
 * @param {string} panelId - ID of the message area element (e.g., 'producerMessage')
 * @param {string} type - Type of message: 'success', 'error', or 'info'
 * @param {string} message - Message text to display (can include HTML)
 */
function setStatusMessage(panelId, type, message) {
    const messageArea = document.getElementById(panelId);
    if (!messageArea) return;
    
    // Remove previous message classes
    messageArea.classList.remove('success', 'error', 'info');
    
    // Add new message class
    messageArea.classList.add(type);
    
    // Set the message content
    messageArea.innerHTML = message;
    
    // Scroll to the message area so user can see it
    messageArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

