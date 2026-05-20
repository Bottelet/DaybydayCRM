<?php

namespace App\Api\v1\Controllers;

use App\Models\User;

class UserController extends ApiController
{
    public function index()
    {
        return User::all();
    }
}
