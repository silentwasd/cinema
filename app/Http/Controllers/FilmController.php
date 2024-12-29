<?php

namespace App\Http\Controllers;

use App\Enums\FilmFormat;
use App\Http\Resources\FilmResource;
use App\Http\Resources\UserResource;
use App\Models\Film;
use App\Models\ListModel;
use App\Models\User;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmController extends Controller
{
    use Searchable, Paginable, Sortable;

    public function index(Request $request)
    {
        $data = $request->validate([
            ...$this->checkSearch(),
            ...$this->checkPage(),
            ...$this->checkSort([
                'id',
                'name',
                'format',
                'release_date'
            ]),
            'list_id' => 'nullable|exists:lists,id'
        ]);

        $query = Film::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        if ($data['list_id'] ?? false)
            $query->whereHas('lists', fn(Builder $lists) => $lists->where('lists.id', $data['list_id']));

        return FilmResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'format'       => ['required', Rule::enum(FilmFormat::class)],
            'cover'        => 'nullable|image|max:10240',
            'release_date' => 'nullable|date',
            'description'  => 'nullable|string|max:65536'
        ]);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('films', 'public');
        }

        Film::create($data);
    }

    public function update(Request $request, Film $film)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'format'       => ['required', Rule::enum(FilmFormat::class)],
            'cover'        => 'nullable|image|max:10240',
            'release_date' => 'nullable|date',
            'description'  => 'nullable|string|max:65536'
        ]);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('films', 'public');
        } else {
            $data['cover'] = $film->cover;
        }

        $film->update($data);
    }

    public function destroy(Film $film)
    {
        $film->delete();
    }

    public function destroyMany(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|exists:films,id'
        ]);

        Film::destroy($data['ids']);
    }

    public function listUsers(Film $film, ListModel $list)
    {
        return UserResource::collection(
            User::findMany(
                $film->listUsers()
                     ->wherePivot('list_id', $list->id)
                     ->get()
            )
        );
    }

    public function updateList(Request $request, Film $film, ListModel $list)
    {
        $data = $request->validate([
            'users' => 'required|array|exists:users,id'
        ]);

        $film->listUsers()
             ->wherePivot('list_id', $list->id)
             ->syncWithPivotValues($data['users'], ['list_id' => $list->id]);
    }

    public function updateListMany(Request $request, ListModel $list)
    {
        $data = $request->validate([
            'films' => 'required|array|exists:films,id',
            'users' => 'required|array|exists:users,id'
        ]);

        foreach (Film::findMany($data['films']) as $film) {
            $film->listUsers()
                 ->wherePivot('list_id', $list->id)
                 ->syncWithPivotValues($data['users'], ['list_id' => $list->id]);
        }
    }
}
