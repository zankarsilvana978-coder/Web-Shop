<?php

namespace App\Livewire\Admin;

use App\Models\Payout;
use App\Services\PayoutService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PayoutsPage extends Component
{
    public string $status = 'pending';

    /** @var array<int, string> */
    public array $rejectNotes = [];

    public function markPaid(int $payoutId, PayoutService $payouts): void
    {
        $payout = Payout::findOrFail($payoutId);

        $payouts->markPaid($payout, auth()->user());

        session()->flash('success', "Payout of \${$payout->amount} marked as paid.");
    }

    public function reject(int $payoutId, PayoutService $payouts): void
    {
        $note = trim($this->rejectNotes[$payoutId] ?? '');

        if ($note === '') {
            session()->flash('error', 'A rejection note is required.');

            return;
        }

        $payout = Payout::findOrFail($payoutId);

        $payouts->reject($payout, auth()->user(), $note);

        session()->flash('success', "Payout rejected; \${$payout->amount} returned to seller balance.");
    }

    public function render()
    {
        return view('livewire.admin.payouts-page', [
            'payouts' => Payout::query()
                ->with(['seller:id,store_name,user_id', 'seller.user:id,name,email'])
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
        ]);
    }
}
