<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black text-gray-900">Manage Payouts</h1>
        <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <div class="mt-6 space-y-4">
        @forelse ($payouts as $payout)
            <div class="bg-white rounded-xl border border-gray-200 p-5" wire:key="ap-{{ $payout->id }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-gray-900">{{ money($payout->amount) }} — {{ $payout->seller?->store_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Requested {{ $payout->created_at->format('M d, Y H:i') }} · {{ $payout->seller?->user?->email }}
                        </p>
                        @if ($payout->bank_details)
                            <p class="text-xs text-gray-600 mt-1.5 max-w-md"><span class="font-semibold">Bank details:</span> {{ $payout->bank_details }}</p>
                        @endif
                        @if ($payout->admin_note)
                            <p class="text-xs mt-1 {{ $payout->status === \App\Enums\PayoutStatus::Rejected ? 'text-red-500' : 'text-gray-500' }}">{{ $payout->admin_note }}</p>
                        @endif
                        @if ($payout->processed_at)
                            <p class="text-[11px] text-gray-400 mt-1">Processed by {{ $payout->processor?->name }} · {{ $payout->processed_at->format('M d, H:i') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        {!! status_badge($payout->status) !!}

                        @if ($payout->status === \App\Enums\PayoutStatus::Pending)
                            <div class="flex gap-2 mt-1">
                                <button wire:click="markPaid({{ $payout->id }})" wire:confirm="Confirm the bank transfer was sent and mark this payout PAID?"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500">Mark as Paid</button>
                                <button
                                        onclick="const r = prompt('Rejection note:', ''); if (r) $wire.reject({{ $payout->id }}, r);"
                                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-500">Reject…</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">No payouts here.</div>
        @endforelse
    </div>

    {{ $payouts->links() }}
</div>
