import { Package, ArrowRight, ShieldCheck } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/Card';
import { Badge } from './ui/Badge';
import { ActivityEvent } from '@/types/supply-chain';
import { timeAgo, truncateAddress } from '@/lib/utils';
import { mockActivities } from '@/lib/mockData';

const eventIcons = {
  registration: Package,
  transfer: ArrowRight,
  verification: ShieldCheck,
};

const eventColors = {
  registration: 'info',
  transfer: 'warning',
  verification: 'success',
} as const;

export function RecentActivity() {
  return (
    <Card variant="glass">
      <CardHeader>
        <CardTitle>Recent Activity</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {mockActivities.map((activity) => {
            const Icon = eventIcons[activity.type];
            const colorVariant = eventColors[activity.type];

            return (
              <div key={activity.id} className="flex items-start gap-3 pb-4 border-b border-border last:border-0 last:pb-0">
                <div className="p-2 rounded-lg bg-primary/10 text-primary">
                  <Icon className="h-4 w-4" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <Badge variant={colorVariant} className="text-xs">
                      {activity.type}
                    </Badge>
                    <span className="text-xs text-muted-foreground">
                      {timeAgo(activity.timestamp)}
                    </span>
                  </div>
                  <p className="text-sm text-foreground mb-1">{activity.description}</p>
                  <p className="text-xs font-mono text-muted-foreground">
                    {truncateAddress(activity.transactionHash, 12, 8)}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
}

