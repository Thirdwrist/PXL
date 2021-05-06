<?php


namespace App\Repositories;


use App\Models\User;

class UserRepository
{
    public function store(array  $data) :User
    {
        return User::create($data);
    }
}