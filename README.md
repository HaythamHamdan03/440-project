Project Guidelines: Supply Chain Transparency
Tracking via Blockchain
ICS 440 - Cryptography and Blockchain Applications
Term 251
1. Project Overview
In modern supply chains, lack of transparency leads to fraud, counterfeiting, and loss of trust
between producers, intermediaries (e.g., distributors, suppliers, retailers), and consumers. This
project aims to develop a blockchain-based supply chain tracking system that records ev-
ery transaction—from production/manufacturing to customer—on an immutable ledger (i.e.
blockchain) using smart contracts. Each participant (producer, supplier, consumer) interacts
with the blockchain to update the product’s status securely and verifiably.
2. Learning Objectives
• Understand the role of blockchain in ensuring data integrity and transparency.
• Implement and deploy smart contracts in Solidity.
• Apply cryptographic techniques and timestamping for authenticity.
• Demonstrate how decentralization enhances trust in supply chain systems.
• Optionally integrate QR-code-based identification for product tracking.
• Optionally integrate balance transfer related to any purchase.
3. System Description
The system should model a simplified supply chain with the following entities:
• Producer: Registers the product on blockchain.
• Supplier: Confirms product purchase and stores product data.
• Consumer: Confirms product purchase, verifies product authenticity (using product ID or
optionally with QR code).
4. Technical Requirements
The following tools and components should be used for implementation:
a) Blockchain Platform
Use Ethereum with tools such as Remix IDE. Sepolia Test Network can be used for blockchain
simulation and MetaMask for wallet integration.
1
b) Smart Contract
Written in Solidity and must support functions such as:
• registerProduct(productID, ..) – to be used by producer
• transferProduct(productID, ..) – to be used by supplier/consumer
• getProductHistory(productID, ..) – to be used by consumer
• (optional) transferBalance(senderID, receiverID) – to be used by all
• (optional) verifyProduct(QR-code) – to be used by all
c) Frontend
A simple web interface (HTML/JS/PHP) can be developed to interact with the smart contract.
It should allow users to view product details and transaction history, possibly with product ID
or optiopnally, with QR code integration.
e) Cryptographic Components
Use required hashing to generate product or transaction identifiers. Store only hashes (not raw
data) on-chain for efficiency and privacy.
5. Expected Deliverables
• Project Report (4–6 pages) detailing problem background, system architecture, smart
contract design, testing scenarios, example transactions, and discussion on security.
• Source Code including smart contracts, frontend codes, setup instructions.
• Demo using Sepolia (or other test network) showing product registration, product transfer,
verification, etc.
6. Grading Rubric (Total 100 Marks)
Component Description Marks
Smart Contract and Web
Implementation
Code correctness, functionality, events, and Web in-
tegration
30
Testing & Demonstration Working demo, clarity of transactions, bug-free op-
eration
30
Report Quality Technical explanation, screenshots, clarity of sys-
tem design, correct modeling and critical security
analysis
40
Innovation/Extension Optional Activities: i) QR code use (or IoT integra-
tion) - Bonus 10 marks; ii) Balance transfer (or any
creative features) - Bonus 10 marks
20
Total (without bonus) 100
2
7. Optional Extensions (Bonus Marks)
• Build QR scanner (or IoT integration) for tracking products.
• Add user-based access control to smart contracts and balance transfer (or any creative fea-
tures).
8. Suggested Deadlines
Date Tasks
December 10, 2025 Final report submission
December 11, 2025 Demo Presentation and evaluation
Submission Format
• Report: PDF named ICS440_KFUP#IDs_ProjectReport.pdf
– Submit via Gradescope by the announced deadline.
– One submission for each group (including the information of all members).
• Demo: Live Demonstration of the developed tool(s)
Additional Notes
• Students must work in a group (maximum 3 members).
• Attendance (of all members in a group) during demo is mandatory.
Useful Links
• Simple Demo (without voice): https://www.youtube.com/watch?v=386UAhGqEFQ
• Remix IDE: https://remix.ethereum.org/
• MetaMask Wallet (Browser Extension): https://metamask.io/download
• Sepolia Testnet Explorer: https://sepolia.etherscan.io/
• Implementation-related Book: https://link.springer.com/book/10.1007/978-1-4842-5086-0
3