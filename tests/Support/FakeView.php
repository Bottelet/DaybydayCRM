<?php

namespace Tests\Support;

use Illuminate\Contracts\View\View;

/**
 * Fake implementation of the View contract for use in unit tests.
 *
 * Replaces Mockery mocks for view-composer tests so that tests use
 * a real (fake) object whose behaviour can be verified by inspecting
 * its state — avoiding mock-expectation-style coupling to internals.
 *
 * Usage:
 *   $view = new FakeView(['tasks' => $task]);
 *   (new TaskHeaderComposer())->compose($view);
 *   $this->assertNull($view->getShared('client'));
 */
class FakeView implements View
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(private array $viewData = []) {}

    // ─── View contract ───────────────────────────────────────────────────────

    public function name(): string
    {
        return 'fake::view';
    }

    /**
     * @param array<string, mixed>|string $key
     * @param mixed                       $value
     */
    public function with($key, $value = null): static
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->shared[$k] = $v;
            }
        } else {
            $this->shared[$key] = $value;
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->viewData;
    }

    // ─── Renderable contract ─────────────────────────────────────────────────

    public function render(): string
    {
        return '';
    }

    // ─── Test helpers ────────────────────────────────────────────────────────

    /**
     * Retrieve all values pushed via with().
     *
     * @return array<string, mixed>
     */
    public function getShared(?string $key = null): mixed
    {
        if ($key !== null) {
            return $this->shared[$key] ?? null;
        }

        return $this->shared;
    }

    /** Assert that the given key was shared. */
    public function assertShared(string $key): void
    {
        if ( ! array_key_exists($key, $this->shared)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "FakeView: key [{$key}] was not shared via with()."
            );
        }
    }
}
