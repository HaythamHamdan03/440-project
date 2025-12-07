import { useState } from 'react';
import { Package } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/Card';
import { Button } from './ui/Button';
import { Input } from './ui/Input';
import { Label } from './ui/Label';
import { toast } from 'sonner';

export function ProductRegistration() {
  const [formData, setFormData] = useState({
    productId: '',
    name: '',
    description: '',
    manufacturer: '',
    manufacturingDate: '',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    // Simulate blockchain transaction
    await new Promise((resolve) => setTimeout(resolve, 2000));

    toast.success('Product registered successfully!', {
      description: `Product ID: ${formData.productId}`,
    });

    // Reset form
    setFormData({
      productId: '',
      name: '',
      description: '',
      manufacturer: '',
      manufacturingDate: '',
    });

    setIsSubmitting(false);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData((prev) => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  return (
    <Card variant="elevated">
      <CardHeader>
        <div className="flex items-center gap-2">
          <Package className="h-5 w-5 text-primary" />
          <CardTitle>Register New Product</CardTitle>
        </div>
        <CardDescription>
          Register a new product on the blockchain to begin tracking its lifecycle
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="productId">Product ID *</Label>
              <Input
                id="productId"
                name="productId"
                value={formData.productId}
                onChange={handleChange}
                placeholder="e.g., PROD-001"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="name">Product Name *</Label>
              <Input
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                placeholder="e.g., Organic Coffee Beans"
                required
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              placeholder="Product description..."
              className="flex min-h-[100px] w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="manufacturer">Manufacturer *</Label>
              <Input
                id="manufacturer"
                name="manufacturer"
                value={formData.manufacturer}
                onChange={handleChange}
                placeholder="Manufacturer name"
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="manufacturingDate">Manufacturing Date *</Label>
              <Input
                id="manufacturingDate"
                name="manufacturingDate"
                type="date"
                value={formData.manufacturingDate}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <Button type="submit" variant="glow" className="w-full" disabled={isSubmitting}>
            {isSubmitting ? 'Registering on Blockchain...' : 'Register Product'}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}

