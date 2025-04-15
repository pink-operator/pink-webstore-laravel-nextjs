# Pink Store E-Commerce API

This is the API backend for the Pink Store e-commerce platform.

## API Documentation

The API documentation is available through Swagger UI at:
- Local development: `http://localhost:8000/api/documentation`
- Production: `https://api.pinkstore.com/api/documentation`
# Pink Store E-commerce API

A Laravel-based RESTful API for an e-commerce platform with product management, user authentication, order processing, and admin features.

## SET UP
- go to project root: **php artisan serve** - should start at http://127.0.0.1:8000
- got to /frontend inside the project root: **npm run dev** - should start at http://localhost:3000
- run migrations: **php artisan migrate**
- run seeders: **php artisan db:seed **

## Project Structure

### Controllers

#### Authentication
- `AuthController` - Handles user registration, login, logout and user info
  - POST `/api/auth/register` - Register new user
  - POST `/api/auth/login` - User login 
  - POST `/api/auth/logout` - User logout
  - GET `/api/auth/user` - Get authenticated user info

#### Products
- `ProductController` - Manages product catalog
  - GET `/api/products` - List products with filters
  - GET `/api/products/{id}` - Get single product
  - POST `/api/products` - Create product (admin only)
  - PUT `/api/products/{id}` - Update product (admin only)
  - DELETE `/api/products/{id}` - Delete product (admin only)

#### Orders
- `OrderController` - Handles order processing
  - GET `/api/orders` - List orders (all for admin, own for customers)
  - POST `/api/orders` - Create new order
  - GET `/api/orders/{id}` - Get order details
  - PATCH `/api/orders/{id}/status` - Update order status (admin only)

#### Categories
- `CategoryController` - Manages product categories
  - GET `/api/categories` - List all categories
  - GET `/api/categories/{id}` - Get category details
  - POST `/api/categories` - Create category (admin only)
  - PUT `/api/categories/{id}` - Update category (admin only)
  - DELETE `/api/categories/{id}` - Delete category (admin only)

### Database Structure

#### Migrations
- `users` - User accounts and authentication
- `products` - Product catalog
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Individual items in orders
- `personal_access_tokens` - Authentication tokens

#### Seeders
- `UserSeeder` - Creates default admin and customer accounts
- `ProductSeeder` - Populates product catalog
- `CategorySeeder` - Creates product categories
- `OrderSeeder` - Generates sample orders
- `OrderItemSeeder` - Creates order line items

### Security Features

- Authentication via Laravel Sanctum
- Role-based access control (admin/customer)
- Rate limiting:
  - Auth endpoints: 5 requests/minute
  - Admin endpoints: 120 requests/minute
  - Public API: 30 requests/minute
  - Authenticated users: 60 requests/minute
- CSRF protection
- Security headers (CSP, HSTS, etc.)

### Configuration

- Database: PostgreSQL (default) with support for MySQL/SQLite
- Session: Database-backed with customizable lifetime
- Cache: Supports file and database storage
- Rate Limiting: Configurable per route/middleware
- CORS: Configured for frontend integration

### Testing

PHPUnit tests covering:
- User authentication
- Product management
- Order processing
- Admin functionality
- API rate limiting

### API Documentation

- Swagger/OpenAPI documentation available at `/api/documentation`
- Includes:
  - Endpoint descriptions
  - Request/response schemas
  - Authentication requirements
  - Rate limit information

### Front-end Integration

- Built to work with Next.js front-end
- CORS configured for localhost:3000 in development
- Proxy support for API requests
- Session handling for SPA authentication

### Rate Limit Headers

The API includes rate limit information in response headers:
- `X-RateLimit-Limit`: Maximum number of requests per window
- `X-RateLimit-Remaining`: Number of requests remaining in current window
- `X-RateLimit-Reset`: Timestamp when the rate limit window resets

## Development

To regenerate the API documentation:

```bash
php artisan l5-swagger:generate
```

The documentation will be generated in the `storage/api-docs` directory.
