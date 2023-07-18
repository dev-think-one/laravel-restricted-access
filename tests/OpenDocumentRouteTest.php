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
            'pin'   => '222',
            'name'  => 'Foo bar',
            'email' => 'foo@bar.com',
        ]);
        $response->assertJsonValidationErrors([
            'pin',
        ]);
        $response->assertJsonMissingValidationErrors([
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

    /** @test */
    public function check_pin_as_user_return_success()
    {
        /** @var RestrictedLink $link */
        $link = RestrictedLink::factory()
            ->linkable($this->createUser())
            ->usePin(7676)
            ->useName()
            ->useEmail()
            ->create();

        $viewer = $this->createUser('fooBar');
        $this->actingAs($viewer);

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

        $openAction = $link->openActions->first();
        $this->assertInstanceOf($viewer::class, $openAction->viewer);
        $this->assertEquals($viewer->getKey(), $openAction->viewer->getKey());
    }

    /** @test */
    public function if_no_json_hearers_do_redirect()
    {
        /** @var RestrictedLink $link */
        $link = RestrictedLink::factory()
            ->linkable($this->createUser())
            ->usePin(7676)
            ->useName()
            ->useEmail()
            ->create();

        $response = $this->post(route('restricted-access-link.open-document', $link->uuid), [
            'pin'   => '7676',
            'name'  => 'Foo bar',
            'email' => 'foo@bar.com',
        ]);

        $response->assertRedirect();
        $response->assertCookie($link->cookieName());
    }
}
