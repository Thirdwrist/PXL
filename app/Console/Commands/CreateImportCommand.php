<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Repositories\ImportRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:new {--storage=local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected  $description = 'Create a new import instance record';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private ImportRepository $importRepository;

    public function __construct(ImportRepository $importRepository)
    {
        parent::__construct();
        $this->importRepository = $importRepository;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle():void
    {
        $diskKeys = array_keys(Config::get('filesystems.disks'));
        if(!in_array($this->option('storage'), $diskKeys))
        {
            $this->error('the selected Storage location disk does not exist');
            die;
        }

        // Run wizard
        $this->createImportWizard();

    }

    private function createImportWizard():void
    {
        $location = $this->option('storage');
        $types = Config::get('constants.import.types');
        $format = $this->choice('Kindly select file format', array_keys($types));
        $name = $this->ask('what is the name of the file you wish to import?');
        $description = $this->ask('Please input file description');

        if($location === 'url')
        {
            $path = $this->ask('input a valid url to the resource (e.g www.google.com/jsonfile.json)');
            $file = Http::get($path);

        }else{
            $path = $this->ask('Input Storage path (e.g public/profiles/new_jsonFile.json)');
            $exists = Storage::disk('local')->exists($path);
            if(!$exists){
                $this->error('The supplied document path does not exist');
                die;
            }
            $file = Storage::disk($location)->get($path);
        }

        // Instantiate selected Strategy
        $strategy = app()->make($types[$format]);
        // Use strategy to convert data to array
        $contentsToArray = $strategy->convertData($file);

        // create Import model
        $import = $this->createImport($name, $description, $location, $format, $path, $contentsToArray);

        $this->info("{$import->name} created with a unique identifier {$import->slug}");
    }

    private function createImport($name, $description, $location, $format,$path, $contents):Import
    {
        return $this->importRepository->store([
            'name'=> $name,
            'description'=> $description,
            'storage_disk'=> $location,
            'file_format'=> $format,
            'path'=> $path,
            'total_count'=> count($contents),
            'current_index'=> 0,
            'slug'=> Str::slug($name, '_') . uniqid('_'),
            'status'=>Config::get('constants.import.status.pending')
        ]);

    }
}
