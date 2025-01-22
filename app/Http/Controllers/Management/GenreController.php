<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\GenreResource;
use App\Models\Genre;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Http\Request;

class GenreController extends Controller
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

        $query = Genre::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        return GenreResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Genre::create($data);
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $genre->update($data);
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();
    }
}
