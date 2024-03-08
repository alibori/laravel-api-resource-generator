<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\Console;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class GenerateApiResourceCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'api-resource:generate {model}';

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
     * @var array
     */
    protected array $php_docs_properties = [];

    /**
     * @var string
     */
    protected string $stub = __DIR__.'/stubs/api-resource.php.stub';

    /**
     * @var string
     */
    protected string $return_case = 'snake_case';

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
        $this->return_case = $this->choice('Which case do you want to use for the returned parameters?', ['snake_case', 'camelCase'], 0);

        $this->dir = $this->defaultResourcesDir();
        $this->namespace = config('apiresourcegenerator.resources.namespace');

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
        return config('apiresourcegenerator.resources.dir');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function loadModel(string $model): Model
    {
        return $this->laravel->make(config('apiresourcegenerator.models.namespace').'\\'.$model);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getPropertiesFromTable(Model $model): void
    {
        $table = $model->getConnection()->getTablePrefix().$model->getTable();

        try {
            // Check if getDoctrineSchemaManager method exists to deal with Laravel 11 upgrade
            if (method_exists($model->getConnection(), 'getDoctrineSchemaManager')) {
                $schema = $model->getConnection()->getDoctrineSchemaManager();
            } else {
                $schema = $model->getConnection()->getSchemaBuilder();
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            $class = get_class($model);
            $driver = $model->getConnection()->getDriverName();

            if (in_array($driver, ['mysql', 'pgsql', 'sqlite'])) {
                $this->error("Database driver ({$driver}) for {$class} model is not configured properly!");
            } else {
                $this->warn("Database driver ({$driver}) for {$class} model is not supported.");
            }

            return;
        }

        $database = null;

        if (Str::contains($table, '.')) {
            [$database, $table] = explode('.', $table);
        }

        //$columns = $schema->listTableColumns($table, $database);
        // Check if listTableColumns method exists to deal with Laravel 11 upgrade
        if (method_exists($schema, 'listTableColumns')) {
            $columns = $schema->listTableColumns($table, $database);
        } else {
            $columns = $schema->getColumns($table);
        }

        if ( ! $columns) {
            return;
        }

        foreach ($columns as $column) {
            // Check if $column is an array to deal with Laravel 11 upgrade
            if (is_array($column)) {
                $field = $column['name'];
                $type = $column['type'];
            } else {
                $field = $column->getName();
                $type = $column->getType()->getName();
            }

            $field_type = match ($type) {
                'string', 'text', 'date', 'time', 'guid', 'datetimetz', 'datetime', 'decimal' => 'string',
                'integer', 'bigint', 'smallint' => 'integer',
                'boolean' => 'boolean',
                'float' => 'float',
                default => 'mixed',
            };

            $this->properties[$field] = $field;
            $this->php_docs_properties[$field] = $field_type.' '.$field;
        }
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateResource(Model $model): void
    {
        $class = get_class($model);
        $name = class_basename($class);
        $path = $this->dir.'/'.$name.'Resource'.'.php';

        if ( ! $this->files->isDirectory($this->dir)) {
            $this->files->makeDirectory($this->dir, 0755, true);
        }

        if ($this->files->exists($path)) {
            if ($this->confirm('The resource already exists. Do you want to overwrite it? [y|N]')) {
                $this->files->delete($path);
            } else {
                return;
            }
        }

        $this->files->put($path, $this->buildResource($name));

        $this->info("API Resource for {$class} created successfully.");
    }

    /**
     * @throws FileNotFoundException
     */
    protected function buildResource(string $name): string
    {
        $properties = $this->properties;
        $doc_block = $this->generatePHPDocs();
        $fields = '';

        $properties_length = count($properties);
        $count = 0;
        foreach ($properties as $property) {
            $array_key = $property;

            if ('camelCase' === $this->return_case) {
                $array_key = Str::camel($property);
            }

            if ($count < 1) {
                $fields .= "'{$array_key}' => \$this->{$property},\n";
            } elseif ($count < $properties_length - 1) {
                $fields .= "\t\t\t'{$array_key}' => \$this->{$property},\n";
            } else {
                $fields .= "\t\t\t'{$array_key}' => \$this->{$property}";
            }

            $count++;
        }

        $stub = $this->files->get($this->stub);

        $stub = str_replace('{{ docblock }}', $doc_block, $stub);
        $stub = str_replace('{{ class }}', $name.'Resource', $stub);
        $stub = str_replace('{{ namespace }}', $this->namespace, $stub);
        return str_replace('{{ fields }}', $fields, $stub);
    }

    protected function generatePHPDocs(): string
    {
        $phpdoc = new DocBlock('Resource generated by alibori/laravel-api-resource-generator');

        foreach ($this->php_docs_properties as $name => $property) {
            $type = explode(' ', $property);
            $name = "\${$name}";

            $attr = 'property';

            $tagLine = trim("@{$attr} {$type[0]} {$name}");
            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->appendTag($tag);
        }

        $serializer = new DocBlockSerializer();
        $docComment = $serializer->getDocComment($phpdoc);

        return "{$docComment}";
    }
}
