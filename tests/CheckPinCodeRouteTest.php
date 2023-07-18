<?php

namespace LinkRestrictedAccess\Tests;

use LinkRestrictedAccess\Models\RestrictedLink;

class CheckPinCodeRouteTest extends TestCase
{
    /** @test */
    public function check_pin_return_success()
    {
        $link = RestrictedLink::factory()->linkable($this->createUser())->usePin(7676)->create();

        $response = $this->postJson(route('restricted-access-link.check-pin', $link->uuid), [
            'pin' => '1234',
        ]);
        $response->assertJsonValidationErrorFor('pin');

        $response = $this->postJson(route('restricted-access-link.check-pin', $link->uuid), [
            'pin' => '7676',
        ]);
        $response->assertJsonMissingValidationErrors('pin');
        $response->assertJsonStructure([
            'message',
            'data' => [
                'success',
            ],
        ]);

        $response->assertJsonPath('data.success', true);
    }

    /** @test */
    public function if_no_json_hearers_do_redirect()
    {
        $link = RestrictedLink::factory()->linkable($this->createUser())->usePin(7676)->create();

        $response = $this->post(route('restricted-access-link.check-pin', $link->uuid), [
            'pin' => '7676',
        ]);

        $response->assertRedirect();
    }
}
