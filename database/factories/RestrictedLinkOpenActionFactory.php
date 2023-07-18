<?php

namespace LinkRestrictedAccess\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;

class RestrictedLinkOpenActionFactory extends Factory
{
    protected $model = RestrictedLinkOpenAction::class;

    public function definition(): array
    {
        return [
            'link_id' => \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory(),
            'browser_fingerprint' => fake()->word(),
        ];
    }
}
