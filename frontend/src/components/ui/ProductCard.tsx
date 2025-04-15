'use client';

import Image from 'next/image';
import Link from 'next/link';
import { StarIcon } from '@heroicons/react/20/solid';
import clsx from 'clsx';
import { Product } from '@/types/models';
import { useCartStore } from '@/store/cart';

interface ProductCardProps {
  product: Product;
}

export function ProductCard({ product }: ProductCardProps) {
  const addItem = useCartStore(state => state.addItem);
  const isDiscounted = product.original_price && product.original_price > product.price;
  const rating = Math.round(product.rating);

  return (
    <div className="group relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
      {/* Image section */}
      <div className="relative aspect-h-4 aspect-w-3 bg-gray-100 rounded-t-lg overflow-hidden">
        {product.featured && (
          <div className="absolute top-2 left-2 z-10">
            <span className="inline-flex items-center rounded-full bg-purple-600 px-2.5 py-0.5 text-xs font-medium text-white">
              genius
            </span>
          </div>
        )}
        <Image
          src={product.image_url || '/product-placeholder.jpg'}
          alt={product.name}
          fill
          className="object-contain object-center p-4"
        />
      </div>

      {/* Content section */}
      <div className="p-4">
        <h3 className="text-sm font-medium text-gray-900 mb-1">
          <Link href={`/products/${product.id}`}>
            <span className="absolute inset-0" />
            {product.name}
          </Link>
        </h3>

        {/* Categories */}
        {product.categories && product.categories.length > 0 && (
          <div className="mb-2 flex flex-wrap gap-1">
            {product.categories.map((category) => (
              <span
                key={category.id}
                className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600"
              >
                {category.name}
              </span>
            ))}
          </div>
        )}

        {/* Rating */}
        <div className="mt-1 flex items-center">
          <div className="flex items-center text-yellow-400">
            {[...Array(5)].map((_, i) => (
              <StarIcon
                key={i}
                className={clsx('h-4 w-4 flex-shrink-0', i < rating ? 'text-yellow-400' : 'text-gray-200')}
              />
            ))}
          </div>
          <span className="ml-1 text-xs text-gray-500">({product.rating_count})</span>
        </div>

        {/* Price */}
        <div className="mt-2 flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-900">
              ${product.price.toFixed(2)}
            </p>
            {isDiscounted && (
              <p className="text-sm text-gray-500 line-through">${product.original_price?.toFixed(2)}</p>
            )}
          </div>
        </div>

        {/* Stock information */}
        <div className="mt-2 flex items-center text-sm">
          <span className={product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'}>
            {product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock'}
          </span>
          {product.stock_quantity > 0 && product.stock_quantity <= 5 && (
            <span className="ml-2 text-yellow-600">Only {product.stock_quantity} left</span>
          )}
        </div>

        {/* Add to cart button */}
        <button
          onClick={() => product.stock_quantity > 0 && addItem(product)}
          disabled={product.stock_quantity === 0}
          className="mt-4 w-full rounded-md bg-pink-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
        </button>
      </div>
    </div>
  );
}