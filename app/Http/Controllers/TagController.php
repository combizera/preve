<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $perPage = request()->input('per_page', 10);

        $tags = Auth::user()->tags()->paginate($perPage);

        return Inertia::render('Tag', compact('tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTagRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->tags()->create($validated);

        $this->toast::success('Tag created successfully.');

        return to_route('tags.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        // TODO: implement
        return Inertia::render('Tag');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);

        $tag->update($request->all());

        $this->toast::success('Tag updated successfully.');

        return to_route('tags.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        $this->toast::success('Tag deleted successfully.');

        return to_route('tags.index');
    }
}
