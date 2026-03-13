<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\RecurringTransactionRequest;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
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

        [$expenseRecurring, $incomeRecurring] = $recurring->partition(fn (RecurringTransaction $recurringTransaction): bool => $recurringTransaction->type === TransactionType::EXPENSE);

        $expenseRecurring = $expenseRecurring->values();
        $incomeRecurring = $incomeRecurring->values();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('RecurringTransaction', compact('expenseRecurring', 'incomeRecurring', 'categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecurringTransactionRequest $request, RecurringTransactionService $service): RedirectResponse
    {
        $validated = $request->validated();

        $recurringTransaction = Auth::user()->recurringTransactions()->create($validated);

        $service->generateFutureTransactions($recurringTransaction, 3);
        $this->toast::success('Recurring transaction created successfully! The next 3 months of transactions are now projected in your statement.');

        return to_route('recurring.index');
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
