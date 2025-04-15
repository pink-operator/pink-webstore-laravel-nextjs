'use client';

import { useState, useEffect } from 'react';
import { Category } from '@/types/models';
import { apiClient } from '@/lib/api/client';

interface CategorySelectorProps {
  selectedCategories: number[];
  onChange: (categoryIds: number[]) => void;
}

export function CategorySelector({ selectedCategories, onChange }: CategorySelectorProps) {
  const [categories, setCategories] = useState<Category[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    async function fetchCategories() {
      try {
        setIsLoading(true);
        const response = await apiClient.get('/categories');
        setCategories(response.data.data);
      } catch (error) {
        console.error('Failed to fetch categories:', error);
      } finally {
        setIsLoading(false);
      }
    }

    fetchCategories();
  }, []);

  if (isLoading) {
    return (
      <div className="flex items-center space-x-2">
        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-pink-600"></div>
        <span className="text-sm text-gray-500">Loading categories...</span>
      </div>
    );
  }

  return (
    <div className="space-y-2">
      {categories.map((category) => (
        <div key={category.id} className="flex items-center">
          <input
            type="checkbox"
            id={`category-${category.id}`}
            checked={selectedCategories.includes(category.id)}
            onChange={(e) => {
              if (e.target.checked) {
                onChange([...selectedCategories, category.id]);
              } else {
                onChange(selectedCategories.filter(id => id !== category.id));
              }
            }}
            className="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
          />
          <label htmlFor={`category-${category.id}`} className="ml-2 block text-sm text-gray-900">
            {category.name}
          </label>
        </div>
      ))}
      {categories.length === 0 && (
        <p className="text-sm text-gray-500">No categories available.</p>
      )}
    </div>
  );
}