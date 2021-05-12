<?php


namespace App\Services\Import;


use App\Exceptions\ImportException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class ImportJSONStrategy implements ImportStrategy
{

    public function convertData($source): Collection
    {

            if ($source instanceof Response) {
                return collect($source->json());
            }
            else if(is_string($source)){
                return collect(json_decode($source, true));
            }
            else {
                throw new ImportException('Source type not supported');
            }
    }
}