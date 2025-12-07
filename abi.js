/**
 * ============================================
 * ICS 440 - Blockchain Supply Chain (BSTS)
 * Contract ABI and Address Configuration
 * ============================================
 * 
 * IMPORTANT: Replace the values below with your actual deployed contract information!
 * 
 * HOW TO GET YOUR CONTRACT ABI:
 * 1. If you deployed using Remix:
 *    - Go to the "Compile" tab
 *    - Click on "ABI" button below your contract
 *    - Copy the entire JSON array
 *    - Replace CONTRACT_ABI below with that array
 * 
 * 2. If you deployed using Hardhat/Truffle:
 *    - Look in your build/artifacts folder
 *    - Find your contract JSON file
 *    - Copy the "abi" array from that file
 *    - Replace CONTRACT_ABI below
 * 
 * HOW TO GET YOUR CONTRACT ADDRESS:
 * 1. After deploying to Sepolia, copy the contract address
 * 2. Replace "0xYOUR_CONTRACT_ADDRESS_HERE_ON_SEPOLIA" below
 * 3. Make sure it's a valid Ethereum address (starts with 0x, 42 characters)
 * 
 * SEPOLIA TESTNET:
 * - Network Name: Sepolia
 * - Chain ID: 11155111
 * - Explorer: https://sepolia.etherscan.io
 */

// Replace this with your deployed contract address on Sepolia
const CONTRACT_ADDRESS = "0xYOUR_CONTRACT_ADDRESS_HERE_ON_SEPOLIA";

// Replace this with your contract's ABI (Application Binary Interface)
// This is a MINIMAL example ABI that matches the required functions
// You should replace it with the full ABI from your compiled contract
const CONTRACT_ABI = [
    // Function: registerProduct(uint256 productId, string memory name, string memory description, string memory batchId)
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "productId",
                "type": "uint256"
            },
            {
                "internalType": "string",
                "name": "name",
                "type": "string"
            },
            {
                "internalType": "string",
                "name": "description",
                "type": "string"
            },
            {
                "internalType": "string",
                "name": "batchId",
                "type": "string"
            }
        ],
        "name": "registerProduct",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    // Function: transferProduct(uint256 productId, address to, string memory newStatus)
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "productId",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "to",
                "type": "address"
            },
            {
                "internalType": "string",
                "name": "newStatus",
                "type": "string"
            }
        ],
        "name": "transferProduct",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    // Function: getProductHistory(uint256 productId) returns (tuple(address owner, string status, uint256 timestamp)[] memory)
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "productId",
                "type": "uint256"
            }
        ],
        "name": "getProductHistory",
        "outputs": [
            {
                "components": [
                    {
                        "internalType": "address",
                        "name": "owner",
                        "type": "address"
                    },
                    {
                        "internalType": "string",
                        "name": "status",
                        "type": "string"
                    },
                    {
                        "internalType": "uint256",
                        "name": "timestamp",
                        "type": "uint256"
                    }
                ],
                "internalType": "tuple[]",
                "name": "",
                "type": "tuple[]"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    // Optional: Event definitions (not required for basic functionality, but useful)
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "productId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "string",
                "name": "name",
                "type": "string"
            }
        ],
        "name": "ProductRegistered",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "productId",
                "type": "uint256"
            },
            {
                "indexed": true,
                "internalType": "address",
                "name": "from",
                "type": "address"
            },
            {
                "indexed": true,
                "internalType": "address",
                "name": "to",
                "type": "address"
            }
        ],
        "name": "ProductTransferred",
        "type": "event"
    }
];

