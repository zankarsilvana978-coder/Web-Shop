<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo content for a fresh install: admin, sample approved sellers
 * with live products, one pending seller awaiting moderation.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingsSeeder::class,
        ]);

        $admin = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@soukelkom.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $buyer = User::create([
            'name' => 'Demo Buyer',
            'email' => 'buyer@soukelkom.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $buyer->assignRole('buyer');

        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $fashion = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
        Category::create(['name' => 'Home & Living', 'slug' => 'home-living']);
        Category::create(['name' => 'Beauty & Health', 'slug' => 'beauty-health']);
        Category::create(['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors']);

        $ahmedUser = User::create([
            'name' => 'Ahmed K.',
            'email' => 'ahmed@soukelkom.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $ahmed = Seller::create([
            'user_id' => $ahmedUser->id,
            'store_name' => 'Ahmed Electronics',
            'slug' => 'ahmed-electronics',
            'description' => 'Phones, laptops and gadgets shipped from Beirut.',
            'phone' => '+961 3 111 222',
            'status' => 'approved',
        ]);
        $ahmedUser->assignRole('seller');

        $nadineUser = User::create([
            'name' => 'Nadine S.',
            'email' => 'nadine@soukelkom.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $nadine = Seller::create([
            'user_id' => $nadineUser->id,
            'store_name' => 'Nadine Fashion',
            'slug' => 'nadine-fashion',
            'description' => 'Streetwear and sneakers, delivered across Lebanon.',
            'phone' => '+961 3 333 444',
            'status' => 'approved',
        ]);
        $nadineUser->assignRole('seller');

        $iphone = Product::create([
            'seller_id' => $ahmed->id,
            'category_id' => $electronics->id,
            'name' => 'iPhone 15 128GB',
            'slug' => 'iphone-15-128gb',
            'description' => 'Brand new sealed iPhone 15. Official warranty, ships from Beirut within 48h.',
            'price' => 1000.00,
            'stock' => 10,
            'sku' => 'IP15-128-BLK',
            'status' => ProductStatus::Active,
        ]);
        $this->attachDemoImage($iphone, 'database/seed-assets/iphone15.jpg');

        $case = Product::create([
            'seller_id' => $ahmed->id,
            'category_id' => $electronics->id,
            'name' => 'iPhone Case Clear',
            'slug' => 'iphone-case-clear',
            'description' => 'Shock-absorbing transparent case for iPhone 15 series.',
            'price' => 20.00,
            'stock' => 50,
            'sku' => 'CASE-CLR-15',
            'status' => ProductStatus::Active,
        ]);
        $this->attachDemoImage($case, 'database/seed-assets/iphone-case-clear.jpg');

        $sneakers = Product::create([
            'seller_id' => $nadine->id,
            'category_id' => $fashion->id,
            'name' => 'Nike Air Sneakers',
            'slug' => 'nike-air-sneakers',
            'description' => 'Original Nike Air sneakers. All sizes available.',
            'price' => 50.00,
            'stock' => 25,
            'sku' => 'NIKE-AIR-42',
            'status' => ProductStatus::Active,
        ]);
        $this->attachDemoImage($sneakers, 'database/seed-assets/nike-air.jpg');

        $tshirt = Product::create([
            'seller_id' => $nadine->id,
            'category_id' => $fashion->id,
            'name' => 'Classic T-Shirt',
            'slug' => 'classic-t-shirt',
            'description' => '100% cotton unisex t-shirt. Black and white.',
            'price' => 20.00,
            'stock' => 100,
            'sku' => 'TSHIRT-CLS-M',
            'status' => ProductStatus::Active,
        ]);
        $this->attachDemoImage($tshirt, 'database/seed-assets/classic-t-shirt.jpg');

        $pendingUser = User::create([
            'name' => 'Karim H.',
            'email' => 'karim@soukelkom.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        Seller::create([
            'user_id' => $pendingUser->id,
            'store_name' => 'Karim Home Decor',
            'slug' => 'karim-home-decor-'.Str::lower(Str::random(4)),
            'phone' => '+961 3 555 666',
            'status' => 'pending',
        ]);

        $this->command?->info('Demo accounts: admin@soukelkom.test / ahmed@soukelkom.test / nadine@soukelkom.test / buyer@soukelkom.test — password: password');
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
