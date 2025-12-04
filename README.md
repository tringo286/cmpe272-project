# MarketHub - E-Commerce Marketplace Platform

A full-featured e-commerce marketplace built with PHP and MySQL, featuring product browsing, shopping cart, user authentication, and Stripe payment integration.

## 🎯 Features

- **User Authentication**: Sign up, login, and secure session management
- **Product Catalog**: Browse products with advanced filtering by price, seller, and ratings
- **Search & Filtering**: Real-time product search with multiple filter options
- **Shopping Cart**: Add/remove items, update quantities, persistent cart storage
- **Checkout**: Secure payment processing with Stripe integration
- **Order Management**: Order history and order tracking
- **Product Reviews**: Leave and view product reviews with star ratings
- **Responsive Design**: Mobile-friendly UI with modern styling

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Payment Processing**: Stripe API (v19.0)
- **Environment Management**: PHP dotenv
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Hosting**: Render (or compatible PHP hosting)

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Stripe API Keys (for payment processing)

## ⚙️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/tringo286/cmpe272-project.git
cd cmpe272-project
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment Variables
Create a `.env` file in the project root:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=marketdb

STRIPE_PUBLISHABLE_KEY=pk_test_your_key
STRIPE_SECRET_KEY=sk_test_your_key
```

### 4. Set Up Database

Import the database schema (create this if not exists):
```sql
-- Users table
CREATE TABLE project_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    seller VARCHAR(255),
    slug VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES project_users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES project_users(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status VARCHAR(50),
    total DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES project_users(id) ON DELETE CASCADE
);
```

## 🚀 Running Locally

### Development Server
```bash
php -S localhost:8000
```

Visit `http://localhost:8000` in your browser.

### Test Credentials
```
Email: admin@marketplace.com
Password: admin123
```

## 📁 Project Structure

```
cmpe272-project/
├── index.php                 # Homepage & product listing
├── product.php              # Product detail page
├── login.php                # Login page
├── signup.php               # Registration page
├── logout.php               # Logout handler
├── cart.php                 # Shopping cart
├── checkout.php             # Checkout page
├── create_checkout_session.php # Stripe session creation (fixed cURL)
├── success.php              # Order success page
├── submit_review.php        # Review submission handler
├── db.php                   # Database connection
├── includes/
│   ├── header.php          # Navigation header
│   ├── footer.php          # Footer
│   └── review_form.php     # Review form component
├── styles/
│   └── index.css           # Main stylesheet
├── assets/
│   └── images/             # Product images
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
├── Dockerfile              # Docker configuration
└── README.md              # This file
```

## 🔑 Key Pages

| Page | Purpose |
|------|---------|
| `index.php` | Product catalog with search & filtering |
| `product.php` | Single product details & reviews |
| `login.php` | User authentication |
| `signup.php` | User registration |
| `cart.php` | Shopping cart management |
| `checkout.php` | Payment processing interface |
| `success.php` | Order confirmation |

## 💳 Stripe Integration

### Payment Flow
1. User adds items to cart
2. User proceeds to checkout
3. `checkout.php` displays order summary
4. User clicks "Pay with Card"
5. `create_checkout_session.php` creates Stripe session via direct cURL (form-encoded)
6. Stripe redirects to payment form
7. On success, `success.php` confirms order and clears cart

### API Keys Configuration
- Set `STRIPE_PUBLISHABLE_KEY` and `STRIPE_SECRET_KEY` in `.env`
- For Render deployments, set these in environment variables

### Known Issues & Fixes
- **Render cURL Header Injection**: Fixed by using form-encoded requests with basic auth instead of custom headers
- **Foreign Key Constraints**: Ensure `reviews.user_id` references `project_users(id)`

## 🔒 Security Features

- ✅ Session-based authentication
- ✅ Prepared statements (SQL injection prevention)
- ✅ Password hashing (use `password_hash()` in production)
- ✅ CSRF-safe form handling
- ✅ User ID validation on sensitive operations

## 📦 Deployment

### Render
1. Connect GitHub repository to Render
2. Set environment variables:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
   - `STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`
3. Render will auto-deploy on `git push`

### Docker
```bash
docker build -t markethub .
docker run -p 8000:8000 markethub
```

## 🐛 Troubleshooting

### "Headers already sent" error
- Ensure all PHP logic runs before including header files
- Move `session_start()` and business logic before `include '/includes/header.php'`

### Foreign key constraint fails
- Verify `reviews.user_id` references `project_users(id)`
- Run: `ALTER TABLE reviews ADD CONSTRAINT reviews_ibfk_2 FOREIGN KEY (user_id) REFERENCES project_users(id) ON DELETE CASCADE;`

### Stripe payment errors
- Check API keys are set correctly in `.env`
- Ensure cURL is enabled in PHP
- Use test keys for development

### Cart not updating
- Verify cart session is initialized
- Check browser cookies are enabled
- Clear session and log in again

## 📝 Database Migrations

To update foreign key constraints:
```sql
-- Drop old constraint
ALTER TABLE reviews DROP FOREIGN KEY reviews_ibfk_2;

-- Create new constraint
ALTER TABLE reviews ADD CONSTRAINT reviews_ibfk_2 
FOREIGN KEY (user_id) REFERENCES project_users(id) ON DELETE CASCADE;
```

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -am 'Add your feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.

## 👤 Author

**Tringo** - [GitHub Profile](https://github.com/tringo286)

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Open an GitHub issue with detailed error logs