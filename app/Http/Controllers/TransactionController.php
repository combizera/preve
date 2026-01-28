<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Auth::user()
            ->transactions()
            ->with(['category', 'tag'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('Transaction', compact('transactions', 'categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->transactions()->create($validated);

        return to_route('transactions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->all());

        // TODO: retornar um Toast Message
        return to_route('transactions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        // TODO: retornar um toast message
        return to_route('transactions.index');
    }
}
