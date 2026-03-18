<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

final class TransactionReceiptController extends Controller
{
    /**
     * Display the publicly accessible receipt for the specified resource.
     */
    public function __invoke(Transaction $transaction): Response
    {
        $transaction->load(['category', 'tag']);

        return Inertia::render('transactions/TransactionShow', compact('transaction'));
    }
}
