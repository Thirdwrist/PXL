<?php


namespace App\Services\Import;


use App\Models\Import;
use Illuminate\Support\Collection;

interface BaseService
{

    function getDataFromFile(Import $import) :Collection;
}