<?php

namespace App\Http\Controllers\Management;

use App\Enums\FilmFormat;
use App\Enums\PersonRole;
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
            'directors' => ['nullable', 'array', 'exists:people,id'],
            'actors'    => ['nullable', 'array', 'exists:people,id']
        ]);

        $query = Film::query()
                     ->with(['people', 'people.person']);

        $query
            ->when($data['format'] ?? false, fn(Builder $when) => $when
                ->where('format', $data['format'])
            )->when($data['directors'] ?? false, fn(Builder $when) => $when
                ->whereHas('people', fn(Builder $has) => $has
                    ->whereIn('film_people.person_id', $data['directors'])
                    ->where('role', PersonRole::Director)
                )
            )->when($data['actors'] ?? false, fn(Builder $when) => $when
                ->whereHas('people', fn(Builder $has) => $has
                    ->whereIn('film_people.person_id', $data['actors'])
                    ->where('role', PersonRole::Actor)
                )
            );

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

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

        Film::create([
            ...$data,
            'author_id' => $request->user()->id
        ]);
    }

    public function show(Film $film)
    {
        $film->load(['ratings', 'people', 'people.person']);

        return new FilmResource($film);
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
}
