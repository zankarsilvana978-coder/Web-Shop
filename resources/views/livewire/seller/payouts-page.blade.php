<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-black text-gray-900">Payouts</h1>
    <p class="text-sm text-gray-500">Request your earnings via bank transfer. Minimum {{ money($minimum) }}.</p>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    @error('amount')
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>
    @enderror

    <div class="mt-6 grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Available balance</p>
            <p class="mt-2 text-3xl font-black text-emerald-600">{{ money($seller->balance) }}</p>
            <form wire:submit="requestPayout" class="mt-4 space-y-3">
                <input type="number" step="0.01" min="0" wire:model="amount" placeholder="Amount to withdraw"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                <textarea wire:model="bank_details" rows="2" placeholder="Bank details (IBAN, account name…)"
                          class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"></textarea>
                <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">
                    Request payout
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2 text-sm">
            <p class="font-black text-gray-900">How payouts work</p>
            <ol class="list-decimal list-inside space-y-1.5 text-gray-600">
                <li>Orders get paid → your earning lands in <strong>on-hold balance</strong>.</li>
                <li>Buyer receives the item → 14-day return window starts.</li>
                <li>After the window, the amount moves to your available balance.</li>
                <li>Request a payout of {{ money($minimum) }} or more — admin sends it by bank transfer.</li>
            </ol>
            <p class="pt-1 text-xs text-gray-400">Currently on hold: <strong>{{ money($seller->pending_balance) }}</strong></p>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Amount</th><th class="px-5 py-3">Method</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Note</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($payouts as $payout)
                    <tr wire:key="pay-{{ $payout->id }}">
                        <td class="px-5 py-3">{{ $payout->created_at->format('M d, Y') }}</td>
                        <td class="px-5 py-3 font-bold">{{ money($payout->amount) }}</td>
                        <td class="px-5 py-3 capitalize">{{ str_replace('_', ' ', $payout->method) }}</td>
                        <td class="px-5 py-3">{!! status_badge($payout->status) !!}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $payout->admin_note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No payout requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <h2 class="px-5 py-4 text-sm font-black uppercase tracking-wide text-gray-500 border-b border-gray-100">Recent transactions</h2>

        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-50">
                @forelse ($seller->transactions as $txn)
                    <tr wire:key="tx-{{ $txn->id }}">
                        <td class="px-5 py-3">{{ ucfirst($txn->type->value) }}</td>
                        <td class="px-5 py-3 text-gray-500 line-clamp-1">{{ $txn->description }}</td>
                        <td class="px-5 py-3 text-right font-bold {{ $txn->amount >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $txn->amount >= 0 ? '+' : '' }}{{ money($txn->amount) }}
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-400 whitespace-nowrap">{{ $txn->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
