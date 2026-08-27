# SOUKELKOM — The Local Marketplace Where Everyone Wins

A multi-vendor marketplace for Lebanon & MENA built with **Laravel 12**, **Livewire 3**, **Alpine.js** and **TailwindCSS**.

The platform owner owns no inventory and ships nothing: approved sellers list products, buyers pay once, and the system automatically splits every payment into **platform commission** + **seller earnings**, holds earnings for a return window, then pays sellers out.

---

## Business Rules Implemented

| Rule | Implementation |
|---|---|
| One cart, multiple sellers, one payment | `CheckoutService` creates ONE order with N order items (one per seller product) |
| Commission split frozen per item | `order_items.commission_amount` / `seller_earning`, resolved by `CommissionService` |
| Rate precedence | Product override → Seller override → Global (Site Settings) |
| Ship by seller | Each order item has its own lifecycle; seller ships within 48h with tracking |
| Auto-cancel + refund | `soukelkom:cancel-unshipped-items` (hourly) cancels unshipped items after the deadline, restocks and reverses the held earning |
| 14-day earning hold | Earnings sit in `pending_balance`; released to `balance` by `soukelkom:release-earnings` (daily at 03:00) after delivery + hold days |
| Payouts ≥ $50 | Balance locked on request; admin marks paid/rejected; every cent logged in `transactions` ledger |
| Manual bank transfer | Buyer uploads proof → admin verifies → order paid → distribution chain fires |
| Stripe Connect | Test-mode integration behind config; activates when `STRIPE_SECRET` is set |

---

## Requirements

- PHP >= 8.2 with `pdo_mysql`, `mbstring`, `openssl`, `curl`, `zip`, `fileinfo`, `gd`, `intl`
- Composer 2.x
- MariaDB 10.4+ / MySQL 8 (XAMPP works out of the box)
- Node.js 18+

## Install

```bash
cd soukelkom
composer install
npm install && npm run build
```

Create `.env` (copy from `.env.example`) and configure:

```env
APP_NAME=Soukelkom
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=soukelkom
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database     # redis + Horizon in production
SCOUT_DRIVER=collection       # meilisearch in production
MAIL_MAILER=log               # smtp in production

STRIPE_KEY=
STRIPE_SECRET=
```

Then:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### Run locally (Windows)

```powershell
# terminal 1
php artisan serve
# terminal 2 — queue worker (emails + earnings distribution)
php artisan queue:listen --tries=1
# scheduler (production cron): * * * * * php artisan schedule:run
```

## Demo accounts (password: `password`)

| Role | Email |
|---|---|
| Admin | admin@soukelkom.test |
| Seller | ahmed@soukelkom.test |
| Seller | nadine@soukelkom.test |
| Buyer | buyer@soukelkom.test |

## Run the QA suite

```bash
php artisan test
```

41 tests / 132 assertions covering the six acceptance scenarios:

1. Seller onboarding (apply → approve → `/seller` access)
2. Product lifecycle (submit → approve → live on homepage)
3. Multi-seller checkout (1 order, 2 items, exact commission math)
4. Earnings distribution (pending balances + immutable ledger)
5. Payout flow ($50 min, lock on request, admin approval)
6. Security (cross-seller access → 403, role-guarded areas)

Plus: 48h auto-cancel/refund, 14-day hold release, commission precedence unit tests.

---

## Architecture

```
app/
├── Enums/          OrderStatus, ProductStatus, SellerStatus, ...
├── Events/         OrderPaid
├── Jobs/           DistributeEarnings (queued, idempotent)
├── Listeners/      ProcessPaidOrder (notifications + job dispatch)
├── Console/Commands/
│   ├── CancelUnshippedItems.php   (48h SLA enforcement)
│   └── ReleaseEarnings.php        (hold window release)
├── Http/Controllers/Storefront/ProductController.php
├── Livewire/       Storefront · Cart · Checkout · Buyer · Sell · Seller · Admin
├── Notifications/  Queued mail + database notifications
├── Policies/       ProductPolicy, OrderItemPolicy (+ Gate::before admin bypass)
└── Services/       CommissionService, CheckoutService, PaymentService,
                    CartService, EarningsService, PayoutService, ShippingStateService
```

Key tables: `sellers` (balances, status, commission override), `products` (lifecycle + media), `orders`, **`order_items`** (the money-split source of truth), `payouts`, `transactions` (accounting ledger), `settings`.

All money columns are `decimal(10,2)`. Foreign keys everywhere; indexes on `seller_id`, `order_id`, status pairs. Soft deletes on business entities.

## Production notes

- **Queue**: set `QUEUE_CONNECTION=redis`, `composer require laravel/horizon` (requires ext-pcntl/redis), run `php artisan horizon`.
- **Search**: install Meilisearch, set `SCOUT_DRIVER=meilisearch` + `MEILISEARCH_HOST/KEY`, then `php artisan scout:import "App\Models\Product"`.
- **Stripe**: fill `STRIPE_*` keys; card payments appear at checkout automatically.
- **Scheduler**: add the Laravel cron entry for `schedule:run`.
