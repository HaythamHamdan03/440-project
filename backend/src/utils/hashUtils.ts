import { keccak256, toUtf8Bytes, id } from "ethers";

/**
 * Generate a keccak256 hash from a string
 */
export function hashString(data: string): string {
  return keccak256(toUtf8Bytes(data));
}

/**
 * Generate a product hash from product details
 */
export function generateProductHash(
  name: string,
  description: string,
  price: string,
  producer: string
): string {
  const data = `${name}${description}${price}${producer}`;
  return hashString(data);
}

/**
 * Generate a unique identifier using keccak256
 */
export function generateUniqueId(...args: string[]): string {
  const data = args.join("");
  return id(data);
}

/**
 * Validate Ethereum address format
 */
export function isValidAddress(address: string): boolean {
  return /^0x[a-fA-F0-9]{40}$/.test(address);
}

/**
 * Validate bytes32 hash format
 */
export function isValidBytes32(hash: string): boolean {
  return /^0x[a-fA-F0-9]{64}$/.test(hash);
}

/**
 * Format timestamp to ISO string
 */
export function formatTimestamp(timestamp: number): string {
  return new Date(timestamp * 1000).toISOString();
}

/**
 * Truncate address for display
 */
export function truncateAddress(address: string, chars: number = 4): string {
  if (!isValidAddress(address)) return address;
  return `${address.slice(0, chars + 2)}...${address.slice(-chars)}`;
}
