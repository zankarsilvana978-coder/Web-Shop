<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo content for a fresh install: admin, sample approved sellers
 * with live products, one pending seller awaiting moderation.
 * Idempotent: safe to run on every boot (db:seed --force).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingsSeeder::class,
        ]);

        $admin = $this->user('Platform Admin', 'admin@soukelkom.test');
        $admin->syncRoles('admin');

        $buyer = $this->user('Demo Buyer', 'buyer@soukelkom.test');
        $buyer->syncRoles('buyer');

        $electronics = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']);
        $fashion = Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion']);
        Category::firstOrCreate(['slug' => 'home-living'], ['name' => 'Home & Living']);
        Category::firstOrCreate(['slug' => 'beauty-health'], ['name' => 'Beauty & Health']);
        Category::firstOrCreate(['slug' => 'sports-outdoors'], ['name' => 'Sports & Outdoors']);

        $ahmed = $this->seller(
            'Ahmed K.',
            'ahmed@soukelkom.test',
            'Ahmed Electronics',
            'ahmed-electronics',
            'Phones, laptops and gadgets shipped from Beirut.',
            '+961 3 111 222',
        );

        $nadine = $this->seller(
            'Nadine S.',
            'nadine@soukelkom.test',
            'Nadine Fashion',
            'nadine-fashion',
            'Streetwear and sneakers, delivered across Lebanon.',
            '+961 3 333 444',
        );

        $this->product($ahmed, $electronics, 'iPhone 15 128GB', 'iphone-15-128gb',
            'Brand new sealed iPhone 15. Official warranty, ships from Beirut within 48h.',
            1000.00, 10, 'IP15-128-BLK', 'database/seed-assets/iphone15.jpg');

        $this->product($ahmed, $electronics, 'iPhone Case Clear', 'iphone-case-clear',
            'Shock-absorbing transparent case for iPhone 15 series.',
            20.00, 50, 'CASE-CLR-15', 'database/seed-assets/iphone-case-clear.jpg');

        $this->product($nadine, $fashion, 'Nike Air Sneakers', 'nike-air-sneakers',
            'Original Nike Air sneakers. All sizes available.',
            50.00, 25, 'NIKE-AIR-42', 'database/seed-assets/nike-air.jpg');

        $this->product($nadine, $fashion, 'Classic T-Shirt', 'classic-t-shirt',
            '100% cotton unisex t-shirt. Black and white.',
            20.00, 100, 'TSHIRT-CLS-M', 'database/seed-assets/classic-t-shirt.jpg');

        $karimUser = $this->user('Karim H.', 'karim@soukelkom.test');
        Seller::firstOrCreate(
            ['user_id' => $karimUser->id],
            [
                'store_name' => 'Karim Home Decor',
                'slug' => 'karim-home-decor-'.Str::lower(Str::random(4)),
                'phone' => '+961 3 555 666',
                'status' => SellerStatus::Pending,
            ],
        );

        $this->command?->info('Demo accounts: admin@soukelkom.test / ahmed@soukelkom.test / nadine@soukelkom.test / buyer@soukelkom.test — password: password');
    }

    protected function user(string $name, string $email): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
        );
    }

    protected function seller(
        string $userName,
        string $userEmail,
        string $storeName,
        string $slug,
        string $description,
        string $phone,
    ): Seller {
        $user = $this->user($userName, $userEmail);
        $user->syncRoles('seller');

        return Seller::firstOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $user->id,
                'store_name' => $storeName,
                'description' => $description,
                'phone' => $phone,
                'status' => SellerStatus::Approved,
            ],
        );
    }

    protected function product(
        Seller $seller,
        Category $category,
        string $name,
        string $slug,
        string $description,
        float $price,
        int $stock,
        string $sku,
        string $imagePath,
    ): Product {
        $product = Product::firstOrCreate(
            ['slug' => $slug],
            [
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'sku' => $sku,
                'status' => ProductStatus::Active,
            ],
        );

        if ($product->getMedia(Product::IMAGE_COLLECTION)->isEmpty()) {
            $this->attachDemoImage($product, $imagePath);
        }

        return $product;
    }

    /** Attach a bundled demo image to a product if the file exists. */
    protected function attachDemoImage(Product $product, string $relativePath): void
    {
        $path = base_path($relativePath);

        if (is_file($path)) {
            $product->addMedia($path)->toMediaCollection(Product::IMAGE_COLLECTION);
        }
    }
}