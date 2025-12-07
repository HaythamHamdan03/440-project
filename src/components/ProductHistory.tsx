import { useState } from 'react';
import { Search, Clock, ArrowRight } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/Card';
import { Button } from './ui/Button';
import { Input } from './ui/Input';
import { Badge } from './ui/Badge';
import { toast } from 'sonner';
import { mockProducts } from '@/lib/mockData';
import { formatDate, truncateAddress } from '@/lib/utils';

export function ProductHistory() {
  const [productId, setProductId] = useState('');
  const [foundProduct, setFoundProduct] = useState<typeof mockProducts[0] | null>(null);

  const handleSearch = () => {
    const product = mockProducts.find((p) => p.id === productId);
    if (product) {
      setFoundProduct(product);
      toast.success('Product history loaded!');
    } else {
      setFoundProduct(null);
      toast.error('Product not found');
    }
  };

  const getStatusVariant = (status: string): 'default' | 'success' | 'warning' | 'info' => {
    switch (status) {
      case 'verified':
        return 'success';
      case 'in-transit':
        return 'warning';
      case 'delivered':
        return 'info';
      default:
        return 'default';
    }
  };

  return (
    <div className="space-y-6">
      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Search Product History</CardTitle>
          <CardDescription>Enter a product ID to view its complete blockchain history</CardDescription>
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
              <Badge variant={getStatusVariant(foundProduct.status)}>
                {foundProduct.status}
              </Badge>
            </div>
            <CardDescription>Product ID: {foundProduct.id}</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <h4 className="font-semibold text-lg mb-4">Transfer Timeline</h4>
              <div className="relative">
                {foundProduct.history.map((record, index) => (
                  <div key={index} className="relative pb-8 last:pb-0">
                    {index < foundProduct.history.length - 1 && (
                      <div className="absolute left-4 top-8 bottom-0 w-0.5 bg-border" />
                    )}
                    <div className="flex gap-4">
                      <div className="flex-shrink-0">
                        <div className="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center">
                          <Clock className="h-4 w-4 text-primary" />
                        </div>
                      </div>
                      <div className="flex-1 space-y-2">
                        <div className="flex items-center gap-2">
                          <Badge variant="info" className="text-xs">
                            {record.eventType}
                          </Badge>
                          <span className="text-xs text-muted-foreground">
                            {formatDate(record.timestamp)}
                          </span>
                        </div>
                        <div className="flex items-center gap-2 text-sm">
                          <span className="font-mono text-muted-foreground">
                            {truncateAddress(record.from)}
                          </span>
                          <ArrowRight className="h-4 w-4 text-primary" />
                          <span className="font-mono text-primary">
                            {truncateAddress(record.to)}
                          </span>
                        </div>
                        <div className="text-xs font-mono text-muted-foreground">
                          {truncateAddress(record.transactionHash, 12, 8)}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

