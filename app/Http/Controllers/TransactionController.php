<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\TransactionFilter;
use App\Http\Requests\BulkAssignCreditCardRequest;
use App\Http\Requests\BulkDeleteTransactionRequest;
use App\Http\Requests\IndexTransactionRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Services\CreditCardService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
            ->with(['category', 'tags', 'creditCard'])
            ->filter($transactionFilter)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();
        $creditCards = Auth::user()->creditCards()->orderBy('name')->get();

        $filters = $request->validated();

        return Inertia::render('transactions/Transaction', compact('transactions', 'categories', 'tags', 'creditCards', 'filters'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(TransactionRequest $request, TransactionService $transactions): RedirectResponse
    {
        $validated = $request->validated();
        $tagIds = Arr::pull($validated, 'tags', []);
        $splits = (int) (Arr::pull($validated, 'splits') ?? 1);

        $transactions->create(Auth::user(), $validated, $splits, $tagIds);

        $this->toast::success(__('messages.transaction.created'));

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        $this->authorize('view', $transaction);

        $transaction->load(['category', 'tags']);

        return Inertia::render('transactions/TransactionShow', compact('transaction'));
    }

    /**
     * Generate and flash a signed URL to share the specified resource.
     */
    public function share(Transaction $transaction): RedirectResponse
    {
        $this->authorize('view', $transaction);

        $url = URL::temporarySignedRoute(
            'transactions.receipt',
            now()->addDays(7),
            ['transaction' => $transaction->id]
        );

        return back()->with('transaction_share_url', $url);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction, TransactionService $transactions): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validated();
        $tagIds = Arr::pull($validated, 'tags', []);
        Arr::forget($validated, 'splits');

        $transactions->update(Auth::user(), $transaction, $validated, $tagIds);

        $this->toast::success(__('messages.transaction.updated'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        $this->toast::success(__('messages.transaction.deleted'));

        return back();
    }

    /**
     * Delete several of the user's own transactions at once. Deleted through the
     * model so the savings-bucket balance observer still runs per transaction.
     */
    public function bulkDestroy(BulkDeleteTransactionRequest $request): RedirectResponse
    {
        Auth::user()->transactions()
            ->whereIn('id', $request->validated('ids'))
            ->get()
            ->each
            ->delete();

        $this->toast::success(__('messages.transaction.bulk_deleted'));

        return back();
    }

    /**
     * Move several of the user's own transactions onto a credit card at once.
     *
     * @throws Throwable
     */
    public function bulkAssignCreditCard(BulkAssignCreditCardRequest $request, CreditCardService $creditCards): RedirectResponse
    {
        $card = Auth::user()->creditCards()->findOrFail($request->validated('credit_card_id'));

        $creditCards->assignTransactionsToCard(Auth::user(), $card, $request->validated('ids'));

        $this->toast::success(__('messages.transaction.bulk_card_assigned'));

        return back();
    }
}
