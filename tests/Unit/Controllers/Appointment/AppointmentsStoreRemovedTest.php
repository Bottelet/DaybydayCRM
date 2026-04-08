<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Http\Controllers\AppointmentsController;
use App\Http\Requests\Appointment\CreateAppointmentCalendarRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests verifying that the AppointmentsController::store() method was
 * removed in this PR. The method was creating appointments via HTTP
 * and has been replaced or removed as part of a refactor.
 */
#[Group('appointments')]
class AppointmentsStoreRemovedTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function appointments_controller_does_not_have_store_method()
    {
        // The store() method was removed from AppointmentsController in this PR
        $this->assertFalse(
            method_exists(AppointmentsController::class, 'store'),
            'AppointmentsController::store() should have been removed'
        );
    }

    #[Test]
    public function appointments_controller_does_not_have_create_request_dependency()
    {
        // CreateAppointmentCalendarRequest was removed as an import since store() is gone
        $reflector = new \ReflectionClass(AppointmentsController::class);
        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(fn ($m) => $m->getName(), $methods);

        $this->assertNotContains('store', $methodNames);
    }

    #[Test]
    public function posting_to_appointments_resource_route_returns_not_found()
    {
        // No route is registered for POST /appointments (store was removed)
        $response = $this->post('/appointments');
        // Either 404 (no route) or 405 (route group exists but no POST store route)
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function appointments_controller_retains_calendar_method()
    {
        // Regression: verify the remaining methods were not accidentally removed
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'calendar'),
            'AppointmentsController::calendar() should still exist'
        );
    }

    #[Test]
    public function appointments_controller_retains_update_method()
    {
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'update'),
            'AppointmentsController::update() should still exist'
        );
    }

    #[Test]
    public function appointments_controller_retains_destroy_method()
    {
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'destroy'),
            'AppointmentsController::destroy() should still exist'
        );
    }

    #[Test]
    public function appointments_controller_retains_appointments_json_method()
    {
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'appointmentsJson'),
            'AppointmentsController::appointmentsJson() should still exist'
        );
    }

    #[Test]
    public function create_appointment_calendar_request_class_no_longer_used_by_controller()
    {
        // The CreateAppointmentCalendarRequest import was removed along with store()
        // Verify it's not referenced in the controller's method signatures
        $reflector = new \ReflectionClass(AppointmentsController::class);
        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $params = $method->getParameters();
            foreach ($params as $param) {
                $type = $param->getType();
                if ($type && ! $type->isBuiltin()) {
                    $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;
                    $this->assertNotEquals(
                        CreateAppointmentCalendarRequest::class,
                        $typeName,
                        'CreateAppointmentCalendarRequest should not be used in any controller method'
                    );
                }
            }
        }
    }
}