import { create } from 'zustand';
import { Product } from '@/types/models';

interface CartItem {
  product: Product;
  quantity: number;
}

interface CartStore {
  items: CartItem[];
  addItem: (product: Product) => void;
  removeItem: (productId: number) => void;
  updateQuantity: (productId: number, quantity: number) => void;
  clearCart: () => void;
  total: () => number;
}

export const useCartStore = create<CartStore>((set, get) => ({
  items: [],
  
  addItem: (product) => {
    set((state) => {
      const existing = state.items.find(item => item.product.id === product.id);
      if (existing) {
        return {
          items: state.items.map(item =>
            item.product.id === product.id
              ? { ...item, quantity: Math.min(item.quantity + 1, product.stock_quantity) }
              : item
          ),
        };
      }
      return { items: [...state.items, { product, quantity: 1 }] };
    });
  },

  removeItem: (productId) => {
    set((state) => ({
      items: state.items.filter(item => item.product.id !== productId),
    }));
  },

  updateQuantity: (productId, quantity) => {
    set((state) => ({
      items: state.items.map(item =>
        item.product.id === productId
          ? { ...item, quantity: Math.min(Math.max(0, quantity), item.product.stock_quantity) }
          : item
      ).filter(item => item.quantity > 0),
    }));
  },

  clearCart: () => set({ items: [] }),

  total: () => {
    const state = get();
    return state.items.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
  },
}));