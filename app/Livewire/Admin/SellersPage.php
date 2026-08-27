<?php

namespace App\Livewire\Admin;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\Setting;
use App\Notifications\SellerApprovedNotification;
use App\Notifications\SellerRejectedNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SellersPage extends Component
{
    public string $status = '';

    /** @var array<int, string> */
    public array $commissionRates = [];

    protected function rules(): array
    {
        return [
            'commissionRates.*' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function approve(int $sellerId): void
    {
        $seller = Seller::findOrFail($sellerId);

        if ($seller->status !== SellerStatus::Pending) {
            return;
        }

        $seller->update(['status' => SellerStatus::Approved, 'rejection_reason' => null]);
        $seller->user->assignRole('seller');
        $seller->user->notify(new SellerApprovedNotification($seller));

        session()->flash('success', "Seller '{$seller->store_name}' approved.");
    }

    public function reject(int $sellerId, string $reason = 'Application did not meet our criteria.'): void
    {
        $seller = Seller::findOrFail($sellerId);

        if ($seller->status !== SellerStatus::Pending) {
            return;
        }

        $seller->update(['status' => SellerStatus::Rejected, 'rejection_reason' => $reason]);
        $seller->user->notify(new SellerRejectedNotification($seller));

        session()->flash('success', "Seller '{$seller->store_name}' rejected.");
    }

    public function suspend(int $sellerId): void
    {
        $seller = Seller::findOrFail($sellerId);

        if ($seller->status === SellerStatus::Approved) {
            $seller->update(['status' => SellerStatus::Suspended]);
            session()->flash('success', "Seller '{$seller->store_name}' suspended.");
        }
    }

    public function reactivate(int $sellerId): void
    {
        $seller = Seller::findOrFail($sellerId);
        $seller->update(['status' => SellerStatus::Approved]);
        session()->flash('success', "Seller '{$seller->store_name}' reactivated.");
    }

    /** Per-seller commission override; empty = fall back to global rate. */
    public function saveCommission(int $sellerId): void
    {
        $this->validate();

        $rate = $this->commissionRates[$sellerId] ?? null;

        Seller::whereKey($sellerId)->update([
            'commission_rate' => filled($rate) ? round((float) $rate, 2) : null,
        ]);

        session()->flash('success', 'Commission rate updated.');
    }

    public function render()
    {
        return view('livewire.admin.sellers-page', [
            'sellers' => Seller::query()
                ->with(['user:id,name,email'])
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
            'globalRate' => (float) Setting::get('global_commission_rate'),
        ]);
    }
}
