'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { apiClient } from '@/lib/api/client';
import { Product } from '@/types/models';
import { Pagination } from '@/components/ui/Pagination';
import { ProductFilters } from '@/components/ui/ProductFilters';
import { EmptyState } from '@/components/ui/EmptyState';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';

interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export default function AdminProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [deleting, setDeleting] = useState<number | null>(null);

  const [requestParams, setRequestParams] = useState<Record<string, any>>({
    page: 1
  });
  
  // Update request params when filters or page changes
  useEffect(() => {
    setRequestParams({
      ...filters,
      page: currentPage,
      per_page: 10
    });
  }, [filters, currentPage]);
  
  // Fetch products when requestParams change
  useEffect(() => {
    fetchProducts();
  }, [requestParams]);

  async function fetchProducts() {
    try {
      setLoading(true);
      const response = await apiClient.get<PaginatedResponse<Product>>('/products', {
        params: requestParams
      });
      
      setProducts(response.data.data);
      setTotalPages(response.data.meta?.last_page || 1);
      setError(null);
    } catch (err) {
      console.error('Error fetching products:', err);
      setError('Failed to load products');
    } finally {
      setLoading(false);
    }
  }

  const handleFilterChange = (newFilters: Record<string, any>) => {
    setCurrentPage(1);
    setFilters(newFilters);
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleDeleteProduct = async (productId: number) => {
    if (!confirm('Are you sure you want to delete this product?')) {
      return;
    }
    
    try {
      setDeleting(productId);
      await apiClient.delete(`/products/${productId}`);
      setProducts(products.filter(p => p.id !== productId));
    } catch (err) {
      console.error('Error deleting product:', err);
      alert('Failed to delete product');
    } finally {
      setDeleting(null);
    }
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold tracking-tight text-gray-900">Products</h1>
        <Link
          href="/admin/products/create"
          className="inline-flex items-center rounded-md bg-pink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600"
        >
          <PlusIcon className="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
          Add Product
        </Link>
      </div>

      <ProductFilters onFilterChange={handleFilterChange} initialFilters={filters} />

      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
        </div>
      ) : error ? (
        <div className="text-center py-12">
          <p className="text-red-500">{error}</p>
        </div>
      ) : products.length > 0 ? (
        <>
          <div className="mt-8 flow-root">
            <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                  <table className="min-w-full divide-y divide-gray-300">
                    <thead className="bg-gray-50">
                      <tr>
                        <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                          Name
                        </th>
                        <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                          Price
                        </th>
                        <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                          Stock
                        </th>
                        <th scope="col" className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                          Featured
                        </th>
                        <th scope="col" className="relative py-3.5 pl-3 pr-4 sm:pr-6">
                          <span className="sr-only">Actions</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 bg-white">
                      {products.map((product) => (
                        <tr key={product.id}>
                          <td className="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {product.name}
                          </td>
                          <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            ${product.price}
                            {product.original_price && (
                              <span className="ml-2 line-through text-gray-400">
                                ${product.original_price}
                              </span>
                            )}
                          </td>
                          <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            <span className={product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'}>
                              {product.stock_quantity}
                            </span>
                          </td>
                          <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {product.featured ? (
                              <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                Yes
                              </span>
                            ) : (
                              <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                No
                              </span>
                            )}
                          </td>
                          <td className="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <div className="flex justify-end space-x-2">
                              <Link
                                href={`/admin/products/edit/${product.id}`}
                                className="text-indigo-600 hover:text-indigo-900"
                              >
                                <PencilIcon className="h-5 w-5" aria-hidden="true" />
                                <span className="sr-only">Edit {product.name}</span>
                              </Link>
                              <button
                                onClick={() => handleDeleteProduct(product.id)}
                                disabled={deleting === product.id}
                                className="text-red-600 hover:text-red-900"
                              >
                                {deleting === product.id ? (
                                  <div className="h-5 w-5 rounded-full border-t-2 border-b-2 border-red-500 animate-spin" />
                                ) : (
                                  <TrashIcon className="h-5 w-5" aria-hidden="true" />
                                )}
                                <span className="sr-only">Delete {product.name}</span>
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          {totalPages > 1 && (
            <div className="mt-8">
              <Pagination
                currentPage={currentPage}
                totalPages={totalPages}
                onPageChange={handlePageChange}
              />
            </div>
          )}
        </>
      ) : (
        <EmptyState
          title="No products found"
          description="Get started by creating a new product."
          actionText="Add Product"
          actionUrl="/admin/products/create"
        />
      )}
    </div>
  );
}