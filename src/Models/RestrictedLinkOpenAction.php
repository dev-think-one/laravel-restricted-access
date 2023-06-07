<?php

namespace LinkRestrictedAccess\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonFieldCast\Casts\SimpleJsonField;
use LinkRestrictedAccess\Database\Factories\RestrictedLinkOpenActionFactory;
use LinkRestrictedAccess\RestrictedAccess;

/**
 * @property \JsonFieldCast\Json\SimpleJsonField $verification_result
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 */
class RestrictedLinkOpenAction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'viewed_at'           => 'datetime',
        'verification_result' => SimpleJsonField::class,
        'meta'                => SimpleJsonField::class,
    ];

    public function getTable(): string
    {
        return config('restricted-access.tables.opens');
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

    public function link(): BelongsTo
    {
        return $this->belongsTo(RestrictedAccess::modelClass('link'), 'link_id', 'id');
    }

    public function viewer(): MorphTo
    {
        return $this->morphTo('viewer');
    }

    public function accessExpired(): bool
    {
        if ($this->viewed_at) {
            return Carbon::now()->subHours(24)->greaterThan($this->viewed_at);
        }

        return true;
    }

    protected function makeBrowserFingerPrint(Request $request): string
    {
        return md5($request->ip() . '_' . $request->userAgent());
    }

    public function fillBrowserFingerPrint(Request $request)
    {
        return $this->fill([
            'browser_fingerprint' => $this->makeBrowserFingerPrint($request),
        ]);
    }

    public function checkBrowserFingerPrint(Request $request): bool
    {
        return $this->browser_fingerprint == $this->makeBrowserFingerPrint($request);
    }

    public function verified(Request $request): bool
    {
        return $this->checkBrowserFingerPrint($request) && !$this->accessExpired();
    }

    protected static function newFactory(): RestrictedLinkOpenActionFactory
    {
        return RestrictedLinkOpenActionFactory::new();
    }
}
