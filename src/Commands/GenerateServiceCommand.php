<?php

namespace Mcones\LaravelBaseHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;


class GenerateServiceCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {class_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new service skeleton';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = app()['composer'];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class_name=$this->argument('class_name');
        $this->info('Generating '.$class_name);
        if ($this->files->exists($path = $this->getPath($class_name))) {
            return $this->info($class_name. ' already exists!');
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->compileServiceStub());
        $this->info($class_name.' created successfully.');
        $this->composer->dumpAutoloads();
    }


    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileServiceStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/service.stub');
        $this->replaceClassName($stub);
        $this->replaceNamespace($stub);
        return $stub;
    }


    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceClassName(&$stub)
    {
        $className = ucwords(camel_case($this->argument('class_name')));
        $stub = str_replace('{{class}}', $className, $stub);
        return $this;
    }

    private function replaceNamespace(&$stub)
    {
        $namespace=app()->getNamespace();
        $stub = str_replace('{{namespace}}', $namespace, $stub);
        return $this;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }


    /**
     * Get the path to where we should store the service.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        return app_path() . '/Services/'  . $name . '.php';
    }



}
