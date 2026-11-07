<?php
/**
 * CLAIMORA Global Helper Functions
 */

if (!function_exists('e')) {
    function e(?string $value): string {
        return \Core\Security::escape($value);
    }
}

if (!function_exists('flash_html')) {
    // Flash messages can embed user input; escape them but keep the <br> separator
    function flash_html(string $message): string {
        return str_replace('&lt;br&gt;', '<br>', e($message));
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah(float $amount): string {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
