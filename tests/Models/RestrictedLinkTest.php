<?php

namespace LinkRestrictedAccess\Tests\Models;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;
use LinkRestrictedAccess\Tests\TestCase;

class RestrictedLinkTest extends TestCase
{

    /** @test */
    public function has_linkable_instance()
    {
        $user = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)->create();

        $this->assertInstanceOf($user::class, $link->linkable);
        $this->assertEquals($user->getKey(), $link->linkable->getKey());
    }

    /** @test */
    public function fillable_check_attribute()
    {
        $user = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)->create();

        $this->assertNull($link->access_configuration->getAttribute('use_name'));
        $link->checkName = true;
        $this->assertNotNull($link->access_configuration->getAttribute('use_name'));
        $link->checkName = false;
        $this->assertNull($link->access_configuration->getAttribute('use_name'));
    }

    /** @test */
    public function check_is_need_verification()
    {
        $user = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)->create();

        $this->assertFalse($link->needVerification());
        $link->checkName = true;
        $this->assertTrue($link->needVerification());
    }

    /** @test */
    public function has_link_parameter()
    {
        $user = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)->create();

        $this->assertIsString($link->link());
        $this->assertTrue(Str::endsWith($link->link(), $link->uuid));

        $found = $user->restrictedLinks->first();
        $this->assertInstanceOf($link::class, $found);
        $this->assertEquals($link->getKey(), $found->getKey());
    }

    /** @test */
    public function scope_by_key()
    {
        $user = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)->create();

        /** @var RestrictedLink $link */
        $found = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::query()->byKey($link->uuid)->first();
        $this->assertInstanceOf($link::class, $found);
        $this->assertEquals($link->getKey(), $found->getKey());

        /** @var RestrictedLink $link */
        $found = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::query()->byKey($link->uuid, $user)->first();
        $this->assertInstanceOf($link::class, $found);
        $this->assertEquals($link->getKey(), $found->getKey());

        $found = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::query()->byKey($link->uuid, 333334)->first();
        $this->assertNull($found);

        $found = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::query()->byKey($link->uuid, $this->createUser('other'))->first();
        $this->assertNull($found);
    }

    /** @test */
    public function verified_open_action_from_cookie()
    {
        /** @var Request $request */
        $request = app(Request::class);
        $user    = $this->createUser();

        /** @var RestrictedLink $link */
        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)
            ->has(\LinkRestrictedAccess\RestrictedAccess::linkOpenActionModel()::factory()->count(2), 'openActions')->create();

        /** @var RestrictedLinkOpenAction $verifiedOpenAction */
        $verifiedOpenAction            = $link->openActions->last();
        $verifiedOpenAction->viewed_at = Carbon::now()->subHours(10);
        $verifiedOpenAction->fillBrowserFingerPrint($request);
        $verifiedOpenAction->save();

        /** @var RestrictedLinkOpenAction $unverifiedOpenAction */
        $unverifiedOpenAction = $link->openActions->first();

        // Set Cookies
        $request->cookies->set($link->cookieName(), $verifiedOpenAction->uuid);
        /** @var RestrictedLinkOpenAction $found */
        $found = $link->verifiedOpenActionFromCookie($request);
        $this->assertInstanceOf($verifiedOpenAction::class, $found);
        $this->assertEquals($verifiedOpenAction->getKey(), $found->getKey());

        // Set Cookies
        $request->cookies->set($link->cookieName(), $unverifiedOpenAction->uuid);
        /** @var RestrictedLinkOpenAction $found */
        $found = $link->openActionFromCookie($request);
        $this->assertInstanceOf($unverifiedOpenAction::class, $found);
        $this->assertEquals($unverifiedOpenAction->getKey(), $found->getKey());
        $this->assertNull($link->verifiedOpenActionFromCookie($request));
    }
}
