<?php

declare(strict_types=1);

use App\Models\CreditCard;
use Illuminate\Support\Facades\Date;

function card(int $closingDay, int $dueDay): CreditCard
{
    return new CreditCard([
        'closing_day' => $closingDay,
        'due_day'     => $dueDay,
    ]);
}

it('bills a purchase before the closing day to the invoice closing that month', function (): void {
    $date = card(20, 1)->effectivePaymentDate(Date::parse('2026-01-10'));

    expect($date->toDateString())->toBe('2026-02-01');
});

it('rolls a purchase after the closing day to the next invoice', function (): void {
    $date = card(20, 1)->effectivePaymentDate(Date::parse('2026-01-25'));

    expect($date->toDateString())->toBe('2026-03-01');
});

it('keeps the due date in the closing month when the due day is after the closing day', function (): void {
    expect(card(5, 15)->effectivePaymentDate(Date::parse('2026-01-03'))->toDateString())
        ->toBe('2026-01-15')
        ->and(card(5, 15)->effectivePaymentDate(Date::parse('2026-01-10'))->toDateString())
        ->toBe('2026-02-15');
});

it('clamps the due day to the last day of a short month', function (): void {
    $date = card(5, 31)->effectivePaymentDate(Date::parse('2026-01-10'));

    expect($date->toDateString())->toBe('2026-02-28');
});

it('treats a closing day longer than the month as end of month', function (): void {
    $date = card(31, 10)->effectivePaymentDate(Date::parse('2026-04-30'));

    expect($date->toDateString())->toBe('2026-05-10');
});
