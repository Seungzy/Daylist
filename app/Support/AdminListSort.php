<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminListSort
{
    public static function apply(Builder $query, Request $request, array $nameColumns, string $default = 'newest', ?string $feeColumn = null): Builder
    {
        $choices = ['newest', 'oldest', 'name_asc', 'name_desc'];
        if ($feeColumn) {
            $choices = [...$choices, 'fee_asc', 'fee_desc'];
        }
        $sort = $request->validate(['sort' => ['nullable', Rule::in($choices)]])['sort'] ?? $default;
        $direction = in_array($sort, ['oldest', 'name_asc', 'fee_asc']) ? 'asc' : 'desc';

        if (str_starts_with($sort, 'fee_')) {
            $query->orderBy($feeColumn, $direction);
        }
        if (str_starts_with($sort, 'name_') || str_starts_with($sort, 'fee_')) {
            foreach ($nameColumns as $column) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->orderBy($query->getModel()->qualifyColumn('id'), $direction);
    }
}
