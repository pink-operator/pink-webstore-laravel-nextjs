'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { CartIndicator } from '@/components/ui/CartIndicator';
import { UserIcon } from '@heroicons/react/24/outline';
import { useRouter } from 'next/navigation';

export function Navbar() {
  const router = useRouter();
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem('token');
    setIsLoggedIn(!!token);
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('token');
    setIsLoggedIn(false);
    router.push('/');
  };

  return (
    <nav className="bg-white shadow-sm">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 justify-between items-center">
          <div className="flex items-center">
            <Link href="/" className="text-xl font-bold text-pink-600">
              Pink Store
            </Link>
            <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
              <Link 
                href="/products" 
                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Products
              </Link>
              <Link 
                href="/featured" 
                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Featured
              </Link>
              {isLoggedIn && (
                <Link 
                  href="/orders" 
                  className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                >
                  My Orders
                </Link>
              )}
            </div>
          </div>

          <div className="flex items-center space-x-4">
            <CartIndicator />
            
            {isLoggedIn ? (
              <div className="relative ml-3">
                <div className="flex items-center space-x-3">
                  <button
                    onClick={handleLogout}
                    className="text-sm font-medium text-gray-500 hover:text-gray-700"
                  >
                    Logout
                  </button>
                  <Link href="/profile" className="group -m-2 flex items-center p-2">
                    <UserIcon className="h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" />
                  </Link>
                </div>
              </div>
            ) : (
              <Link 
                href="/auth/login"
                className="text-sm font-medium text-gray-500 hover:text-gray-700"
              >
                Sign in
              </Link>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}