'use client';

import { AdminNavbar } from '@/components/ui/AdminNavbar';
import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { apiClient } from '@/lib/api/client';
import { User } from '@/types/models';

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const router = useRouter();
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function checkAdminAccess() {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          router.push('/auth/login');
          return;
        }

        const response = await apiClient.get('/auth/user');
        const user = response.data.data as User;
        
        if (user.role !== 'admin') {
          router.push('/auth/login');
          return;
        }
        
        setLoading(false);
      } catch (err) {
        console.error('Error checking admin access:', err);
        router.push('/auth/login');
      }
    }

    checkAdminAccess();
  }, [router]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
      </div>
    );
  }

  return (
    <div>
      <AdminNavbar />
      <div className="lg:pl-72">
        <main className="py-10">
          <div className="px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}