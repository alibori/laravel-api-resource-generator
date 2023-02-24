<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;

class GenerateApiResourceCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'alibori:api-resource {model}';

    /**
     * @var string
     */
    protected $description = 'Generate a Laravel API Resource for a given model.';

    /**
     * @var string
     */
    protected string $dir;

    /**
     * @var string
     */
    protected string $namespace;

    /**
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * @var array
     */
    protected array $properties = [];

    /**
     * @var string
     */
    protected string $stub = __DIR__ . '/stubs/api-resource.php.stub';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * @throws BindingResolutionException
     * @throws \Doctrine\DBAL\Exception
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->dir = $this->defaultResourcesDir();
        $this->namespace = 'App\Http\Resources';

        $model = $this->loadModel($this->argument('model'));

        $this->getPropertiesFromTable($model);

        $this->generateResource($model);
    }

    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'The model class name.'],
        ];
    }

    protected function defaultResourcesDir(): string
    {
        return 'app/Http/Resources';
    }

    /**
     * @throws BindingResolutionException
     */
    protected function loadModel(string $model): Model
    {

        return $this->laravel->make('App\\Models\\' . $model);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getPropertiesFromTable(Model $model): void
    {
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();

        try {
            $schema = $model->getConnection()->getDoctrineSchemaManager();
        } catch (Exception $exception) {
            $class = get_class($model);
            $driver = $model->getConnection()->getDriverName();

            if (in_array($driver, ['mysql', 'pgsql', 'sqlite'])) {
                $this->error("Database driver ($driver) for $class model is not configured properly!");
            } else {
                $this->warn("Database driver ($driver) for $class model is not supported.");
            }

            return;
        }

        $database = null;

        if (Str::contains($table, '.')) {
            [$database, $table] = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        if (! $columns) {
            return;
        }

        foreach ($columns as $column) {
            $field = $column->getName();

            $this->properties[$field] = $field;
        }
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateResource(Model $model): void
    {
        $class = get_class($model);
        $name = class_basename($class);
        $path = $this->dir . '/' . $name . 'Resource' . '.php';

        if ($this->files->exists($path)) {
            $this->error("API Resource for $class already exists!");

            return;
        }

        $this->files->put($path, $this->buildResource($class, $name));

        $this->info("API Resource for $class created successfully.");
    }

    /**
     * @throws FileNotFoundException
     */
    protected function buildResource(string $class, string $name): string
    {
        $properties = $this->properties;
        $fields = '';

        $properties_length = count($properties);
        $count = 0;
        foreach ($properties as $property) {
            if ($count === 0) {
                $fields .= "'$property' => \$this->$property,\n";
            } else if ($count < $properties_length - 1) {
                $fields .= "\t\t\t'$property' => \$this->$property,\n";
            } else {
                $fields .= "\t\t\t'$property' => \$this->$property";
            }

            $count++;
        }

        $stub = $this->files->get($this->stub);

        $stub = str_replace('{{ class }}', $name . 'Resource', $stub);
        $stub = str_replace('{{ namespace }}', $this->namespace, $stub);
        return str_replace('{{ fields }}', $fields, $stub);
    }
}