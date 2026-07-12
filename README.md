# SSC Transparency System — Native PHP to Laravel Migration Guide

> A complete, step-by-step explanation of how the legacy native PHP SSC system was converted into a modern Laravel application.

---

## Table of Contents

1. [Why Migrate to Laravel?](#1-why-migrate-to-laravel)
2. [Architecture Comparison](#2-architecture-comparison)
3. [Step 1 — Project Initialization](#3-step-1--project-initialization)
4. [Step 2 — Database Connection (No Rebuild)](#4-step-2--database-connection)
5. [Step 3 — Models (Replacing Raw SQL)](#5-step-3--models)
6. [Step 4 — Middleware (Replacing auth.php)](#6-step-4--middleware)
7. [Step 5 — Controllers (Replacing Procedural Logic)](#7-step-5--controllers)
8. [Step 6 — Routes (Replacing Direct File Access)](#8-step-6--routes)
9. [Step 7 — Blade Views (Replacing PHP/HTML Mix)](#9-step-7--blade-views)
10. [Step 8 — Assets & Helpers](#10-step-8--assets--helpers)
11. [File-by-File Migration Map](#11-file-by-file-migration-map)
12. [How to Run](#12-how-to-run)
13. [Default Credentials](#13-default-credentials)

---

## 1. Why Migrate to Laravel?

The native PHP system worked, but had problems that grow worse over time:

| Problem in Native PHP | Laravel Solution |
|---|---|
| SQL queries scattered in every `.php` file | **Eloquent ORM** — one Model per table |
| `include('auth.php')` copy-pasted everywhere | **Middleware** — automatic route protection |
| URLs like `admin/proposals.php?action=approve` | **Named Routes** — `route('admin.proposals.review', $id)` |
| HTML mixed directly with PHP logic | **Blade Templates** — clean separation |
| No CSRF protection on forms | **Built-in `@csrf`** — automatic token on every form |
| Manual `$_SESSION` management | **Auth facade** — `Auth::user()`, `Auth::check()` |
| File uploads with `move_uploaded_file()` | **Storage facade** — `$request->file()->store()` |

---

## 2. Architecture Comparison

### Before (Native PHP)
```
SSC/
├── admin/
│   ├── dashboard.php      ← HTML + SQL + PHP logic ALL in one file
│   ├── proposals.php
│   └── ...
├── officer/
├── student/
├── includes/
│   ├── db.php             ← mysqli_connect()
│   ├── auth.php           ← session_start(), role checks
│   ├── functions.php      ← utility functions
│   ├── header.php         ← repeated in every page
│   └── footer.php
├── assets/css/style.css
└── assets/js/main.js
```

### After (Laravel MVC)
```
ssc-laravel/
├── app/
│   ├── Models/            ← 1 file per database table
│   ├── Http/
│   │   ├── Controllers/   ← Logic only (no HTML)
│   │   │   ├── Admin/
│   │   │   ├── Officer/
│   │   │   └── Student/
│   │   └── Middleware/    ← Role checks (replaces auth.php)
│   └── Helpers/           ← Utility functions
├── resources/views/       ← HTML only (Blade templates)
│   ├── layouts/app.blade.php  ← replaces header.php + footer.php
│   ├── admin/
│   ├── officer/
│   └── student/
├── routes/web.php         ← ALL URLs defined in ONE file
├── public/assets/         ← CSS/JS copied here
└── .env                   ← Database config (replaces db.php)
```

**Key principle:** In native PHP, everything is mixed together. In Laravel, each concern has its own dedicated place.

---

## 3. Step 1 — Project Initialization

```bash
# Create a fresh Laravel project
composer create-project laravel/laravel ssc-laravel

# Generate application encryption key
php artisan key:generate

# Create the public/storage symlink for file uploads
php artisan storage:link
```

This gives us the entire Laravel skeleton with routing, middleware, Eloquent, Blade, and security features pre-configured.

---

## 4. Step 2 — Database Connection

### Native PHP (`includes/db.php`):
```php
$conn = mysqli_connect("localhost", "root", "", "ssc_system");
```

### Laravel (`.env` file):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ssc_system
DB_USERNAME=root
DB_PASSWORD=
```

**What changed:** Instead of creating a connection manually, Laravel reads `.env` and manages the connection automatically. Every Model, Controller, and query uses this connection without you writing `mysqli_connect()` ever again.

**Important:** We kept the existing `ssc_system` database as-is. No need to recreate tables — Laravel connects directly to it.

---

## 5. Step 3 — Models

Models replace **all raw SQL queries**. Each database table gets one Model file.

### Native PHP (raw SQL scattered everywhere):
```php
// In admin/proposals.php
$sql = "SELECT p.*, u.fullname FROM proposals p 
        LEFT JOIN users u ON p.officer_id = u.id 
        WHERE p.status = 'Pending'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) { ... }
```

### Laravel (Eloquent Model + Controller):

**Model** (`app/Models/Proposal.php`):
```php
class Proposal extends Model
{
    protected $fillable = [
        'officer_id', 'project_title', 'requested_budget',
        'description', 'status', ...
    ];

    // Relationships replace JOIN queries
    public function officer() {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
```

**Usage in Controller:**
```php
// This ONE line replaces the entire SQL query + while loop above
$proposals = Proposal::with('officer')->where('status', 'Pending')->get();
```

### All 10 Models Created:

| Model File | Replaces SQL in... | Table |
|---|---|---|
| `User.php` | `auth.php`, `officers.php` | `users` |
| `Budget.php` | `admin/budgets.php` | `budgets` |
| `Proposal.php` | `admin/proposals.php`, `officer/proposals.php` | `proposals` |
| `Expense.php` | `admin/expenses.php`, `officer/expenses.php` | `expenses` |
| `Announcement.php` | `officer/announcements.php` | `announcements` |
| `Feedback.php` | `student/feedback.php`, `admin/feedback.php` | `feedback` |
| `ActivityLog.php` | `admin/logs.php` | `activity_logs` |
| `SchoolYear.php` | `admin/settings.php` | `school_years` |
| `ProposalComment.php` | `student/proposal_details.php` | `proposal_comments` |
| `Liquidation.php` | `officer/allocation.php` | `liquidations` |

---

## 6. Step 4 — Middleware

Middleware replaces the `requireRole()` function from `includes/auth.php`.

### Native PHP (`includes/auth.php`):
```php
session_start();
function requireRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: /login.php');
        exit();
    }
}

// Called at the top of EVERY admin page:
requireRole('admin');
```

### Laravel (`app/Http/Middleware/RoleMiddleware.php`):
```php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
    }
}
```

**Registered in** `bootstrap/app.php`:
```php
$middleware->alias(['role' => RoleMiddleware::class]);
```

**Applied to route groups** (not individual files):
```php
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // ALL admin routes are automatically protected
});
```

**Key difference:** In native PHP, you must remember to call `requireRole()` in every single file. If you forget one, it's a security hole. In Laravel, middleware protects the entire group — impossible to forget.

---

## 7. Step 5 — Controllers

Controllers contain the **logic only** (no HTML). Each native PHP file becomes a Controller method.

### Native PHP (`officer/proposals.php` — logic + HTML mixed):
```php
<?php
require_once '../includes/auth.php';
requireRole('officer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['project_title']);
    $budget = floatval($_POST['requested_budget']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    $sql = "INSERT INTO proposals (officer_id, project_title, requested_budget, description) 
            VALUES ({$_SESSION['user']['id']}, '$title', $budget, '$desc')";
    mysqli_query($conn, $sql);
    header('Location: proposals.php?success=1');
    exit();
}

// Then 200+ lines of HTML below...
?>
<html>...</html>
```

### Laravel Controller (`app/Http/Controllers/Officer/ProposalController.php`):
```php
class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::with('approver')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        
        return view('officer.proposals', compact('proposals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_title'    => 'required|string|max:255',
            'requested_budget' => 'required|numeric|min:1',
            'description'      => 'required|string',
        ]);

        Proposal::create([
            'officer_id'       => Auth::id(),
            'project_title'    => $request->project_title,
            'requested_budget' => $request->requested_budget,
            'description'      => $request->description,
        ]);

        return redirect()->route('officer.proposals')
                         ->with('success', 'Proposal submitted!');
    }
}
```

### What improved:
- **No SQL injection risk** — `$request->validate()` + Eloquent handles escaping
- **No `$_POST` / `$_SESSION`** — `$request->input()` + `Auth::id()`
- **No manual redirects** — `redirect()->route('name')`
- **Validation** — automatic with clear error messages
- **HTML is separate** — the view file handles display

### All Controllers Created:

| Native PHP File | → Laravel Controller | Methods |
|---|---|---|
| `login.php` / `register.php` | `AuthController` | showLogin, login, register, logout |
| `admin/dashboard.php` | `Admin\DashboardController` | index (stats + charts) |
| `admin/budgets.php` | `Admin\BudgetController` | index, store, approve, reject, destroy |
| `admin/proposals.php` | `Admin\ProposalController` | index, review |
| `admin/expenses.php` | `Admin\ExpenseController` | index, review |
| `admin/officers.php` | `Admin\OfficerController` | index, store, toggleStatus, changeRole, destroy |
| `admin/feedback.php` | `Admin\FeedbackController` | index, reply |
| `admin/logs.php` | `Admin\LogController` | index (with pagination) |
| `admin/settings.php` | `Admin\SettingsController` | index, addSchoolYear, activateSchoolYear, deleteSchoolYear |
| `officer/dashboard.php` | `Officer\DashboardController` | index |
| `officer/proposals.php` | `Officer\ProposalController` | index, store, update, complete |
| `officer/expenses.php` | `Officer\ExpenseController` | index, store |
| `officer/announcements.php` | `Officer\AnnouncementController` | index, store, destroy |
| `officer/allocation.php` | `Officer\LiquidationController` | index, store |
| `student/proposals.php` | `Student\ProposalController` | index, show, comment |
| `student/announcements.php` | `Student\AnnouncementController` | index |
| `student/feedback.php` | `Student\FeedbackController` | index, store |
| `student/officers.php` | `Student\OfficerController` | index |

---

## 8. Step 6 — Routes

Routes replace direct file access (`/admin/proposals.php`) with clean URLs.

### Native PHP:
```
http://localhost/SSC/admin/proposals.php?action=approve&id=5
http://localhost/SSC/officer/expenses.php
http://localhost/SSC/student/feedback.php
```

### Laravel (`routes/web.php`):
```php
// All admin routes — protected by auth + admin role
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals');
    Route::post('/proposals/{proposal}/review', [ProposalController::class, 'review'])->name('proposals.review');
    // ...
});

// Officer routes — protected by auth + officer/treasurer role
Route::prefix('officer')->name('officer.')->middleware(['auth', 'role:officer,treasurer'])->group(function () {
    Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals');
    Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    // ...
});

// Student routes
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    // ...
});
```

**Result:** Clean URLs like `/admin/proposals`, `/officer/expenses`, `/student/feedback`

**Named routes** let you reference URLs by name instead of hardcoding paths:
```php
// Instead of: <a href="/admin/proposals.php">
// You write:  <a href="{{ route('admin.proposals') }}">
```

If you ever change the URL structure, you only change it in `routes/web.php` — all links update automatically.

---

## 9. Step 7 — Blade Views

Blade views replace the HTML portions of native PHP files.

### Native PHP (header + content + footer):
```php
<?php include '../includes/header.php'; ?>

<h1>My Proposals</h1>
<table>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= htmlspecialchars($row['project_title']) ?></td>
        <td><?= number_format($row['requested_budget'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php include '../includes/footer.php'; ?>
```

### Laravel Blade (`resources/views/officer/proposals.blade.php`):
```blade
@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<h1>My Proposals</h1>
<table class="table-custom">
    @forelse($proposals as $p)
    <tr>
        <td>{{ $p->project_title }}</td>
        <td>{{ number_format($p->requested_budget, 2) }}</td>
    </tr>
    @empty
    <tr><td colspan="2">No proposals yet.</td></tr>
    @endforelse
</table>
@endsection
```

### Key Blade concepts:

| Blade Syntax | Replaces | Purpose |
|---|---|---|
| `@extends('layouts.app')` | `include('header.php')` + `include('footer.php')` | Uses the master layout |
| `@section('content')` | — | Defines where page content goes |
| `@include('partials.sidebar-admin')` | Copy-pasted sidebar code | Reusable sidebar component |
| `{{ $variable }}` | `<?= htmlspecialchars($var) ?>` | Auto-escaped output (XSS safe) |
| `@csrf` | — | CSRF protection token |
| `@forelse / @empty` | `while` loop + empty check | Loop with built-in empty state |
| `{{ route('name') }}` | Hardcoded URLs | Named route URL generation |
| `{{ old('field') }}` | — | Preserves form input after validation errors |

### The Layout System:

**`layouts/app.blade.php`** = the master template (replaces `header.php` + `footer.php`):
- Contains the sidebar, topbar, flash messages, CSS/JS links
- Has `@yield('content')` where each page inserts its content
- Has `@yield('sidebar-nav')` where each page inserts its navigation

**Each page** just extends this layout and fills in the sections — no more copy-pasting the entire header/footer on every page.

---

## 10. Step 8 — Assets & Helpers

### CSS/JS Assets
The original `assets/css/style.css` and `assets/js/main.js` were copied directly to `public/assets/`. No changes needed — Laravel serves files from `public/` automatically.

```blade
{{-- In Blade templates, use asset() helper --}}
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
<script src="{{ asset('assets/js/main.js') }}"></script>
```

### Helper Class (`app/Helpers/SscHelper.php`)

Replaces `includes/functions.php`:

```php
class SscHelper
{
    // Replaces: function formatCurrency($amount)
    public static function formatCurrency(float $amount): string {
        return '₱' . number_format($amount, 2);
    }

    // Replaces: function statusBadge($status)
    public static function statusBadge(string $status): string { ... }

    // Replaces: function logActivity($userId, $action, $details)
    public static function logActivity(int $userId, string $action, string $details = ''): void {
        ActivityLog::create([...]);  // Uses Eloquent instead of raw SQL
    }
}
```

---

## 11. File-by-File Migration Map

| Native PHP File | → What it became in Laravel |
|---|---|
| `includes/db.php` | `.env` file (DB_* variables) |
| `includes/auth.php` | `app/Http/Middleware/RoleMiddleware.php` |
| `includes/functions.php` | `app/Helpers/SscHelper.php` |
| `includes/header.php` | `resources/views/layouts/app.blade.php` |
| `includes/footer.php` | (merged into `layouts/app.blade.php`) |
| `index.php` (portal selection) | `resources/views/welcome.blade.php` |
| `login.php` | `AuthController@showLogin` + `views/auth/login.blade.php` |
| `register.php` | `AuthController@register` + `views/auth/register.blade.php` |
| `admin/dashboard.php` | `Admin\DashboardController` + `views/admin/dashboard.blade.php` |
| `admin/budgets.php` | `Admin\BudgetController` + `views/admin/budgets.blade.php` |
| `admin/proposals.php` | `Admin\ProposalController` + `views/admin/proposals.blade.php` |
| `admin/expenses.php` | `Admin\ExpenseController` + `views/admin/expenses.blade.php` |
| `admin/officers.php` | `Admin\OfficerController` + `views/admin/officers.blade.php` |
| `admin/logs.php` | `Admin\LogController` + `views/admin/logs.blade.php` |
| `admin/settings.php` | `Admin\SettingsController` + `views/admin/settings.blade.php` |
| `officer/dashboard.php` | `Officer\DashboardController` + `views/officer/dashboard.blade.php` |
| `officer/proposals.php` | `Officer\ProposalController` + `views/officer/proposals.blade.php` |
| `officer/expenses.php` | `Officer\ExpenseController` + `views/officer/expenses.blade.php` |
| `officer/announcements.php` | `Officer\AnnouncementController` + `views/officer/announcements.blade.php` |
| `officer/allocation.php` | `Officer\LiquidationController` + `views/officer/liquidation.blade.php` |
| `student/proposals.php` | `Student\ProposalController` + `views/student/proposals.blade.php` |
| `student/proposal_details.php` | `Student\ProposalController@show` + `views/student/proposal_details.blade.php` |
| `student/feedback.php` | `Student\FeedbackController` + `views/student/feedback.blade.php` |
| `student/announcements.php` | `Student\AnnouncementController` + `views/student/announcements.blade.php` |
| `setup.sql` | Kept as-is + `database/migrations/2026_05_18_000001_add_evolved_schema.php` |

---

## 12. How to Run

### Prerequisites
- PHP 8.2+ (via XAMPP at `C:\xampp\php\php.exe`)
- MySQL running (XAMPP MySQL)
- The `ssc_system` database from `setup.sql`

### Steps
```bash
# 1. Navigate to the project
cd c:\Users\davea\OneDrive\Desktop\SSC\ssc-laravel

# 2. Start MySQL via XAMPP Control Panel

# 3. Run migrations (adds new columns to existing DB)
C:\xampp\php\php.exe artisan migrate

# 4. Start the development server
C:\xampp\php\php.exe artisan serve --port=8000

# 5. Open browser
# http://127.0.0.1:8000
```

---

## 13. Default Credentials

| Portal | Email | Password |
|---|---|---|
| **Admin** | `admin@mcclawis.edu.ph` | `password` |
| **Treasurer** | `treasurer@mcclawis.edu.ph` | `password` |
| **Officer** | `officer@mcclawis.edu.ph` | `password` |
| **Student** | `student@mcclawis.edu.ph` | `password` |

---

## Summary

The migration follows one core principle: **Separation of Concerns**.

```
Native PHP:  One file does EVERYTHING (SQL + Logic + HTML + Auth)
Laravel:     Each concern has its own place:
             → Database queries  → Models
             → Business logic    → Controllers
             → Access control    → Middleware
             → HTML display      → Blade Views
             → URL structure     → Routes
             → Configuration     → .env
```

This makes the code easier to maintain, debug, test, and extend.
