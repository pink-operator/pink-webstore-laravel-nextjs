'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { CategoryForm } from '@/components/ui/CategoryForm';
import { apiClient } from '@/lib/api/client';

export default function CreateCategoryPage() {
  const router = useRouter();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (formData: FormData) => {
    try {
      setIsSubmitting(true);
      await apiClient.post('/categories', formData);
      router.push('/admin/categories');
    } catch (error) {
      console.error('Failed to create category:', error);
      alert('Failed to create category. Please try again.');
      setIsSubmitting(false);
    }
  };

  return (
    <div className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Create Category</h1>
        <p className="mt-2 text-sm text-gray-600">
          Add a new category to organize your products.
        </p>
      </div>

      <div className="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div className="px-4 py-6 sm:p-8">
          <CategoryForm onSubmit={handleSubmit} isSubmitting={isSubmitting} />
        </div>
      </div>
    </div>
  );
}