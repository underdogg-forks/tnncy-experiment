# Copilot Instructions for tnncy-experiment

## Project Overview

This is a **multi-tenant SaaS application** built on Laravel 7 using the `hyn/multi-tenant` package. It implements a sophisticated dual-layer architecture separating system administration from tenant operations, with complete database isolation per tenant.

### Key Architectural Concepts

- **System Level**: Master database managing all tenants, accessed via system guard
- **Tenant Level**: Individual databases per tenant, accessed via tenant guard  
- **Domain-Based Recognition**: Tenants identified automatically by their domain
- **Permission Inheritance**: Tenants inherit permissions from customer configuration
- **Complete Isolation**: Each tenant has its own database, users, and data

## Code Style and Formatting

- **Preset**: Laravel coding standards (PSR-2/PSR-12)
- **Indentation**: 4 spaces for PHP, 2 spaces for YAML files
- **Line Endings**: LF (Unix-style)
- **Charset**: UTF-8
- **Final Newline**: Always include a final newline in files
- **Trailing Whitespace**: Remove trailing whitespace (except in Markdown files)

Refer to `.editorconfig` and `.styleci.yml` for specific formatting rules.

## Project Structure

```
app/
├── Console/         # Artisan commands
├── Exceptions/      # Custom exception handlers
├── Http/           # Controllers, middleware, requests
├── Providers/      # Service providers
└── models/
    ├── System/     # System-level models (cross-tenant)
    └── Tenant/     # Tenant-specific models
```

## Multi-Tenancy Architecture

This project uses a sophisticated multi-tenancy pattern with separate databases per tenant:

### System Level (Administrator/Master)
- **Location**: `app/Models/System/` and `app/Http/Controllers/System/`
- **Database**: Single system database (default connection)
- **Purpose**: Manage tenants, customers, system users, and cross-tenant operations
- **Auth Guard**: `system` guard with JWT
- **Models**: Customer, User, Permission, Hostname
- **Responsibilities**:
  - Create and configure tenants (customers)
  - Assign permissions to customers
  - Manage system administrators
  - Configure domains and tenant databases

### Tenant Level (Customer/Client)
- **Location**: `app/Models/Tenant/` and `app/Http/Controllers/Tenant/`
- **Database**: Separate database per tenant (auto-switched by middleware)
- **Purpose**: Isolated tenant operations and business logic
- **Auth Guard**: `tenant` guard with JWT
- **Models**: User, Permission, and all business entities
- **Responsibilities**:
  - Manage tenant-specific users
  - Handle business operations within permission boundaries
  - Complete data isolation from other tenants

### How Tenant Recognition Works

1. **Request arrives** → Hits tenant domain (e.g., `acme.example.com`)
2. **Middleware intercepts** → Looks up domain in `hostnames` table
3. **Database context switches** → Connects to tenant's isolated database
4. **Operations execute** → All queries run against tenant database
5. **Response sent** → Connection maintained for request lifecycle

### Critical Rules for Multi-Tenancy

- **Never mix contexts**: Don't use System models in Tenant controllers or vice versa
- **Always switch back**: After programmatic tenant operations, switch back to system context
- **Be explicit**: Always use fully qualified model names (`\App\Models\System\User` vs `\App\Models\Tenant\User`)
- **Test isolation**: Ensure tenant data never leaks between tenants

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

- **Unit Tests** (`tests/Unit/`): Test individual classes/methods in isolation
- **Feature Tests** (`tests/Feature/`): Test HTTP endpoints and full request/response cycles
  - `System/`: Tests for system-level controllers (system admin operations)
  - `Tenant/`: Tests for tenant-level controllers (tenant user operations)

### Testing Standards

- **Every controller method must have tests** covering:
  - Happy path (successful execution)
  - Validation errors (invalid input)
  - Authentication failures (unauthorized access)
  - Authorization checks (permission validation)
  - Edge cases (missing data, boundary conditions)

- **Test naming**: Use descriptive names that explain what's being tested
  ```php
  test_user_can_login_with_valid_credentials()
  test_login_fails_with_invalid_password()
  test_unauthenticated_user_cannot_access_users_endpoint()
  ```

- **Arrange-Act-Assert pattern**: Structure tests clearly
  ```php
  // Arrange: Set up test data
  $user = User::factory()->create();
  
  // Act: Perform the action
  $response = $this->postJson('/api/login', $credentials);
  
  // Assert: Verify the outcome
  $response->assertStatus(200);
  ```

### Test Database

Tests use an in-memory SQLite database by default (configured in `phpunit.xml`). This provides:
- Fast test execution
- Isolated test environment
- Automatic cleanup between tests

Use `RefreshDatabase` trait in test classes to reset database state.

## Building Assets

### NPM Scripts

```bash
# Install dependencies
npm install

# Development build
npm run dev

# Production build
npm run prod

# Watch for changes
npm run watch

# Hot module replacement
npm run hot
```

## Laravel Conventions

### Naming Conventions

- **Controllers**: PascalCase with `Controller` suffix (e.g., `UserController`)
- **Models**: Singular PascalCase (e.g., `User`, `Post`)
- **Database Tables**: Plural snake_case (e.g., `users`, `blog_posts`)
- **Migrations**: Descriptive with timestamp prefix
- **Routes**: kebab-case for URL segments
- **Variables**: camelCase

### Best Practices

- Use Eloquent ORM for database operations
- Follow Laravel's service container and dependency injection patterns
- Use form requests for validation logic
- Keep controllers thin, use services for business logic
- Use resource controllers for RESTful operations
- Leverage Laravel's built-in features (validation, authentication, caching, etc.)

## Dependencies

### PHP Requirements

- PHP ^7.2.5
- Composer for dependency management

### Key Dependencies

- Laravel Framework ^7.0
- hyn/multi-tenant ^5.6 (multi-tenancy support)
- Guzzle HTTP client
- Laravel Tinker

### Development Dependencies

- PHPUnit ^8.5
- Faker for test data
- Mockery for mocking

## Environment Setup

1. Copy `.env.example` to `.env`
2. Generate application key: `php artisan key:generate`
3. Configure database connections in `.env`
4. Run migrations: `php artisan migrate`
5. Install Composer dependencies: `composer install`
6. Install NPM dependencies: `npm install`

## Common Commands

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate files
php artisan make:controller ControllerName
php artisan make:model ModelName
php artisan make:migration create_table_name
php artisan make:seeder SeederName

# Database
php artisan migrate
php artisan migrate:rollback
php artisan db:seed
```

## Security Considerations

### Authentication & Authorization

- **Dual Guard System**: Never mix `system` and `tenant` guards
  ```php
  // System controllers use 'system' guard
  Auth::guard('system')->attempt($credentials)
  
  // Tenant controllers use 'tenant' guard
  Auth::guard('tenant')->attempt($credentials)
  ```

- **JWT Tokens**: All API endpoints use JWT for authentication
- **Permission-Based Access**: Use Spatie permissions package for granular control
- **Permission Inheritance**: Tenants can only use permissions assigned to their customer

### Security Best Practices

- **Never commit secrets**: `.env` file must never be committed
- **Input validation**: Always validate and sanitize user input
  ```php
  $validated = $request->validate([
      'email' => 'required|email',
      'password' => 'required|string|min:8'
  ]);
  ```
- **SQL Injection Prevention**: Use Eloquent or Query Builder (never raw concatenation)
- **CSRF Protection**: Laravel's built-in CSRF protection for state-changing requests
- **Password Hashing**: Always use `bcrypt()` or `Hash::make()` for passwords
- **Mass Assignment Protection**: Define `$fillable` or `$guarded` in all models
- **Tenant Isolation**: Critical - ensure tenant data never crosses boundaries
  - Test cross-tenant access attempts
  - Validate database context switches
  - Never expose tenant IDs to other tenants

## Notes for AI Assistants

### Critical Reminders

- **Database Context Awareness**: Always verify which database context you're in
  - System operations → System models and database
  - Tenant operations → Tenant models and database
  - When programmatically switching, ALWAYS switch back

- **Model Usage**: Use fully qualified model names to avoid confusion
  ```php
  // Clear and explicit
  $systemUser = \App\Models\System\User::find($id);
  $tenantUser = \App\Models\Tenant\User::find($id);
  ```

- **Testing Requirements**: Every controller method needs comprehensive tests
  - Write tests BEFORE marking a feature complete
  - Test both success and failure scenarios
  - Test authentication and authorization

- **Code Consistency**: Maintain the "single programmer" aesthetic
  - Follow existing patterns exactly
  - Use consistent naming conventions
  - Match code style throughout
  - Keep documentation updated

### When Adding New Features

1. **Determine layer**: Is this system-level or tenant-level?
2. **Place correctly**: Models and controllers in appropriate directories
3. **Add routes**: With correct middleware (`auth.guard.checker:system` or `auth.guard.checker:tenant`)
4. **Write tests**: Feature tests for all endpoints
5. **Update docs**: README and guidelines if architectural

### Common Pitfalls to Avoid

- ❌ Mixing System and Tenant models in same method
- ❌ Forgetting to switch back after programmatic tenant operation
- ❌ Using wrong auth guard in controller
- ❌ Missing validation on user input
- ❌ Not testing edge cases
- ❌ Leaving debug statements (`dd()`, `dump()`, `var_dump()`)
- ❌ Commenting out code instead of removing it
- ❌ Not eager loading relationships (N+1 queries)

### Multi-Tenancy Checklist

When working with tenant-related code, verify:

- [ ] Correct models used (System vs Tenant)
- [ ] Proper database context
- [ ] Guard matches controller context
- [ ] Permissions checked appropriately
- [ ] Data isolation maintained
- [ ] Tests cover cross-tenant access attempts
- [ ] Database context switched back if programmatic

### Performance Considerations

- **Eager load relationships**: Prevent N+1 queries with `with()`
- **Cache expensive operations**: Use Laravel's cache for frequently accessed data
- **Index database columns**: Add indexes for columns used in WHERE/JOIN clauses
- **Chunk large queries**: Use `chunk()` for processing large datasets
- **Monitor query count**: Keep queries per request minimal
