<?php


namespace App\Services\Import;


use App\Models\Import;
use App\Repositories\CreditCardRepository;
use App\Repositories\ImportRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportUserJSONService implements ImportInterface
{
    protected $importRepository;
    protected $userRepository;
    protected $creditCardRepository;
    public function __construct(ImportRepository $importRepository, UserRepository $userRepository, CreditCardRepository $creditCardRepository)
    {
        $this->importRepository = $importRepository;
        $this->userRepository = $userRepository;
        $this->creditCardRepository = $creditCardRepository;
    }

    function import(Import $import):void
    {
        if($import->status === Config::get('constants.import.status.completed'))
            return;

       $file = collect($this->getFile($import));
       $total =  $import->total_count - 1;
       $this->importRepository->updateStatus($import, Config::get('constants.import.status.in_progress'));

        for ($key=$import->current_index; $key <=$total; ++$key)
        {
            $this->createRecords($key, $file[$key], $import);
        }

      $this->importRepository->updateStatus($import, Config::get('constants.import.status.completed'));
    }

    private function createRecords(int $key, array $record, Import $import) :void
    {
        try{
            DB::beginTransaction();
            // Create user
            $user = $this->userRepository->store([
                'name'=> Arr::get($record, 'name'),
                'email'=> Arr::get($record, 'email'),
                'address'=> Arr::get($record, 'address'),
                'description'=> Arr::get($record, 'description'),
                'interest'=> Arr::get($record, 'interest'),
                'date_of_birth'=> sanitizeDate(Arr::get($record, 'date_of_birth')),
                'account'=> Arr::get($record, 'account'),
                'checked'=> Arr::get($record, 'checked')
            ]);

            // Create credit card
            $this->creditCardRepository->store([
               'name'=> Arr::get($record, 'creditCard.name'),
               'type'=> Arr::get($record, 'creditCard.type'),
               'number'=>  Arr::get($record, 'creditCard.number'),
               'expiration_date'=>  sanitizeDate(Arr::get($record, 'creditCard.expirationDate')),
                'user_id'=> $user->id
            ]);

            // update index
            $this->importRepository->updateImport(
                $import,
                [
                    'current_index'=> $key
                ]);

            DB::commit();
        }catch (\Exception $e)
        {
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    private function getFile(Import $import) :array
    {
        if($import->storage_disk === $disk = Config::get('constants.import.storage.local'))
        {
            $file = Storage::disk($disk)->get($import->path);
            return json_decode($file, true);
        }
        elseif ($import->storage_disk ===  Config::get('constants.import.storage.url'))
        {
           return  Http::get($import->path)->json();
        }

        abort(500, 'storage format not supported');
    }

}