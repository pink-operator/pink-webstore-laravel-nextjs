'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { apiClient } from '@/lib/api/client';
import Link from 'next/link';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { CategorySelector } from '@/components/ui/CategorySelector';

interface ProductFormData {
  name: string;
  description: string;
  price: string;
  original_price: string;
  stock_quantity: string;
  featured: boolean;
  image_url: string;
  rating: string;
  rating_count: string;
  category_ids: number[];
}

export default function CreateProductPage() {
  const router = useRouter();
  const [formData, setFormData] = useState<ProductFormData>({
    name: '',
    description: '',
    price: '',
    original_price: '',
    stock_quantity: '',
    featured: false,
    image_url: '',
    rating: '0',
    rating_count: '0',
    category_ids: [],
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    
    // Handle checkbox inputs
    if (type === 'checkbox') {
      const checkbox = e.target as HTMLInputElement;
      setFormData({
        ...formData,
        [name]: checkbox.checked,
      });
    } else {
      setFormData({
        ...formData,
        [name]: value,
      });
    }
    
    // Clear error for this field
    if (errors[name]) {
      setErrors({
        ...errors,
        [name]: '',
      });
    }
  };

  const validate = (): boolean => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required';
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Description is required';
    }
    
    if (!formData.price.trim()) {
      newErrors.price = 'Price is required';
    } else if (isNaN(parseFloat(formData.price)) || parseFloat(formData.price) <= 0) {
      newErrors.price = 'Price must be a positive number';
    }
    
    if (formData.original_price.trim() && (isNaN(parseFloat(formData.original_price)) || parseFloat(formData.original_price) <= 0)) {
      newErrors.original_price = 'Original price must be a positive number';
    }
    
    if (!formData.stock_quantity.trim()) {
      newErrors.stock_quantity = 'Stock quantity is required';
    } else if (isNaN(parseInt(formData.stock_quantity)) || parseInt(formData.stock_quantity) < 0) {
      newErrors.stock_quantity = 'Stock quantity must be a non-negative integer';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validate()) {
      return;
    }
    
    try {
      setLoading(true);
      
      // Convert form data to appropriate types
      const productData = {
        name: formData.name,
        description: formData.description,
        price: parseFloat(formData.price),
        original_price: formData.original_price ? parseFloat(formData.original_price) : null,
        stock_quantity: parseInt(formData.stock_quantity),
        featured: formData.featured,
        image_url: formData.image_url || null,
        rating: formData.rating ? parseFloat(formData.rating) : 0,
        rating_count: formData.rating_count ? parseInt(formData.rating_count) : 0,
        category_ids: formData.category_ids,
      };
      
      await apiClient.post('/products', productData);
      
      router.push('/admin/products');
    } catch (err: any) {
      console.error('Error creating product:', err);
      
      // Handle validation errors from the server
      if (err.response?.data?.errors) {
        const serverErrors: Record<string, string> = {};
        const errorData = err.response.data.errors;
        
        Object.keys(errorData).forEach(key => {
          serverErrors[key] = Array.isArray(errorData[key]) 
            ? errorData[key][0] 
            : errorData[key];
        });
        
        setErrors(serverErrors);
      } else {
        alert('Failed to create product. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <div className="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-gray-900">Create Product</h1>
          <p className="mt-1 text-sm text-gray-500">Add a new product to your inventory.</p>
        </div>
        <Link
          href="/admin/products"
          className="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
        >
          <ArrowLeftIcon className="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
          Back to Products
        </Link>
      </div>

      <form onSubmit={handleSubmit} className="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div className="px-4 py-6 sm:p-8">
          <div className="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div className="sm:col-span-4">
              <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                Product Name
              </label>
              <div className="mt-2">
                <input
                  type="text"
                  name="name"
                  id="name"
                  value={formData.name}
                  onChange={handleChange}
                  className={`block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 ${errors.name ? 'ring-red-500' : ''}`}
                />
                {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
              </div>
            </div>

            <div className="col-span-full">
              <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                Description
              </label>
              <div className="mt-2">
                <textarea
                  id="description"
                  name="description"
                  rows={3}
                  value={formData.description}
                  onChange={handleChange}
                  className={`block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 ${errors.description ? 'ring-red-500' : ''}`}
                />
                {errors.description && <p className="mt-2 text-sm text-red-600">{errors.description}</p>}
              </div>
            </div>

            <div className="sm:col-span-2">
              <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                Price
              </label>
              <div className="mt-2">
                <div className="relative">
                  <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span className="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input
                    type="text"
                    name="price"
                    id="price"
                    value={formData.price}
                    onChange={handleChange}
                    className={`block w-full rounded-md border-0 py-1.5 pl-7 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 ${errors.price ? 'ring-red-500' : ''}`}
                    placeholder="0.00"
                  />
                </div>
                {errors.price && <p className="mt-2 text-sm text-red-600">{errors.price}</p>}
              </div>
            </div>

            <div className="sm:col-span-2">
              <label htmlFor="original_price" className="block text-sm font-medium leading-6 text-gray-900">
                Original Price (Optional)
              </label>
              <div className="mt-2">
                <div className="relative">
                  <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span className="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input
                    type="text"
                    name="original_price"
                    id="original_price"
                    value={formData.original_price}
                    onChange={handleChange}
                    className={`block w-full rounded-md border-0 py-1.5 pl-7 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 ${errors.original_price ? 'ring-red-500' : ''}`}
                    placeholder="0.00"
                  />
                </div>
                {errors.original_price && <p className="mt-2 text-sm text-red-600">{errors.original_price}</p>}
              </div>
            </div>

            <div className="sm:col-span-2">
              <label htmlFor="stock_quantity" className="block text-sm font-medium leading-6 text-gray-900">
                Stock Quantity
              </label>
              <div className="mt-2">
                <input
                  type="text"
                  name="stock_quantity"
                  id="stock_quantity"
                  value={formData.stock_quantity}
                  onChange={handleChange}
                  className={`block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6 ${errors.stock_quantity ? 'ring-red-500' : ''}`}
                  placeholder="0"
                />
                {errors.stock_quantity && <p className="mt-2 text-sm text-red-600">{errors.stock_quantity}</p>}
              </div>
            </div>

            <div className="sm:col-span-4">
              <label htmlFor="image_url" className="block text-sm font-medium leading-6 text-gray-900">
                Image URL (Optional)
              </label>
              <div className="mt-2">
                <input
                  type="text"
                  name="image_url"
                  id="image_url"
                  value={formData.image_url}
                  onChange={handleChange}
                  className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"
                  placeholder="https://example.com/image.jpg"
                />
              </div>
            </div>

            <div className="sm:col-span-2">
              <label htmlFor="rating" className="block text-sm font-medium leading-6 text-gray-900">
                Rating (0-5)
              </label>
              <div className="mt-2">
                <input
                  type="text"
                  name="rating"
                  id="rating"
                  value={formData.rating}
                  onChange={handleChange}
                  className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"
                  placeholder="0"
                />
              </div>
            </div>

            <div className="sm:col-span-2">
              <label htmlFor="rating_count" className="block text-sm font-medium leading-6 text-gray-900">
                Rating Count
              </label>
              <div className="mt-2">
                <input
                  type="text"
                  name="rating_count"
                  id="rating_count"
                  value={formData.rating_count}
                  onChange={handleChange}
                  className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-pink-600 sm:text-sm sm:leading-6"
                  placeholder="0"
                />
              </div>
            </div>

            <div className="sm:col-span-4">
              <label className="block text-sm font-medium leading-6 text-gray-900">
                Categories
              </label>
              <div className="mt-2">
                <CategorySelector
                  selectedCategories={formData.category_ids}
                  onChange={(categoryIds) => setFormData({ ...formData, category_ids: categoryIds })}
                />
              </div>
            </div>

            <div className="sm:col-span-2">
              <div className="relative flex gap-x-3">
                <div className="flex h-6 items-center">
                  <input
                    id="featured"
                    name="featured"
                    type="checkbox"
                    checked={formData.featured}
                    onChange={handleChange}
                    className="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-600"
                  />
                </div>
                <div className="text-sm leading-6">
                  <label htmlFor="featured" className="font-medium text-gray-900">
                    Featured Product
                  </label>
                  <p className="text-gray-500">Show this product in featured sections.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
          <Link
            href="/admin/products"
            className="text-sm font-semibold leading-6 text-gray-900"
          >
            Cancel
          </Link>
          <button
            type="submit"
            disabled={loading}
            className="rounded-md bg-pink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 disabled:bg-pink-300 disabled:cursor-not-allowed"
          >
            {loading ? 'Creating...' : 'Create Product'}
          </button>
        </div>
      </form>
    </div>
  );
}