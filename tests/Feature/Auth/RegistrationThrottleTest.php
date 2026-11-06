<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registration throttling had zero test coverage despite 13 throttled
 * routes (per the Phase 4 validation audit) -- nothing asserted a 429.
 * This covers the student-registration submit route (`throttle:6,1`),
 * the tightest of the three public registration endpoints. The throttle
 * middleware runs before controller validation, so an empty payload still
 * counts against the limit -- the request's content doesn't matter here,
 * only that the 7th hit within a minute is rejected.
 */
class RegistrationThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_7th_student_registration_submission_within_a_minute_is_throttled(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post(route('register.student.submit'), []);
            $response->assertStatus(302);
        }

        $response = $this->post(route('register.student.submit'), []);

        $response->assertStatus(429);
    }
}
