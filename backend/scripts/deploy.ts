import { ethers } from "hardhat";

async function main() {
  console.log("Deploying SupplyChain contract...");

  // Log deployment info
  const [deployer] = await ethers.getSigners();
  console.log(`Deployed by: ${deployer.address}`);
  
  const network = await ethers.provider.getNetwork();
  console.log(`Network: ${network.name} (chainId: ${network.chainId})`);

  const SupplyChain = await ethers.getContractFactory("SupplyChain");
  const supplyChain = await SupplyChain.deploy();

  await supplyChain.waitForDeployment();

  const address = await supplyChain.getAddress();
  console.log(`SupplyChain deployed to: ${address}`);
  
  // Save contract address for frontend/API use
  console.log("\n--- Copy this to your .env file ---");
  console.log(`CONTRACT_ADDRESS=${address}`);
  console.log("-----------------------------------\n");

  return address;
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
