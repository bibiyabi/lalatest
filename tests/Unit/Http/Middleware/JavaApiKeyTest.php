<?php

namespace Tests\Unit\Http\Middleware;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use Tests\TestCase;

class JavaApiKeyTest extends TestCase
{
    use DatabaseTransactions;


    public function setUp():void
    {
        parent::setUp();
    }


}
