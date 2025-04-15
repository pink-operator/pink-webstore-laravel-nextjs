'use client';

import { useState, useEffect } from 'react';
import { Category } from '@/types/models';
import { apiClient } from '@/lib/api/client';

interface CategoryFilterProps {
  onCategoryChange: (categorySlug: string | null) => void;
  selectedCategory?: string | null;
}

export function CategoryFilter({ onCategoryChange, selectedCategory }: CategoryFilterProps) {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchCategories() {
      try {
        const response = await apiClient.get('/categories');
        setCategories(response.data.data);
      } catch (error) {
        console.error('Failed to fetch categories:', error);
      } finally {
        setLoading(false);
      }
    }

    fetchCategories();
  }, []);

  if (loading) {
    return <div className="animate-pulse h-10 bg-gray-200 rounded"></div>;
  }

  return (
    <div className="flex flex-wrap gap-2">
      <button
        onClick={() => onCategoryChange(null)}
        className={`px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200
          ${!selectedCategory 
            ? 'bg-pink-600 text-white' 
            : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          }`}
      >
        All
      </button>
      {categories.map((category) => (
        <button
          key={category.id}
          onClick={() => onCategoryChange(category.slug)}
          className={`px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200
            ${selectedCategory === category.slug
              ? 'bg-pink-600 text-white'
              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
            }`}
        >
          {category.name}
          {category.products_count !== undefined && (
            <span className="ml-1 text-xs">({category.products_count})</span>
          )}
        </button>
      ))}
    </div>
  );
}