'use client';

import { ProductCard } from '@/components/ui/ProductCard';
import { CategoryFilter } from '@/components/ui/CategoryFilter';
import { apiClient } from '@/lib/api/client';
import { Product } from '@/types/models';
import { useState, useEffect } from 'react';

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [filters, setFilters] = useState({
    min_price: '',
    max_price: '',
    in_stock: false,
    category: null as string | null,
  });

  useEffect(() => {
    async function fetchProducts() {
      try {
        setIsLoading(true);
        const params = new URLSearchParams();
        
        if (filters.min_price) params.append('min_price', filters.min_price);
        if (filters.max_price) params.append('max_price', filters.max_price);
        if (filters.in_stock) params.append('in_stock', 'true');
        if (filters.category) params.append('category', filters.category);

        const response = await apiClient.get(`/products?${params.toString()}`);
        setProducts(response.data.data);
      } catch (error) {
        console.error('Failed to fetch products:', error);
      } finally {
        setIsLoading(false);
      }
    }

    fetchProducts();
  }, [filters]);

  return (
    <div className="space-y-6">
      {/* Category Filter */}
      <div className="bg-white shadow-sm rounded-lg p-6">
        <h2 className="text-lg font-medium text-gray-900 mb-4">Categories</h2>
        <CategoryFilter
          selectedCategory={filters.category}
          onCategoryChange={(category) => setFilters(prev => ({ ...prev, category }))}
        />
      </div>

      {/* Price and Stock Filters */}
      <div className="bg-white shadow-sm rounded-lg p-6">
        <div className="grid grid-cols-1 gap-6 md:grid-cols-4">
          <div>
            <label htmlFor="min_price" className="block text-sm font-medium text-gray-700">
              Min Price
            </label>
            <input
              type="number"
              id="min_price"
              value={filters.min_price}
              onChange={(e) => setFilters(prev => ({ ...prev, min_price: e.target.value }))}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
            />
          </div>
          <div>
            <label htmlFor="max_price" className="block text-sm font-medium text-gray-700">
              Max Price
            </label>
            <input
              type="number"
              id="max_price"
              value={filters.max_price}
              onChange={(e) => setFilters(prev => ({ ...prev, max_price: e.target.value }))}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
            />
          </div>
          <div className="flex items-center">
            <input
              type="checkbox"
              id="in_stock"
              checked={filters.in_stock}
              onChange={(e) => setFilters(prev => ({ ...prev, in_stock: e.target.checked }))}
              className="h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
            />
            <label htmlFor="in_stock" className="ml-2 block text-sm text-gray-900">
              In Stock Only
            </label>
          </div>
        </div>
      </div>

      {/* Products Grid */}
      <div className="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
        {isLoading ? (
          <div className="col-span-full flex justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-pink-600"></div>
          </div>
        ) : products.length > 0 ? (
          products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))
        ) : (
          <div className="col-span-full text-center py-12">
            <p className="text-gray-500">No products found matching your criteria.</p>
          </div>
        )}
      </div>
    </div>
  );
}