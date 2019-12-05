<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test if users can get website defaults
     * 
     * @return void
     */
    public function testUsersCanGetWebsiteDefaults()
    {
        $defaults['account_types'] = config('website.account_types');
        $defaults['banks'] = config('website.banks');

        $this->get("/api/website")->assertJson(
            $defaults
        )->assertStatus(200);
    }
}
