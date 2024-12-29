<?php

namespace App\Http\Controllers;

use App\Enums\FilmWatchStatus;
use App\Http\Resources\FilmWatcherResource;
use App\Models\Film;
use App\Models\FilmWatcher;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmWatcherController extends Controller
{
    use Searchable, Paginable, Sortable;

    public function index(Request $request)
    {
        $data = $request->validate([
            ...$this->checkSearch(),
            ...$this->checkPage(),
            ...$this->checkSort([
                'id',
                'film.name',
                'film.format',
                'film.release_date'
            ])
        ]);

        $query = $request->user()->films()->getQuery();

        $query->when($data['name'] ?? false, fn(Builder $when) => $when
            ->whereHas('film', fn(Builder $has) => $has->where('name', 'LIKE', '%' . $data['name'] . '%'))
        );

        $this->applySort($data, $query);

        return FilmWatcherResource::collection($this->applyPagination($data, $query));
    }

    protected function sort(Builder $query, string $column, string $direction): Builder
    {
        return match ($column) {
            'film.name'         => $query->with('film')
                                         ->orderBy(
                                             Film::select('name')
                                                 ->whereColumn('films.id', 'film_watchers.film_id')
                                                 ->limit(1),
                                             $direction
                                         ),
            'film.format'       => $query->with('film')
                                         ->orderBy(
                                             Film::select('format')
                                                 ->whereColumn('films.id', 'film_watchers.film_id')
                                                 ->limit(1),
                                             $direction
                                         ),
            'film.release_date' => $query->with('film')
                                         ->orderBy(
                                             Film::select('release_date')
                                                 ->whereColumn('films.id', 'film_watchers.film_id')
                                                 ->limit(1),
                                             $direction
                                         ),
            default             => $query->orderBy($column, $direction),
        };
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'film_id' => 'required|integer|exists:films,id',
            'status'  => ['required', Rule::enum(FilmWatchStatus::class)]
        ]);

        $request->user()->films()->create($data);
    }

    public function update(Request $request, FilmWatcher $filmWatcher)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(FilmWatchStatus::class)]
        ]);

        $filmWatcher->update($data);
    }
}
