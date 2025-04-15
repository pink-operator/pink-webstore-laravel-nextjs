'use client';

import { useState, useEffect } from 'react';

interface PriceRange {
  min_price: string;
  max_price: string;
}

interface ProductFiltersProps {
  onFilterChange: (filters: Record<string, any>) => void;
  initialFilters?: Record<string, any>;
}

export function ProductFilters({ onFilterChange, initialFilters = {} }: ProductFiltersProps) {
  const [priceRange, setPriceRange] = useState<PriceRange>({
    min_price: initialFilters.min_price || '',
    max_price: initialFilters.max_price || '',
  });
  const [inStock, setInStock] = useState<boolean>(!!initialFilters.in_stock);
  const [searchTerm, setSearchTerm] = useState<string>(initialFilters.search || '');
  const [debouncedPriceRange, setDebouncedPriceRange] = useState<PriceRange>(priceRange);

  // Debounce price range changes
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedPriceRange(priceRange);
    }, 500);

    return () => {
      clearTimeout(handler);
    };
  }, [priceRange]);
  
  // Debounce search term
  const [debouncedSearchTerm, setDebouncedSearchTerm] = useState(searchTerm);
  
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedSearchTerm(searchTerm);
    }, 500);

    return () => {
      clearTimeout(handler);
    };
  }, [searchTerm]);

  // Call onFilterChange when debounced values change
  useEffect(() => {
    const filters: Record<string, any> = {};
    
    if (debouncedPriceRange.min_price) {
      filters.min_price = debouncedPriceRange.min_price;
    }
    
    if (debouncedPriceRange.max_price) {
      filters.max_price = debouncedPriceRange.max_price;
    }
    
    if (inStock) {
      filters.in_stock = true;
    }
    
    if (debouncedSearchTerm) {
      filters.search = debouncedSearchTerm;
    }
    
    onFilterChange(filters);
    // Removed onFilterChange from dependency array to prevent infinite loop
  }, [debouncedPriceRange, inStock, debouncedSearchTerm]);

  return (
    <div className="bg-white shadow-sm rounded-lg p-6">
      <h2 className="text-lg font-medium text-gray-900 mb-4">Filters</h2>
      
      {/* Search box */}
      <div className="mb-6">
        <label htmlFor="search" className="block text-sm font-medium text-gray-700">
          Search Products
        </label>
        <div className="mt-1">
          <input
            type="text"
            id="search"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Enter product name..."
            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
          />
        </div>
      </div>
      
      <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div>
          <label htmlFor="min_price" className="block text-sm font-medium text-gray-700">
            Min Price
          </label>
          <div className="mt-1 relative rounded-md shadow-sm">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span className="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              type="number"
              id="min_price"
              value={priceRange.min_price}
              onChange={(e) => setPriceRange({ ...priceRange, min_price: e.target.value })}
              className="mt-1 block w-full pl-7 pr-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
              placeholder="0"
              min="0"
            />
          </div>
        </div>
        <div>
          <label htmlFor="max_price" className="block text-sm font-medium text-gray-700">
            Max Price
          </label>
          <div className="mt-1 relative rounded-md shadow-sm">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span className="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              type="number"
              id="max_price"
              value={priceRange.max_price}
              onChange={(e) => setPriceRange({ ...priceRange, max_price: e.target.value })}
              className="mt-1 block w-full pl-7 pr-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
              placeholder="1000"
              min="0"
            />
          </div>
        </div>
        <div className="flex items-center">
          <input
            type="checkbox"
            id="in_stock"
            checked={inStock}
            onChange={(e) => setInStock(e.target.checked)}
            className="h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
          />
          <label htmlFor="in_stock" className="ml-2 block text-sm text-gray-900">
            In Stock Only
          </label>
        </div>
      </div>
    </div>
  );
}