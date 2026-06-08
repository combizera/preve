<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Throwable;

final readonly class TransactionService
{
    public function __construct(private CreditCardService $creditCards) {}

    /**
     * Persist a new transaction. A card purchase is fanned out into one row per
     * installment by the CreditCardService; a cash transaction is a single row.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int>  $tagIds
     *
     * @throws Throwable
     */
    public function create(User $user, array $data, int $splits, array $tagIds): void
    {
        if (!empty($data['credit_card_id'])) {
            $card = $user->creditCards()->findOrFail($data['credit_card_id']);
            $this->creditCards->createPurchase($user, $card, $data, $splits, $tagIds);

            return;
        }

        $user->transactions()->create($data)->tags()->sync($tagIds);
    }

    /**
     * Update an existing transaction. When tied to a card, the purchase date and
     * effective payment date are recomputed; otherwise the purchase date is cleared.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int>  $tagIds
     */
    public function update(User $user, Transaction $transaction, array $data, array $tagIds): void
    {
        if (!empty($data['credit_card_id'])) {
            $card = $user->creditCards()->findOrFail($data['credit_card_id']);
            $data = $this->creditCards->resolveSingle($card, $data);
        } else {
            $data['purchase_date'] = null;
        }

        $transaction->update($data);
        $transaction->tags()->sync($tagIds);
    }
}
