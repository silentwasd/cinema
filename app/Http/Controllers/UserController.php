<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ComposableTable\Paginable;
use App\Services\ComposableTable\Searchable;
use App\Services\ComposableTable\Sortable;
use Illuminate\Http\Request;

class UserController extends Controller
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

        $query = User::query();

        $this->applySearch($data, $query);
        $this->applySort($data, $query);

        return UserResource::collection($this->applyPagination($data, $query));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        User::create($data);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user->update($data);
    }

    public function destroy(User $user)
    {
        $user->delete();
    }

    public function destroyMany(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|exists:users,id'
        ]);

        User::destroy($data['ids']);
    }
}
