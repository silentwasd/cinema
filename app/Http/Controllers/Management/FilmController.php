<?php

namespace App\Http\Controllers\Management;

use App\Enums\FilmFormat;
use App\Http\Controllers\Controller;
use App\Http\Resources\Management\FilmResource;
use App\Models\Film;
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
            'format'    => ['nullable', Rule::enum(FilmFormat::class)],
            'people'    => ['nullable', 'array', 'exists:people,id'],
            'genres'    => ['nullable', 'array', 'exists:genres,id'],
            'countries' => ['nullable', 'array', 'exists:countries,id'],
            'tags'      => ['nullable', 'array', 'exists:tags,id']
        ]);

        $query = Film::query()
                     ->with(['people', 'people.person', 'genres', 'countries', 'tags', 'companies']);

        $query
            ->when($data['format'] ?? false, fn(Builder $when) => $when
                ->where('format', $data['format'])
            )->when($data['people'] ?? false, fn(Builder $when) => $when
                ->whereHas('people', fn(Builder $has) => $has
                    ->whereIn('film_people.person_id', $data['people'])
                )
            )->when($data['genres'] ?? false, fn(Builder $when) => $when
                ->whereHas('genres', fn(Builder $has) => $has
                    ->whereIn('film_genre.genre_id', $data['genres'])
                )
            )->when($data['countries'] ?? false, fn(Builder $when) => $when
                ->whereHas('countries', fn(Builder $has) => $has
                    ->whereIn('country_film.country_id', $data['countries'])
                )
            )->when($data['name'] ?? false, fn(Builder $when) => $when
                ->where('name', 'LIKE', '%' . $data['name'] . '%')
                ->orWhere('original_name', 'LIKE', '%' . $data['name'] . '%')
            )->when($data['tags'] ?? false, fn(Builder $when) => $when
                ->whereHas('tags', fn(Builder $has) => $has
                    ->whereIn('film_tag.tag_id', $data['tags'])
                )
            );

        $this->applySort($data, $query);

        $query->orderBy('id');

        return FilmResource::collection($this->applyPagination($data, $query));
    }

    public function show(Film $film)
    {
        $film->load(['ratings', 'people', 'people.person', 'genres', 'countries', 'tags', 'companies']);

        return new FilmResource($film);
    }
}
