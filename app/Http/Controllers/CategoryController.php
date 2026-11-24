<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use App\Support\AdminListSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('membership.catalog', ['kind' => 'categories', 'items' => $this->items($request), 'editing' => new MembershipCategory]);
    }

    public function edit(Request $request, MembershipCategory $category)
    {
        return view('membership.catalog', ['kind' => 'categories', 'items' => $this->items($request), 'editing' => $category]);
    }

    private function items(Request $request)
    {
        return AdminListSort::apply(MembershipCategory::withCount('members'), $request, ['name'], 'name_asc')->paginate(10)->withQueryString();
    }

    private function data(Request $request, ?MembershipCategory $category = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('membership_categories')->ignore($category?->id)], 'description' => 'nullable|string|max:2000']);
    }

    public function store(Request $request)
    {
        MembershipCategory::create($this->data($request));

        return redirect()->route('categories.index')->with('success', 'Category added.');
    }

    public function update(Request $request, MembershipCategory $category)
    {
        $category->update($this->data($request, $category));

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(MembershipCategory $category)
    {
        DB::transaction(function () use ($category) {
            $category = MembershipCategory::lockForUpdate()->findOrFail($category->id);
            if ($category->members()->exists()) {
                throw ValidationException::withMessages(['category' => 'Reassign members before deleting this category.']);
            } $category->delete();
        });

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
