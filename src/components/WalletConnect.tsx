import { Wallet, LogOut, Loader2 } from 'lucide-react';
import { Button } from './ui/Button';
import { Badge } from './ui/Badge';
import { useWallet } from '@/hooks/useWallet';
import { truncateAddress } from '@/lib/utils';

export function WalletConnect() {
  const { walletState, connect, disconnect, isLoading } = useWallet();

  if (!walletState.isConnected) {
    return (
      <Button
        onClick={connect}
        disabled={isLoading}
        variant="glow"
        className="gap-2"
      >
        {isLoading ? (
          <>
            <Loader2 className="h-4 w-4 animate-spin" />
            Connecting...
          </>
        ) : (
          <>
            <Wallet className="h-4 w-4" />
            Connect Wallet
          </>
        )}
      </Button>
    );
  }

  return (
    <div className="flex items-center gap-3">
      <div className="flex flex-col items-end gap-1">
        <div className="flex items-center gap-2">
          <Badge variant="success" className="gap-1.5">
            <div className="h-2 w-2 rounded-full bg-green-400 animate-pulse" />
            {walletState.network || 'Unknown'}
          </Badge>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <span className="font-mono text-primary">
            {walletState.address ? truncateAddress(walletState.address) : 'Not connected'}
          </span>
          {walletState.balance && (
            <span className="text-muted-foreground">
              {walletState.balance} ETH
            </span>
          )}
        </div>
      </div>
      <Button
        onClick={disconnect}
        variant="ghost"
        size="sm"
        className="gap-2"
      >
        <LogOut className="h-4 w-4" />
      </Button>
    </div>
  );
}

