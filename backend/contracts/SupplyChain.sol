// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

/**
 * @title SupplyChain
 * @dev A blockchain-based supply chain tracking system
 * @notice This contract allows producers to register products and track them through the supply chain
 */
contract SupplyChain {
    
    // ============ Enums ============
    
    enum Role { None, Producer, Supplier, Consumer }
    enum ProductStatus { Created, InTransit, WithSupplier, Sold, Delivered }
    
    // ============ Structs ============
    
    struct Product {
        bytes32 productHash;        // Hash of product details for privacy
        string name;
        string description;
        address producer;
        address currentOwner;
        uint256 price;              // Price in wei
        uint256 createdAt;
        ProductStatus status;
        bool exists;
    }
    
    struct TransferRecord {
        address from;
        address to;
        uint256 timestamp;
        ProductStatus status;
        string location;
        bytes32 transactionHash;
    }
    
    struct User {
        Role role;
        string name;
        bool isRegistered;
    }
    
    // ============ State Variables ============
    
    address public owner;
    uint256 public productCount;
    
    // Mappings
    mapping(bytes32 => Product) public products;
    mapping(bytes32 => TransferRecord[]) public productHistory;
    mapping(address => User) public users;
    mapping(address => bytes32[]) public userProducts;
    
    // Array to track all product IDs
    bytes32[] public allProductIds;
    
    // ============ Events ============
    
    event UserRegistered(address indexed userAddress, Role role, string name);
    event ProductRegistered(
        bytes32 indexed productId,
        string name,
        address indexed producer,
        uint256 price,
        uint256 timestamp
    );
    event ProductTransferred(
        bytes32 indexed productId,
        address indexed from,
        address indexed to,
        ProductStatus status,
        string location,
        uint256 timestamp
    );
    event PaymentTransferred(
        address indexed from,
        address indexed to,
        uint256 amount,
        bytes32 indexed productId
    );
    event ProductStatusUpdated(
        bytes32 indexed productId,
        ProductStatus oldStatus,
        ProductStatus newStatus
    );
    
    // ============ Modifiers ============
    
    modifier onlyOwner() {
        require(msg.sender == owner, "Only contract owner can call this");
        _;
    }
    
    modifier onlyRegistered() {
        require(users[msg.sender].isRegistered, "User not registered");
        _;
    }
    
    modifier onlyProducer() {
        require(users[msg.sender].role == Role.Producer, "Only producers can call this");
        _;
    }
    
    modifier onlySupplierOrConsumer() {
        require(
            users[msg.sender].role == Role.Supplier || users[msg.sender].role == Role.Consumer,
            "Only suppliers or consumers can call this"
        );
        _;
    }
    
    modifier productExists(bytes32 _productId) {
        require(products[_productId].exists, "Product does not exist");
        _;
    }
    
    modifier isProductOwner(bytes32 _productId) {
        require(products[_productId].currentOwner == msg.sender, "Not the product owner");
        _;
    }
    
    // ============ Constructor ============
    
    constructor() {
        owner = msg.sender;
        productCount = 0;
    }
    
    // ============ User Management Functions ============
    
    /**
     * @dev Register a new user with a role
     * @param _role The role of the user (1=Producer, 2=Supplier, 3=Consumer)
     * @param _name The name of the user/company
     */
    function registerUser(Role _role, string memory _name) external {
        require(!users[msg.sender].isRegistered, "User already registered");
        require(_role != Role.None, "Invalid role");
        require(bytes(_name).length > 0, "Name cannot be empty");
        
        users[msg.sender] = User({
            role: _role,
            name: _name,
            isRegistered: true
        });
        
        emit UserRegistered(msg.sender, _role, _name);
    }
    
    /**
     * @dev Get user information
     * @param _userAddress The address of the user
     */
    function getUser(address _userAddress) external view returns (Role, string memory, bool) {
        User memory user = users[_userAddress];
        return (user.role, user.name, user.isRegistered);
    }
    
    // ============ Product Management Functions ============
    
    /**
     * @dev Register a new product (only producers)
     * @param _name Product name
     * @param _description Product description
     * @param _price Product price in wei
     * @param _location Initial location
     * @return productId The unique identifier for the product
     */
    function registerProduct(
        string memory _name,
        string memory _description,
        uint256 _price,
        string memory _location
    ) external onlyRegistered onlyProducer returns (bytes32) {
        require(bytes(_name).length > 0, "Name cannot be empty");
        
        // Generate unique product ID using hash
        bytes32 productId = keccak256(
            abi.encodePacked(
                msg.sender,
                _name,
                block.timestamp,
                productCount
            )
        );
        
        // Create product hash for data integrity
        bytes32 productHash = keccak256(
            abi.encodePacked(_name, _description, _price, msg.sender)
        );
        
        products[productId] = Product({
            productHash: productHash,
            name: _name,
            description: _description,
            producer: msg.sender,
            currentOwner: msg.sender,
            price: _price,
            createdAt: block.timestamp,
            status: ProductStatus.Created,
            exists: true
        });
        
        // Record initial history
        bytes32 txHash = keccak256(
            abi.encodePacked(productId, msg.sender, block.timestamp)
        );
        
        productHistory[productId].push(TransferRecord({
            from: address(0),
            to: msg.sender,
            timestamp: block.timestamp,
            status: ProductStatus.Created,
            location: _location,
            transactionHash: txHash
        }));
        
        userProducts[msg.sender].push(productId);
        allProductIds.push(productId);
        productCount++;
        
        emit ProductRegistered(productId, _name, msg.sender, _price, block.timestamp);
        
        return productId;
    }
    
    /**
     * @dev Transfer product to another user
     * @param _productId The product identifier
     * @param _to The recipient address
     * @param _location Current location during transfer
     */
    function transferProduct(
        bytes32 _productId,
        address _to,
        string memory _location
    ) external onlyRegistered productExists(_productId) isProductOwner(_productId) {
        require(_to != address(0), "Invalid recipient address");
        require(_to != msg.sender, "Cannot transfer to yourself");
        require(users[_to].isRegistered, "Recipient not registered");
        
        Product storage product = products[_productId];
        
        // Determine new status based on recipient's role
        ProductStatus newStatus;
        Role recipientRole = users[_to].role;
        
        if (recipientRole == Role.Supplier) {
            newStatus = ProductStatus.WithSupplier;
        } else if (recipientRole == Role.Consumer) {
            newStatus = ProductStatus.Sold;
        } else {
            newStatus = ProductStatus.InTransit;
        }
        
        ProductStatus oldStatus = product.status;
        product.currentOwner = _to;
        product.status = newStatus;
        
        // Record transfer in history
        bytes32 txHash = keccak256(
            abi.encodePacked(_productId, msg.sender, _to, block.timestamp)
        );
        
        productHistory[_productId].push(TransferRecord({
            from: msg.sender,
            to: _to,
            timestamp: block.timestamp,
            status: newStatus,
            location: _location,
            transactionHash: txHash
        }));
        
        userProducts[_to].push(_productId);
        
        emit ProductTransferred(_productId, msg.sender, _to, newStatus, _location, block.timestamp);
        emit ProductStatusUpdated(_productId, oldStatus, newStatus);
    }
    
    /**
     * @dev Transfer product with payment (bonus feature)
     * @param _productId The product identifier
     * @param _location Current location during transfer
     */
    function transferProductWithPayment(
        bytes32 _productId,
        string memory _location
    ) external payable onlyRegistered productExists(_productId) {
        Product storage product = products[_productId];
        require(msg.value >= product.price, "Insufficient payment");
        require(msg.sender != product.currentOwner, "Cannot buy your own product");
        
        address previousOwner = product.currentOwner;
        
        // Transfer payment to current owner
        (bool sent, ) = payable(previousOwner).call{value: msg.value}("");
        require(sent, "Payment transfer failed");
        
        // Update product ownership
        ProductStatus newStatus;
        Role buyerRole = users[msg.sender].role;
        
        if (buyerRole == Role.Supplier) {
            newStatus = ProductStatus.WithSupplier;
        } else if (buyerRole == Role.Consumer) {
            newStatus = ProductStatus.Sold;
        } else {
            newStatus = ProductStatus.InTransit;
        }
        
        ProductStatus oldStatus = product.status;
        product.currentOwner = msg.sender;
        product.status = newStatus;
        
        // Record transfer in history
        bytes32 txHash = keccak256(
            abi.encodePacked(_productId, previousOwner, msg.sender, block.timestamp, msg.value)
        );
        
        productHistory[_productId].push(TransferRecord({
            from: previousOwner,
            to: msg.sender,
            timestamp: block.timestamp,
            status: newStatus,
            location: _location,
            transactionHash: txHash
        }));
        
        userProducts[msg.sender].push(_productId);
        
        emit ProductTransferred(_productId, previousOwner, msg.sender, newStatus, _location, block.timestamp);
        emit PaymentTransferred(msg.sender, previousOwner, msg.value, _productId);
        emit ProductStatusUpdated(_productId, oldStatus, newStatus);
    }
    
    /**
     * @dev Mark product as delivered (only consumer)
     * @param _productId The product identifier
     */
    function confirmDelivery(
        bytes32 _productId,
        string memory _location
    ) external onlyRegistered productExists(_productId) isProductOwner(_productId) {
        require(users[msg.sender].role == Role.Consumer, "Only consumers can confirm delivery");
        
        Product storage product = products[_productId];
        ProductStatus oldStatus = product.status;
        product.status = ProductStatus.Delivered;
        
        bytes32 txHash = keccak256(
            abi.encodePacked(_productId, msg.sender, "DELIVERED", block.timestamp)
        );
        
        productHistory[_productId].push(TransferRecord({
            from: msg.sender,
            to: msg.sender,
            timestamp: block.timestamp,
            status: ProductStatus.Delivered,
            location: _location,
            transactionHash: txHash
        }));
        
        emit ProductStatusUpdated(_productId, oldStatus, ProductStatus.Delivered);
    }
    
    // ============ Query Functions ============
    
    /**
     * @dev Get product details
     * @param _productId The product identifier
     */
    function getProduct(bytes32 _productId) 
        external 
        view 
        productExists(_productId) 
        returns (
            bytes32 productHash,
            string memory name,
            string memory description,
            address producer,
            address currentOwner,
            uint256 price,
            uint256 createdAt,
            ProductStatus status
        ) 
    {
        Product memory product = products[_productId];
        return (
            product.productHash,
            product.name,
            product.description,
            product.producer,
            product.currentOwner,
            product.price,
            product.createdAt,
            product.status
        );
    }
    
    /**
     * @dev Get complete product history
     * @param _productId The product identifier
     */
    function getProductHistory(bytes32 _productId) 
        external 
        view 
        productExists(_productId) 
        returns (TransferRecord[] memory) 
    {
        return productHistory[_productId];
    }
    
    /**
     * @dev Verify product authenticity using hash
     * @param _productId The product identifier
     * @param _name Expected product name
     * @param _description Expected product description
     * @param _price Expected price
     * @param _producer Expected producer address
     */
    function verifyProduct(
        bytes32 _productId,
        string memory _name,
        string memory _description,
        uint256 _price,
        address _producer
    ) external view productExists(_productId) returns (bool) {
        bytes32 calculatedHash = keccak256(
            abi.encodePacked(_name, _description, _price, _producer)
        );
        return products[_productId].productHash == calculatedHash;
    }
    
    /**
     * @dev Get all products owned by a user
     * @param _userAddress The user address
     */
    function getUserProducts(address _userAddress) external view returns (bytes32[] memory) {
        return userProducts[_userAddress];
    }
    
    /**
     * @dev Get total number of products
     */
    function getTotalProducts() external view returns (uint256) {
        return productCount;
    }
    
    /**
     * @dev Get all product IDs (for enumeration)
     */
    function getAllProductIds() external view returns (bytes32[] memory) {
        return allProductIds;
    }
    
    /**
     * @dev Get product history length
     * @param _productId The product identifier
     */
    function getProductHistoryLength(bytes32 _productId) 
        external 
        view 
        productExists(_productId) 
        returns (uint256) 
    {
        return productHistory[_productId].length;
    }
}
