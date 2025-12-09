// Supply Chain Contract ABI - Generated from SupplyChain.sol
// This is a simplified ABI containing the functions we need for the API

export const SUPPLY_CHAIN_ABI = [
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
  {
    "inputs": [
      { "internalType": "bytes32", "name": "_productId", "type": "bytes32" },
      { "internalType": "string", "name": "_name", "type": "string" },
      { "internalType": "string", "name": "_description", "type": "string" },
      { "internalType": "uint256", "name": "_price", "type": "uint256" },
      { "internalType": "address", "name": "_producer", "type": "address" }
    ],
    "name": "verifyProduct",
    "outputs": [
      { "internalType": "bool", "name": "", "type": "bool" }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      { "internalType": "address", "name": "_userAddress", "type": "address" }
    ],
    "name": "getUserProducts",
    "outputs": [
      { "internalType": "bytes32[]", "name": "", "type": "bytes32[]" }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getTotalProducts",
    "outputs": [
      { "internalType": "uint256", "name": "", "type": "uint256" }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getAllProductIds",
    "outputs": [
      { "internalType": "bytes32[]", "name": "", "type": "bytes32[]" }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      { "internalType": "bytes32", "name": "_productId", "type": "bytes32" }
    ],
    "name": "getProductHistoryLength",
    "outputs": [
      { "internalType": "uint256", "name": "", "type": "uint256" }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  // Events
  {
    "anonymous": false,
    "inputs": [
      { "indexed": true, "internalType": "address", "name": "userAddress", "type": "address" },
      { "indexed": false, "internalType": "uint8", "name": "role", "type": "uint8" },
      { "indexed": false, "internalType": "string", "name": "name", "type": "string" }
    ],
    "name": "UserRegistered",
    "type": "event"
  },
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
  },
  {
    "anonymous": false,
    "inputs": [
      { "indexed": true, "internalType": "address", "name": "from", "type": "address" },
      { "indexed": true, "internalType": "address", "name": "to", "type": "address" },
      { "indexed": false, "internalType": "uint256", "name": "amount", "type": "uint256" },
      { "indexed": true, "internalType": "bytes32", "name": "productId", "type": "bytes32" }
    ],
    "name": "PaymentTransferred",
    "type": "event"
  },
  {
    "anonymous": false,
    "inputs": [
      { "indexed": true, "internalType": "bytes32", "name": "productId", "type": "bytes32" },
      { "indexed": false, "internalType": "uint8", "name": "oldStatus", "type": "uint8" },
      { "indexed": false, "internalType": "uint8", "name": "newStatus", "type": "uint8" }
    ],
    "name": "ProductStatusUpdated",
    "type": "event"
  }
];

export default SUPPLY_CHAIN_ABI;
