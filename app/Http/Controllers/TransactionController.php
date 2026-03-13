<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\TransactionFilter;
use App\Http\Requests\IndexTransactionRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTransactionRequest $request): Response
    {
        $transactionFilter = new TransactionFilter($request);

        $transactions = Auth::user()
            ->transactions()
            ->with(['category', 'tag'])
            ->filter($transactionFilter)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        $filters = $request->validated();

        return Inertia::render('transactions/Transaction', compact('transactions', 'categories', 'tags', 'filters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->transactions()->create($validated);

        $this->toast::success('Transaction created successfully.');

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        $transaction->load(['category', 'tag']);

        return Inertia::render('transactions/TransactionShow', compact('transaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->all());

        $this->toast::success('Transaction updated successfully.');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        $this->toast::success('Transaction deleted successfully.');

        return back();
    }
}
