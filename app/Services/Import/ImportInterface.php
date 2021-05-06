<?php


namespace App\Services\Import;


use App\Models\Import;

interface ImportInterface
{
    function import(Import $import) :void;
}