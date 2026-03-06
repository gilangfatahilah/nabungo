# Nabungo - Project Overview

## 📋 Deskripsi Proyek

Nabungo adalah aplikasi manajemen keuangan pribadi berbasis web yang dibangun menggunakan stack **Laravel 12 + Vue 3 + Inertia.js**. Aplikasi ini memungkinkan pengguna untuk mengelola akun keuangan, melacak transaksi, mengatur budget, dan menetapkan target finansial (goals).

---

## 🏗️ Pola Arsitektur

### 1. **Monolithic MVC dengan SPA-like Experience**

Project ini menggunakan arsitektur **Monolithic MVC** (Model-View-Controller) yang diperkaya dengan **Inertia.js** untuk memberikan pengalaman Single Page Application tanpa memisahkan backend dan frontend secara penuh.

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT BROWSER                       │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                 Vue 3 + Inertia Client                │  │
│  │    (Components, Pages, Composables, Types)            │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP (Inertia Protocol)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      LARAVEL BACKEND                        │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────────┐    │
│  │   Routes    │ → │ Controllers │ → │    Services     │    │
│  └─────────────┘   └─────────────┘   └─────────────────┘    │
│         │                 │                   │             │
│         │                 │                   ▼             │
│         │                 │          ┌─────────────┐        │
│         │                 │          │   Models    │        │
│         │                 │          │  (Eloquent) │        │
│         │                 │          └─────────────┘        │
│         │                 │                   │             │
│  ┌──────┴─────────────────┴───────────────────┴───────────┐ │
│  │                    Helpers / Utilities                 │ │
│  │           (QueryFilters, FilterParser)                 │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │    Database     │
                    │    (PostgreSQL) │
                    └─────────────────┘
```

### 2. **Arsitektur yang Digunakan**

| Pattern | Implementasi |
|---------|--------------|
| **MVC** | Laravel Controllers, Eloquent Models, Vue Pages (View) |
| **Repository-like Pattern** | Service layer (`TransactionService`) untuk business logic kompleks |
| **SPA Monolith** | Inertia.js sebagai bridge antara Laravel dan Vue |
| **Component-Based UI** | Vue 3 Composition API dengan reusable components |

---

## 📚 Layering yang Terlihat

### Layer 1: Presentation Layer (Frontend)

```
resources/js/
├── pages/                    # Inertia Pages (Route-based views)
│   ├── account/
│   ├── budget/
│   ├── category/
│   ├── dashboard/
│   ├── goal/
│   ├── transaction/
│   └── settings/
├── components/
│   ├── ui/                   # Primitive UI components (shadcn-vue style)
│   └── common/               # Business-specific reusable components
│       ├── data-table/       # Generic DataTable dengan filtering
│       ├── dialog/
│       ├── page/
│       └── popover/
├── layouts/                  # Page layouts (AppLayout)
├── composables/              # Vue composables (useAppearance, useInitials)
├── types/                    # TypeScript type definitions
└── lib/                      # Utility functions
```

### Layer 2: Application Layer (Controllers & Requests)

```
app/Http/
├── Controllers/
│   ├── AccountController.php
│   ├── TransactionController.php
│   ├── BudgetController.php
│   ├── CategoryController.php
│   ├── GoalController.php
│   ├── DashboardController.php
│   ├── ActivityLogController.php
│   └── AccountHistoryController.php
├── Requests/                 # Form Request Validation
│   ├── Account/
│   ├── Budget/
│   ├── Category/
│   ├── Goal/
│   └── Transaction/
└── Middleware/
```

### Layer 3: Domain/Business Logic Layer

```
app/
├── Services/
│   └── TransactionService.php    # Complex transaction logic with balance management
├── Helpers/
│   ├── QueryFilters.php          # Dynamic query filtering engine
│   └── FilterParser.php          # Filter parsing & validation
└── Models/                       # Eloquent Models with relationships
    ├── User.php
    ├── Account.php
    ├── Transaction.php
    ├── Category.php
    ├── Budget.php
    ├── Goal.php
    ├── Debt.php
    ├── DebtPayment.php
    └── AccountHistory.php
```

### Layer 4: Data Layer

```
database/
├── migrations/               # Schema definitions
├── factories/                # Test data factories
└── seeders/                  # Database seeders
```

---

## 🗄️ Domain Model (ERD Summary)

```
┌──────────┐     ┌─────────────┐     ┌──────────────┐
│   User   │────<│  Account    │────<│ Transaction  │
└──────────┘     └─────────────┘     └──────────────┘
     │                 │                    │
     │                 │                    │
     │           ┌─────┴─────┐              │
     │           ▼           ▼              │
     │      ┌────────┐  ┌──────────────┐    │
     │      │  Goal  │  │AccountHistory│    │
     │      └────────┘  └──────────────┘    │
     │                                      │
     │    ┌──────────┐                      │
     └───<│ Category │<─────────────────────┘
          └──────────┘
               │
               ▼
          ┌──────────┐
          │  Budget  │
          └──────────┘
```

### Entitas Utama:
- **User**: Autentikasi dan ownership
- **Account**: Akun keuangan (cash, bank, ewallet, asset, liability, goal)
- **Transaction**: Transaksi (income, expense, transfer)
- **Category**: Kategori transaksi (income/expense)
- **Budget**: Anggaran per kategori per bulan
- **Goal**: Target tabungan dengan akun dedicated
- **Debt/DebtPayment**: Hutang/piutang (belum fully implemented)
- **AccountHistory**: Audit trail perubahan saldo

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | ^8.2 | Runtime |
| Laravel | ^12.0 | Framework |
| Inertia.js | ^2.0 | SPA Bridge |
| Spatie Activity Log | ^4.10 | Audit logging |
| Ziggy | ^2.4 | Route sharing to JS |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| Vue | ^3.5 | UI Framework |
| TypeScript | ^5.2 | Type safety |
| Tailwind CSS | ^4.1 | Styling |
| Vite | ^6.2 | Build tool |
| TanStack Table | ^8.21 | Data table |
| Reka UI | ^2.8 | Headless UI components |
| Zod | ^4.3 | Schema validation |

### Development & Testing
| Technology | Purpose |
|------------|---------|
| Pest | Testing framework |
| Laravel Pint | Code style |
| ESLint + Prettier | Frontend linting |
| Laravel Sail | Docker environment |

---

## ⚠️ Potensi Technical Debt

### 1. **Inkonsistensi Service Layer**

**Issue**: Hanya `TransactionService` yang menggunakan pattern service layer. Controller lain (Account, Budget, Category, Goal) melakukan business logic langsung.

**Impact**: Duplikasi kode, susah di-test, inkonsistensi architecture.

**Recommendation**:
```
app/Services/
├── TransactionService.php     ✅ (existing)
├── AccountService.php         ❌ (missing)
├── BudgetService.php          ❌ (missing)
├── GoalService.php            ❌ (missing)
└── CategoryService.php        ❌ (missing)
```

### 2. **N+1 Query Potential pada Model Accessors**

**Issue**: `Budget` model memiliki computed attributes (`usage`, `total_expense`) yang melakukan query di dalam accessor.

```php
// app/Models/Budget.php - Potential N+1
protected function usage(): Attribute {
    return Attribute::make(
        get: function () {
            // Query executed per model instance!
            $totalExpense = Transaction::where(...)
        }
    );
}
```

**Impact**: Performance degradation saat listing budgets.

**Recommendation**: Precompute atau gunakan eager loading dengan subqueries.

### 3. **Missing Authorization Layer**

**Issue**: Authorization dilakukan secara manual di controller (`authorizeAccess()`), bukan menggunakan Laravel Policies.

**Impact**: Inkonsisten, mudah lupa implement, susah di-audit.

**Recommendation**: Implement Laravel Policies untuk setiap model.

### 4. **Incomplete Test Coverage**

**Issue**: Folder `tests/Feature` hanya memiliki test minimal (Auth dan Dashboard). Business logic utama tidak ter-cover.

**Current**:
```
tests/Feature/
├── Auth/           # Auth tests only
├── Settings/       # Settings tests only
├── DashboardTest.php
└── ExampleTest.php
```

**Missing**:
- Transaction CRUD tests
- Account balance calculation tests
- Budget calculation tests
- Goal progress tests

### 5. **Unused/Incomplete Features**

**Issue**: Model `Debt` dan `DebtPayment` sudah ada di migration tapi belum ada Controller/Service.

**Recommendation**: Implement atau remove dari codebase.

### 6. **Hardcoded Enum Values**

**Issue**: Account types, transaction types tersebar di migration, model, dan frontend.

**Recommendation**: Gunakan PHP Enums (PHP 8.1+) dan share ke frontend.

### 7. **Missing Repository/Interface Layer**

**Issue**: Models langsung di-query dari Controller/Service, tidak ada abstraksi.

**Impact**: Tight coupling, susah mock untuk testing.

### 8. **Filter Schema Definition Coupling**

**Issue**: `filterableFields()` di Model melakukan query database untuk enum options.

```php
// app/Models/Transaction.php
'enumOptions' => Category::all()->map(...) // Query setiap kali dipanggil
```

**Impact**: N+1 queries, performance issue.

**Recommendation**: Cache atau lazy load enum options.

### 9. **Missing API Documentation**

**Issue**: Tidak ada dokumentasi API (OpenAPI/Swagger), meski ini SPA monolith.

### 10. **Vue Component Organization**

**Issue**: Mix antara `ui/` components (primitives) dan business components di root `components/`.

**Recommendation**: 
```
components/
├── ui/              # Primitives only (Button, Input, etc)
├── common/          # Shared business components
├── features/        # Feature-specific components
└── layouts/         # Layout components
```

---

## 📊 Architecture Quality Metrics

| Aspect | Score | Notes |
|--------|-------|-------|
| Code Organization | ⭐⭐⭐⭐ | Well-structured Laravel + Vue |
| Separation of Concerns | ⭐⭐⭐ | Partial - Service layer incomplete |
| Type Safety | ⭐⭐⭐⭐ | TypeScript + Zod on frontend |
| Test Coverage | ⭐⭐ | Minimal test coverage |
| Documentation | ⭐⭐ | Missing API docs |
| Scalability | ⭐⭐⭐ | Good foundation, needs optimization |

---

## 🚀 Recommendations for Improvement

### Short-term (Quick Wins)
1. Add Laravel Policies for all models
2. Fix N+1 queries in Budget model
3. Add basic feature tests for transactions

### Medium-term
1. Extract all business logic to Service classes
2. Implement PHP Enums for type safety
3. Add caching for filter schemas
4. Add API documentation

### Long-term
1. Consider event-driven architecture for complex workflows
2. Add comprehensive test suite (80%+ coverage)
3. Implement Repository pattern for data access
4. Consider microservices if scaling required

---

## 📁 File Structure Summary

```
nabungo/
├── app/
│   ├── Helpers/           # Query filtering utilities
│   ├── Http/
│   │   ├── Controllers/   # MVC Controllers
│   │   ├── Middleware/    # HTTP Middleware
│   │   └── Requests/      # Form Request Validation
│   ├── Models/            # Eloquent Models
│   ├── Providers/         # Service Providers
│   └── Services/          # Business Logic (partial)
├── bootstrap/             # App Bootstrap
├── config/                # Configuration files
├── database/
│   ├── factories/         # Model Factories
│   ├── migrations/        # Database Migrations
│   └── seeders/           # Database Seeders
├── public/                # Public assets
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # Vue/TypeScript source
│   └── views/             # Blade templates (minimal, for app shell)
├── routes/
│   ├── web.php            # Main routes
│   ├── auth.php           # Auth routes
│   └── settings.php       # Settings routes
├── storage/               # File storage
├── tests/                 # Test suites
└── vendor/                # Composer dependencies
```

---

*Document generated for architectural analysis - Last updated: 2025*
