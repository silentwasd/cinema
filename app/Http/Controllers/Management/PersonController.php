<?php

namespace App\Http\Controllers\Management;

use App\Enums\PersonRole;
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
                       ->with(['country', 'films'])
                       ->withCount('films');

        $this->applySort($data, $query);

        $query->orderBy('id');

        return PersonResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'birth_date'    => 'nullable|date',
            'death_date'    => 'nullable|date',
            'photo'         => 'nullable|image|max:10240',
            'country_id'    => 'nullable|exists:countries,id'
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        }

        Person::create([
            ...$data,
            'author_id' => $request->user()->id
        ]);
    }

    public function update(Request $request, Person $person)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'birth_date'    => 'nullable|date',
            'death_date'    => 'nullable|date',
            'photo'         => 'nullable|image|max:10240',
            'country_id'    => 'nullable|exists:countries,id'
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        } else {
            $data['photo'] = $person->photo;
        }

        $person->update($data);
    }

    public function destroy(Person $person)
    {
        $person->delete();
    }
}
