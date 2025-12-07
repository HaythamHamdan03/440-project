import { Package, Truck, ShieldCheck } from 'lucide-react';
import { UserRole } from '@/types/supply-chain';
import { Card } from './ui/Card';
import { cn } from '@/lib/utils';

interface RoleSelectorProps {
  selectedRole: UserRole | null;
  onRoleSelect: (role: UserRole) => void;
}

const roles: Array<{
  id: UserRole;
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  capabilities: string[];
}> = [
  {
    id: 'producer',
    title: 'Producer',
    description: 'Register new products on the blockchain',
    icon: Package,
    capabilities: [
      'Register products with unique IDs',
      'Set product metadata',
      'Initialize product lifecycle',
    ],
  },
  {
    id: 'supplier',
    title: 'Supplier',
    description: 'Transfer products and update status',
    icon: Truck,
    capabilities: [
      'Transfer products to recipients',
      'Update product status',
      'View product history',
    ],
  },
  {
    id: 'consumer',
    title: 'Consumer',
    description: 'Verify products and view history',
    icon: ShieldCheck,
    capabilities: [
      'Verify product authenticity',
      'View complete product history',
      'Check product ownership',
    ],
  },
];

export function RoleSelector({ selectedRole, onRoleSelect }: RoleSelectorProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      {roles.map((role) => {
        const Icon = role.icon;
        const isSelected = selectedRole === role.id;

        return (
          <Card
            key={role.id}
            variant={isSelected ? 'elevated' : 'default'}
            className={cn(
              'cursor-pointer transition-all duration-300 hover:border-primary/50',
              isSelected && 'border-primary glow-effect'
            )}
            onClick={() => onRoleSelect(role.id)}
          >
            <div className="flex flex-col items-center text-center space-y-4">
              <div
                className={cn(
                  'p-4 rounded-full transition-all duration-300',
                  isSelected
                    ? 'bg-primary/20 text-primary'
                    : 'bg-secondary text-muted-foreground'
                )}
              >
                <Icon className="h-8 w-8" />
              </div>
              <div>
                <h3 className="text-xl font-semibold mb-1">{role.title}</h3>
                <p className="text-sm text-muted-foreground mb-4">{role.description}</p>
                <ul className="text-xs text-muted-foreground space-y-1 text-left">
                  {role.capabilities.map((cap, idx) => (
                    <li key={idx} className="flex items-start gap-2">
                      <span className="text-primary mt-1">•</span>
                      <span>{cap}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </Card>
        );
      })}
    </div>
  );
}

