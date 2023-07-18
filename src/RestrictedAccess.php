<?php

namespace LinkRestrictedAccess;

use Illuminate\Database\Eloquent\Model;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;

class RestrictedAccess
{
    const MODEL_KEY_LINK = 'link';
    const MODEL_KEY_OPEN = 'open';

    public static bool $runsMigrations = true;

    public static bool $registersRoutes = true;

    protected static array $models = [
        self::MODEL_KEY_LINK => RestrictedLink::class,
        self::MODEL_KEY_OPEN => RestrictedLinkOpenAction::class,
    ];

    /**
     * Disable provided by packages migrations.
     */
    public static function ignoreMigrations(): static
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Disable provided by packages routes.
     */
    public static function ignoreRoutes(): static
    {
        static::$registersRoutes = false;

        return new static;
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


    /**
     * @return class-string<RestrictedLink>
     * @throws \Exception
     */
    public static function restrictedLinkModel(): string
    {
        return static::modelClass(self::MODEL_KEY_LINK);
    }


    /**
     * @return class-string<RestrictedLinkOpenAction>
     * @throws \Exception
     */
    public static function linkOpenActionModel(): string
    {
        return static::modelClass(self::MODEL_KEY_OPEN);
    }
}
