<?php

namespace LinkRestrictedAccess\Models;

use Carbon\Carbon;
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
        return $this->hasMany(RestrictedAccess::modelClass('open'), 'link_id', 'id');
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

    public function checkName(): Attribute
    {
        return Attribute::make(
            fn () => (bool)$this->access_configuration->getDateAttribute('use_name'),
            function ($value) {
                if ($value) {
                    $this->access_configuration->setDate('use_name', $this->access_configuration->getDateAttribute('use_name', Carbon::now()));
                } else {
                    $this->access_configuration->removeAttribute('use_name');
                }

                return [];
            },
        );
    }

    public function checkEmail(): Attribute
    {
        return Attribute::make(
            fn () => (bool)$this->access_configuration->getDateAttribute('use_email'),
            function ($value) {
                if ($value) {
                    $this->access_configuration->setDate('use_email', $this->access_configuration->getDateAttribute('use_email', Carbon::now()));
                } else {
                    $this->access_configuration->removeAttribute('use_email');
                }

                return [];
            },
        );
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
            return RestrictedAccess::modelClass('open')::query()->where('uuid', $openUuid)->first();
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

    protected static function newFactory(): RestrictedLinkFactory
    {
        return RestrictedLinkFactory::new();
    }
}
