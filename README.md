# E-commerce Platform with Referral System

A modern e-commerce platform with integrated referral system, built with Laravel, Vue.js 3, and Inertia.js. Features include product management, user authentication, and a comprehensive admin dashboard.

## 🚀 Features

### Core Features
- **Product Management**
  - CRUD operations for products
  - Image upload and management
  - Inventory tracking
  - Product categories and tags

- **User System**
  - JWT authentication with Laravel Sanctum
  - Role-based access control (Admin/User)
  - User profiles and settings

- **Referral System**
  - Unique referral codes
  - Referral tracking

- **Admin Dashboard**
  - Comprehensive analytics
  - User management
  - Product management

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 10.x
- **API**: RESTful API with Laravel Sanctum
- **Database**: MySQL/PostgreSQL
- **Caching**: Redis (optional)

### Frontend
- **Framework**: Vue.js 3 with Composition API
- **State Management**: Pinia
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with custom components
- **Form Handling**: vee-validate
- **UI Components**: Headless UI

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 16.0
- NPM or Yarn
- MySQL/PostgreSQL
- Redis (optional, for caching)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/santhoshg4747/referral_system.git
   cd referral-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   # or
   yarn
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Configure your `.env` file with these essential settings:
   ```env
   APP_NAME="Your App Name"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   
   SANCTUM_STATEFUL_DOMAINS=localhost:5173
   SESSION_DOMAIN=localhost
   ```

5. **Database Setup**
   - Create a new database
   - Run migrations and seed the database with initial data:
     ```bash
     php artisan migrate --seed
     ```
   - This will create an admin user with the following credentials:
     - **Email**: admin@example.com
     - **Password**: admin123

6. **Storage Link**
   ```bash
   php artisan storage:link
   ```

7. **Start the development servers**
   ```bash
   # Terminal 1: Start Laravel backend
   php artisan serve --port=8000

   # Terminal 2: Start Vite dev server
   npm run dev
   # or
   yarn dev
   ```

8. **Access the application**
   - Frontend: http://localhost:8000
   - Admin Dashboard: http://localhost:8000/admin

## 🎨 Design Decisions

### Frontend Architecture
- **Vue 3 with Composition API**: Chosen for its better TypeScript support and more flexible code organization
- **Inertia.js**: Enables building single-page applications using classic server-side routing, providing a snappier user experience
- **Pinia for State Management**: Lightweight and modular state management solution that works perfectly with Vue 3
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development and consistent design

### Backend Architecture
- **Laravel Sanctum**: Lightweight authentication system for SPA authentication and API tokens
- **Repository Pattern**: Used for better separation of concerns and testability
- **API Resources**: For consistent API responses and data transformation
- **Caching**: Implemented with Redis for improved performance

### Performance Optimizations
- **Lazy Loading**: Images and components are loaded only when needed
- **Code Splitting**: Automatic code splitting with Vite for faster initial load
- **Eager Loading**: Used in Eloquent relationships to prevent N+1 query issues

## 🔒 Test Credentials

### Admin Access
- **URL**: http://localhost:8000/admin/login
- **Email**: admin@example.com
- **Password**: admin123

### Regular User
- **URL**: http://localhost:8000/register
- Or register a new account through the registration form

## 🧪 Testing

Run the test suite with:

```bash
# Run PHPUnit tests
php artisan test

# Run PHPStan for static analysis
composer analyse

# Run ESLint for frontend code quality
npm run lint
# or
yarn lint
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgements

- [Laravel](https://laravel.com/)
- [Vue.js](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
