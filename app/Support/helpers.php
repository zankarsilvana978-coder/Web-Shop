<?php

if (! function_exists('money')) {
    /** Format an amount as USD with 2 decimals, e.g. $1,234.56 */
    function money(float|string|null $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }
}

if (! function_exists('seller_status_badge')) {
    /** Tailwind badge classes for a backed-enum with color()/label(). */
    function status_badge(mixed $status): string
    {
        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.$status->color().'">'
            .$status->label()
            .'</span>';
    }
}
