export type UserRole = 'producer' | 'supplier' | 'consumer';

export type ProductStatus = 'registered' | 'in-transit' | 'delivered' | 'verified';

export interface Product {
  id: string;
  name: string;
  description: string;
  manufacturer: string;
  manufacturingDate: string;
  currentOwner: string;
  status: ProductStatus;
  registeredAt: string;
  history: TransferRecord[];
}

export interface TransferRecord {
  from: string;
  to: string;
  timestamp: string;
  transactionHash: string;
  status: ProductStatus;
  eventType: 'registration' | 'transfer' | 'verification';
}

export interface WalletState {
  isConnected: boolean;
  address: string | null;
  balance: string | null;
  network: string | null;
}

export interface ActivityEvent {
  id: string;
  type: 'registration' | 'transfer' | 'verification';
  description: string;
  timestamp: string;
  transactionHash: string;
  productId?: string;
}

