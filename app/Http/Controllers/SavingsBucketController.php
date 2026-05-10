<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateSavingsBucketRequest;
use App\Http\Requests\UpdateSavingsBucketRequest;
use App\Models\SavingsBucket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class SavingsBucketController extends Controller
{
    public function index(): Response
    {
        $savingsBuckets = Auth::user()->savingsBuckets()->orderBy('id')->get();

        return Inertia::render('SavingsBucket', compact('savingsBuckets'));
    }

    public function store(CreateSavingsBucketRequest $request): RedirectResponse
    {
        Auth::user()->savingsBuckets()->create($request->validated());

        $this->toast::success(__('messages.savings_bucket.created'));

        return to_route('savings.index');
    }

    public function update(UpdateSavingsBucketRequest $request, SavingsBucket $savings): RedirectResponse
    {
        $this->authorize('update', $savings);

        $savings->update($request->validated());

        $this->toast::success(__('messages.savings_bucket.updated'));

        return to_route('savings.index');
    }

    public function destroy(SavingsBucket $savings): RedirectResponse
    {
        $this->authorize('delete', $savings);

        if ($savings->current_amount > 0) {
            throw ValidationException::withMessages([
                'savings_bucket' => __('messages.savings_bucket.not_empty'),
            ]);
        }

        $savings->delete();

        $this->toast::success(__('messages.savings_bucket.deleted'));

        return to_route('savings.index');
    }
}
