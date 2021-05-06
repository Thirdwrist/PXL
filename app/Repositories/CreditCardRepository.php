<?php


namespace App\Repositories;


use App\Models\CreditCard;

class CreditCardRepository
{

    public function store(array $data) :CreditCard
    {
        return CreditCard::create($data);
    }
}