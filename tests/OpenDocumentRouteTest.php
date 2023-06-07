<?php

namespace LinkRestrictedAccess\Tests;

use LinkRestrictedAccess\Models\RestrictedLink;

class OpenDocumentRouteTest extends TestCase
{
    /** @test */
    public function check_pin_return_success()
    {
        /** @var RestrictedLink $link */
        $link = RestrictedLink::factory()
            ->linkable($this->createUser())
            ->usePin(7676)
            ->useName()
            ->useEmail()
            ->create();

        $response = $this->postJson(route('restricted-access-link.open-document', $link->uuid), []);
        $response->assertJsonValidationErrors([
            'pin',
            'name',
            'email',
        ]);

        $response = $this->postJson(route('restricted-access-link.open-document', $link->uuid), [
            'pin'   => '7676',
            'name'  => 'Foo bar',
            'email' => 'foo@bar.com',
        ]);

        $response->assertSuccessful();
        $response->assertCookie($link->cookieName());
        $response->assertJsonStructure([
            'message',
            'data' => [
                'success',
            ],
        ]);

        $response->assertJsonPath('data.success', true);
    }
}
