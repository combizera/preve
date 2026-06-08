<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Services\CreditCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CreditCardInvoiceReceiptController extends Controller
{
    /**
     * Display the publicly accessible invoice for the specified card.
     */
    public function __invoke(Request $request, CreditCard $creditCard, CreditCardService $service): Response
    {
        $month = $service->resolveMonth($request->query('month'));
        $invoice = $service->invoiceFor($creditCard, $month);

        return Inertia::render('CreditCardInvoice', [
            'card'         => $creditCard,
            'year'         => $month->year,
            'month'        => $month->month,
            'transactions' => $invoice['transactions'],
            'total'        => $invoice['total'],
            'shared'       => true,
        ]);
    }
}
