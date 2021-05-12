<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Repositories\ImportRepository;
use App\Services\Import\ImportManager;
use App\Services\Import\ImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ImportRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:records {type} {--id=}';

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
    protected ImportRepository $importRepository;
    protected ImportManager $importManager;

    // Laravel queue by default handles SIGTERM and other interruptions
    protected $queue = 'import';

    public function __construct(ImportRepository $importRepository, ImportManager $importManager)
    {
        $this->importRepository = $importRepository;
        $this->importManager = $importManager;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle():void
    {
        $allTypes = Config::get('constants.import.types');
        $type = $this->argument('type');

        if(in_array($type, array_keys($allTypes)))
        {
            $strategy =  app()->make($allTypes[$type]);
            $service = new ImportService($strategy, $this->importManager);

            if($id = $this->option('id'))
                $service->run($this->getImport($id));
            else
                $this->instructions();
        }else{
            $this->error('invalid format supplied');
            die;
        }
    }

    public function getImport(int $id):Import
    {
        $import = $this->importRepository->getById($id);
        if(!$import)
        {
            $this->error('selection is invalid');
            die;
        }

        return $import;

    }

   private function instructions() :void
   {
       $this->info('kindly select what import you would want to upload');
       $this->line('...............');
       $this->info('specify the ID from the table listed below  as an argument to this command');
       $this->line('...............');
       $this->table(
           $columns = ['id', 'name', 'status', 'current_index', 'total_count', 'storage_disk', 'file_format', 'path'],
           $this->importRepository->list($columns)
       );
   }
}
