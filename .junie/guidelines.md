# Tnncy-Experiment Development Guidelines

## Table of Contents

1. [Project Vision](#project-vision)
2. [Architecture Principles](#architecture-principles)
3. [Code Standards](#code-standards)
4. [Multi-Tenancy Guidelines](#multi-tenancy-guidelines)
5. [Authentication & Authorization](#authentication--authorization)
6. [Database Guidelines](#database-guidelines)
7. [API Design Principles](#api-design-principles)
8. [Testing Standards](#testing-standards)
9. [Security Best Practices](#security-best-practices)
10. [Performance Optimization](#performance-optimization)
11. [Error Handling](#error-handling)
12. [Documentation Standards](#documentation-standards)

---

## Project Vision

This project aims to provide a **production-ready multi-tenant SaaS platform** with:

- Complete data isolation between tenants
- Scalable architecture supporting thousands of tenants
- Granular permission management at system and tenant levels
- Clean, maintainable codebase that looks like it was written by a single developer
- Comprehensive test coverage ensuring reliability
- Clear documentation for rapid onboarding

### Design Philosophy

- **Consistency over cleverness**: Prefer consistent patterns over clever tricks
- **Explicit over implicit**: Be explicit about intentions, especially with database connections
- **Simple over complex**: Choose simple solutions that are easy to understand and maintain
- **Tested over assumed**: Write tests for all critical functionality
- **Documented over tribal knowledge**: Document architectural decisions and complex logic

---

## Architecture Principles

### Separation of Concerns

The application maintains strict separation between system and tenant levels:

```
System Level (Master)         Tenant Level (Isolated)
├── Manages tenants          ├── Manages tenant users
├── System database          ├── Separate database per tenant
├── System auth guard        ├── Tenant auth guard
├── System permissions       └── Inherited permissions
└── Cross-tenant operations
```

### Model Organization

**Always** place models in the correct directory:

- **System Models**: `app/Models/System/` - For cross-tenant data
  - User, Customer, Hostname, Permission
  - Use system database connection
  
- **Tenant Models**: `app/Models/Tenant/` - For tenant-specific data
  - User, Permission, and all business entities
  - Use tenant database connection (automatically switched by middleware)

### Controller Organization

Match controller organization to model organization:

- **System Controllers**: `app/Http/Controllers/System/`
- **Tenant Controllers**: `app/Http/Controllers/Tenant/`

### Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Controllers | PascalCase + Controller suffix | `UserController`, `AuthController` |
| Models | Singular PascalCase | `User`, `Customer`, `Permission` |
| Tables | Plural snake_case | `users`, `customers`, `permissions` |
| Methods | camelCase | `getUser`, `makeAdmin`, `respondWithToken` |
| Variables | camelCase | `$userName`, `$customerId`, `$token` |
| Routes | kebab-case | `/api/users`, `/api/tenant/check-permissions` |
| Migrations | Descriptive with action | `create_users_table`, `add_status_to_customers` |

---

## Code Standards

### PSR-2/PSR-12 Compliance

All code must follow PSR-2/PSR-12 standards:

```php
<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }
}
```

### Code Formatting Rules

1. **Indentation**: 4 spaces (never tabs) for PHP
2. **Line length**: Maximum 120 characters per line
3. **Blank lines**: One blank line between methods
4. **Braces**: Opening brace on same line for methods, control structures
5. **Namespaces**: One blank line after namespace declaration

### Documentation Standards

**Every public method must have a DocBlock:**

```php
/**
 * Create a new customer with isolated database.
 *
 * This method creates a customer record in the system database,
 * generates a new tenant database, and sets up the initial admin user.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \App\Models\System\Customer
 * @throws \Illuminate\Validation\ValidationException
 */
public function store(Request $request)
{
    // Implementation
}
```

### Import Organization

Organize imports in this order:

1. Laravel/Framework imports
2. Third-party packages
3. Application imports (Models, Services, etc.)
4. Alphabetically within each group

```php
<?php

namespace App\Http\Controllers\System;

// Laravel imports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Third-party
use Hyn\Tenancy\Environment;

// Application
use App\Http\Controllers\Controller;
use App\Models\System\Customer;
use App\Models\System\User;
```

---

## Multi-Tenancy Guidelines

### Understanding Database Context

**Critical**: Always be aware of which database context you're operating in.

```php
// System context (default)
$systemUsers = \App\Models\System\User::all();

// Tenant context (after middleware switches connection)
$tenantUsers = \App\Models\Tenant\User::all();
```

### Creating Tenants

Follow this exact pattern when creating tenants:

```php
private function makeDBForCustomer($customer, $domain)
{
    // 1. Create website (generates unique database)
    $website = new Website();
    app(WebsiteRepository::class)->create($website);

    // 2. Create hostname (links domain to database)
    $hostname = new Hostname();
    $hostname->customer_id = $customer->id;
    $hostname->fqdn = $domain;
    app(HostnameRepository::class)->attach($hostname, $website);
    
    // 3. Switch to tenant context
    $tenancy = app(Environment::class);
    $tenancy->tenant($website);

    // 4. Perform tenant initialization
    $this->makeAdmin($customer);

    // 5. ALWAYS switch back to system context
    $tenancy->identifyHostname();
}
```

**Rules:**
- **Always** switch back to system context after tenant operations
- **Never** mix system and tenant models in the same query
- **Always** verify tenant context before sensitive operations

### Tenant Isolation

Ensure complete data isolation:

```php
// ✅ GOOD: Explicit tenant context
public function index()
{
    // Middleware has switched to tenant DB
    return \App\Models\Tenant\User::all();
}

// ❌ BAD: Mixing contexts
public function index()
{
    // Which database? Unclear!
    return User::all();
}
```

---

## Authentication & Authorization

### Dual Guard System

The application uses two separate authentication guards:

1. **System Guard** (`system`): For system administrators
2. **Tenant Guard** (`tenant`): For tenant users

**Never** mix guards in the same controller.

### Guard Usage

```php
// System controller
class AuthController extends Controller
{
    public function guard()
    {
        return Auth::guard('system');
    }
    
    public function login(Request $request)
    {
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
```

### Permission Inheritance

Permissions flow from system to tenant:

```
System Admin
    ↓ Assigns to Customer
Customer Permissions (e.g., can_create_users, can_view_reports)
    ↓ Available to Tenant
Tenant Admin
    ↓ Can assign to
Tenant Users (within customer's permissions)
```

**Implementation:**

```php
// Get permissions available to tenant
public function tenantPermissions()
{
    $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
    
    if (!$hostname || !$hostname->customer) {
        return collect();
    }
    
    // Only permissions assigned to customer are available
    return $hostname->customer->getDirectPermissions();
}
```

---

## Database Guidelines

### Migration Standards

1. **Naming**: Be descriptive
   ```
   2024_01_15_create_customers_table.php
   2024_01_16_add_status_to_users_table.php
   ```

2. **Reversibility**: Every migration must be reversible
   ```php
   public function up()
   {
       Schema::create('customers', function (Blueprint $table) {
           $table->id();
           $table->string('name');
           $table->timestamps();
       });
   }

   public function down()
   {
       Schema::dropIfExists('customers');
   }
   ```

3. **Foreign Keys**: Always define relationships
   ```php
   $table->foreignId('customer_id')
         ->constrained()
         ->onDelete('cascade');
   ```

### Query Optimization

1. **Eager Loading**: Prevent N+1 queries
   ```php
   // ✅ GOOD
   $customers = Customer::with('hostname')->get();
   
   // ❌ BAD
   $customers = Customer::all();
   foreach ($customers as $customer) {
       $domain = $customer->hostname->fqdn; // N+1 query!
   }
   ```

2. **Select Specific Columns**: Don't fetch unnecessary data
   ```php
   // ✅ GOOD
   $users = User::select('id', 'name', 'email')->get();
   
   // ❌ BAD (unless you need all columns)
   $users = User::all();
   ```

3. **Chunking Large Results**: For large datasets
   ```php
   User::chunk(100, function ($users) {
       foreach ($users as $user) {
           // Process user
       }
   });
   ```

---

## API Design Principles

### RESTful Conventions

Follow RESTful principles consistently:

| Method | Endpoint | Action | Response |
|--------|----------|--------|----------|
| GET | `/api/users` | List all users | 200 + Collection |
| GET | `/api/users/{id}` | Show specific user | 200 + Resource |
| POST | `/api/users` | Create user | 201 + Resource |
| PUT/PATCH | `/api/users/{id}` | Update user | 200 + Resource |
| DELETE | `/api/users/{id}` | Delete user | 200/204 |

### Response Structure

**Success Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-01-15T10:30:00Z"
}
```

**Error Response:**
```json
{
  "error": "Unauthorized",
  "message": "Invalid credentials"
}
```

**Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### HTTP Status Codes

Use appropriate status codes:

- `200`: Success (GET, PUT, PATCH)
- `201`: Created (POST)
- `204`: No Content (DELETE)
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

---

## Testing Standards

### Test Coverage Requirements

Every controller method **must** have tests covering:

1. **Happy path**: Successful execution
2. **Validation errors**: Invalid input handling
3. **Authentication**: Unauthorized access attempts
4. **Edge cases**: Boundary conditions, missing data
5. **Permissions**: Authorization checks

### Test Structure

Organize tests to mirror controllers:

```
tests/
├── Feature/
│   ├── System/
│   │   ├── AuthControllerTest.php
│   │   ├── UserControllerTest.php
│   │   └── CustomersControllerTest.php
│   └── Tenant/
│       ├── AuthControllerTest.php
│       └── UserControllerTest.php
└── Unit/
    ├── Models/
    │   ├── System/
    │   └── Tenant/
    └── Services/
```

### Test Naming Convention

Test method names must clearly describe what's being tested:

```php
// ✅ GOOD: Descriptive test names
public function test_user_can_login_with_valid_credentials()
public function test_login_fails_with_invalid_password()
public function test_unauthenticated_user_cannot_access_protected_route()

// ❌ BAD: Vague test names
public function test_login()
public function test_it_works()
```

### Test Template

```php
<?php

namespace Tests\Feature\System;

use Tests\TestCase;
use App\Models\System\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can list all users.
     *
     * @return void
     */
    public function test_authenticated_user_can_list_users()
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth()->guard('system')->login($user);
        
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');
        
        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'email']
        ]);
    }
}
```

---

## Security Best Practices

### Input Validation

**Always** validate user input:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'permissions' => 'array',
        'permissions.*' => 'exists:permissions,id'
    ]);
    
    // Use $validated, not $request->all()
    $user = User::create($validated);
}
```

### Password Handling

```php
// ✅ GOOD: Hashing passwords
$user->password = bcrypt($request->password);

// ❌ BAD: Storing plain text
$user->password = $request->password;
```

### SQL Injection Prevention

```php
// ✅ GOOD: Using Eloquent/Query Builder (automatic escaping)
$users = User::where('email', $email)->get();

// ✅ GOOD: Prepared statements
$users = DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ❌ BAD: Raw SQL with concatenation
$users = DB::select("SELECT * FROM users WHERE email = '$email'");
```

### Mass Assignment Protection

Define fillable or guarded in models:

```php
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    
    // Or use guarded to protect specific fields
    protected $guarded = ['id', 'is_admin'];
}
```

### CSRF Protection

All state-changing requests must include CSRF token (Laravel handles this automatically for forms).

---

## Performance Optimization

### Caching Strategies

```php
// Cache expensive queries
$permissions = Cache::remember('user.permissions.' . $userId, 3600, function () use ($userId) {
    return User::find($userId)->getAllPermissions();
});

// Clear cache when data changes
public function update(Request $request, $id)
{
    $user = User::find($id);
    $user->update($validated);
    
    // Clear cached permissions
    Cache::forget('user.permissions.' . $id);
}
```

### Database Indexing

Add indexes for frequently queried columns:

```php
Schema::table('customers', function (Blueprint $table) {
    $table->index('email');
    $table->index('created_at');
});
```

### Lazy Loading vs Eager Loading

```php
// ✅ GOOD: Eager load relationships
$customers = Customer::with(['hostname', 'permissions'])->get();

// ❌ BAD: Lazy loading causes N+1
$customers = Customer::all();
foreach ($customers as $customer) {
    echo $customer->hostname->fqdn; // Query executed for each customer!
}
```

---

## Error Handling

### Exception Handling

Use appropriate exceptions:

```php
public function show($id)
{
    $user = User::find($id);
    
    if (!$user) {
        abort(404, 'User not found');
    }
    
    return $user;
}
```

### Custom Exceptions

For complex error handling, create custom exceptions:

```php
namespace App\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'error' => 'Tenant not found',
            'message' => 'The requested tenant does not exist or is inactive.'
        ], 404);
    }
}
```

### Logging

Log important events and errors:

```php
use Illuminate\Support\Facades\Log;

try {
    $this->makeDBForCustomer($customer, $domain);
} catch (\Exception $e) {
    Log::error('Failed to create tenant database', [
        'customer_id' => $customer->id,
        'domain' => $domain,
        'error' => $e->getMessage()
    ]);
    
    throw $e;
}
```

---

## Documentation Standards

### Code Comments

Write comments for **why**, not **what**:

```php
// ✅ GOOD: Explains reasoning
// Switch back to system context to prevent subsequent queries
// from executing against the tenant database
$tenancy->identifyHostname();

// ❌ BAD: States the obvious
// Call the identifyHostname method
$tenancy->identifyHostname();
```

### README Updates

When adding features:

1. Update API documentation with new endpoints
2. Add usage examples
3. Update installation steps if dependencies change
4. Document breaking changes

### Architectural Decision Records

For significant architectural decisions, document:

1. **Context**: What's the situation?
2. **Decision**: What was decided?
3. **Rationale**: Why this decision?
4. **Consequences**: What are the trade-offs?

---

## Version Control

### Commit Messages

Follow conventional commit format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Examples:**
```
feat(auth): add JWT refresh token endpoint

Implemented token refresh functionality to allow users to obtain
new tokens without re-authenticating.

Closes #123

fix(tenancy): ensure database context switch on tenant creation

Previously, the system remained in tenant context after creating
a new tenant, causing subsequent system operations to fail.

test(controllers): add comprehensive tests for UserController

Added tests covering all CRUD operations, validation errors,
and authorization checks.
```

### Branch Naming

- Feature: `feature/user-management`
- Bug fix: `fix/tenant-context-leak`
- Refactor: `refactor/simplify-auth-flow`

---

## Continuous Improvement

### Code Review Checklist

Before submitting code for review:

- [ ] All tests pass
- [ ] New tests added for new functionality
- [ ] Code follows PSR-2/PSR-12 standards
- [ ] No commented-out code
- [ ] No debug statements (`dd()`, `var_dump()`)
- [ ] Documentation updated
- [ ] No sensitive data in code
- [ ] Proper error handling
- [ ] Database queries optimized
- [ ] Tenant isolation maintained

### Performance Monitoring

Monitor these metrics:

- Response times
- Database query count per request
- Memory usage
- Failed authentication attempts
- Error rates

---

## Quick Reference

### Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run tests
./vendor/bin/phpunit

# Generate key
php artisan key:generate

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Create files
php artisan make:controller System/ExampleController
php artisan make:model Models/System/Example
php artisan make:test Feature/System/ExampleControllerTest
```

### Environment Variables

Critical `.env` variables:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_DATABASE=tnncy_system
JWT_SECRET=<generated-secret>
TENANCY_DATABASE_AUTO_DELETE=false
```

---

## Support and Resources

- **Laravel Documentation**: https://laravel.com/docs/7.x
- **hyn/multi-tenant**: https://tenancy.dev/docs/hyn/5.x
- **JWT Auth**: https://jwt-auth.readthedocs.io/
- **PSR-12**: https://www.php-fig.org/psr/psr-12/

---

**Remember**: Consistency is key. When in doubt, follow existing patterns in the codebase. This project should look and feel like it was crafted by a single, meticulous developer over a focused period of time.
