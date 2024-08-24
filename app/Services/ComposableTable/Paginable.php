<?php

namespace App\Services\ComposableTable;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

trait Paginable
{
    protected function checkPage(): array
    {
        return [
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1'
        ];
    }

    protected function applyPagination(array $data, Builder $query): LengthAwarePaginator
    {
        return $query->paginate($data['per_page'] ?? 5, page: $data['page'] ?? 1);
    }
}
