<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class TagController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Auth::user()->tags()->get();

        return Inertia::render('Tag', compact('tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTagRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->tags()->create($validated);

        return to_route('tags.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $tag->update($request->all());

        // TODO: retornar um Toast Message
        return to_route('tags.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        // TODO: retornar um toast message
        return to_route('tags.index');
    }
}
