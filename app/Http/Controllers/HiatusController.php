<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HiatusRequest;
use App\Models\Hiatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class HiatusController extends Controller
{
    public function store(HiatusRequest $request): RedirectResponse
    {
        $this->authorize('create', Hiatus::class);

        Auth::user()->hiatuses()->create($request->validated());

        $this->toast::success(__('messages.hiatus.created'));

        return back();
    }

    public function update(HiatusRequest $request, Hiatus $hiatus): RedirectResponse
    {
        $this->authorize('update', $hiatus);

        $hiatus->update($request->validated());

        $this->toast::success(__('messages.hiatus.updated'));

        return back();
    }

    public function destroy(Hiatus $hiatus): RedirectResponse
    {
        $this->authorize('delete', $hiatus);

        $hiatus->delete();

        $this->toast::success(__('messages.hiatus.deleted'));

        return back();
    }
}
