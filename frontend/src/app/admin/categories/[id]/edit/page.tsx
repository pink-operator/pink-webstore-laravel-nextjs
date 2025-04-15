'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { CategoryForm } from '@/components/ui/CategoryForm';
import { apiClient } from '@/lib/api/client';
import { Category } from '@/types/models';

export default function EditCategoryPage() {
  const router = useRouter();
  const params = useParams();
  const categoryId = params.id;
  const [category, setCategory] = useState<Category | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchCategory() {
      try {
        setIsLoading(true);
        const response = await apiClient.get(`/categories/${categoryId}`);
        setCategory(response.data.data);
        setError(null);
      } catch (err) {
        setError('Failed to load category');
        console.error(err);
      } finally {
        setIsLoading(false);
      }
    }

    if (categoryId) {
      fetchCategory();
    }
  }, [categoryId]);

  const handleSubmit = async (formData: FormData) => {
    if (!category) return;

    try {
      setIsSubmitting(true);
      await apiClient.post(`/categories/${category.id}`, formData);
      router.push('/admin/categories');
    } catch (error) {
      console.error('Failed to update category:', error);
      alert('Failed to update category. Please try again.');
      setIsSubmitting(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-[50vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-pink-600"></div>
      </div>
    );
  }

  if (error || !category) {
    return (
      <div className="text-center py-12">
        <p className="text-red-600">{error || 'Category not found'}</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Edit Category</h1>
        <p className="mt-2 text-sm text-gray-600">
          Update the category information below.
        </p>
      </div>

      <div className="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div className="px-4 py-6 sm:p-8">
          <CategoryForm
            initialData={category}
            onSubmit={handleSubmit}
            isSubmitting={isSubmitting}
          />
        </div>
      </div>
    </div>
  );
}