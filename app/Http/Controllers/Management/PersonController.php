<?php

namespace App\Http\Controllers\Management;

use App\Enums\PersonRole;
use App\Enums\PersonSex;
use App\Http\Controllers\Controller;
use App\Http\Resources\Management\PersonResource;
use App\Models\Person;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonController extends Controller
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
                'films_count',
                'birth_date',
                'death_date'
            ]),
            'role'      => ['nullable', Rule::enum(PersonRole::class)],
            'countries' => ['nullable', 'array', 'exists:countries,id']
        ]);

        $query = Person::query()
                       ->when($data['role'] ?? null, fn(Builder $when) => $when
                           ->whereHas('films', fn(Builder $has) => $has
                               ->where('film_people.role', $data['role'])
                           )
                       )
                       ->when($data['countries'] ?? false, fn(Builder $when) => $when
                           ->whereIn('country_id', $data['countries'])
                       )
                       ->when($data['name'] ?? false, fn(Builder $when) => $when
                           ->where('name', 'LIKE', '%' . $data['name'] . '%')
                           ->orWhere('original_name', 'LIKE', '%' . $data['name'] . '%')
                       )
                       ->when($data['model_id'] ?? [], fn(Builder $when) => $when
                           ->whereIn('id', is_array($data['model_id']) ? $data['model_id'] : [$data['model_id']], ($data['name'] ?? false) ? 'OR' : 'AND')
                       )
                       ->with(['country', 'films'])
                       ->withCount('films');

        $this->applySort($data, $query);

        $query->orderBy('id');

        return PersonResource::collection($this->applyPagination($data, $query));
    }

    public function show(Person $person)
    {
        $person->load(['films', 'films.film', 'country'])
               ->loadCount('films');

        return new PersonResource($person);
    }
}
