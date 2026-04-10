<?php

namespace Tests\Unit\Api;

use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private ConcreteApiController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ConcreteApiController();
    }

    #[Test]
    public function respond_returns_json_response_with_data()
    {
        $data = ['key' => 'value'];
        $response = $this->controller->callRespond($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_returns_json_response_with_custom_status_code()
    {
        $response = $this->controller->callRespond(['test' => true], 201);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function respond_returns_json_response_with_custom_headers()
    {
        $headers = ['X-Custom-Header' => 'custom-value'];
        $response = $this->controller->callRespond(['data' => true], 200, $headers);

        $this->assertEquals('custom-value', $response->headers->get('X-Custom-Header'));
    }

    #[Test]
    public function respond_success_returns_status200_with_null_data()
    {
        $response = $this->controller->callRespondSuccess();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }

    #[Test]
    public function respond_created_returns_status201_with_data()
    {
        $data = ['id' => 42, 'name' => 'Test'];
        $response = $this->controller->callRespondCreated($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_no_content_returns_status204()
    {
        $response = $this->controller->callRespondNoContent();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    #[Test]
    public function respond_error_returns_correct_structure()
    {
        $response = $this->controller->callRespondError('Something went wrong', 500);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertEquals('Something went wrong', $data['errors']['message']);
        $this->assertEquals(500, $data['errors']['status_code']);
    }

    #[Test]
    public function respond_unauthorized_returns_status401()
    {
        $response = $this->controller->callRespondUnauthorized();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Unauthorized', $data['errors']['message']);
    }

    #[Test]
    public function respond_unauthorized_accepts_custom_message()
    {
        $response = $this->controller->callRespondUnauthorized('Custom unauthorized message');

        $this->assertEquals(401, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Custom unauthorized message', $data['errors']['message']);
    }

    #[Test]
    public function respond_forbidden_returns_status403()
    {
        $response = $this->controller->callRespondForbidden();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Forbidden', $data['errors']['message']);
    }

    #[Test]
    public function respond_forbidden_accepts_custom_message()
    {
        $response = $this->controller->callRespondForbidden('Access denied to this resource');

        $this->assertEquals(403, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Access denied to this resource', $data['errors']['message']);
    }

    #[Test]
    public function respond_not_found_returns_status404()
    {
        $response = $this->controller->callRespondNotFound();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Not Found', $data['errors']['message']);
    }

    #[Test]
    public function respond_not_found_accepts_custom_message()
    {
        $response = $this->controller->callRespondNotFound('Resource not found');

        $this->assertEquals(404, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Resource not found', $data['errors']['message']);
    }

    #[Test]
    public function respond_error_includes_status_code_in_error_body()
    {
        $response = $this->controller->callRespondError('Not found', 404);

        $data = $response->getData(true);
        $this->assertEquals(404, $data['errors']['status_code']);
    }

    #[Test]
    public function controller_does_not_depend_on_dingo_api()
    {
        // Verify that the ApiController no longer requires the Dingo\Api\Routing\Helpers trait
        $uses = class_uses(ApiController::class);
        $this->assertNotContains('Dingo\Api\Routing\Helpers', $uses);
    }

    #[Test]
    public function respond_with_empty_array_returns_empty_json_object()
    {
        $response = $this->controller->callRespond([]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    #[Test]
    public function respond_with_nested_data_preserves_structure()
    {
        $data = ['user' => ['id' => 1, 'name' => 'Alice'], 'roles' => ['admin', 'editor']];
        $response = $this->controller->callRespond($data);

        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_error_message_and_status_code_are_both_in_body()
    {
        $response = $this->controller->callRespondError('Bad Request', 400);

        $data = $response->getData(true);
        $this->assertEquals('Bad Request', $data['errors']['message']);
        $this->assertEquals(400, $data['errors']['status_code']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function controller_extends_illuminate_routing_controller()
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }
}
