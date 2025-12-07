import { useState, useEffect, useCallback } from 'react';
import { WalletState } from '@/types/supply-chain';

declare global {
  interface Window {
    ethereum?: {
      isMetaMask?: boolean;
      request: (args: { method: string; params?: any[] }) => Promise<any>;
      on: (event: string, handler: (...args: any[]) => void) => void;
      removeListener: (event: string, handler: (...args: any[]) => void) => void;
    };
  }
}

const SEPOLIA_CHAIN_ID = '0xaa36a7'; // 11155111 in hex

export function useWallet() {
  const [walletState, setWalletState] = useState<WalletState>({
    isConnected: false,
    address: null,
    balance: null,
    network: null,
  });
  const [isLoading, setIsLoading] = useState(false);

  const getNetworkName = (chainId: string): string => {
    switch (chainId) {
      case SEPOLIA_CHAIN_ID:
        return 'Sepolia';
      case '0x1':
        return 'Ethereum Mainnet';
      case '0x5':
        return 'Goerli';
      default:
        return `Chain ${parseInt(chainId, 16)}`;
    }
  };

  const getBalance = useCallback(async (address: string): Promise<string> => {
    try {
      if (!window.ethereum) return '0';
      const balance = await window.ethereum.request({
        method: 'eth_getBalance',
        params: [address, 'latest'],
      });
      // Convert from wei to ETH
      const ethBalance = (parseInt(balance, 16) / 1e18).toFixed(4);
      return ethBalance;
    } catch (error) {
      console.error('Error fetching balance:', error);
      return '0';
    }
  }, []);

  const connect = useCallback(async () => {
    if (!window.ethereum) {
      alert('Please install MetaMask to connect your wallet');
      return;
    }

    setIsLoading(true);
    try {
      // Request account access
      const accounts = await window.ethereum.request({
        method: 'eth_requestAccounts',
      });

      if (accounts.length > 0) {
        const address = accounts[0];
        const balance = await getBalance(address);
        const chainId = await window.ethereum.request({
          method: 'eth_chainId',
        });
        const network = getNetworkName(chainId);

        setWalletState({
          isConnected: true,
          address,
          balance,
          network,
        });
      }
    } catch (error: any) {
      if (error.code !== 4001) {
        // 4001 is user rejection, don't show error
        console.error('Error connecting wallet:', error);
        alert('Failed to connect wallet: ' + error.message);
      }
    } finally {
      setIsLoading(false);
    }
  }, [getBalance]);

  const disconnect = useCallback(() => {
    setWalletState({
      isConnected: false,
      address: null,
      balance: null,
      network: null,
    });
  }, []);

  // Check if already connected on mount
  useEffect(() => {
    const checkConnection = async () => {
      if (!window.ethereum) return;

      try {
        const accounts = await window.ethereum.request({
          method: 'eth_accounts',
        });

        if (accounts.length > 0) {
          const address = accounts[0];
          const balance = await getBalance(address);
          const chainId = await window.ethereum.request({
            method: 'eth_chainId',
          });
          const network = getNetworkName(chainId);

          setWalletState({
            isConnected: true,
            address,
            balance,
            network,
          });
        }
      } catch (error) {
        console.error('Error checking connection:', error);
      }
    };

    checkConnection();

    // Listen for account changes
    const handleAccountsChanged = async (accounts: string[]) => {
      if (accounts.length === 0) {
        disconnect();
      } else {
        const balance = await getBalance(accounts[0]);
        const chainId = await window.ethereum?.request({
          method: 'eth_chainId',
        });
        const network = chainId ? getNetworkName(chainId) : null;

        setWalletState((prev) => ({
          ...prev,
          address: accounts[0],
          balance,
          network,
        }));
      }
    };

    // Listen for chain changes
    const handleChainChanged = async (chainId: string) => {
      const network = getNetworkName(chainId);
      if (walletState.address) {
        const balance = await getBalance(walletState.address);
        setWalletState((prev) => ({
          ...prev,
          network,
          balance,
        }));
      }
    };

    if (window.ethereum) {
      window.ethereum.on('accountsChanged', handleAccountsChanged);
      window.ethereum.on('chainChanged', handleChainChanged);

      return () => {
        window.ethereum?.removeListener('accountsChanged', handleAccountsChanged);
        window.ethereum?.removeListener('chainChanged', handleChainChanged);
      };
    }
  }, [getBalance, disconnect, walletState.address]);

  return {
    walletState,
    connect,
    disconnect,
    isLoading,
  };
}

