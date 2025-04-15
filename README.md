# Pink Store E-Commerce API

This is the API backend for the Pink Store e-commerce platform.

## API Documentation

The API documentation is available through Swagger UI at:
- Local development: `http://localhost:8000/api/documentation`
- Production: `https://api.pinkstore.com/api/documentation`

### Security Features

The API implements several security measures:

- **Authentication**: Bearer token authentication using Laravel Sanctum
- **CSRF Protection**: Required for all mutation operations
- **Rate Limiting**:
  - Public API: 30 requests per minute
  - Authenticated users: 60 requests per minute
  - Authentication endpoints: 5 requests per minute
  - Admin endpoints: 120 requests per minute for admins, 5 for others
- **Security Headers**:
  - Content Security Policy (CSP)
  - X-Content-Type-Options
  - X-Frame-Options
  - X-XSS-Protection
  - Strict-Transport-Security
  - Referrer-Policy
  - Permissions-Policy
- **CORS**: Configured for secure cross-origin requests

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
