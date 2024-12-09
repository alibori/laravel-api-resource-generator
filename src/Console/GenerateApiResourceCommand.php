<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\Console;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
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
    protected $description = 'Generate a Laravel API Resource for a given model or models.';

    protected string $dir;

    protected string $namespace;

    protected Filesystem $files;

    protected array $properties = [];

    protected array $php_docs_properties = [];

    protected string $stub = __DIR__.'/stubs/api-resource.php.stub';

    protected string $return_case = 'snake_case';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->return_case = $this->choice('Which case do you want to use for the returned parameters?', ['snake_case', 'camelCase'], 0);

        $this->dir = $this->defaultResourcesDir();
        $this->namespace = config('apiresourcegenerator.resources.namespace');

        // If the model argument contains a comma, we assume it's a list of models. We will generate a resource for each model.
        if (Str::contains($this->argument('model'), ',')) {
            $models = explode(',', $this->argument('model'));

            foreach ($models as $model_classname) {
                $model = $this->loadModel($model_classname);

                $this->getPropertiesFromTable($model);

                $this->generateResource($model);
            }
        } else {
            $model = $this->loadModel($this->argument('model'));

            $this->getPropertiesFromTable($model);

            $this->generateResource($model);
        }
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

    protected function getPropertiesFromTable(Model $model): void
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumns($table);


        if (!$columns) {
            return;
        }

        $this->properties = [];
        $this->php_docs_properties = [];

        foreach ($columns as $column) {
            $name = $column['name'];

            if (in_array($name, $model->getDates())) {
                $type = 'string';
            } else {
                // Match types to php equivalent
                $type = match ($column['type_name']) {
                    'tinyint', 'bit',
                    'integer', 'int', 'int4',
                    'smallint', 'int2',
                    'mediumint',
                    'bigint', 'int8' => 'int',

                    'boolean', 'bool' => 'bool',

                    'float', 'real', 'float4',
                    'double', 'float8' => 'float',

                    default => 'string',
                };
            }

            $this->properties[$name] = $name;
            $this->php_docs_properties[$name] = $type.' '.$name;
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

            $tag_line = trim("@{$attr} {$type[0]} {$name}");
            $tag = Tag::createInstance($tag_line, $phpdoc);
            $phpdoc->appendTag($tag);
        }

        $serializer = new DocBlockSerializer();
        $doc_comment = $serializer->getDocComment($phpdoc);

        return "{$doc_comment}";
    }
}
