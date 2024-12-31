<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\PersonResource;
use App\Models\Person;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Http\Request;

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
                'name'
            ])
        ]);

        $query = Person::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        return PersonResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'photo' => 'nullable|image|max:10240'
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
            'name'  => 'required|string|max:255',
            'photo' => 'nullable|image|max:10240'
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
