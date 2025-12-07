import { ButtonHTMLAttributes, forwardRef } from 'react';
import { cn } from '@/lib/utils';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'glow' | 'elevated' | 'glass' | 'outline' | 'ghost' | 'destructive';
  size?: 'sm' | 'md' | 'lg';
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'default', size = 'md', ...props }, ref) => {
    const baseStyles = 'inline-flex items-center justify-center rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:pointer-events-none';
    
    const variants = {
      default: 'bg-gradient-to-r from-primary to-accent text-black hover:from-primary/90 hover:to-accent/90 glow-effect',
      glow: 'bg-gradient-to-r from-primary to-accent text-black hover:from-primary/90 hover:to-accent/90 glow-effect-lg',
      elevated: 'bg-gradient-to-r from-primary to-accent text-black shadow-lg hover:shadow-xl transform hover:-translate-y-0.5',
      glass: 'glass text-foreground hover:bg-card/80',
      outline: 'border-2 border-primary text-primary hover:bg-primary/10',
      ghost: 'text-foreground hover:bg-secondary',
      destructive: 'bg-red-600 text-white hover:bg-red-700',
    };

    const sizes = {
      sm: 'px-3 py-1.5 text-sm',
      md: 'px-4 py-2 text-base',
      lg: 'px-6 py-3 text-lg',
    };

    return (
      <button
        className={cn(baseStyles, variants[variant], sizes[size], className)}
        ref={ref}
        {...props}
      />
    );
  }
);

Button.displayName = 'Button';

export { Button };

