import Image from "next/image";
import Link from 'next/link';
import { Product } from '@/types/models';
import { ProductCard } from '@/components/ui/ProductCard';

async function getFeaturedProducts() {
  try {
    // Use our API proxy with the corrected URL
    const apiUrl = `/api/proxy/products?featured=true`;
    console.log(`Fetching featured products from: ${apiUrl}`);
    
    const response = await fetch(apiUrl, {
      cache: 'no-store', // Disable caching for now
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      console.error('Failed to fetch products:', await response.text());
      throw new Error(`Failed to fetch products: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Featured products response:', data);
    return (data.data || []).slice(0, 4) as Product[];
  } catch (error) {
    console.error('Error fetching featured products:', error);
    return [];
  }
}

export default async function Home() {
  const featuredProducts = await getFeaturedProducts();

  return (
    <div className="space-y-12">
      {/* Hero Section */}
      <div className="relative">
        <div className="mx-auto max-w-7xl">
          <div className="relative z-10 pt-14 lg:w-full lg:max-w-2xl">
            <div className="relative px-6 py-32 sm:py-40 lg:px-8 lg:py-56">
              <div className="mx-auto max-w-2xl lg:mx-0">
                <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                  Your One-Stop Tech Shop
                </h1>
                <p className="mt-6 text-lg leading-8 text-gray-600">
                  Discover amazing deals on the latest tech products. From laptops to accessories,
                  we've got everything you need.
                </p>
                <div className="mt-10 flex items-center gap-x-6">
                  <Link
                    href="/products"
                    className="rounded-md bg-pink-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600"
                  >
                    Browse Products
                  </Link>
                  <Link href="/featured" className="text-sm font-semibold leading-6 text-gray-900">
                    View Featured <span aria-hidden="true">→</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Featured Products Section */}
      <div className="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl">
        <div className="md:flex md:items-center md:justify-between">
          <h2 className="text-2xl font-bold tracking-tight text-gray-900">Featured Products</h2>
          <Link 
            href="/products" 
            className="hidden text-sm font-medium text-pink-600 hover:text-pink-500 md:block"
          >
            Shop the collection
            <span aria-hidden="true"> →</span>
          </Link>
        </div>

        <div className="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
          {featuredProducts.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>

        <div className="mt-8 text-sm md:hidden">
          <Link href="/products" className="font-medium text-pink-600 hover:text-pink-500">
            Shop the collection
            <span aria-hidden="true"> →</span>
          </Link>
        </div>
      </div>
    </div>
  );
}
