<<<<<<< HEAD
# Laravel Project

A comprehensive Laravel application with Filament admin panel for order management.

<p align="center">
    <a href="https://github.com/vamsi0310/laravel_project" target="_blank">
        <img src="/art/banner.png" alt="Laravel Project" style="width:70%;">
    </a>
</p>

## Features

- **Filament Admin Panel**: Modern admin interface for managing all aspects of the application
- **Order Management System**: Complete order lifecycle management with statuses, billing, and reviews
- **Customer Management**: Customer profiles with address management and Google Places integration
- **Product Catalog**: Products with categories, grades, and cut types
- **Driver Management**: Driver profiles for delivery management
- **Billing & Payment**: Comprehensive billing system with multiple payment options
- **Feedback System**: Customer feedback and review management
- **Coupon System**: Discount and coupon management
- **Store Management**: Multi-store support

## Tech Stack

- **Laravel 11**: Modern PHP framework
- **Filament**: Admin panel and form builder
- **PHP 8.4+**: Latest PHP features
- **MySQL**: Database
- **Pest**: Testing framework
- **PHPStan**: Static analysis
- **Rector**: Code refactoring
- **Pint**: Code formatting

## Getting Started

> **Requires [PHP 8.4+](https://php.net/releases/)**.

### Installation

1. Clone the repository:
```bash
git clone https://github.com/vamsi0310/laravel_project.git
cd laravel_project
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Set up database:
```bash
php artisan migrate
php artisan db:seed
```

6. Start the development server:
```bash
php artisan serve
```

### Available Commands

#### Development
- `composer dev` - Start development server with hot reloading
- `php artisan serve` - Start Laravel development server

#### Code Quality
- `composer lint` - Run code formatting and refactoring
- `composer test:lint` - Dry-run linting for CI/CD

#### Testing
- `composer test` - Run complete test suite
- `composer test:unit` - Run unit tests
- `composer test:types` - Run static analysis

## Project Structure

- `app/Models/` - Eloquent models for all entities
- `app/Filament/Resources/` - Filament admin resources
- `app/Enums/` - Application enums for status management
- `app/Actions/` - Business logic actions
- `app/Services/` - External service integrations
- `database/migrations/` - Database schema migrations

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
=======
# laravel_project
>>>>>>> 4c6b1ac1c791a24106df21b73f178e4a60df745a
