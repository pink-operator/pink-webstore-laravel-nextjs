'use client';

import Link from 'next/link';
import { ShoppingBagIcon } from '@heroicons/react/24/outline';

interface EmptyStateProps {
  title: string;
  description?: string;
  icon?: React.ReactNode;
  actionText?: string;
  actionUrl?: string;
}

export function EmptyState({
  title,
  description,
  icon,
  actionText,
  actionUrl
}: EmptyStateProps) {
  return (
    <div className="text-center py-12">
      <div className="flex justify-center">
        {icon || (
          <ShoppingBagIcon className="h-16 w-16 text-gray-400" aria-hidden="true" />
        )}
      </div>
      <h3 className="mt-2 text-xl font-semibold text-gray-900">{title}</h3>
      {description && (
        <p className="mt-1 text-sm text-gray-500">{description}</p>
      )}
      {actionText && actionUrl && (
        <div className="mt-6">
          <Link
            href={actionUrl}
            className="inline-flex items-center rounded-md bg-pink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600"
          >
            {actionText}
          </Link>
        </div>
      )}
    </div>
  );
}