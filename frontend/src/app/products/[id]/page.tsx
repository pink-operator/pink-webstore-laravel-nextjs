'use client';

import { useState, useEffect } from 'react';
import { apiClient } from '@/lib/api/client';
import { Product } from '@/types/models';
import { StarIcon } from '@heroicons/react/20/solid';
import { useCartStore } from '@/store/cart';
import Image from 'next/image';
import Link from 'next/link';
import { useParams } from 'next/navigation';

export default function ProductDetailPage() {
  const params = useParams();
  const productId = params.id;
  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [quantity, setQuantity] = useState(1);
  
  const addItem = useCartStore(state => state.addItem);

  useEffect(() => {
    async function fetchProduct() {
      try {
        setLoading(true);
        const response = await apiClient.get(`/products/${productId}`);
        setProduct(response.data.data);
        setError(null);
      } catch (err) {
        setError('Failed to load product details');
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    if (productId) {
      fetchProduct();
    }
  }, [productId]);

  const handleAddToCart = () => {
    if (product && product.stock_quantity > 0) {
      for (let i = 0; i < quantity; i++) {
        addItem(product);
      }
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[50vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-pink-500"></div>
      </div>
    );
  }

  if (error || !product) {
    return (
      <div className="text-center py-12">
        <h2 className="text-2xl font-bold text-gray-900">Error</h2>
        <p className="mt-4 text-gray-500">{error || 'Product not found'}</p>
      </div>
    );
  }

  const isDiscounted = product.original_price && product.original_price > product.price;

  return (
    <div className="bg-white">
      <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div className="lg:grid lg:grid-cols-2 lg:gap-x-8">
          {/* Product Image */}
          <div className="aspect-square overflow-hidden rounded-lg bg-gray-100 relative">
            <Image 
              src={product.image_url || '/product-placeholder.jpg'} 
              alt={product.name}
              fill
              className="object-contain object-center p-4"
            />
            {product.featured && (
              <div className="absolute top-4 left-4 z-10">
                <span className="inline-flex items-center rounded-full bg-purple-600 px-2.5 py-0.5 text-xs font-medium text-white">
                  genius
                </span>
              </div>
            )}
          </div>

          {/* Product Details */}
          <div className="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
            <h1 className="text-3xl font-bold tracking-tight text-gray-900">{product.name}</h1>
            
            {/* Categories */}
            {product.categories && product.categories.length > 0 && (
              <div className="mt-3 flex flex-wrap gap-2">
                {product.categories.map((category) => (
                  <Link
                    key={category.id}
                    href={`/products?category=${category.slug}`}
                    className="inline-flex items-center rounded-full bg-gray-100 px-3 py-0.5 text-sm font-medium text-gray-800 hover:bg-gray-200 transition-colors duration-200"
                  >
                    {category.name}
                  </Link>
                ))}
              </div>
            )}

            <div className="mt-3">
              <h2 className="sr-only">Product information</h2>
              <div className="flex items-center">
                <p className="text-3xl font-bold text-gray-900">${product.price.toFixed(2)}</p>
                {isDiscounted && (
                  <p className="ml-3 text-lg text-gray-500 line-through">
                    ${product.original_price?.toFixed(2)}
                  </p>
                )}
              </div>
              {isDiscounted && (
                <span className="inline-flex mt-2 items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  MyWallet
                </span>
              )}
            </div>

            {/* Ratings */}
            <div className="mt-4">
              <div className="flex items-center">
                <div className="flex items-center">
                  {[0, 1, 2, 3, 4].map((rating) => (
                    <StarIcon
                      key={rating}
                      className={`h-5 w-5 flex-shrink-0 ${
                        rating < product.rating ? 'text-yellow-400' : 'text-gray-200'
                      }`}
                    />
                  ))}
                </div>
                <p className="ml-3 text-sm text-gray-500">{product.rating_count} reviews</p>
              </div>
            </div>

            {/* Stock information */}
            <div className="mt-4">
              <span className={`${product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'} font-medium`}>
                {product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock'}
              </span>
              {product.stock_quantity > 0 && product.stock_quantity <= 5 && (
                <span className="ml-2 text-yellow-600">
                  Only {product.stock_quantity} left
                </span>
              )}
            </div>

            {/* Description */}
            <div className="mt-6">
              <h3 className="sr-only">Description</h3>
              <div className="prose prose-sm max-w-none text-gray-500">
                {product.description}
              </div>
            </div>

            {/* Quantity selection */}
            {product.stock_quantity > 0 && (
              <div className="mt-8">
                <div className="flex items-center">
                  <label htmlFor="quantity" className="mr-5 text-sm font-medium text-gray-700">
                    Quantity
                  </label>
                  <select
                    id="quantity"
                    name="quantity"
                    value={quantity}
                    onChange={(e) => setQuantity(Number(e.target.value))}
                    className="max-w-full rounded-md border border-gray-300 py-1.5 text-left text-base font-medium text-gray-700 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500 sm:text-sm"
                  >
                    {[...Array(Math.min(10, product.stock_quantity))].map((_, idx) => (
                      <option key={idx} value={idx + 1}>
                        {idx + 1}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            )}

            {/* Add to cart button */}
            <div className="mt-8 flex">
              <button
                type="button"
                onClick={handleAddToCart}
                disabled={product.stock_quantity === 0}
                className="flex max-w-xs flex-1 items-center justify-center rounded-md border border-transparent bg-pink-600 py-3 px-8 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 focus:ring-offset-gray-50 disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}