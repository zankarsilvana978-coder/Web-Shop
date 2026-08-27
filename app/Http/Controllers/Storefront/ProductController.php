<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load(['seller', 'category', 'media']);

        return view('storefront.product-show', [
            'product' => $product,
        ]);
    }
}
