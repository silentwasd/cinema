<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListResource;
use App\Models\ListModel;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Http\Request;

class ListController extends Controller
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

        $query = ListModel::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        return ListResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        ListModel::create($data);
    }

    public function update(Request $request, ListModel $list)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $list->update($data);
    }

    public function destroy(ListModel $list)
    {
        $list->delete();
    }

    public function destroyMany(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|exists:lists,id'
        ]);

        ListModel::destroy($data['ids']);
    }
}
