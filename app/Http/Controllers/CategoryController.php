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
        $categories = Auth::user()->categories()
            ->with(['forecastSeries.forecasts' => fn ($query) => $query->latest('month')->limit(1)])
            ->get();

        [$expenseCategories, $incomeCategories] = $categories->partition(fn (Category $category): bool => $category->type === TransactionType::EXPENSE);

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

        $this->toast::success(__('messages.category.created'));

        return to_route('categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        $this->toast::success(__('messages.category.updated'));

        return to_route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        $this->toast::success(__('messages.category.deleted'));

        return to_route('categories.index');
    }
}
