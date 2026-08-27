<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SettingsPage extends Component
{
    public string $site_name = '';

    public string $support_email = '';

    public $global_commission_rate;

    public $shipping_flat_rate;

    public $payout_min;

    public $ship_deadline_hours;

    public $earning_hold_days;

    public function mount(): void
    {
        $settings = Setting::current();

        $this->fill($settings->only([
            'site_name',
            'support_email',
            'global_commission_rate',
            'shipping_flat_rate',
            'payout_min',
            'ship_deadline_hours',
            'earning_hold_days',
        ]));
    }

    protected function rules(): array
    {
        return [
            'site_name' => 'required|string|max:100',
            'support_email' => 'nullable|email|max:255',
            'global_commission_rate' => 'required|numeric|min:0|max:100',
            'shipping_flat_rate' => 'required|numeric|min:0|max:9999',
            'payout_min' => 'required|numeric|min:0|max:999999',
            'ship_deadline_hours' => 'required|integer|min:1|max:720',
            'earning_hold_days' => 'required|integer|min:0|max:365',
        ];
    }

    public function save(): void
    {
        Setting::current()->update($this->validate());

        session()->flash('success', 'Site settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings-page');
    }
}
