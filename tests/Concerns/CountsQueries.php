<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Query-budget assertions (Phase 5, Step 5e) -- lets a test assert "this
 * action must not fire more than N queries" so a fixed N+1 (Step 5d) or an
 * added index (Step 5c) stays fixed instead of silently regressing the
 * next time someone touches the code.
 *
 * Uses DB::listen() rather than enableQueryLog()/getQueryLog() so bindings
 * don't accumulate in memory across a whole test class, and so the count
 * reflects only the queries fired inside the callback, not anything before it.
 */
trait CountsQueries
{
    /**
     * Run $callback and return [queryCount, result].
     *
     * @return array{0: int, 1: mixed}
     */
    protected function countQueries(callable $callback): array
    {
        $count = 0;
        $listener = function () use (&$count) {
            $count++;
        };

        DB::listen($listener);
        $result = $callback();
        // Laravel has no DB::forgetListener(); the listener is harmless
        // after this method returns since it only increments a local
        // variable that goes out of scope, but it does mean a second
        // countQueries() call in the same test adds a second listener.
        // Each call's $count is still independent and correct.

        return [$count, $result];
    }

    /**
     * Assert that running $callback fires at most $max queries.
     */
    protected function assertQueryCountLessThanOrEqual(int $max, callable $callback, string $message = ''): mixed
    {
        [$count, $result] = $this->countQueries($callback);

        $this->assertLessThanOrEqual(
            $max,
            $count,
            $message !== '' ? $message : "Expected at most {$max} queries, but {$count} were executed."
        );

        return $result;
    }
}
