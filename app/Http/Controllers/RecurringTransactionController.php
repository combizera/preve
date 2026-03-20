<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\RecurringTransactionRequest;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
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
        $this->toast::success(__('messages.recurring.created'));

        return to_route('recurring.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);

        $recurring->update($request->validated());

        $this->toast::success(__('messages.recurring.updated'));

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
            $this->toast::success(__('messages.recurring.activated'));
        } else {
            $this->toast::warning(__('messages.recurring.deactivated'));
        }

        return to_route('recurring.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);

        $startOfNextMonth = Date::now()->endOfMonth()->addDay()->startOfDay();

        $recurring->transactions()
            ->where('transaction_date', '>=', $startOfNextMonth)
            ->delete();

        $recurring->delete();

        $this->toast::success(__('messages.recurring.deleted'));

        return to_route('recurring.index');
    }
}
