<?php

namespace App\Http\Controllers\Management;

use App\Enums\PersonRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Management\FilmPersonResource;
use App\Models\Film;
use App\Models\FilmPerson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmPersonController extends Controller
{
    public function index(Film $film)
    {
        return FilmPersonResource::collection(
            $film->people()->orderBy('order_id')->get()
        );
    }

    public function store(Request $request, Film $film)
    {
        $data = $request->validate([
            'person_id'    => 'required|exists:people,id',
            'role'         => ['required', Rule::enum(PersonRole::class)],
            'role_details' => 'nullable|string|max:255'
        ]);

        $film->people()->create([
            ...$data,
            'order_id' => (FilmPerson::latest('order_id')->first()?->order_id ?? 0) + 1
        ]);
    }

    public function update(Request $request, Film $film, FilmPerson $person)
    {
        $data = $request->validate([
            'role'         => ['required', Rule::enum(PersonRole::class)],
            'role_details' => 'nullable|string|max:255'
        ]);

        $person->update($data);
    }

    public function destroy(Film $film, FilmPerson $person)
    {
        $person->delete();
    }
}
