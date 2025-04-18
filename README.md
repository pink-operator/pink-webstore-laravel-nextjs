# Pink Store E-Commerce API

A Laravel-based RESTful API for an e-commerce platform with product management, user authentication, order processing, and admin features.

## API Documentation

The API documentation is available through Swagger UI at:
- Local development: `http://localhost:8000/api/documentation`
- Production: `https://api.pinkstore.com/api/documentation`

## SET UP
- Go to project root: **php artisan serve** - should start at http://127.0.0.1:8000
- Go to /frontend inside the project root: **npm run dev** - should start at http://localhost:3000
- Run migrations: **php artisan migrate**
- Run seeders: **php artisan db:seed**
- The .env.example is the exact same as my own
- Run the 73 tests (384 assertions) on all the endpoints: **php artisan test**
- Another way to list routes: **php artisan route:list**

## Project Structure

### Controllers

#### Authentication
- `AuthController` - Handles user registration, login, logout and user info
  - POST `/api/auth/register` - Register new user (creates account and sends verification email) - 2 tests, 5 assertions
  - POST `/api/auth/login` - User login (returns auth token for API access) - 2 tests, 4 assertions
  - POST `/api/auth/logout` - User logout (invalidates current token) - 1 test, 2 assertions
  - GET `/api/auth/user` - Get authenticated user info (requires valid token) - 2 tests, 3 assertions
- `PasswordResetController` - Manages password reset functionality
  - POST `/api/auth/forgot-password` - Request password reset link (emails reset token to user) - 2 tests, 2 assertions
  - POST `/api/auth/reset-password` - Reset password with token (validates token and updates password) - 3 tests, 4 assertions
- `VerificationController` - Handles email verification
  - GET `/api/email/verify/{id}/{hash}` - Verify user's email address - 2 tests, 3 assertions
  - POST `/api/auth/email/verification-notification` - Resend verification email - 1 test, 2 assertions

#### Profile Management
- `ProfileController` - Manages user profile data
  - PUT `/api/auth/profile` - Update user profile information - 3 tests, 5 assertions
  - DELETE `/api/auth/profile` - Delete user account - 2 tests, 3 assertions

#### Products
- `ProductController` - Manages product catalog
  - GET `/api/products` - List products with filters (public, supports pagination and category filtering) - 4 tests, 8 assertions
  - GET `/api/products/search` - Search for products by name or description - 1 test, 3 assertions
  - GET `/api/products/{id}` - Get single product details (public) - 2 tests, 3 assertions
  - POST `/api/products` - Create product (admin only, includes validation) - 5 tests, 12 assertions
  - PUT `/api/products/{id}` - Update product (admin only, includes validation) - 4 tests, 8 assertions
  - DELETE `/api/products/{id}` - Delete product (admin only, handles relationships) - 2 tests, 4 assertions

#### Orders
- `OrderController` - Handles order processing
  - GET `/api/orders` - List orders (all for admin, own for customers) - 3 tests, 6 assertions
  - POST `/api/orders` - Create new order (validates product availability and calculates totals) - 5 tests, 10 assertions
  - GET `/api/orders/{id}` - Get order details (includes items and status) - 3 tests, 5 assertions
  - PATCH `/api/orders/{id}/status` - Update order status (admin only, includes status validation) - 3 tests, 6 assertions

#### Categories
- `CategoryController` - Manages product categories
  - GET `/api/categories` - List all categories (public) - 1 test, 3 assertions
  - GET `/api/categories/{id}` - Get category details with associated products - 3 tests, 5 assertions
  - POST `/api/categories` - Create category (admin only) - 3 tests, 7 assertions
  - PUT `/api/categories/{id}` - Update category (admin only) - 4 tests, 6 assertions
  - DELETE `/api/categories/{id}` - Delete category (admin only, handles product relationships) - 3 tests, 5 assertions

### Main Workflows

#### User Registration and Authentication
1. User registers → Verification email sent → User verifies email → User can log in
2. User logs in → Receives token → Uses token for authenticated requests
3. Forgotten password → Request reset → Receive email → Reset password → Login with new password

#### Product Management (Admin)
1. Admin creates categories → Admin adds products to categories → Products appear in catalog
2. Admin can update or delete products and categories as needed

#### Shopping Experience (Customer)
1. Browse products → Search or filter by category → View product details
2. Add products to cart → Create order → View order history and status

#### Order Management (Admin)
1. View all orders → Update order status → Customer sees updated status

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

## Development

- We are using PostgreSQL as the database backend
- PHP Artisan commands are used for Laravel CLI operations (no Brew)
- Email verification and password reset functionality are implemented
- API routes are protected with appropriate middleware for authentication and authorization
- Tests are implemented for all major features including the password reset workflow
