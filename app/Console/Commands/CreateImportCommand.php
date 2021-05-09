<?php

namespace App\Console\Commands;

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
    protected string $signature = 'import:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Create a new import instance record';

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
     * @return int
     */
    public function handle()
    {

        $this->createImportWizard();

        return 0;

    }

    private function createImport($name, $description, $location, $format,$path, $contents)
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

    private function createImportWizard()
    {
        $format = $this->choice('Kindly select file format', ['JSON']);
        $name = $this->ask('what is the name of the file you wish to import?');
        $description = $this->ask('Please input file description');
        $location = $this->choice(
            'where is the file located?',
            [Config::get('constants.import.storage.local'), Config::get('constants.import.storage.url')]
        );

        if($location === Config::get('constants.import.storage.local'))
        {
            $path = $this->ask('Input Storage path (e.g public/profiles/new_jsonFile.json)');
            $exists = Storage::disk('local')->exists($path);
            if(!$exists){
                $this->error('The supplied document path does not exist');
            }
            $contents = Storage::disk('local')->get($path);
            $contents = json_decode($contents, true);
        }elseif(Config::get('constants.import.storage.url') === $location){
            $path = $this->ask('input a valid url to the resource (e.g www.google.com/jsonfile.json)');
            $contents = Http::get($path);
            if($contents->ok() && count($contents->json()) < 1)
            {
                $this->error('The json file url is invalid or empty');
            }
        }


        $import = $this->createImport($name, $description, $location, $format, $path, $contents);

        $this->info("{$import->name} created with a unique identifier {$import->slug}");
    }
}
