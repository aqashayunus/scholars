<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationAndRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_trial_redirects_to_register(): void
    {
        $response = $this->get('/free-trial');
        $response->assertStatus(301);
        $response->assertRedirect(route('register'));

        $postResponse = $this->post('/free-trial');
        $postResponse->assertStatus(301);
        $postResponse->assertRedirect(route('register'));
    }

    public function test_login_and_register_routes_work(): void
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
        $this->get('/register/student')->assertStatus(200);
        $this->get('/register/parent')->assertStatus(200);
        $this->get('/register/teacher')->assertStatus(200);
    }

    public function test_public_pages_render_without_free_trial_links(): void
    {
        $pricing = $this->get('/pricing');
        $pricing->assertStatus(200);
        $pricing->assertDontSee('/free-trial');

        $faq = $this->get('/faq');
        $faq->assertStatus(200);
        $faq->assertDontSee('/free-trial');

        $stories = $this->get('/success-stories');
        $stories->assertStatus(200);
        $stories->assertDontSee('/free-trial');
    }
}
