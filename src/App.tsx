import { useState } from 'react';
import { Boxes } from 'lucide-react';
import { Toaster } from 'sonner';
import { UserRole } from '@/types/supply-chain';
import { WalletConnect } from './components/WalletConnect';
import { RoleSelector } from './components/RoleSelector';
import { DashboardStats } from './components/DashboardStats';
import { RecentActivity } from './components/RecentActivity';
import { ProductRegistration } from './components/ProductRegistration';
import { ProductTransfer } from './components/ProductTransfer';
import { ProductHistory } from './components/ProductHistory';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './components/ui/Tabs';

function App() {
  const [selectedRole, setSelectedRole] = useState<UserRole | null>(null);

  return (
    <div className="min-h-screen bg-background">
      <Toaster position="top-right" richColors />
      
      {/* Header */}
      <header className="border-b border-border bg-card/50 backdrop-blur-sm sticky top-0 z-50">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="p-2 rounded-lg bg-primary/20 text-primary">
                <Boxes className="h-6 w-6" />
              </div>
              <div>
                <h1 className="text-2xl font-bold text-foreground">ChainTrack</h1>
                <p className="text-xs text-muted-foreground">Blockchain Supply Chain Tracking</p>
              </div>
            </div>
            <WalletConnect />
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        {/* Role Selector */}
        <RoleSelector selectedRole={selectedRole} onRoleSelect={setSelectedRole} />

        {/* Tabs Interface */}
        <Tabs defaultValue="dashboard" className="w-full">
          <TabsList className="mb-6">
            <TabsTrigger value="dashboard">Dashboard</TabsTrigger>
            {selectedRole === 'producer' && (
              <TabsTrigger value="register">Register</TabsTrigger>
            )}
            {(selectedRole === 'supplier' || selectedRole === 'consumer') && (
              <TabsTrigger value="transfer">Transfer</TabsTrigger>
            )}
            <TabsTrigger value="history">History</TabsTrigger>
          </TabsList>

          <TabsContent value="dashboard">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div className="lg:col-span-2 space-y-6">
                <DashboardStats />
                <div className="text-center py-12 text-muted-foreground">
                  <p className="text-lg mb-2">Welcome to ChainTrack</p>
                  <p>Select a role above to get started with blockchain supply chain tracking</p>
                </div>
              </div>
              <div>
                <RecentActivity />
              </div>
            </div>
          </TabsContent>

          {selectedRole === 'producer' && (
            <TabsContent value="register">
              <ProductRegistration />
            </TabsContent>
          )}

          {(selectedRole === 'supplier' || selectedRole === 'consumer') && (
            <TabsContent value="transfer">
              <ProductTransfer />
            </TabsContent>
          )}

          <TabsContent value="history">
            <ProductHistory />
          </TabsContent>
        </Tabs>
      </main>

      {/* Footer */}
      <footer className="border-t border-border mt-12 py-6">
        <div className="container mx-auto px-4 text-center text-sm text-muted-foreground">
          <p>ICS 440 - Cryptography and Blockchain Applications | Term 251</p>
          <p className="mt-1">Blockchain-Based Supply Chain Tracking System</p>
        </div>
      </footer>
    </div>
  );
}

export default App;

