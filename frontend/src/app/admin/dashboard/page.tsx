'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { apiClient } from '@/lib/api/client';
import { User } from '@/types/models';
import Link from 'next/link';

export default function AdminDashboardPage() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [stats, setStats] = useState({
    totalProducts: 0,
    totalOrders: 0,
    pendingOrders: 0,
    revenue: 0
  });

  useEffect(() => {
    async function fetchAdminData() {
      try {
        setLoading(true);
        
        // Get logged in user
        const userResponse = await apiClient.get('/auth/user');
        const userData = userResponse.data.data as User;
        
        if (userData.role !== 'admin') {
          router.push('/auth/login');
          return;
        }
        
        setUser(userData);
        
        // Get product count
        const productsResponse = await apiClient.get('/products');
        const productCount = productsResponse.data.meta?.total || productsResponse.data.data.length;
        
        // Get orders stats
        const ordersResponse = await apiClient.get('/orders');
        const orders = ordersResponse.data.data || [];
        const orderCount = ordersResponse.data.meta?.total || orders.length;
        const pendingOrders = orders.filter(order => order.status === 'pending').length;
        const revenue = orders.reduce((sum, order) => sum + parseFloat(order.total_price), 0);
        
        setStats({
          totalProducts: productCount,
          totalOrders: orderCount,
          pendingOrders,
          revenue
        });
        
        setError(null);
      } catch (err) {
        console.error('Error fetching admin data:', err);
        setError('Failed to load admin dashboard data');
        
        // If unauthorized, redirect to login
        if (err.response?.status === 401 || err.response?.status === 403) {
          localStorage.removeItem('token');
          router.push('/auth/login');
        }
      } finally {
        setLoading(false);
      }
    }

    fetchAdminData();
  }, [router]);

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[50vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <h2 className="text-2xl font-bold text-gray-900">Error</h2>
        <p className="mt-4 text-gray-500">{error}</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
      <header className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Admin Dashboard</h1>
        <p className="mt-2 text-sm text-gray-500">
          Manage your products, orders, and more.
        </p>
      </header>

      {/* Stats section */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <dt className="text-sm font-medium text-gray-500 truncate">Total Products</dt>
            <dd className="mt-1 text-3xl font-semibold text-gray-900">{stats.totalProducts}</dd>
          </div>
          <div className="bg-gray-50 px-4 py-4 sm:px-6">
            <Link 
              href="/admin/products" 
              className="text-sm font-medium text-pink-600 hover:text-pink-500"
            >
              View all products
            </Link>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <dt className="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
            <dd className="mt-1 text-3xl font-semibold text-gray-900">{stats.totalOrders}</dd>
          </div>
          <div className="bg-gray-50 px-4 py-4 sm:px-6">
            <Link 
              href="/admin/orders" 
              className="text-sm font-medium text-pink-600 hover:text-pink-500"
            >
              View all orders
            </Link>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <dt className="text-sm font-medium text-gray-500 truncate">Pending Orders</dt>
            <dd className="mt-1 text-3xl font-semibold text-gray-900">{stats.pendingOrders}</dd>
          </div>
          <div className="bg-gray-50 px-4 py-4 sm:px-6">
            <Link 
              href="/admin/orders?status=pending" 
              className="text-sm font-medium text-pink-600 hover:text-pink-500"
            >
              View pending orders
            </Link>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="px-4 py-5 sm:p-6">
            <dt className="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
            <dd className="mt-1 text-3xl font-semibold text-gray-900">${stats.revenue.toFixed(2)}</dd>
          </div>
          <div className="bg-gray-50 px-4 py-4 sm:px-6">
            <Link 
              href="/admin/dashboard" 
              className="text-sm font-medium text-pink-600 hover:text-pink-500"
            >
              View reports
            </Link>
          </div>
        </div>
      </div>

      {/* Actions section */}
      <div className="mt-8">
        <h2 className="text-lg font-medium text-gray-900">Quick Actions</h2>
        <div className="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <Link 
            href="/admin/products/create" 
            className="inline-flex items-center justify-center rounded-md border border-transparent bg-pink-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
          >
            Add New Product
          </Link>
          
          <Link 
            href="/admin/orders" 
            className="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
          >
            Manage Orders
          </Link>
          
          <Link 
            href="/admin/users" 
            className="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
          >
            Manage Users
          </Link>
        </div>
      </div>
    </div>
  );
}