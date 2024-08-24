<?php

namespace App\Services\ComposableTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

trait Sortable
{
    protected function checkSort(array $sortable): array
    {
        return [
            'sort_column'    => ['nullable', Rule::in($sortable)],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])]
        ];
    }

    protected function sort(Builder $query, string $column, string $direction): Builder
    {
        return $query->orderBy($column, $direction);
    }

    protected function applySort(array $data, Builder $query): Builder
    {
        if (isset($data['sort_column']) && isset($data['sort_direction']))
            return $this->sort($query, $data['sort_column'], $data['sort_direction']);

        return $query->latest();
    }
}
