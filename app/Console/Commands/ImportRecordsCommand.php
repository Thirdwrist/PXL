<?php

namespace App\Console\Commands;

use App\Repositories\ImportRepository;
use App\Services\Import\ImportUserJSONService;
use Illuminate\Console\Command;

class ImportRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:records {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import new set of records into the db';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $importService;
    protected $importRepository;

    public function __construct(ImportUserJSONService $importUserJSONService, ImportRepository $importRepository)
    {
        $this->importService = $importUserJSONService;
        $this->importRepository = $importRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($id = $this->option('id'))
            $this->importRecords($id);
        else
            $this->instructions();
    }

    public function importRecords(int $id)
    {
        $import = $this->importRepository->getById($id);
        if(!$import)
        {
            $this->error('selection is invalid');
            die;
        }

        $this->importService->import($import);
    }

   private function instructions()
   {
       $this->info('kindly select what import you would want to upload');
       $this->line('...............');
       $this->info('specify the ID from the table listed below  as an argument to this command');
       $this->line('...............');
       $this->table(
           ['id', 'name', 'status', 'current_index', 'total_count'],
           $this->importRepository->list(['id', 'name', 'status', 'current_index', 'total_count'])
       );
   }
}
