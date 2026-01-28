<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class CategoryController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Auth::user()->categories()->get();

        [$expenseCategories, $incomeCategories] = $categories->partition(function (Category $category) {
            return $category->type === TransactionType::EXPENSE;
        });

        return Inertia::render('Category', compact('expenseCategories', 'incomeCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->categories()->create($validated);

        return to_route('categories.index');
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
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $category->update($request->all());

        // TODO: retornar um Toast Message
        return to_route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        // TODO: retornar um toast message
        return to_route('categories.index');
    }
}
