# 💰 Preve

[![Tests](https://github.com/combizera/preve/actions/workflows/tests.yml/badge.svg)](https://github.com/combizera/preve/actions/workflows/tests.yml)
[![Linter](https://github.com/combizera/preve/actions/workflows/lint.yml/badge.svg)](https://github.com/combizera/preve/actions/workflows/lint.yml)
[![Coverage](https://raw.githubusercontent.com/combizera/preve/main/.github/badges/coverage.svg)](https://github.com/combizera/preve/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-8.4%20%7C%208.5-blue)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A modern personal finance management application built with Laravel and Vue.js, designed to help you track expenses, manage budgets, and organize your financial life.

## 🚀 Features

- **Transaction Management**: Track income and expenses with detailed categorization
- **Smart Categories**: Pre-configured categories with customizable colors and icons
- **Tag System**: Organize transactions with flexible tagging
- **User-Scoped Data**: Complete multi-tenant isolation at user level
- **Modern UI**: Clean, responsive interface built with Tailwind CSS and shadcn/ui
- **Real-time Updates**: Seamless experience with Inertia.js

## 🛠️ Tech Stack

**Backend:**
- Laravel 12
- PHP 8.4+
- MySQL
- Inertia.js

**Frontend:**
- Vue 3 + TypeScript
- Tailwind CSS
- shadcn/ui components
- Vite

**Testing:**
- Pest PHP

## 📋 Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js 22 or higher
- MySQL 8.0+

## 🔧 Installation

1. **Clone the repository**
```bash
git clone git@github.com:combizera/preve.git
cd preve
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure your database** in `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=preve
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

7. **Build frontend assets**
```bash
npm run build
# or for development
npm run dev
```

8. **Start the server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run with coverage (requires Xdebug or PCOV)
php artisan test --coverage

# Run specific test file
php artisan test --filter CategoryCrudTest

# Run with parallel execution
php artisan test --parallel
```

## 📁 Project Structure

```
app/
├── Enums/                 # Application enums (TransactionType, CategoryColor, etc.)
├── Http/
│   ├── Controllers/       # Route controllers
│   └── Requests/          # Form request validation
├── Models/                # Eloquent models
└── Policies/              # Authorization policies

resources/
├── js/
│   ├── components/        # Vue components
│   │   ├── ui/           # shadcn/ui components
│   │   ├── Category/     # Category-specific components
│   │   └── Transaction/  # Transaction-specific components
│   ├── enums/            # Frontend constants
│   ├── lib/              # Utility functions
│   ├── pages/            # Inertia pages
│   ├── routes/           # Route helpers (Ziggy)
│   └── types/            # TypeScript definitions
└── views/                # Blade templates

tests/
├── Feature/              # Feature tests
└── Unit/                 # Unit tests
```

## 🎯 Code Standards

This project follows strict coding standards:

**Backend:**
- PSR-12 coding standard
- Laravel best practices
- Automated formatting with Laravel Pint

**Frontend:**
- ESLint + Prettier
- TypeScript strict mode
- Vue 3 Composition API

Check code style:
```bash
# Backend
composer lint

# Frontend
npm run lint
npm run format
```

## 📖 Documentation

For detailed development guidelines, patterns, and conventions, see [CLAUDE.md](CLAUDE.md).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 👤 Author

**Combizera**
- GitHub: [@combizera](https://github.com/combizera)

[//]: # (TODO: colocar nome do L0rd)

---

Made with ❤️ using Laravel and Vue.js
