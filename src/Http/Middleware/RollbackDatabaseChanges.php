<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Http\Middleware;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Runs every storybook request inside a database transaction that is always
 * rolled back, so a story that creates records (a factory create(), a parent
 * created by a factory definition) never leaves anything behind.
 *
 * The response is fully rendered before the rollback, so stories and the
 * components they render can still read what they wrote.
 */
final readonly class RollbackDatabaseChanges
{
    public function __construct(private ConnectionResolverInterface $connections) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config()->boolean('blade-storybook.rollback.enabled', true)) {
            return $next($request);
        }

        $levels = [];

        foreach ($this->connectionNames() as $name) {
            try {
                $connection = $this->connections->connection($name);

                if (! $connection instanceof Connection) {
                    continue;
                }

                $level = $connection->transactionLevel();
                $connection->beginTransaction();
            } catch (Throwable) {
                continue;
            }

            $levels[] = [$connection, $level];
        }

        try {
            return $next($request);
        } finally {
            foreach (array_reverse($levels) as [$connection, $level]) {
                $this->rollBack($connection, $level);
            }
        }
    }

    /**
     * Rolls back to the level the connection was at before this request, which
     * also discards transactions a story opened but never closed.
     */
    private function rollBack(Connection $connection, int $level): void
    {
        if ($connection->transactionLevel() > $level) {
            $connection->rollBack($level);
        }
    }

    /**
     * @return list<string|null>
     */
    private function connectionNames(): array
    {
        /** @var list<string|null> $names */
        $names = config()->array('blade-storybook.rollback.connections', [null]);

        return array_values(array_unique($names, SORT_REGULAR));
    }
}
