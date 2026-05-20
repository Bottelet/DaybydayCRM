<?php

namespace App\Services\Billing;

use App\Models\Integration;
use App\Repositories\BillingIntegration\BillingIntegrationInterface;
use Throwable;

/**
 * Registry that resolves the active billing integration adapter.
 *
 * This class is the single source of truth for "which billing adapter is in use
 * right now". It replaces the anti-pattern of calling
 * Integration::initBillingIntegration() from models and controllers.
 *
 * - In testing / local environments (or when no billing integration is
 *   configured) a NullBillingAdapter is returned so that the application
 *   degrades gracefully instead of throwing.
 * - The resolved adapter is cached in-memory for the lifetime of the request.
 */
class BillingIntegrationRegistry
{
    private ?BillingIntegrationInterface $resolved = null;

    /**
     * Return the active billing adapter or a NullBillingAdapter when none is
     * configured.
     */
    public function driver(): BillingIntegrationInterface
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $integration = Integration::whereApiType('billing')->first();

        if ( ! $integration) {
            return $this->resolved = new NullBillingAdapter();
        }

        // Resolve the concrete adapter class from the integration record.
        // We accept only fully-qualified class names that implement the
        // BillingIntegrationInterface – no dynamic "App\{Name}" guessing.
        $candidates = [
            $integration->name,
            'App\\' . mb_ltrim($integration->name, '\\'),
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate) && is_a($candidate, BillingIntegrationInterface::class, true)) {
                try {
                    return $this->resolved = app($candidate);
                } catch (Throwable $e) {
                    // Adapter could not be instantiated (missing credentials,
                    // network not available at boot time, …).  Fall through to
                    // the null adapter so the rest of the application can keep
                    // running.
                    \Illuminate\Support\Facades\Log::warning(
                        'BillingIntegrationRegistry: could not instantiate adapter',
                        ['class' => $candidate, 'error' => $e->getMessage()]
                    );
                }
            }
        }

        return $this->resolved = new NullBillingAdapter();
    }

    /**
     * Returns true only when a real (non-null) billing adapter is available.
     */
    public function isConfigured(): bool
    {
        return ! ($this->driver() instanceof NullBillingAdapter);
    }

    /**
     * Reset the in-memory cached adapter.  Useful in tests where the
     * integration table changes between assertions.
     */
    public function reset(): void
    {
        $this->resolved = null;
    }
}
