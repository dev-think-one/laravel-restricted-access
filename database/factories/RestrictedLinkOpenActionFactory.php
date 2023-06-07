<?php

namespace LinkRestrictedAccess\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;
use LinkRestrictedAccess\RestrictedAccess;

class RestrictedLinkOpenActionFactory extends Factory
{
    protected $model = RestrictedLinkOpenAction::class;

    public function definition(): array
    {
        return [
            'link_id' => RestrictedAccess::modelClass('link')->factory(),
        ];
    }
}
