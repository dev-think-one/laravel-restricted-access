<?php

namespace LinkRestrictedAccess\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LinkRestrictedAccess\RestrictedAccess;

trait HasRestrictedLink
{
    public function restrictedLinks(): MorphMany
    {
        return $this->morphMany(RestrictedAccess::modelClass('link'), 'linkable');
    }

    public function restrictedLink(RestrictedLink $restrictedLink): string
    {
        return url($restrictedLink->uuid);
    }
}
