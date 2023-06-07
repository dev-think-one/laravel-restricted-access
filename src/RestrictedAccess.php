<?php

namespace LinkRestrictedAccess;

use Illuminate\Database\Eloquent\Model;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;

class RestrictedAccess
{
    public static bool $runsMigrations = true;

    public static bool $registersRoutes = true;

    public static array $classMap = [];

    protected static array $models = [
        'link' => RestrictedLink::class,
        'open' => RestrictedLinkOpenAction::class,
    ];


    public static function ignoreMigrations(): static
    {
        static::$runsMigrations = false;

        return new static;
    }

    public static function ignoreRoutes(): static
    {
        static::$registersRoutes = false;

        return new static;
    }

    /**
     * @param array|null $map
     * @param bool $merge Merge or replace class map.
     * @return array
     */
    public static function classMap(array $map = null, bool $merge = true): array
    {
        if (is_array($map)) {
            static::$classMap = $merge && static::$classMap
                ? $map + static::$classMap : $map;
        }

        return static::$classMap;
    }

    /**
     * @param string $alias
     * @return string|null
     */
    public static function getMappedClass(string $alias): ?string
    {
        return static::$classMap[$alias] ?? null;
    }

    /**
     * @param string $key
     * @param string $modelClass
     * @return class-string<static>
     * @throws \Exception
     */
    public static function useModel(string $key, string $modelClass): string
    {
        if (!in_array($key, array_keys(static::$models))) {
            throw new \Exception(
                "Incorrect model key [{$key}], allowed keys are: " . implode(', ', array_keys(static::$models))
            );
        }
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \Exception("Class should be a model [{$modelClass}]");
        }

        static::$models[$key] = $modelClass;

        return static::class;
    }

    /**
     * @param string $key
     * @return class-string<Model|RestrictedLink|RestrictedLinkOpenAction>
     * @throws \Exception
     */
    public static function modelClass(string $key): string
    {
        return static::$models[$key] ?? throw new \Exception(
            "Incorrect model key [{$key}], allowed keys are: " . implode(', ', array_keys(static::$models))
        );
    }

    /**
     * @param string $key
     * @param array $attributes
     * @return Model|RestrictedLink|RestrictedLinkOpenAction
     * @throws \Exception
     */
    public static function model(string $key, array $attributes = []): Model
    {
        $modelClass = static::modelClass($key);

        /** @var Model $model */
        $model = new $modelClass($attributes);

        return $model;
    }
}
