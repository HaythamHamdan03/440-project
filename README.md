# ChainTrack - Blockchain Supply Chain Tracking System

A modern React + TypeScript frontend for blockchain-based supply chain tracking, built for ICS 440 - Cryptography and Blockchain Applications.

## 🎨 Design Theme

- **Dark industrial theme** with deep navy/slate backgrounds
- **Cyan/teal accent colors** (primary: hsl(186, 100%, 50%))
- **Typography**: Space Grotesk for headings, JetBrains Mono for code/addresses
- **Glow effects** on buttons and cards
- **Glass morphism** effects with backdrop blur

## 🛠️ Tech Stack

- **React 18** with TypeScript
- **Vite** as bundler
- **Tailwind CSS** for styling
- **Shadcn/ui** components (Button, Card, Input, Label, Badge, Tabs)
- **Lucide React** for icons
- **Sonner** for toast notifications

## ✨ Features

### 1. Wallet Connection Component
- MetaMask wallet connection button
- Display connected wallet address (truncated), network name, and ETH balance
- Connection status indicator with animations
- Disconnect functionality

### 2. Role Selector Component
- Three roles: Producer, Supplier, Consumer
- Each role has an icon, title, description, and list of capabilities
- Visual selection with glow effect on selected role

### 3. Product Registration Form (Producer only)
- Fields: Product ID, Product Name, Description, Manufacturer, Manufacturing Date
- Form validation
- Success toast on registration
- Mock blockchain transaction simulation

### 4. Product Transfer Component (Supplier/Consumer)
- Search for product by ID
- Display product details when found
- Recipient address input
- Transfer confirmation with toast

### 5. Product History Component
- Search products by ID
- Display timeline of all transfers
- Show: event type, from/to addresses, timestamps, transaction hashes
- Visual timeline with status badges

### 6. Dashboard Stats
- Stats cards showing: Total Products, Active Transfers, Verified Products, Network Status
- Animated counters and status indicators

### 7. Recent Activity Feed
- List of recent blockchain events
- Show event type, description, time ago, transaction hash

## 🚀 Getting Started

### Prerequisites

- Node.js 18+ and npm/yarn/pnpm
- MetaMask browser extension (for wallet connection)

### Installation

1. Clone the repository or extract the project files

2. Install dependencies:
```bash
npm install
```

3. Start the development server:
```bash
npm run dev
```

4. Open your browser and navigate to `http://localhost:5173`

### Build for Production

```bash
npm run build
```

The production build will be in the `dist` folder.

## 📁 Project Structure

```
chaintrack/
├── src/
│   ├── components/
│   │   ├── ui/          # Reusable UI components
│   │   ├── WalletConnect.tsx
│   │   ├── RoleSelector.tsx
│   │   ├── ProductRegistration.tsx
│   │   ├── ProductTransfer.tsx
│   │   ├── ProductHistory.tsx
│   │   ├── DashboardStats.tsx
│   │   └── RecentActivity.tsx
│   ├── hooks/
│   │   └── useWallet.ts
│   ├── lib/
│   │   ├── utils.ts
│   │   └── mockData.ts
│   ├── types/
│   │   └── supply-chain.ts
│   ├── App.tsx
│   ├── main.tsx
│   └── index.css
├── index.html
├── package.json
├── vite.config.ts
├── tsconfig.json
└── tailwind.config.js
```

## 🎯 Usage

1. **Connect Wallet**: Click "Connect Wallet" button in the header to connect MetaMask
2. **Select Role**: Choose your role (Producer, Supplier, or Consumer)
3. **Navigate Tabs**: Use the tabs to access different features:
   - **Dashboard**: View statistics and recent activity
   - **Register** (Producer): Register new products
   - **Transfer** (Supplier/Consumer): Transfer products
   - **History**: View product history and traceability

## 🔌 Smart Contract Integration

The frontend is designed to be easily connected to a Solidity smart contract. To integrate:

1. Update `src/hooks/useWallet.ts` to use your contract ABI and address
2. Replace mock data in `src/lib/mockData.ts` with actual contract calls
3. Update component functions to call contract methods instead of mock simulations

## 🎨 Customization

### Color Variables

Edit `src/index.css` to customize colors:

```css
:root {
  --background: 222 47% 6%;
  --foreground: 210 40% 98%;
  --primary: 186 100% 50%;
  --secondary: 217 33% 17%;
  --accent: 199 89% 48%;
  --muted: 217 33% 17%;
  --card: 222 47% 8%;
  --border: 217 33% 20%;
}
```

## 📝 Notes

- All data is currently **mock/simulated** - no actual blockchain integration
- The UI is ready to connect to a Solidity smart contract
- MetaMask connection is functional but uses mock data for transactions
- For production use, integrate with your deployed smart contract

## 📄 License

This project is created for educational purposes as part of ICS 440 course requirements.

## 👥 Authors

ICS 440 - Cryptography and Blockchain Applications | Term 251

