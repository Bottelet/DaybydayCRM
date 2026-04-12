<?php

namespace Tests\Unit\Controllers\Appointment;

use App\Http\Controllers\AppointmentsController;
use App\Http\Requests\Appointment\CreateAppointmentCalendarRequest;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

#[Group('appointments')]
class AppointmentsStoreRemovedTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region edge_cases

    #[Test]
    public function it_appointments_controller_does_not_have_store_method()
    {
        /** Arrange */
        // Already arranged

        /** Act & Assert */
        $this->assertFalse(
            method_exists(AppointmentsController::class, 'store'),
            'AppointmentsController::store() should have been removed'
        );
    }

    #[Test]
    public function it_appointments_controller_does_not_have_create_request_dependency()
    {
        /** Arrange */
        $reflector = new ReflectionClass(AppointmentsController::class);

        /** Act */
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(fn ($m) => $m->getName(), $methods);

        /** Assert */
        $this->assertNotContains('store', $methodNames);
    }

    #[Test]
    public function it_posting_to_appointments_resource_route_returns_not_found()
    {
        /** Arrange */
        // Already arranged

        /** Act */
        $response = $this->post('/appointments');

        /** Assert */
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    #[Test]
    public function it_appointments_controller_retains_calendar_method()
    {
        /** Arrange */
        // Already arranged

        /** Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'calendar'),
            'AppointmentsController::calendar() should still exist'
        );
    }

    #[Test]
    public function it_appointments_controller_retains_update_method()
    {
        /** Arrange */
        // Already arranged

        /** Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'update'),
            'AppointmentsController::update() should still exist'
        );
    }

    #[Test]
    public function it_appointments_controller_retains_destroy_method()
    {
        /** Arrange */
        // Already arranged

        /** Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'destroy'),
            'AppointmentsController::destroy() should still exist'
        );
    }

    #[Test]
    public function it_appointments_controller_retains_appointments_json_method()
    {
        /** Arrange */
        // Already arranged

        /** Act & Assert */
        $this->assertTrue(
            method_exists(AppointmentsController::class, 'appointmentsJson'),
            'AppointmentsController::appointmentsJson() should still exist'
        );
    }

    #[Test]
    public function it_creates_appointment_calendar_request_class_no_longer_used_by_controller()
    {
        /** Arrange */
        $reflector = new ReflectionClass(AppointmentsController::class);
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        /** Act & Assert */
        foreach ($methods as $method) {
            $params = $method->getParameters();
            foreach ($params as $param) {
                $type = $param->getType();
                if ($type && ! $type->isBuiltin()) {
                    $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;
                    $this->assertNotEquals(
                        CreateAppointmentCalendarRequest::class,
                        $typeName,
                        'CreateAppointmentCalendarRequest should not be used in any controller method'
                    );
                }
            }
        }
    }

    // endregion
}
