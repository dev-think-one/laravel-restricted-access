<?php

namespace LinkRestrictedAccess\Tests\Models;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;
use LinkRestrictedAccess\Tests\TestCase;

class RestrictedLinkOpenActionTest extends TestCase
{
    /** @test */
    public function has_link_relation()
    {
        $user = $this->createUser();

        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)
            ->has(\LinkRestrictedAccess\RestrictedAccess::linkOpenActionModel()::factory()->count(2), 'openActions')->create();

        $this->assertCount(2, $link->openActions);

        $openAction = $link->openActions->first();

        $this->assertEquals($link->getKey(), $openAction->link->getKey());
    }

    /** @test */
    public function access_can_be_expired()
    {
        $user = $this->createUser();

        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)
            ->has(\LinkRestrictedAccess\RestrictedAccess::linkOpenActionModel()::factory(), 'openActions')->create();

        /** @var RestrictedLinkOpenAction $openAction */
        $openAction = $link->openActions->first();

        $this->assertTrue($openAction->accessExpired());

        $openAction->viewed_at = Carbon::now()->subHours(10);
        $this->assertFalse($openAction->accessExpired());

        $openAction->viewed_at = Carbon::now()->subHours(25);
        $this->assertTrue($openAction->accessExpired());
    }

    /** @test */
    public function verification()
    {
        $request = app(Request::class);
        $user = $this->createUser();

        $link = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::factory()
            ->linkable($user)
            ->has(\LinkRestrictedAccess\RestrictedAccess::linkOpenActionModel()::factory(), 'openActions')->create();

        /** @var RestrictedLinkOpenAction $openAction */
        $openAction = $link->openActions->first();

        $this->assertFalse($openAction->verified($request));

        $openAction->viewed_at = Carbon::now()->subHours(10);
        $openAction->fillBrowserFingerPrint($request);
        $this->assertTrue($openAction->checkBrowserFingerPrint($request));
        $this->assertTrue($openAction->verified($request));


    }
}
