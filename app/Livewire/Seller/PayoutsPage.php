<?php

namespace App\Livewire\Seller;

use App\Models\Setting;
use App\Services\PayoutService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PayoutsPage extends Component
{
    public string $amount = '';

    public string $bank_details = '';

    protected function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01|max:999999',
            'bank_details' => 'nullable|string|max:1000',
        ];
    }

    public function requestPayout(PayoutService $payouts): void
    {
        $data = $this->validate();

        $payout = $payouts->request(
            auth()->user()->seller,
            round((float) $data['amount'], 2),
            $data['bank_details'] ?: null,
        );

        $this->reset(['amount', 'bank_details']);
        session()->flash('success', "Payout request for \${$payout->amount} submitted. Balance updated.");
    }

    public function render()
    {
        $seller = auth()->user()->seller->load('transactions:id,seller_id,type,amount,description,created_at');

        return view('livewire.seller.payouts-page', [
            'seller' => $seller,
            'minimum' => (float) Setting::get('payout_min'),
            'payouts' => $seller->payouts()->with('processor:id,name')->take(20)->get(),
        ]);
    }
}
