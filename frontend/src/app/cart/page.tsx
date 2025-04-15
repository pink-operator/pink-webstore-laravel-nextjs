'use client';

import { useState } from 'react';
import { useCartStore } from '@/store/cart';
import { XMarkIcon } from '@heroicons/react/24/outline';
import Image from 'next/image';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { apiClient } from '@/lib/api/client';
import { Modal } from '@/components/ui/Modal';

export default function CartPage() {
  const router = useRouter();
  const { items, removeItem, updateQuantity, clearCart, total } = useCartStore();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [orderId, setOrderId] = useState<number | null>(null);

  const handleCheckout = async () => {
    try {
      setLoading(true);
      setError(null);

      const response = await apiClient.post('/orders', {
        items: items.map(item => ({
          product_id: item.product.id,
          quantity: item.quantity
        }))
      });

      setOrderId(response.data.data.id);
      clearCart();
      setIsModalOpen(true);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to create order. Please try again.');
      console.error('Checkout error:', err);
    } finally {
      setLoading(false);
    }
  };

  if (items.length === 0) {
    return (
      <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div className="text-center">
          <h1 className="text-2xl font-bold tracking-tight text-gray-900">Your Cart</h1>
          <p className="mt-4 text-gray-500">Your cart is empty.</p>
          <div className="mt-6">
            <Link 
              href="/products"
              className="inline-block rounded-md border border-transparent bg-pink-600 px-6 py-3 text-base font-medium text-white hover:bg-pink-700"
            >
              Continue Shopping
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <h1 className="text-2xl font-bold tracking-tight text-gray-900">Shopping Cart</h1>

      <div className="mt-8">
        <div className="flow-root">
          <ul className="-my-6 divide-y divide-gray-200">
            {items.map((item) => (
              <li key={item.product.id} className="flex py-6">
                <div className="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 relative">
                  <Image
                    src={item.product.image_url || "/product-placeholder.jpg"}
                    alt={item.product.name}
                    fill
                    className="object-contain object-center p-2"
                  />
                </div>

                <div className="ml-4 flex flex-1 flex-col">
                  <div>
                    <div className="flex justify-between text-base font-medium text-gray-900">
                      <h3>
                        <Link href={`/products/${item.product.id}`}>
                          {item.product.name}
                        </Link>
                      </h3>
                      <p className="ml-4">${(item.product.price * item.quantity).toFixed(2)}</p>
                    </div>
                    <p className="mt-1 text-sm text-gray-500">
                      ${item.product.price.toFixed(2)} each
                    </p>
                  </div>
                  <div className="flex flex-1 items-end justify-between text-sm">
                    <div className="flex items-center">
                      <label htmlFor={`quantity-${item.product.id}`} className="mr-2 text-gray-500">
                        Qty
                      </label>
                      <select
                        id={`quantity-${item.product.id}`}
                        value={item.quantity}
                        onChange={(e) => updateQuantity(item.product.id, parseInt(e.target.value))}
                        className="rounded-md border border-gray-300 py-1 text-base sm:text-sm"
                      >
                        {[...Array(Math.min(10, item.product.stock_quantity))].map((_, idx) => (
                          <option key={idx} value={idx + 1}>
                            {idx + 1}
                          </option>
                        ))}
                      </select>
                    </div>

                    <button
                      type="button"
                      onClick={() => removeItem(item.product.id)}
                      className="font-medium text-pink-600 hover:text-pink-500"
                    >
                      <span className="sr-only">Remove</span>
                      <XMarkIcon className="h-5 w-5" aria-hidden="true" />
                    </button>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="mt-10 border-t border-gray-200 pt-6">
        <div className="flex justify-between text-base font-medium text-gray-900">
          <p>Subtotal</p>
          <p>${total().toFixed(2)}</p>
        </div>
        <p className="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
        
        {error && (
          <div className="mt-4 rounded-md bg-red-50 p-4">
            <div className="flex">
              <div className="ml-3">
                <h3 className="text-sm font-medium text-red-800">Error</h3>
                <div className="mt-2 text-sm text-red-700">
                  <p>{error}</p>
                </div>
              </div>
            </div>
          </div>
        )}
        
        <div className="mt-6">
          <button
            onClick={handleCheckout}
            disabled={loading}
            className="w-full rounded-md border border-transparent bg-pink-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-pink-700 disabled:bg-pink-300 disabled:cursor-not-allowed"
          >
            {loading ? 'Processing...' : 'Checkout'}
          </button>
        </div>
        <div className="mt-6 flex justify-center text-center text-sm text-gray-500">
          <p>
            or{' '}
            <Link href="/products" className="font-medium text-pink-600 hover:text-pink-500">
              Continue Shopping
              <span aria-hidden="true"> &rarr;</span>
            </Link>
          </p>
        </div>
      </div>

      <Modal 
        isOpen={isModalOpen} 
        onClose={() => {
          setIsModalOpen(false);
          if (orderId) {
            router.push(`/orders/${orderId}`);
          }
        }}
        title="Order Confirmed!"
      >
        <div className="text-center">
          <p className="text-sm text-gray-500">
            Your order has been placed successfully! Thank you for your purchase.
          </p>
          <div className="mt-4">
            <button
              type="button"
              className="inline-flex justify-center rounded-md border border-transparent bg-pink-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
              onClick={() => {
                setIsModalOpen(false);
                if (orderId) {
                  router.push(`/orders/${orderId}`);
                }
              }}
            >
              View Order Details
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
}