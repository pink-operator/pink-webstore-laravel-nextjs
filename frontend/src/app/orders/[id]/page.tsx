'use client';

import { useState, useEffect } from 'react';
import { apiClient } from '@/lib/api/client';
import { Order } from '@/types/models';
import Image from 'next/image';
import Link from 'next/link';
import { useParams } from 'next/navigation';

export default function OrderDetailPage() {
  const params = useParams();
  const orderId = params.id;
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchOrder() {
      try {
        setLoading(true);
        const response = await apiClient.get(`/orders/${orderId}`);
        setOrder(response.data.data);
        setError(null);
      } catch (err: any) {
        console.error('Error fetching order:', err);
        setError(err.response?.data?.message || 'Failed to load order details');
      } finally {
        setLoading(false);
      }
    }

    if (orderId) {
      fetchOrder();
    }
  }, [orderId]);

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[50vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="text-center py-12">
        <h2 className="text-2xl font-bold text-gray-900">Error</h2>
        <p className="mt-4 text-gray-500">{error || 'Order not found'}</p>
        <div className="mt-6">
          <Link 
            href="/orders"
            className="inline-block rounded-md border border-transparent bg-pink-600 px-6 py-3 text-base font-medium text-white hover:bg-pink-700"
          >
            Back to Orders
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div className="space-y-2 pb-5 sm:flex sm:items-baseline sm:justify-between sm:space-y-0 sm:pb-0">
        <div className="space-y-1">
          <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
            Order #{order.id}
          </h1>
          <p className="text-sm text-gray-500">
            Placed on {new Date(order.created_at).toLocaleDateString()}
          </p>
        </div>
        <div className="flex">
          <Link
            href="/orders"
            className="flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
          >
            Back to orders
          </Link>
        </div>
      </div>

      <div className="mt-6">
        <div className="space-y-8">
          <div className="border-t border-gray-200 pt-6">
            <h2 className="text-lg font-medium text-gray-900">Order Status</h2>
            <div className="mt-2 flex flex-col gap-y-6 text-sm sm:flex-row sm:gap-x-6">
              <div className="flex-auto">
                <div className="flex mt-2">
                  <span className={`inline-flex rounded-full px-3 py-1 text-sm font-semibold 
                    ${order.status === 'completed' ? 'bg-green-100 text-green-800' : 
                      order.status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                      order.status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                      'bg-yellow-100 text-yellow-800'}`}>
                    {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div className="border-t border-gray-200 pt-6">
            <h2 className="text-lg font-medium text-gray-900">Order Items</h2>
            <div className="mt-4 rounded-lg border border-gray-200 bg-white shadow-sm">
              <ul className="divide-y divide-gray-200">
                {order.items.map((item) => (
                  <li key={item.id} className="flex py-6 px-4 sm:px-6">
                    <div className="h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 relative">
                      <Image
                        src={item.product?.image_url || "/product-placeholder.jpg"}
                        alt={item.product?.name || `Product #${item.product_id}`}
                        fill
                        className="object-contain object-center p-2"
                      />
                    </div>
                    <div className="ml-6 flex flex-1 flex-col">
                      <div className="flex justify-between">
                        <div className="pr-6">
                          <h3 className="text-sm font-medium text-gray-900">
                            <Link href={`/products/${item.product_id}`}>
                              {item.product?.name || `Product #${item.product_id}`}
                            </Link>
                          </h3>
                        </div>
                        <p className="text-sm font-medium text-gray-900">${item.price.toFixed(2)}</p>
                      </div>
                      <div className="mt-1 flex flex-1 items-end justify-between">
                        <p className="text-sm text-gray-500">Qty {item.quantity}</p>
                        <p className="text-sm font-medium text-gray-900">
                          ${(item.price * item.quantity).toFixed(2)}
                        </p>
                      </div>
                    </div>
                  </li>
                ))}
              </ul>

              <dl className="space-y-6 border-t border-gray-200 py-6 px-4 sm:px-6">
                <div className="flex items-center justify-between border-t border-gray-200 pt-6">
                  <dt className="text-base font-medium">Total</dt>
                  <dd className="text-base font-medium text-gray-900">
                    ${order.total_price.toFixed(2)}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}