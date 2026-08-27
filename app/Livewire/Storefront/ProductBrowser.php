<?php

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductBrowser extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $category = null;

    #[Url(history: true)]
    public $minPrice = null;

    #[Url(history: true)]
    public $maxPrice = null;

    #[Url(history: true)]
    public string $sort = 'newest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->category = null;
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->sort = 'newest';
        $this->resetPage();
    }

    /** Instant as-you-type search via Scout; collection engine locally, Meilisearch in prod. */
    protected function products()
    {
        $query = Product::query()
            ->active()
            ->with(['seller', 'category', 'media']);

        if (filled($this->search)) {
            $ids = Product::search($this->search)
                ->query(fn ($q) => $q->select('id')->active())
                ->keys();

            $query->whereIn('id', $ids);
        }

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        if (filled($this->minPrice)) {
            $query->where('price', '>=', (float) $this->minPrice);
        }

        if (filled($this->maxPrice)) {
            $query->where('price', '<=', (float) $this->maxPrice);
        }

        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return $query->paginate(12);
    }

    public function render()
    {
        return view('livewire.storefront.product-browser', [
            'products' => $this->products(),
            'categories' => Category::query()->active()->withCount(['products' => fn ($q) => $q->whereNull('deleted_at')])->get(),
        ]);
    }
}
