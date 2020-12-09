<?php

namespace mhamzeh\packageFp\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;

class FilterMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create A new Filter Class';
    /**
     * @var Filesystem
     */
    private $file;

    private $nameClass;

    protected $namespace = "App\\Filters";

    /**
     * Create a new command instance.
     *
     * @param Filesystem $file
     */
    public function __construct(Filesystem $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model');

        $filterNameSpace = $this->getNamespace($name);

        $this->getNameClass($name);

        $this->classExists();

        $this->fileExists($filterNameSpace);

        $stub = $this->changeInStub($filterNameSpace);

        if (!is_null($model)) {
            try {
                $this->updateModel($model, $filterNameSpace);
            } catch (FileNotFoundException $e) {
                $this->error("Model $model Not Found");
                die();
            }
        }

        $this->putFileFilter($filterNameSpace, $stub);

        echo "The Filter $name successFully Created";
    }


    /**
     * get The NameSpace of Name Argument
     * the code from core laravel
     * @param $name
     * @return string
     */
    private
    function getNamespace(string $name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * get Name Class Filter WithOut Namespace
     * @param $name
     *
     * @return mixed
     */
    private function getNameClass($name)
    {
        $nameClass = explode('\\', $name);
        return $this->nameClass = end($nameClass);
    }

    /**
     * check  the file Filter Has Exists if yes return error
     * */
    private function classExists()
    {
        if ($this->file->exists(app_path("Filters/$this->nameClass.php"))) {
            $this->error("Class $this->nameClass Has Exists");
            die();
        };
    }

    /**
     * check the Folder in $this->namespace has Exists if yes do nothing else create A Directory
     * @param $filterNameSpace
     * @return bool
     */
    private function fileExists($filterNameSpace)
    {
        if (!file_exists(app_path("Filters/$filterNameSpace")))
            return mkdir(app_path("Filters/$filterNameSpace"));
    }

    private function changeInStub($filterNameSpace)
    {
        $stub = $this->getStub();
        $nameClass = $this->nameClass;
        $search = [
            'namespace' => '{{ namespace }}',
            'class' => '{{ class }}'
        ];
        $namespace = $filterNameSpace == "" ? '' : "\\$filterNameSpace";
        return Str::of($stub)->replace($search['namespace'], $this->namespace . $namespace)->replace($search['class'],
            $nameClass);
    }

    /*
     * get the stub for create A new Filter
     * */
    private function getStub()
    {
        return $this->file->get(__DIR__ . "/stubs/Filters/filters.stub");
    }

    /**
     *put file filter in App\\Filters
     * @param String $filterNameSpace
     * @param Stringable $stub
     *
     * @return bool|int
     */
    private function putFileFilter(string $filterNameSpace, \Illuminate\Support\Stringable $stub)
    {
        $filterNameSpace= str_replace('\\','//',$filterNameSpace);
        return $this->file->put(app_path("Filters/$filterNameSpace/$this->nameClass.php"), $stub);
    }

    /**
     * Update the model => import Filterable and namespace
     * @param $model
     * @param string $filterNameSpace
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function updateModel($model, string $filterNameSpace)
    {
        $modelPath = config('modules.modelPath');
        $fullPath = app_path("$modelPath$model.php");
        $originalContent = $this->file->get($fullPath);

        /*import NameSpace*/
        $originalContent = $this->importNameSpaceModel($originalContent);
        /*End Import Name Space*/

        /*import Class*/
        $originalContent = $this->importClassAndTraitInModel($originalContent);
        /*End Import Class*/

        /*updateFile Model */
        $this->file->put($fullPath, $originalContent);
        /*End Update file model*/

    }

    /**
     * @return \Illuminate\Support\Stringable
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @var string $originalContent
     * import the NameSpace Filterable to Model
     */
    private function importNameSpaceModel(string $originalContent)
    {
        $setting = [
            'search' => "use Illuminate\Database\Eloquent\Model;\n",
            'stubNameSpace' => __DIR__ . "/stubs/Filters/filterImportNamespace.stub",
        ];
        $stub = $this->file->get($setting['stubNameSpace']);
        $stub = $setting['search'] . $stub;
        return Str::of($originalContent)->replace($setting['search'], $stub);
    }

    /**
     * import the trait Filterable to Model
     * @param $originalContent
     *
     * @return Stringable
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function importClassAndTraitInModel($originalContent)
    {
        $setting = [
            'stubImportClass' => __DIR__ . "/stubs/Filters/filterImportClass&Trait.stub",
            'search2' => "Model\n{"
        ];
        $stub = $this->file->get($setting['stubImportClass']);
        $stub = $setting['search2'] . $stub;
        $originalContent2 = Str::of($originalContent)->replace($setting['search2'], $stub);
        return $originalContent2;
    }

    /**
     * validation for input Arguments
     */
    protected function getArguments()
    {
        return [
            [
                'name', InputArgument::REQUIRED,
                '--model', InputArgument::OPTIONAL
            ],
        ];
    }

}
