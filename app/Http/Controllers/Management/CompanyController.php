<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\CompanyResource;
use App\Models\Company;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use Searchable, Paginable, Sortable;

    public function index(Request $request)
    {
        $data = $request->validate([
            ...$this->checkSearch(),
            ...$this->checkPage(),
            ...$this->checkSort([
                'id',
                'name'
            ])
        ]);

        $query = Company::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        $query->orderBy('id');

        return CompanyResource::collection($this->applyPagination($data, $query));
    }

    public function show(Company $company)
    {
        $company->load('films');

        return new CompanyResource($company);
    }
}
