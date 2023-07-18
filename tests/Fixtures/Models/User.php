<?php

namespace LinkRestrictedAccess\Tests\Fixtures\Models;

use LinkRestrictedAccess\Models\HasRestrictedLink;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasRestrictedLink;

    protected $guarded = [];

}
