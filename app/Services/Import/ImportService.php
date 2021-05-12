<?php


namespace App\Services\Import;


use App\Exceptions\ImportException;
use App\Filters\AgeFilter;
use App\Models\Import;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportService implements BaseService
{

    public ImportManager $importManager;
    public ImportStrategy $importStrategy;

    public function __construct(ImportStrategy $strategy, ImportManager $importManager)
    {
        $this->importManager = $importManager;
        $this->importStrategy = $strategy;
    }

    protected array $filters = [
      AgeFilter::class
    ];

    public function run(Import $import){
        try{
            $this->importManager->setFilters($this->filters);
            $this->importManager->import($import, $this->getDataFromFile($import));
        }catch (ImportException $e){
            return $e->getMessage();
        }

        return true;
    }
    public function getDataFromFile(Import $import) :Collection
    {
        $fileOrStream = null;
        if($import->storage_disk === $disk = Config::get('constants.import.storage.local'))
        {
            $fileOrStream = Storage::disk($disk)->get($import->path);
        }
        elseif ($import->storage_disk ===  Config::get('constants.import.storage.url'))
        {
           $fileOrStream =  Http::get($import->path);
        }
        else{
            throw new ImportException("Storage disk location [{$import->storage_disk}] not supported");
        }

        return $this->importStrategy->convertData($fileOrStream);

    }

}