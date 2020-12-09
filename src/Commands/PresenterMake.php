<?php

namespace mhamzeh\packageFp\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class PresenterMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:presenter {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create A new Presenter Class';
    /**
     * @var Filesystem
     */
    private $file;
    /**
     * input name argument without namespace
     */
    protected $nameClass;

    /**
     * storage of presenters
     */
    protected $namespace = "App\\Presenter";

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
     */
    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model');
        /*todo not found model */
        $PresenterNameSpace = $this->getNamespace($name);

        $this->getNameClass($name);

        $this->classExists($name);

        $this->fileExists($PresenterNameSpace);

        $stub = $this->changeInStub($PresenterNameSpace);

        if (!is_null($model)) {
            try {
                $this->UpdateModel($model, $PresenterNameSpace);
            } catch (FileNotFoundException $e) {
                $this->error('Model Not Found');
                die();
            }
        }
        $this->putFilePresenter($PresenterNameSpace, $stub);


        echo "The Presenter $name successFully Created";
    }

    /**
     * validation for input Arguments
     */
    protected
    function getArguments()
    {
        return [
            [
                'name', InputArgument::REQUIRED,
                '--model', InputArgument::OPTIONAL
            ],
        ];
    }

    /**
     * Get The File Stub
     */
    private
    function getStub()
    {
        return $this->file->get(__DIR__.'/stubs/Presenter/presenter.stub');
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
     * check  the file Presenter Has Exists if yes return error
     * @param $name
     * */
    private function classExists($name)
    {
        if ($this->file->exists(app_path("Presenter/$name.php"))) {
            $this->error("Class $name Has Exists");
            die();
        };
    }

    /**
     * search and replace name Class & namespace in stub and return it
     * @param $namespace
     * @return \Illuminate\Support\Stringable
     */
    private
    function changeInStub($namespace)
    {
        $stub = $this->getStub();
        $nameClass = $this->nameClass;
        $settings =
            [
                'search' => '{{ class }}',
                'namespace' => '{{ namespace }}'
            ];
        $namespace = $namespace == "" ? '' : "\\$namespace";
        return Str::of($stub)->replace($settings['search'], $nameClass)->replace($settings['namespace'],
            $this->namespace . $namespace);
    }

    /**
     * get Name Class Presenter WithOut Namespace
     * @param $name
     *
     * @return mixed
     */
    private
    function getNameClass($name)
    {
        $nameClass = explode('\\', $name);
        return $this->nameClass = end($nameClass);
    }

    /**
     * check the Folder in $this->namespace has Exists if yes do nothing else create A Directory
     * @param $PresenterNameSpace
     * @return bool
     */
    private
    function fileExists($PresenterNameSpace)
    {
        if (!file_exists(app_path("Presenter/$PresenterNameSpace")))
            return mkdir(app_path("Presenter/$PresenterNameSpace"));
    }

    /**
     * put The file in App\\Presenter\\$namespace\\$file
     * @param $PresenterNameSpace
     * @param $stub
     * @return bool|int
     */
    private function putFilePresenter($PresenterNameSpace, $stub)
    {
        $PresenterNameSpace= str_replace('\\','//',$PresenterNameSpace);
        return $this->file->put(app_path("Presenter/$PresenterNameSpace/$this->nameClass.php"), $stub);
    }

    /**
     * @param $modelName
     * @param $PresenterNameSpace
     * @throws FileNotFoundException
     * import NameSpace Presenter And PresenterClass And Trait Presentable  In Model
     */
    private
    function UpdateModel($modelName, $PresenterNameSpace)
    {
        $modelPath = config('modules.modelPath');
        $fullPath = app_path("$modelPath$modelName.php");
        $originalContent = $this->file->get($fullPath);

        /*import NameSpace*/
        $originalContent = $this->importNameSpaceModel($PresenterNameSpace, $originalContent);
        /*End Import Name Space*/

        /*import Class*/
        $originalContent = $this->importClassAndTraitInModel($originalContent);
        /*End Import Class*/

        /*updateFile Model */
        $this->file->put($fullPath, $originalContent);
        /*End Update file model*/
    }

    /**
     * @param $PresenterNameSpace
     * @param array $setting
     * @param string $originalContent
     * @return \Illuminate\Support\Stringable|string
     * @throws FileNotFoundException
     */
    private
    function importNameSpaceModel($PresenterNameSpace, string $originalContent)
    {
        $setting = [
            'search' => "use Illuminate\Database\Eloquent\Model;\n",
            'stubNameSpace' => __DIR__ . "/stubs/Presenter/presenterImportNamespace.stub",
            'namespace' => '{{ namespace }}',
        ];
        $stub = $this->file->get($setting['stubNameSpace']);
        $namespace = $PresenterNameSpace == '' ? "\\$this->nameClass" : "\\$PresenterNameSpace\\$this->nameClass";
        $stub = Str::of($stub)->replace($setting['namespace'], $this->namespace . "$namespace;");
        $stub = $setting['search'] . $stub;
        $originalContent = Str::of($originalContent)->replace($setting['search'], $stub);
        return $originalContent;
    }

    /**
     * @param array $setting
     * @param $originalContent
     * @return \Illuminate\Support\Stringable
     * @throws FileNotFoundException
     */
    private
    function importClassAndTraitInModel($originalContent): \Illuminate\Support\Stringable
    {
        $setting = [
            'stubImportClass' => __DIR__ . "/stubs/Presenter/presenterImportClass&Trait.stub",
            'search2' => "Model\n{"
        ];
        $stub = $this->file->get($setting['stubImportClass']);
        $stub = Str::of($stub)->replace('{{ namePresenter }}', $this->nameClass);
        $stub = $setting['search2'] . $stub;
        $originalContent2 = Str::of($originalContent)->replace($setting['search2'], $stub);
        return $originalContent2;
    }

}
