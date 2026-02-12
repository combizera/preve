<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\TransactionFilter;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Request;

final class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TransactionFilter $filters): Response
    {
        $transactions = Auth::user()
            ->transactions()
            ->with(['category', 'tag'])
            ->filter($filters)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        $filters = $request->only(['search', 'type', 'category_id', 'date_start', 'date_end', 'tags']);

        return Inertia::render('Transaction', compact('transactions', 'categories', 'tags', 'filters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->transactions()->create($validated);

        return to_route('transactions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        // TODO: implement
        return Inertia::render('Transaction');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->all());

        // TODO: retornar um Toast Message
        return to_route('transactions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        // TODO: retornar um toast message
        return to_route('transactions.index');
    }
}
