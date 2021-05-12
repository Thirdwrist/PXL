<?php


namespace App\Services\Import;


use Illuminate\Support\Collection;

interface ImportStrategy
{
    public function convertData($data) :Collection;
}