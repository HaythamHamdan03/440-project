import { Package, Truck, ShieldCheck, Wifi } from 'lucide-react';
import { Card } from './ui/Card';
import { Badge } from './ui/Badge';

interface StatCardProps {
  title: string;
  value: string | number;
  icon: React.ComponentType<{ className?: string }>;
  variant?: 'default' | 'success' | 'warning' | 'info';
  description?: string;
}

function StatCard({ title, value, icon: Icon, variant = 'default', description }: StatCardProps) {
  const variants = {
    default: 'text-foreground',
    success: 'text-green-400',
    warning: 'text-yellow-400',
    info: 'text-primary',
  };

  return (
    <Card variant="elevated" className="hover:glow-effect transition-all duration-300">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-muted-foreground mb-1">{title}</p>
          <p className={`text-3xl font-bold ${variants[variant]}`}>{value}</p>
          {description && <p className="text-xs text-muted-foreground mt-2">{description}</p>}
        </div>
        <div className={`p-3 rounded-lg ${variants[variant]} bg-opacity-10`}>
          <Icon className="h-6 w-6" />
        </div>
      </div>
    </Card>
  );
}

export function DashboardStats() {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard
        title="Total Products"
        value="1,234"
        icon={Package}
        variant="info"
        description="Registered on blockchain"
      />
      <StatCard
        title="Active Transfers"
        value="42"
        icon={Truck}
        variant="warning"
        description="In transit"
      />
      <StatCard
        title="Verified Products"
        value="892"
        icon={ShieldCheck}
        variant="success"
        description="Authenticated"
      />
      <StatCard
        title="Network Status"
        value="Online"
        icon={Wifi}
        variant="success"
        description="Sepolia Testnet"
      />
    </div>
  );
}

