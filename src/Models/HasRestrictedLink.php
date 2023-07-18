<?php

namespace LinkRestrictedAccess\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRestrictedLink
{
    public function restrictedLinks(): MorphMany
    {
        return $this->morphMany(\LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel(), 'linkable');
    }

    public function restrictedLink(RestrictedLink $restrictedLink): string
    {
        return url($restrictedLink->uuid);
    }
}
