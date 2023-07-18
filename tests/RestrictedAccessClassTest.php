<?php

namespace LinkRestrictedAccess\Tests;

use Illuminate\Database\Eloquent\Model;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\RestrictedAccess;
use LinkRestrictedAccess\Tests\Fixtures\Models\CustomRestrictedLink;

class RestrictedAccessClassTest extends TestCase
{
    /** @test */
    public function model_can_be_changed()
    {
        $this->assertEquals(RestrictedLink::class, RestrictedAccess::restrictedLinkModel());
        $this->assertInstanceOf(RestrictedLink::class, RestrictedAccess::model(RestrictedAccess::MODEL_KEY_LINK));
        $this->assertNotInstanceOf(CustomRestrictedLink::class, RestrictedAccess::model(RestrictedAccess::MODEL_KEY_LINK));

        RestrictedAccess::useModel(RestrictedAccess::MODEL_KEY_LINK, CustomRestrictedLink::class);

        $this->assertEquals(CustomRestrictedLink::class, RestrictedAccess::restrictedLinkModel());
        $this->assertInstanceOf(CustomRestrictedLink::class, RestrictedAccess::model(RestrictedAccess::MODEL_KEY_LINK));
    }

    /** @test */
    public function model_to_override_should_be_child_of_model()
    {
        $this->expectExceptionMessage('Class should be a model [Illuminate\Database\Eloquent\Model]');
        RestrictedAccess::useModel(RestrictedAccess::MODEL_KEY_LINK, Model::class);
    }

    /** @test */
    public function to_override_developer_should_pass_correct_key()
    {
        $this->expectExceptionMessage('Incorrect model key [foo], allowed keys are: link, open');
        RestrictedAccess::useModel('foo', CustomRestrictedLink::class);
    }

    /** @test */
    public function developer_can_ignore_migrations()
    {
        $this->assertTrue(RestrictedAccess::$runsMigrations);
        RestrictedAccess::ignoreMigrations();
        $this->assertFalse(RestrictedAccess::$runsMigrations);
        RestrictedAccess::$runsMigrations = true;
    }

    /** @test */
    public function developer_can_ignore_routes()
    {
        $this->assertTrue(RestrictedAccess::$registersRoutes);
        RestrictedAccess::ignoreRoutes();
        $this->assertFalse(RestrictedAccess::$registersRoutes);
        RestrictedAccess::$registersRoutes = true;
    }
}
