<?php

namespace LinkRestrictedAccess\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use LinkRestrictedAccess\Models\RestrictedLink;

class RestrictedLinkFactory extends Factory
{
    protected $model = RestrictedLink::class;

    public function definition(): array
    {
        return [
            'name'    => fake()->word(),
            'use_pin' => null,
            'pin'     => fake()->numberBetween(1000, 9999),
        ];
    }

    public function linkable(Model $linkable): static
    {
        return $this->state([
            'linkable_type' => $linkable->getMorphClass(),
            'linkable_id'   => $linkable->getKey(),
        ]);
    }

    public function usePin(string $pin): static
    {
        return $this->state([
            'use_pin' => Carbon::now(),
            'pin'     => $pin,
        ]);
    }

    public function useName(): static
    {
        return $this->state(function (array $attributes) {
            $accessConfiguration             = $attributes['access_configuration']??[];
            $accessConfiguration['use_name'] = Carbon::now();

            return [
                'access_configuration' => $accessConfiguration,
            ];
        });
    }

    public function useEmail(): static
    {
        return $this->state(function (array $attributes) {
            $accessConfiguration              = $attributes['access_configuration']??[];
            $accessConfiguration['use_email'] = Carbon::now();

            return [
                'access_configuration' => $accessConfiguration,
            ];
        });
    }
}
