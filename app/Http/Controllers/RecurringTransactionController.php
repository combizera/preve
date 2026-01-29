<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RecurringTransactionRequest;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RecurringTransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $recurringTransactions = Auth::user()
            ->recurringTransactions()
            ->with(['transactions', 'category', 'tag'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('RecurringTransaction', compact('recurringTransactions', 'categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecurringTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->recurringTransactions()->create($validated);

        return to_route('recurring.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO: implement
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('update', $recurringTransaction);

        $recurringTransaction->update($request->all());

        // TODO: retornar um Toast Message
        return to_route('recurring.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('destroy', $recurringTransaction);

        $recurringTransaction->delete();

        // TODO: retornar um Toast Message
        return to_route('recurring.index');
    }
}
