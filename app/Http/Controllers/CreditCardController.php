<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateCreditCardRequest;
use App\Http\Requests\UpdateCreditCardRequest;
use App\Models\CreditCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CreditCardController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', CreditCard::class);

        $creditCards = Auth::user()->creditCards()->orderBy('name')->get();

        return Inertia::render('CreditCard', compact('creditCards'));
    }

    public function store(CreateCreditCardRequest $request): RedirectResponse
    {
        $this->authorize('create', CreditCard::class);

        Auth::user()->creditCards()->create($request->validated());

        $this->toast::success(__('messages.credit_card.created'));

        return to_route('credit-cards.index');
    }

    public function update(UpdateCreditCardRequest $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorize('update', $creditCard);

        $creditCard->update($request->validated());

        $this->toast::success(__('messages.credit_card.updated'));

        return to_route('credit-cards.index');
    }

    public function destroy(CreditCard $creditCard): RedirectResponse
    {
        $this->authorize('delete', $creditCard);

        $creditCard->delete();

        $this->toast::success(__('messages.credit_card.deleted'));

        return to_route('credit-cards.index');
    }
}
