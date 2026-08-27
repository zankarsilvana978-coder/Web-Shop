<?php

namespace App\Livewire\Seller;

use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

#[Layout('layouts.app')]
class ProductsPage extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public $price = null;

    public $stock = 1;

    public string $sku = '';

    public $category_id = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $images = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0|max:100000',
            'sku' => 'nullable|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|max:4096',
            'images' => 'max:5',
        ];
    }

    protected $validationAttributes = [
        'images.*' => 'image',
    ];

    public function createNew(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);

        $this->authorize('update', $product);

        $this->fill([
            'editingId' => $product->id,
            'name' => $product->name,
            'description' => (string) $product->description,
            'price' => (float) $product->price,
            'stock' => $product->stock,
            'sku' => (string) ($product->sku ?? ''),
            'category_id' => $product->category_id,
        ]);

        $this->reset('images');
        $this->showForm = true;
    }

    /**
     * Save as draft or submit for admin review. New products start as
     * pending_review when submitted; admins approve -> active.
     */
    public function save(bool $submitForReview = false): void
    {
        $data = $this->validate();

        $seller = auth()->user()->seller;

        if ($submitForReview && $seller->status !== SellerStatus::Approved) {
            $this->addError('form', 'Your store must be approved before submitting products for review.');

            return;
        }

        $payload = [
            ...collect($data)->except(['images'])->all(),
            'price' => round((float) $data['price'], 2),
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
        ];

        if ($this->editingId) {
            unset($payload['slug']);
        }

        $product = $seller->products()->updateOrCreate(['id' => $this->editingId], $payload);

        foreach ($this->images as $image) {
            try {
                $product
                    ->addMedia($image->getRealPath())
                    ->usingFilename(Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$image->extension())
                    ->toMediaCollection(Product::IMAGE_COLLECTION);
            } catch (FileIsTooBig) {
                $this->addError('images', 'One of the images is too large (max 4MB).');
            }
        }

        if ($submitForReview && $product->fresh()->status === ProductStatus::Draft) {
            $product->update(['status' => ProductStatus::PendingReview]);
        }

        $this->resetForm();
        $this->showForm = false;
        session()->flash('success', $submitForReview ? "Product '{$product->name}' submitted for review." : "Product '{$product->name}' saved as draft.");
    }

    public function delete(int $id): void
    {
        $product = Product::query()->findOrFail($id);

        $this->authorize('delete', $product);

        $product->delete();
        session()->flash('success', 'Product deleted.');
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'description', 'price', 'stock', 'sku', 'category_id', 'images', 'editingId']);
        $this->stock = 1;
    }

    public function render()
    {
        return view('livewire.seller.products-page', [
            'products' => auth()->user()->seller->products()->with(['category', 'media'])->paginate(10),
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }
}
