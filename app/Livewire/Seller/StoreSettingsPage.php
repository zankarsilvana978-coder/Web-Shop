<?php

namespace App\Livewire\Seller;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class StoreSettingsPage extends Component
{
    public string $store_name = '';

    public string $phone = '';

    public string $description = '';

    public function mount(): void
    {
        $seller = auth()->user()->seller;

        $this->fill([
            'store_name' => $seller->store_name,
            'phone' => (string) ($seller->phone ?? ''),
            'description' => (string) ($seller->description ?? ''),
        ]);
    }

    protected function rules(): array
    {
        return [
            'store_name' => 'required|string|min:3|max:100',
            'phone' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        auth()->user()->seller->update($this->validate());

        session()->flash('success', 'Store settings saved.');
    }

    public function render()
    {
        return view('livewire.seller.store-settings-page', [
            'commissionRate' => auth()->user()->seller->commission_rate ?? Setting::get('global_commission_rate'),
        ]);
    }
}
