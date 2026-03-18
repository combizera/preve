# Preve - Project Context for AI

This file helps AI assistants understand the Preve project structure, patterns, and conventions.

## Project Overview

**Preve** is a personal finance management application built with:
- **Backend**: Laravel 13 + Inertia.js
- **Frontend**: Vue 3 + TypeScript + Tailwind CSS
- **UI Components**: shadcn/ui pattern
- **Database**: MySQL (with user-scoped data)

## Core Entities

1. **User** - Authentication and ownership
2. **Category** - Transaction categories (with color and icon)
3. **Tag** - Optional transaction tags
4. **Transaction** - Income/Expense records

All entities are **user-scoped** (multi-tenant at user level).

---

## Backend Patterns

### File Structure
```
app/
├── Enums/TransactionType.php
├── Http/Controllers/{Entity}Controller.php
├── Http/Requests/Create{Entity}Request.php
├── Models/{Entity}.php
└── Policies/{Entity}Policy.php
```

### Form Requests Pattern
```php
class CreateCategoryRequest extends FormRequest
{
    use GeneratesUniqueSlug; // Trait for shared logic

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:...'],
        ];
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }
}
```

**Key Points:**
- ✅ Use traits in `/Concerns/` for shared logic (e.g., slug generation)
- ✅ Slugs are auto-generated in `prepareForValidation()`
- ✅ User-scoped uniqueness checks

### Controller Pattern
```php
class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $transactions = Auth::user()->transactions()->get();
        $categories = Auth::user()->categories()->get();

        return Inertia::render('Transaction', compact('transactions', 'categories'));
    }

    public function store(CreateTransactionRequest $request)
    {
        Auth::user()->transactions()->create($request->validated());
        return to_route('transactions.index');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        $transaction->update($request->all());
        return to_route('transactions.index');
    }
}
```

**Key Points:**
- ✅ Use `Inertia::render()` for views
- ✅ Use `compact()` for passing data
- ✅ Use `Auth::user()->relationships()` for user-scoped queries
- ✅ Use `$this->authorize()` with policies
- ✅ Use `to_route()` for redirects

### Model Pattern
```php
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'amount', 'type', ...];

    protected $casts = [
        'transaction_date' => 'datetime',
        'type' => TransactionType::class,
    ];

    public function user(): BelongsTo { ... }
    public function category(): BelongsTo { ... }
}
```

### Enum Pattern
```php
enum TransactionType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => 'Income',
            self::EXPENSE => 'Expense',
        };
    }
}
```

---

## Frontend Patterns

### File Structure
```
resources/js/
├── components/
│   ├── ui/                          # shadcn/ui components
│   │   ├── button/, dialog/, input/, select/, toggle-group/
│   ├── Category/                    # Entity components
│   │   ├── CreateCategory.vue
│   │   ├── EditCategoryDialog.vue
│   │   ├── DeleteCategoryDialog.vue
│   │   └── TableCategory.vue
│   └── Transaction/
├── enums/transaction-type.ts        # Frontend constants
├── lib/
│   ├── currency.ts                  # Utility functions
│   ├── category-colors.ts
│   └── category-icons.ts
├── pages/Category.vue               # Inertia pages
├── routes/categories.ts             # Route helpers
└── types/models/category.d.ts       # TypeScript types
```

### Type Definitions Pattern

**IMPORTANT:** All model interfaces use `I` prefix:

```typescript
// types/models/transaction.d.ts
export type TransactionType = 'income' | 'expense';

export interface ITransaction {
    id?: string;
    category_id: number;
    category?: ICategory;       // Relationship
    tag_id: number | null;      // Optional foreign key
    tag?: ITag | null;          // Optional relationship
    amount: number;             // In cents (integer)
    type: TransactionType;
    description: string;
    notes: string | null;       // Optional field
    transaction_date: string;
    created_at?: string;
    updated_at?: string;
}
```

### Constants Pattern (NOT TypeScript Enums)

```typescript
// enums/transaction-type.ts
import type { TransactionType } from '@/types/models/transaction';

export const TRANSACTION_TYPE = {
    INCOME: 'income' as TransactionType,
    EXPENSE: 'expense' as TransactionType,
};
```

**Why not TypeScript enums?**
- Better Inertia.js compatibility
- Predictable JSON serialization
- No runtime overhead

### Form Pattern (ALWAYS use useForm)

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store } from '@/routes/transactions';
import type { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

defineProps<{
  categories: ICategory[];
}>();

const form = useForm<ITransaction>({
  category_id: 0,
  tag_id: null,           // Optional = null
  amount: 0,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  notes: null,            // Optional = null
  transaction_date: new Date().toISOString().split('T')[0],
});

const createTransaction = () => {
  form.submit(store(), {
    onSuccess: () => {
      open.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <form @submit.prevent="createTransaction">
    <Input v-model="form.name" />
    <InputError :message="form.errors.name" />

    <Button type="submit" :disabled="form.processing">
      Create
    </Button>
  </form>
</template>
```

**Key Points:**
- ✅ ALWAYS use `useForm()` composable (never `<Form>` component or `router.*`)
- ✅ Show errors with `<InputError :message="form.errors.field" />`
- ✅ Disable buttons with `:disabled="form.processing"`
- ✅ Use `null` for optional fields (not `0` or `''`)
- ✅ Use `form.submit(route(), { onSuccess, onError })`

### Component Structure Pattern

```vue
<script setup lang="ts">
// 1. Imports (grouped)
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// UI Components
import { Button } from '@/components/ui/button';

// Utils
import { formatCentsToDisplay } from '@/lib/currency';

// Types
import type { ICategory } from '@/types/models/category';

// 2. Props/Model
const open = defineModel<boolean>('open', { required: true });

defineProps<{
  categories: ICategory[];
}>();

// 3. State
const form = useForm({ ... });

// 4. Computed
const displayAmount = computed(() => ...);

// 5. Methods
const handleSubmit = () => { ... };
</script>

<template>
  <!-- Template -->
</template>

<style scoped>
/* Only if necessary */
</style>
```

### Utility Functions Pattern

```typescript
// lib/currency.ts

/**
 * Formats a value in cents to Brazilian currency display format
 * @param cents - Value in cents (e.g., 12510 = R$ 125.10)
 * @returns Formatted string (e.g., "125,10")
 */
export function formatCentsToDisplay(cents: number | string): string {
  const numericCents = typeof cents === 'string' ? parseInt(cents, 10) : cents;
  if (isNaN(numericCents) || numericCents === 0) return '0,00';
  return (numericCents / 100).toFixed(2).replace('.', ',');
}
```

**Key Points:**
- ✅ Pure functions (no side effects)
- ✅ Include examples in comments
- ✅ Place in `/lib/` folder

---

## Important Conventions

### Naming

**Backend:**
- Classes: `{Entity}Controller`, `Create{Entity}Request`, `{Entity}Policy`
- Methods: RESTful (index, store, show, update, destroy)
- Variables: `$user`, `$validated`, `$transactions`

**Frontend:**
- Files: PascalCase for components, kebab-case for utilities
- Interfaces: `I` prefix (IUser, ICategory, ITransaction)
- Constants: UPPERCASE (`TRANSACTION_TYPE`)
- Functions: camelCase (`formatCentsToDisplay`)

### Data Flow

1. **Backend** returns full objects: `$categories = Auth::user()->categories()->get()`
2. **Inertia** passes to frontend: `Inertia::render('Page', compact('categories'))`
3. **Frontend** receives typed props: `defineProps<{ categories: ICategory[] }>()`
4. **Form** submits with `form.submit(route())`
5. **Backend** validates with FormRequest, saves, redirects

### Money/Currency Handling

- **Backend**: Stores amount in **cents (integer)** in database
- **Frontend**:
  - Displays formatted: `formatCentsToDisplay(12510)` → `"125,10"`
  - Sends as integer cents: `parseToCents("12510")` → `12510`
  - Uses currency mask input

### Special UI Patterns

**Currency Input with Mask:**
```vue
<Input
  v-model="displayAmount"
  inputmode="numeric"
  @keypress="(e) => { if (!/[0-9]/.test(e.key)) e.preventDefault(); }"
  class="text-right font-mono"
/>
```

**Date Input with Visible Icon:**
```vue
<Input
  type="date"
  v-model="form.transaction_date"
  class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
/>
```

**Toggle Group for Type:**
```vue
<ToggleGroup v-model="form.type">
  <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1 gap-2">
    <ArrowUpRight :size="16" />
    Income
  </ToggleGroupItem>
  <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1 gap-2">
    <ArrowDownLeft :size="16" />
    Expense
  </ToggleGroupItem>
</ToggleGroup>
```

---

## Critical Rules

### ✅ ALWAYS DO

**Backend:**
- Use Form Requests for validation
- Extract shared logic to traits in `/Concerns/`
- Use `Inertia::render()` for views
- Use policies for authorization (`$this->authorize()`)
- Use `compact()` for passing data
- Use `Auth::user()` for current user
- Use `to_route()` for redirects

**Frontend:**
- Use `useForm()` composable for ALL forms
- Use interfaces with `I` prefix (IUser, ICategory, ITransaction)
- Show validation errors: `<InputError :message="form.errors.field" />`
- Disable buttons during submit: `:disabled="form.processing"`
- Use `null` for optional fields (not `''` or `0`)
- Extract utilities to `/lib/` folder
- Document utility functions with JSDoc (English)
- Use `import type` for type-only imports

### ❌ NEVER DO

**Backend:**
- Don't validate in controllers (use Form Requests)
- Don't use `auth()->user()` (use `Auth::user()`)
- Don't return views directly (use Inertia)
- Don't check authorization manually (use policies)

**Frontend:**
- Don't use `<Form>` component (use `useForm()`)
- Don't use `router.post/put/delete` (use `form.submit()`)
- Don't use TypeScript enums (use const objects)
- Don't use empty strings for optional fields (use `null`)
- Don't forget `form.errors` and `form.processing`
- Don't put complex logic in templates (use computed/methods)

---

## Quick Reference

### Creating a New Entity

1. **Backend:**
   - Create migration
   - Create model with relationships
   - Create FormRequest (use trait if needs slug)
   - Create controller (use Inertia)
   - Create policy if needed
   - Add routes

2. **Frontend:**
   - Create type in `/types/models/{entity}.d.ts` (use `I` prefix)
   - Create components folder: `/components/{Entity}/`
   - Create page in `/pages/{Entity}.vue`
   - Create route helpers (Ziggy)
   - Add entity-specific utilities if needed

### Common File Locations

- **Form validation**: `app/Http/Requests/`
- **Shared request logic**: `app/Http/Requests/Concerns/`
- **Type definitions**: `resources/js/types/models/`
- **Utility functions**: `resources/js/lib/`
- **UI components**: `resources/js/components/ui/`
- **Entity components**: `resources/js/components/{Entity}/`
- **Pages**: `resources/js/pages/`

---

**Last Updated**: 2026-01-25
**Laravel Version**: 13
**Vue Version**: 3
**TypeScript**: Strict mode

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `wayfinder-development` — Activates whenever referencing backend routes in frontend components. Use when importing from @/actions or @/routes, calling Laravel routes from TypeScript, or working with Wayfinder route functions.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-vue-development` — Develops Inertia.js v2 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using <Link>, <Form>, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

# Laravel Wayfinder

Wayfinder generates TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

- IMPORTANT: Activate `wayfinder-development` skill whenever referencing backend routes in frontend components.
- Invokable Controllers: `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Parameter Binding: Detects route keys (`{post:slug}`) — `show({ slug: "my-post" })`.
- Query Merging: `show(1, { mergeQuery: { page: 2, sort: null } })` merges with current URL, `null` removes params.
- Inertia: Use `.form()` with `<Form>` component or `form.submit(store())` with useForm.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
