<?php


namespace App\Repositories;


use App\Models\Import;
use Illuminate\Database\Eloquent\Collection;

class ImportRepository
{
    function list($select = ['*']) : Collection
    {
        return Import::all($select);
    }

    function getById(int $id): ?Import
    {
        return Import::find($id);
    }

    function store(array  $data): Import
    {
        return Import::create($data);
    }

    function updateImport(Import $import, array $data) :bool
    {
        return $import->update($data);
    }

    function updateStatus(Import $import, string $status) :bool
    {
        return $import->update(['status'=> $status]);
    }
}