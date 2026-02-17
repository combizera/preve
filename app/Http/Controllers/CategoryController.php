<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $categories = Auth::user()->categories()->get();

        [$expenseCategories, $incomeCategories] = $categories->partition(function (Category $category) {
            return $category->type === TransactionType::EXPENSE;
        });

        $expenseCategories = $expenseCategories->values();
        $incomeCategories = $incomeCategories->values();

        return Inertia::render('Category', compact('expenseCategories', 'incomeCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::user()->categories()->create($validated);

        $this->toast::success('Category created successfully.');

        return to_route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        // TODO: implement
        return Inertia::render('Category');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->all());

        $this->toast::success('Category updated successfully.');

        return to_route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        $this->toast::success('Category deleted successfully.');

        return to_route('categories.index');
    }
}
