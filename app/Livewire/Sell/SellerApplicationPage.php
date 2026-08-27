<?php

namespace App\Livewire\Sell;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\SellerApplicationSubmittedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SellerApplicationPage extends Component
{
    public string $store_name = '';

    public string $phone = '';

    public string $description = '';

    public bool $submitted = false;

    public function mount(): void
    {
        if ($existing = auth()->user()->seller) {
            $this->submitted = true;
            $this->store_name = $existing->store_name;
        }
    }

    protected function rules(): array
    {
        return [
            'store_name' => 'required|string|min:3|max:100',
            'phone' => 'required|string|max:30',
            'description' => 'required|string|min:20|max:2000',
        ];
    }

    public function submit(): void
    {
        $user = auth()->user();

        if ($user->seller) {
            return;
        }

        $data = $this->validate();

        $seller = Seller::create([
            ...$data,
            'user_id' => $user->id,
            'slug' => Str::slug($data['store_name']).'-'.Str::lower(Str::random(5)),
            'status' => SellerStatus::Pending,
        ]);

        Notification::send(
            User::role('admin')->get(),
            new SellerApplicationSubmittedNotification($seller),
        );

        $this->submitted = true;
        session()->flash('success', 'Application received! We will review it and email you the decision.');
    }

    public function render()
    {
        return view('livewire.sell.seller-application-page');
    }
}
