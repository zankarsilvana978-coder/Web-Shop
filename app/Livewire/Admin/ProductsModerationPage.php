<?php

namespace App\Livewire\Admin;

use App\Enums\ProductStatus;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductsModerationPage extends Component
{
    use WithPagination;

    public string $status = ProductStatus::PendingReview->value;

    /** @var array<int, bool> */
    public array $showReject = [];

    public function approve(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['status' => ProductStatus::Active, 'rejection_reason' => null]);

        session()->flash('success', "Product '{$product->name}' approved and live.");
    }

    public function reject(int $productId, string $reason): void
    {
        $product = Product::findOrFail($productId);

        if (trim($reason) === '') {
            session()->flash('error', 'A rejection reason is required.');

            return;
        }

        $product->update(['status' => ProductStatus::Rejected, 'rejection_reason' => trim($reason)]);

        $this->showReject[$productId] = false;
        session()->flash('success', "Product '{$product->name}' rejected with reason.");
    }

    public function render()
    {
        return view('livewire.admin.products-moderation-page', [
            'products' => Product::query()
                ->with(['seller:id,store_name,user_id', 'category:id,name', 'media'])
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
            'statuses' => ProductStatus::cases(),
        ]);
    }
}
