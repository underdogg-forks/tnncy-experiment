# Tnncy-Experiment - Multi-Tenant Laravel Application

A robust multi-tenant Laravel 7 application with separate database architecture for each tenant, JWT authentication, and granular permission management system.

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [How It Works](#how-it-works)
- [Installation](#installation)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Development Guidelines](#development-guidelines)
- [License](#license)

## Overview

This application is a multi-tenant SaaS platform built on Laravel 7 using the `hyn/multi-tenant` package. It provides a complete solution for managing multiple tenants (customers) with isolated databases, separate authentication systems, and role-based permissions.

### Key Features

- **Multi-Tenancy**: Each tenant gets a separate database, ensuring complete data isolation
- **Dual Authentication System**: Separate authentication for system administrators and tenant users
- **Permission Management**: Granular permission control at both system and tenant levels
- **JWT Authentication**: Secure token-based authentication using `tymon/jwt-auth`
- **RESTful API**: Clean API endpoints for all operations
- **Automatic Tenant Recognition**: Domain-based tenant identification

## Architecture

### System vs Tenant: Understanding the Dual-Layer Architecture

This application operates on two distinct levels:

#### 1. System Level (Administrator/Master Level)
- **Purpose**: Manages the entire multi-tenant infrastructure
- **Database**: Single shared database (`system` connection)
- **Models Location**: `app/Models/System/`
- **Controllers**: `app/Http/Controllers/System/`
- **Auth Guard**: `system` guard
- **Responsibilities**:
  - Create and manage customers (tenants)
  - Assign permissions to customers
  - Manage system-level users
  - Configure tenant databases and domains

#### 2. Tenant Level (Customer/Client Level)
- **Purpose**: Individual tenant's application instance
- **Database**: Separate database per tenant (isolated data)
- **Models Location**: `app/Models/Tenant/`
- **Controllers**: `app/Http/Controllers/Tenant/`
- **Auth Guard**: `tenant` guard
- **Responsibilities**:
  - Manage tenant-specific users
  - Handle tenant's business logic
  - Operate within customer's permission boundaries

### Directory Structure

```
app/
├── Models/
│   ├── System/              # System-level models (cross-tenant)
│   │   ├── User.php         # System administrators
│   │   ├── Customer.php     # Tenant/customer records
│   │   ├── Hostname.php     # Domain mappings
│   │   └── Permission.php   # System permissions
│   └── Tenant/              # Tenant-level models (isolated per tenant)
│       ├── User.php         # Tenant users
│       └── Permission.php   # Tenant permissions
├── Http/Controllers/
│   ├── System/              # System-level controllers
│   │   ├── AuthController.php
│   │   ├── CustomersController.php
│   │   ├── UserController.php
│   │   ├── PermissionsController.php
│   │   └── ConfigController.php
│   └── Tenant/              # Tenant-level controllers
│       ├── AuthController.php
│       ├── UserController.php
│       └── PermissionsController.php
```

## How It Works

### The Multi-Tenant Workflow

#### Step 1: Creating a Tenant (From System Level)

1. System administrator logs into the **system** using system credentials
2. Administrator creates a new customer via `/api/customers` endpoint
3. The system automatically:
   - Creates a `Customer` record in the system database
   - Generates a new isolated database for the tenant
   - Creates a `Website` record with unique database identifier
   - Creates a `Hostname` record linking the domain to the database
   - Switches context to the new tenant database
   - Creates a default admin user in the tenant's database
   - Assigns permissions to the tenant based on the customer's permission set
   - Switches back to system database

**Example Customer Creation:**
```bash
POST /api/customers
{
  "name": "Acme Corporation",
  "email": "admin@acme.com",
  "domain": "acme.example.com",
  "permissions": [1, 2, 3]  // Permission IDs the tenant has access to
}
```

#### Step 2: Tenant Recognition

Tenants are automatically recognized through **domain-based identification**:

1. User accesses the application via tenant's domain (e.g., `acme.example.com`)
2. The `hyn/multi-tenant` middleware intercepts the request
3. System looks up the domain in the `hostnames` table
4. Retrieves the associated website/database connection
5. Switches database context to tenant's database
6. All subsequent operations execute against tenant's isolated database

**How to check if you're in a tenant context:**
```bash
GET /api/checkTenant
# Returns: true if tenant context, false if system context
```

#### Step 3: Tenant Operations

Once a tenant is identified:

1. Tenant users access their domain (e.g., `acme.example.com`)
2. They authenticate using **tenant** guard credentials
3. All operations are scoped to their database
4. Permissions are filtered based on customer's allowed permissions
5. Complete data isolation from other tenants

### Permission Inheritance Flow

```
System Admin
    ↓ assigns permissions to
Customer/Tenant (e.g., can create users, view reports)
    ↓ filtered permissions available to
Tenant Admin
    ↓ can assign to
Tenant Users (within customer's permission boundaries)
```

### Database Connection Flow

```
Request arrives → Domain checked → Hostname found → Database switched → Operations executed → Response sent
     ↓                                    ↓
System DB (default)              Tenant DB (acme_xxxx)
```

## Installation

### Prerequisites

- PHP ^7.2.5
- Composer
- MySQL/MariaDB
- Node.js & NPM
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd tnncy-experiment
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tnncy_system
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Tenancy database prefix
   TENANCY_DATABASE_AUTO_DELETE=false
   TENANCY_DATABASE_AUTO_DELETE_USER=false
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database (optional)**
   ```bash
   php artisan db:seed
   ```

8. **Generate JWT secret**
   ```bash
   php artisan jwt:secret
   ```

9. **Build frontend assets**
   ```bash
   npm run dev
   ```

10. **Start the development server**
    ```bash
    php artisan serve
    ```

## Usage

### System Administrator Workflow

1. **Login as system administrator**
   ```bash
   POST /api/login
   {
     "email": "system-admin@example.com",
     "password": "password"
   }
   ```

2. **Create a new customer/tenant**
   ```bash
   POST /api/customers
   {
     "name": "New Company",
     "email": "admin@newcompany.com",
     "domain": "newcompany.localhost",
     "permissions": [1, 2, 3]
   }
   ```

3. **Manage system users**
   ```bash
   GET /api/users           # List all system users
   POST /api/users          # Create new system user
   PUT /api/users/{id}      # Update system user
   DELETE /api/users/{id}   # Delete system user
   ```

### Tenant User Workflow

1. **Access tenant domain** (e.g., `http://newcompany.localhost`)

2. **Login as tenant user**
   ```bash
   POST /api/tenant/login
   {
     "email": "admin@mail.com",
     "password": "password"
   }
   ```

3. **Manage tenant users** (within permission boundaries)
   ```bash
   GET /api/tenant/users
   POST /api/tenant/users
   PUT /api/tenant/users/{id}
   DELETE /api/tenant/users/{id}
   ```

## API Documentation

### System Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | System admin login | No |
| POST | `/api/logout` | System admin logout | Yes (system) |
| POST | `/api/refresh` | Refresh JWT token | Yes (system) |
| POST | `/api/user` | Get authenticated user | Yes (system) |
| GET | `/api/customers` | List all customers | Yes (system) |
| POST | `/api/customers` | Create new customer | Yes (system) |
| GET | `/api/customers/{id}` | Get customer details | Yes (system) |
| PUT | `/api/customers/{id}` | Update customer | Yes (system) |
| DELETE | `/api/customers/{id}` | Delete customer | Yes (system) |
| GET | `/api/users` | List system users | Yes (system) |
| POST | `/api/users` | Create system user | Yes (system) |
| GET | `/api/users/{id}` | Get user details | Yes (system) |
| PUT | `/api/users/{id}` | Update user | Yes (system) |
| DELETE | `/api/users/{id}` | Delete user | Yes (system) |
| GET | `/api/permissions` | List permissions | Yes (system) |
| GET | `/api/checkTenant` | Check if in tenant context | No |

### Tenant Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/tenant/login` | Tenant user login | No |
| POST | `/api/tenant/logout` | Tenant user logout | Yes (tenant) |
| POST | `/api/tenant/refresh` | Refresh JWT token | Yes (tenant) |
| POST | `/api/tenant/user` | Get authenticated user | Yes (tenant) |
| GET | `/api/tenant/users` | List tenant users | Yes (tenant) |
| POST | `/api/tenant/users` | Create tenant user | Yes (tenant) |
| GET | `/api/tenant/users/{id}` | Get user details | Yes (tenant) |
| PUT | `/api/tenant/users/{id}` | Update user | Yes (tenant) |
| DELETE | `/api/tenant/users/{id}` | Delete user | Yes (tenant) |
| GET | `/api/tenant/permissions` | List available permissions | Yes (tenant) |

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# Run specific test file
./vendor/bin/phpunit tests/Feature/System/AuthControllerTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Test Structure

- **Unit Tests** (`tests/Unit/`): Test individual methods in isolation
- **Feature Tests** (`tests/Feature/`): Test complete HTTP request/response cycles
  - `System/`: Tests for system-level controllers
  - `Tenant/`: Tests for tenant-level controllers

## Development Guidelines

### Code Style

- Follow **PSR-2/PSR-12** coding standards
- Use **4 spaces** for PHP indentation
- Use **Laravel naming conventions**
- Keep controllers thin, use services for business logic
- Write descriptive method and variable names

### Git Workflow

- Create feature branches from `main`
- Write clear, descriptive commit messages
- Keep commits atomic and focused
- Run tests before committing

### Adding New Features

1. Determine if feature is system-level or tenant-level
2. Create models in appropriate directory (`System/` or `Tenant/`)
3. Create controllers in matching directory
4. Add routes in `routes/api.php` with proper middleware
5. Write tests for all new functionality
6. Update documentation

### Multi-Tenancy Best Practices

- Always use appropriate models (`System\*` vs `Tenant\*`)
- Be explicit about database connections
- Test tenant isolation thoroughly
- Consider performance implications of switching databases
- Handle edge cases (missing tenant, invalid domain)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
