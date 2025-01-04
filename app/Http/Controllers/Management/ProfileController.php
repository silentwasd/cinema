<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\ProfileResource;

class ProfileController extends Controller
{
    public function show()
    {
        return new ProfileResource(auth()->user());
    }
}
