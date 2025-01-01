<?php

namespace App\Services\ComposableTable;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    protected function checkSearch(string $column = 'name'): array
    {
        return [
            $column    => 'nullable|string|max:255',
            'model_id' => 'nullable'
        ];
    }

    protected function applySearch(array $data, Builder $query, string $column = 'name'): Builder
    {
        return $query->when($data[$column] ?? false, fn(Builder $when) => $when->where($column, 'LIKE', '%' . $data[$column] . '%'))
                     ->when($data['model_id'] ?? false, fn(Builder $when) => $when->where('id', $data['model_id']));
    }
}
