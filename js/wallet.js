/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * MetaMask Wallet Integration
 * ============================================
 */

let currentAccount = null;
let isConnected = false;

/**
 * Initialize wallet connection on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize button state first (show connect by default - NO auto-connect)
    updateWalletButton();
    setupEventListeners();
    
    // Listen for account changes (but don't auto-connect)
    if (window.ethereum) {
        window.ethereum.on('accountsChanged', handleAccountsChanged);
        window.ethereum.on('chainChanged', handleChainChanged);
    }
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Connect wallet button
    const connectBtn = document.querySelector('button[onclick="connectWallet()"]');
    if (connectBtn) {
        connectBtn.addEventListener('click', connectWallet);
    }
}

/**
 * Check if wallet is already connected (called only when user explicitly connects)
 * This function is NOT called on page load - user must click Connect button
 */
async function checkWalletConnection() {
    if (typeof window.ethereum === 'undefined') {
        updateWalletStatus('MetaMask not installed', false);
        updateWalletButton();
        return;
    }
    
    try {
        const accounts = await window.ethereum.request({ method: 'eth_accounts' });
        if (accounts.length > 0) {
            currentAccount = accounts[0];
            isConnected = true;
            updateWalletStatus(currentAccount, true);
            // Store in session
            await storeWalletAddress(currentAccount);
            // Initialize blockchain only after user connects
            if (typeof initBlockchain === 'function') {
                await initBlockchain();
            }
            updateWalletButton();
        } else {
            currentAccount = null;
            isConnected = false;
            updateWalletStatus('Not connected', false);
            updateWalletButton();
        }
    } catch (error) {
        console.error('Error checking wallet:', error);
        updateWalletStatus('Error', false);
        updateWalletButton();
    }
}

/**
 * Connect to MetaMask wallet (user-initiated only)
 */
async function connectWallet() {
    if (typeof window.ethereum === 'undefined') {
        alert('MetaMask is not installed. Please install MetaMask to continue.');
        window.open('https://metamask.io/', '_blank');
        return;
    }
    
    try {
        // Request account access
        const accounts = await window.ethereum.request({
            method: 'eth_requestAccounts'
        });
        
        if (accounts.length > 0) {
            currentAccount = accounts[0];
            isConnected = true;
            updateWalletStatus(currentAccount, true);
            
            // Store wallet address in session
            await storeWalletAddress(currentAccount);
            
            // Check network
            await checkNetwork();
            
            // Initialize blockchain after connection
            if (typeof initBlockchain === 'function') {
                await initBlockchain();
            }
            
            // Update button visibility
            updateWalletButton();
            
            // Trigger blockchain initialization event
            if (typeof window !== 'undefined' && window.dispatchEvent) {
                window.dispatchEvent(new Event('walletConnected'));
            }
            
            console.log('Wallet connected:', currentAccount);
        }
    } catch (error) {
        console.error('Error connecting wallet:', error);
        if (error.code === 4001) {
            alert('Please connect to MetaMask to continue.');
        } else {
            alert('Error connecting wallet: ' + error.message);
        }
        updateWalletStatus('Connection failed', false);
        updateWalletButton();
    }
}

/**
 * Handle account changes
 */
async function handleAccountsChanged(accounts) {
    if (accounts.length === 0) {
        currentAccount = null;
        isConnected = false;
        updateWalletStatus('Not connected', false);
    } else {
        currentAccount = accounts[0];
        updateWalletStatus(currentAccount, true);
        await storeWalletAddress(currentAccount);
    }
}

/**
 * Handle network changes
 */
function handleChainChanged(chainId) {
    // Reload page on network change
    window.location.reload();
}

/**
 * Check if connected to correct network (Sepolia or Local Hardhat)
 */
async function checkNetwork() {
    if (!window.ethereum) return;
    
    try {
        const chainId = await window.ethereum.request({ method: 'eth_chainId' });
        const sepoliaChainId = '0xaa36a7'; // Sepolia testnet (11155111)
        const localChainId = '0x7a69'; // Local Hardhat (31337)
        
        // Allow both Sepolia and Local Hardhat networks
        if (chainId !== sepoliaChainId && chainId !== localChainId) {
            // Try to switch to Sepolia first (for testnet)
            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{ chainId: sepoliaChainId }],
                });
            } catch (switchError) {
                // If chain doesn't exist, add Sepolia
                if (switchError.code === 4902) {
                    await window.ethereum.request({
                        method: 'wallet_addEthereumChain',
                        params: [{
                            chainId: sepoliaChainId,
                            chainName: 'Sepolia Test Network',
                            nativeCurrency: {
                                name: 'ETH',
                                symbol: 'ETH',
                                decimals: 18
                            },
                            rpcUrls: ['https://rpc.sepolia.org'],
                            blockExplorerUrls: ['https://sepolia.etherscan.io']
                        }],
                    });
                }
            }
        }
    } catch (error) {
        console.error('Error checking network:', error);
    }
}

/**
 * Update wallet status display
 */
function updateWalletStatus(address, connected) {
    const statusElement = document.getElementById('wallet-status');
    if (statusElement) {
        if (connected && address) {
            const shortAddress = address.substring(0, 6) + '...' + address.substring(address.length - 4);
            statusElement.textContent = shortAddress;
            statusElement.className = 'wallet-status connected';
        } else {
            statusElement.textContent = address || 'Not connected';
            statusElement.className = 'wallet-status';
        }
    }
    // Update button visibility
    setTimeout(updateWalletButton, 50);
}

/**
 * Store wallet address in session via PHP
 */
async function storeWalletAddress(address) {
    try {
        const response = await fetch('store_wallet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ address: address })
        });
        
        const result = await response.json();
        if (!result.success) {
            console.error('Error storing wallet address:', result.error);
        }
    } catch (error) {
        console.error('Error storing wallet address:', error);
    }
}

/**
 * Get current connected account
 */
function getCurrentAccount() {
    return currentAccount;
}

/**
 * Check if wallet is connected
 */
function isWalletConnected() {
    return isConnected && currentAccount !== null;
}

/**
 * Get wallet address (for use in forms)
 */
function getWalletAddress() {
    return currentAccount;
}

/**
 * Disconnect wallet
 */
async function disconnectWallet() {
    if (!confirm('Are you sure you want to disconnect your wallet?')) {
        return;
    }
    
    currentAccount = null;
    isConnected = false;
    updateWalletStatus('Not connected', false);
    
    // Clear from session
    try {
        const response = await fetch('store_wallet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ address: '' })
        });
    } catch (error) {
        console.error('Error clearing wallet address:', error);
    }
    
    // Update button visibility
    updateWalletButton();
    
    console.log('Wallet disconnected');
}

/**
 * Update wallet button based on connection status
 */
function updateWalletButton() {
    const connectBtn = document.getElementById('connect-wallet-btn');
    const disconnectBtn = document.getElementById('disconnect-wallet-btn');
    
    if (!connectBtn || !disconnectBtn) {
        // Buttons not loaded yet, try again later
        setTimeout(updateWalletButton, 100);
        return;
    }
    
    // Show Connect by default, Disconnect only when actually connected
    if (isConnected && currentAccount) {
        connectBtn.style.display = 'none';
        disconnectBtn.style.display = 'inline-block';
    } else {
        // Default state: show Connect, hide Disconnect
        connectBtn.style.display = 'inline-block';
        disconnectBtn.style.display = 'none';
        // Reset connection state if not connected
        isConnected = false;
        currentAccount = null;
    }
}
