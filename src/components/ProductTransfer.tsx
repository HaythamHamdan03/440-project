import { useState } from 'react';
import { ArrowRight, Search } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/Card';
import { Button } from './ui/Button';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
import { Badge } from './ui/Badge';
import { toast } from 'sonner';
import { mockProducts } from '@/lib/mockData';

export function ProductTransfer() {
  const [productId, setProductId] = useState('');
  const [recipientAddress, setRecipientAddress] = useState('');
  const [foundProduct, setFoundProduct] = useState<typeof mockProducts[0] | null>(null);
  const [isTransferring, setIsTransferring] = useState(false);

  const handleSearch = () => {
    const product = mockProducts.find((p) => p.id === productId);
    if (product) {
      setFoundProduct(product);
      toast.success('Product found!');
    } else {
      setFoundProduct(null);
      toast.error('Product not found');
    }
  };

  const handleTransfer = async () => {
    if (!foundProduct || !recipientAddress) {
      toast.error('Please search for a product and enter recipient address');
      return;
    }

    setIsTransferring(true);
    // Simulate blockchain transaction
    await new Promise((resolve) => setTimeout(resolve, 2000));

    toast.success('Product transferred successfully!', {
      description: `Transferred to ${recipientAddress.slice(0, 10)}...`,
    });

    setProductId('');
    setRecipientAddress('');
    setFoundProduct(null);
    setIsTransferring(false);
  };

  return (
    <div className="space-y-6">
      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Search Product</CardTitle>
          <CardDescription>Enter a product ID to view details and transfer</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex gap-2">
            <Input
              placeholder="Enter Product ID"
              value={productId}
              onChange={(e) => setProductId(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
            />
            <Button onClick={handleSearch} variant="glow">
              <Search className="h-4 w-4 mr-2" />
              Search
            </Button>
          </div>
        </CardContent>
      </Card>

      {foundProduct && (
        <Card variant="glass">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>{foundProduct.name}</CardTitle>
              <Badge variant="info">{foundProduct.status}</Badge>
            </div>
            <CardDescription>Product ID: {foundProduct.id}</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span className="text-muted-foreground">Manufacturer:</span>
                <p className="font-medium">{foundProduct.manufacturer}</p>
              </div>
              <div>
                <span className="text-muted-foreground">Current Owner:</span>
                <p className="font-mono text-primary">{foundProduct.currentOwner.slice(0, 20)}...</p>
              </div>
            </div>

            <div className="pt-4 border-t border-border">
              <Label htmlFor="recipient">Recipient Address</Label>
              <div className="flex gap-2 mt-2">
                <Input
                  id="recipient"
                  placeholder="0x..."
                  value={recipientAddress}
                  onChange={(e) => setRecipientAddress(e.target.value)}
                  className="font-mono"
                />
                <Button
                  onClick={handleTransfer}
                  disabled={!recipientAddress || isTransferring}
                  variant="glow"
                  className="gap-2"
                >
                  {isTransferring ? (
                    'Transferring...'
                  ) : (
                    <>
                      Transfer <ArrowRight className="h-4 w-4" />
                    </>
                  )}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

