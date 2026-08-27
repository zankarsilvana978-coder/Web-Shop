<?php

use App\Http\Controllers\Storefront\ProductController;
use App\Livewire\Admin\DashboardPage as AdminDashboard;
use App\Livewire\Admin\OrdersPage as AdminOrders;
use App\Livewire\Admin\PayoutsPage as AdminPayouts;
use App\Livewire\Admin\ProductsModerationPage as AdminProducts;
use App\Livewire\Admin\SellersPage as AdminSellers;
use App\Livewire\Admin\SettingsPage as AdminSettings;
use App\Livewire\Buyer\OrderDetailPage;
use App\Livewire\Buyer\OrderIndexPage;
use App\Livewire\Cart\CartPage;
use App\Livewire\Checkout\CheckoutPage;
use App\Livewire\Sell\SellerApplicationPage;
use App\Livewire\Seller\DashboardPage as SellerDashboard;
use App\Livewire\Seller\OrdersPage as SellerOrders;
use App\Livewire\Seller\PayoutsPage as SellerPayouts;
use App\Livewire\Seller\ProductsPage as SellerProducts;
use App\Livewire\Seller\StoreSettingsPage as SellerStoreSettings;
use App\Livewire\Storefront\ProductBrowser;
use Illuminate\Support\Facades\Route;

Route::get('/', ProductBrowser::class)->name('home');
Route::get('/category/{category:slug}', ProductBrowser::class)->name('categories.show');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');

require __DIR__.'/auth.php';

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->isSeller()) {
        return redirect()->route('seller.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/cart', CartPage::class)->name('cart');
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/orders', OrderIndexPage::class)->name('orders.index');
    Route::get('/orders/{order}', OrderDetailPage::class)->name('orders.show');
    Route::get('/become-a-seller', SellerApplicationPage::class)->name('sell.apply');
});

Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/', SellerDashboard::class)->name('dashboard');
    Route::get('/products', SellerProducts::class)->name('products');
    Route::get('/orders', SellerOrders::class)->name('orders');
    Route::get('/payouts', SellerPayouts::class)->name('payouts');
    Route::get('/settings', SellerStoreSettings::class)->name('settings');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/sellers', AdminSellers::class)->name('sellers');
    Route::get('/products', AdminProducts::class)->name('products');
    Route::get('/orders', AdminOrders::class)->name('orders');
    Route::get('/payouts', AdminPayouts::class)->name('payouts');
    Route::get('/settings', AdminSettings::class)->name('settings');
});
