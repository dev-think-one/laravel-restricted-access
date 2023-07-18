<?php

namespace LinkRestrictedAccess\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use JsonFieldCast\Casts\SimpleJsonField;
use LinkRestrictedAccess\Database\Factories\RestrictedLinkFactory;
use LinkRestrictedAccess\RestrictedAccess;

/**
 * @property \JsonFieldCast\Json\SimpleJsonField $access_configuration
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 */
class RestrictedLink extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'use_pin'              => 'datetime',
        'access_configuration' => SimpleJsonField::class,
        'meta'                 => SimpleJsonField::class,
    ];

    protected $accessConfigsMap = [
        'checkName'               => 'use_name',
        'checkEmail'              => 'use_email',
    ];

    public function getTable(): string
    {
        return config('restricted-access.tables.links');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo('linkable');
    }


    public function openActions(): HasMany
    {
        return $this->hasMany(RestrictedAccess::linkOpenActionModel(), 'link_id', 'id');
    }

    public function checkPin(): Attribute
    {
        return Attribute::make(
            fn () => (bool)$this->use_pin,
            fn ($value) => [
                'use_pin' => $value ? $this->use_pin ?? Carbon::now() : null,
            ],
        );
    }

    protected function getAccessConfig(string $key): Attribute
    {
        $field = $this->accessConfigsMap[$key]??Str::snake($key);

        return Attribute::make(
            fn () => (bool)$this->access_configuration->getDateAttribute($field),
            function ($value) use ($field) {
                if ($value) {
                    $this->access_configuration->setDate($field, $this->access_configuration->getDateAttribute($field, Carbon::now()));
                } else {
                    $this->access_configuration->removeAttribute($field);
                }

                return [];
            },
        );
    }

    public function checkName(): Attribute
    {
        return $this->getAccessConfig(__FUNCTION__);
    }

    public function checkEmail(): Attribute
    {
        return $this->getAccessConfig(__FUNCTION__);
    }

    public function needVerification(): bool
    {
        return $this->check_pin ||
            $this->check_email  ||
            $this->check_name;
    }

    public function link(): string
    {
        return $this->linkable->restrictedLink($this);
    }

    public function cookieName(): string
    {
        return "ral_{$this->uuid}";
    }

    public function openActionFromCookie(\Illuminate\Http\Request $request): ?RestrictedLinkOpenAction
    {
        $openUuid = $request->cookie($this->cookieName());
        if ($openUuid) {
            return RestrictedAccess::linkOpenActionModel()::query()->where('uuid', $openUuid)->first();
        }

        return null;
    }

    public function verifiedOpenActionFromCookie(\Illuminate\Http\Request $request): ?RestrictedLinkOpenAction
    {
        $open = $this->openActionFromCookie($request);
        if($open?->verified($request)) {
            return $open;
        }

        return null;
    }

    public static function scopeByKey(Builder $query, string $uuid, Model|int|null $relatedModel = null): Builder
    {
        $query->where('uuid', $uuid);

        if ($relatedModel) {
            if ($relatedModel instanceof Model) {
                $relatedModel = $relatedModel->getKey();
            }

            $query->whereHas('linkable', fn (Builder $q) => $q->whereKey($relatedModel));
        }

        return $query;
    }

    protected static function newFactory(): RestrictedLinkFactory
    {
        return RestrictedLinkFactory::new();
    }
}
