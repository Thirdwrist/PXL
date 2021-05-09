<?php


namespace App\Services\Import;


use App\Exceptions\ImportException;
use App\Models\Import;
use App\Repositories\CreditCardRepository;
use App\Repositories\ImportRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

abstract class BaseImport
{
    protected ImportRepository $importRepository;
    protected UserRepository $userRepository;
    protected CreditCardRepository $creditCardRepository;
    protected array $filters = [];

    public function __construct(ImportRepository $importRepository, UserRepository $userRepository, CreditCardRepository $creditCardRepository)
    {
        $this->importRepository = $importRepository;
        $this->userRepository = $userRepository;
        $this->creditCardRepository = $creditCardRepository;
    }

    // get the file
    abstract function getFile(Import $import) :array;

    public function import(Import $import) :void
    {
        if($import->status === Config::get('constants.import.status.completed'))
            return;

        $file = collect($this->getFile($import));
        $this->importRepository->updateStatus($import, Config::get('constants.import.status.in_progress'));

        for ($key=$import->current_index; $key < $import->total_count; $key++)
        {
            $this->createRecords($key, $file[$key], $import);
        }

        $this->importRepository->updateStatus($import, Config::get('constants.import.status.completed'));
    }

    public  function createRecords(int $key, array $record, Import $import) :void
    {
        try{
            DB::beginTransaction();


          if($this->filters($record)) {
              // Create user
              $user = $this->userRepository->store([
                  'name' => Arr::get($record, 'name'),
                  'email' => Arr::get($record, 'email'),
                  'address' => Arr::get($record, 'address'),
                  'description' => Arr::get($record, 'description'),
                  'interest' => Arr::get($record, 'interest'),
                  'date_of_birth' => sanitizeDate(Arr::get($record, 'date_of_birth')),
                  'account' => Arr::get($record, 'account'),
                  'checked' => Arr::get($record, 'checked')
              ]);

              // Create credit card
              $this->creditCardRepository->store([
                  'name' => Arr::get($record, 'creditCard.name'),
                  'type' => Arr::get($record, 'creditCard.type'),
                  'number' => Arr::get($record, 'creditCard.number'),
                  'expiration_date' => sanitizeDate(Arr::get($record, 'creditCard.expirationDate')),
                  'user_id' => $user->id
              ]);
          }

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
            throw new ImportException($e->getMessage());
        }
    }

    public function filters(array $data):bool
    {
        foreach($this->filters as $filter)
        {
            $res = (new $filter($data))->handle();
            if(!$res)
                    return $res;
        }

        return true;
    }

}