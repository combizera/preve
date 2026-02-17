<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\RecurringTransactionRequest;
use App\Models\RecurringTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $recurring = Auth::user()
            ->recurringTransactions()
            ->with(['transactions', 'category', 'tag'])
            ->orderBy('day_of_month', 'asc')
            ->get();

        [$expenseRecurring, $incomeRecurring] = $recurring->partition(function (RecurringTransaction $recurringTransaction) {
            return $recurringTransaction->type === TransactionType::EXPENSE;
        });

        $expenseRecurring = $expenseRecurring->values();
        $incomeRecurring = $incomeRecurring->values();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('RecurringTransaction', compact('expenseRecurring', 'incomeRecurring', 'categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecurringTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->recurringTransactions()->create($validated);

        $this->toast::success('Recurring transaction created successfully.');

        return to_route('recurring.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringTransaction $recurring): Response
    {
        // TODO: implement
        return Inertia::render('RecurringTransaction');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);

        $recurring->update($request->all());

        $this->toast::success('Recurring transaction updated successfully.');

        return to_route('recurring.index');
    }

    /**
     * Toggle the active status of a recurring transaction.
     */
    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);

        $recurring->update([
            'is_active' => !$recurring->is_active,
        ]);

        if ($recurring->is_active) {
            $this->toast::success('Recurring transaction activated successfully.');
        } else {
            $this->toast::warning('Recurring transaction deactivated successfully.');
        }

        return to_route('recurring.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);

        $recurring->delete();

        $this->toast::success('Recurring transaction deleted successfully.');

        return to_route('recurring.index');
    }
}
