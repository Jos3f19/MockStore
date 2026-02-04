# MockStore - PlacetoPay WebCheckout Integration

A demo e-commerce store showcasing the integration with PlacetoPay WebCheckout payment gateway, built using PHP with the MVC (Model-View-Controller) architecture pattern.

## 🌟 Features

### Core Functionality
- **Complete MVC Architecture**: Clean separation of concerns with Models, Views, and Controllers
- **PlacetoPay WebCheckout Integration**: Full integration with PlacetoPay's payment API
- **Shopping Cart**: Add/remove products, view cart, checkout flow
- **Order Management**: View order history and details
- **Responsive Design**: Bootstrap 5-based UI that works on all devices
- **SQLite Database**: Lightweight, file-based database for easy setup (stored in `database/store.db`, auto-created on first run)
- **API Request Logging**: All PlacetoPay API requests are logged for debugging

## 📋 Requirements

- PHP 8.0 or higher
- PDO SQLite extension
- cURL extension
- mod_rewrite (for Apache) or equivalent URL rewriting

## 🚀 Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/Jos3f19/MockStore.git
cd MockStore
```

### 2. Configure environment variables

Copy the example environment file and add your credentials:

```bash
cp .env.example .env
```

Edit `.env` and add your PlacetoPay credentials:

```env
PLACETOPAY_LOGIN=your_login_here
PLACETOPAY_SECRET_KEY=your_secret_key_here
```

### 3. Start the PHP development server

```bash
cd pathtotheproject
php -S localhost:8000
```

NOTE: Make sure PHP is installed on your device.

### 3. Open in browser

Navigate to `http://localhost:8000` in your web browser.

## 📁 Project Structure

```
MockStore/
├── app/
│   ├── Controllers/           # Request handlers
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── HomeController.php
│   │   ├── OrderController.php
│   │   ├── PaymentController.php
│   │   └── ProductController.php
│   │
│   ├── Core/                  # Framework core classes
│   │   ├── Controller.php     # Base controller class
│   │   ├── Database.php       # Database connection & migrations
│   │   ├── Env.php            # Environment variable loader
│   │   ├── ErrorHandler.php   # Error & exception handling
│   │   ├── RateLimiter.php    # Rate limiting & abuse prevention
│   │   ├── Router.php         # URL routing
│   │   ├── Security.php       # CSRF, sessions, headers
│   │   └── Validator.php      # Input validation
│   │
│   ├── Models/                # Data access layer
│   │   ├── Order.php
│   │   └── Product.php
│   │
│   ├── Services/              # External service integrations
│   │   └── PlacetoPayService.php  # PlacetoPay API client
│   │
│   └── Views/                 # HTML templates
│       ├── layouts/
│       │   ├── header.php
│       │   └── footer.php
│       ├── cart/
│       ├── checkout/
│       ├── home/
│       ├── orders/
│       ├── payment/
│       └── products/
│
├── config/
│   └── config.php             # Application configuration
│
├── database/
│   └── store.db               # SQLite database (auto-created, gitignored)
│
├── logs/
│   ├── api_YYYY-MM-DD.log     # API request logs
│   ├── error.log              # Application error logs
│   ├── rate_limit_exceeded.log # Rate limit violations
│   └── rate_limits/           # Rate limiting data
│
├── public/
│   ├── .htaccess              # Apache rewrite rules
│   └── index.php              # Application entry point
│
├── screenshots/               # Visual evidence 
├── .env                       # Environment variables (not in repo)
├── .env.example               # Environment template
├── .gitignore
├── README.md
```

**Note:** If it doesn't exist, the SQLite database (`database/store.db`) is automatically created when you first run the application and is initialized with sample products on first launch.

## ⚙️ Configuration

Configuration is managed through environment variables in the `.env` file:

```env
# PlacetoPay API Credentials
PLACETOPAY_LOGIN=your_login_here
PLACETOPAY_SECRET_KEY=your_secret_key_here
PLACETOPAY_WEBCHECKOUT_URL=https://checkout-test.placetopay.com
PLACETOPAY_GATEWAY_URL=https://api-test.placetopay.com/rest

# Application Settings
APP_NAME=MockStore
APP_URL=http://localhost:8000
APP_CURRENCY=USD
APP_LOCALE=en_US
APP_ENV=development
```

## 🔐 PlacetoPay Integration

### Authentication

The PlacetoPay API uses a secure authentication mechanism. For each request, the software generates:

```php
// Generate authentication object
$auth = [
    'login' => $this->login,
    'tranKey' => base64_encode(
        hash('sha256', $rawNonce . $seed . $this->secretKey, true)
    ),
    'nonce' => base64_encode($rawNonce),
    'seed' => date('c'),  // ISO 8601 format
];
```

### API Endpoints Used

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/session` | POST | Create a new payment session |
| `/api/session/{requestId}` | POST | Query session status |
| `/api/session/{requestId}/cancel` | POST | Cancel a pending session |

### Payment Flow

1. **Customer fills checkout form** → Customer information collected
2. **Create session** → API call to PlacetoPay to create payment session
3. **Redirect to PlacetoPay** → Customer redirected to secure checkout
4. **Customer completes payment** → Payment processed by PlacetoPay
5. **Return to store** → Customer redirected back with result
6. **Query final status** → Store queries PlacetoPay for final status

## 💳 Test Cards

For testing in the sandbox environment:

| Scenario | Card Number | CVV | Expiry |
|----------|-------------|-----|--------|
| **Approved** | 4111 1111 1111 1111 | 123 | Any future date |
| **Pending** | 4864 9213 3682 4366 | 123 | Any future date |
| **Rejected** | 5367 6800 0000 0013 | 123 | Any future date |

## 🔧 API Request Logs

All API requests are logged to `logs/api_YYYY-MM-DD.log` for debugging:

```json
{
  "timestamp": "2026-02-03T10:00:00-05:00",
  "method": "POST",
  "url": "https://checkout-test.placetopay.com/api/session",
  "http_code": 200,
  "request": {...},
  "response": {...}
}
```

**Security Features:**

1. **CSRF Protection**
   - Token-based CSRF validation on all forms
   - Secure token generation and verification
   - Session-bound tokens with proper expiration

2. **Session Security**
   - Secure session configuration (httponly, secure flags)
   - Session fixation prevention with regeneration
   - Proper session timeout management

3. **HTTP Security Headers**
   - X-Frame-Options: SAMEORIGIN (clickjacking protection)
   - X-Content-Type-Options: nosniff
   - X-XSS-Protection: 1; mode=block
   - Strict-Transport-Security (HSTS)
   - Content-Security-Policy (CSP)

4. **Input Validation & Sanitization**
   - Comprehensive validation for all user inputs
   - Email, phone, document, name validation
   - Quantity limits and positive integer checks
   - XSS prevention with htmlspecialchars on all output
   - SQL injection protection (PDO prepared statements)

5. **Error Handling**
   - Environment-aware error display (development vs production)
   - Secure error logging without sensitive data exposure
   - Custom error pages for production
   - Database exception handling with safe error messages

6. **Rate Limiting**
   - File-based rate limiting system
   - Per-action rate limits (checkout: 5/min, add-to-cart: 20/min, payment: 10/min)
   - HTTP 429 responses with Retry-After headers
   - Automatic cleanup of expired rate limit data
   - Abuse logging for monitoring

**Additional Security:**
- Credentials stored in `.env` (not hardcoded)
- Sensitive data redaction in logs

## 📚 References

- [PlacetoPay WebCheckout Documentation](https://docs.placetopay.dev/en/checkout/)
- [PlacetoPay Gateway Documentation](https://docs.placetopay.dev/en/gateway/)
- [Authentication Guide](https://docs.placetopay.dev/en/checkout/authentication/)
- [Session API Reference](https://docs.placetopay.dev/en/checkout/api/reference/session/)

## 📄 License

This project is created for demonstration and testing purposes.

## 🤝 Author

Josef L Bohorquez

---

**Note**: This application uses sandbox credentials for testing PlacetoPay integration. All security best practices have been implemented.
