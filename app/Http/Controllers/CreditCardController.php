<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateCreditCardRequest;
use App\Http\Requests\UpdateCreditCardRequest;
use App\Models\CreditCard;
use App\Services\CreditCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

final class CreditCardController extends Controller
{
    public function index(CreditCardService $service): Response
    {
        $this->authorize('viewAny', CreditCard::class);

        return Inertia::render('CreditCard', $service->overview(Auth::user(), Date::now()));
    }

    public function invoice(Request $request, CreditCard $creditCard, CreditCardService $service): Response
    {
        $this->authorize('view', $creditCard);

        $month = $service->resolveMonth($request->query('month'));
        $invoice = $service->invoiceFor($creditCard, $month);

        return Inertia::render('CreditCardInvoice', [
            'card'         => $creditCard,
            'year'         => $month->year,
            'month'        => $month->month,
            'transactions' => $invoice['transactions'],
            'total'        => $invoice['total'],
            'shared'       => false,
        ]);
    }

    public function shareInvoice(Request $request, CreditCard $creditCard, CreditCardService $service): RedirectResponse
    {
        $this->authorize('view', $creditCard);

        $month = $service->resolveMonth($request->input('month'));

        $url = URL::temporarySignedRoute(
            'credit-cards.invoice.receipt',
            Date::now()->addDays(7),
            ['creditCard' => $creditCard->id, 'month' => $month->format('Y-m')],
        );

        return back()->with('credit_card_invoice_share_url', $url);
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
