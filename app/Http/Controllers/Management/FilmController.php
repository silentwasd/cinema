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

        Film::create([
            ...$data,
            'author_id' => $request->user()->id
        ]);
    }

    public function show(Film $film)
    {
        $film->load('ratings');

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
