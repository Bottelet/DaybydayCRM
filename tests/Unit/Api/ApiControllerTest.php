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

    # region happy_path

    #[Test]
    public function respond_returns_json_response_with_data()
    {
        /** Arrange */
        $data = ['key' => 'value'];

        /** Act */
        $response = $this->controller->callRespond($data);

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_returns_json_response_with_custom_status_code()
    {
        /** Arrange */
        $data = ['test' => true];
        $statusCode = 201;

        /** Act */
        $response = $this->controller->callRespond($data, $statusCode);

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function respond_returns_json_response_with_custom_headers()
    {
        /** Arrange */
        $headers = ['X-Custom-Header' => 'custom-value'];

        /** Act */
        $response = $this->controller->callRespond(['data' => true], 200, $headers);

        /** Assert */
        $this->assertEquals('custom-value', $response->headers->get('X-Custom-Header'));
    }

    #[Test]
    public function respond_success_returns_status200_with_null_data()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondSuccess();

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }

    #[Test]
    public function respond_created_returns_status201_with_data()
    {
        /** Arrange */
        $data = ['id' => 42, 'name' => 'Test'];

        /** Act */
        $response = $this->controller->callRespondCreated($data);

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_no_content_returns_status204()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondNoContent();

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    #[Test]
    public function respond_error_returns_correct_structure()
    {
        /** Arrange */
        $message = 'Something went wrong';
        $statusCode = 500;

        /** Act */
        $response = $this->controller->callRespondError($message, $statusCode);

        /** Assert */
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
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondUnauthorized();

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Unauthorized', $data['errors']['message']);
    }

    #[Test]
    public function respond_unauthorized_accepts_custom_message()
    {
        /** Arrange */
        $customMessage = 'Custom unauthorized message';

        /** Act */
        $response = $this->controller->callRespondUnauthorized($customMessage);

        /** Assert */
        $this->assertEquals(401, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Custom unauthorized message', $data['errors']['message']);
    }

    #[Test]
    public function respond_forbidden_returns_status403()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondForbidden();

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Forbidden', $data['errors']['message']);
    }

    #[Test]
    public function respond_forbidden_accepts_custom_message()
    {
        /** Arrange */
        $customMessage = 'Access denied to this resource';

        /** Act */
        $response = $this->controller->callRespondForbidden($customMessage);

        /** Assert */
        $this->assertEquals(403, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Access denied to this resource', $data['errors']['message']);
    }

    #[Test]
    public function respond_not_found_returns_status404()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondNotFound();

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Not Found', $data['errors']['message']);
    }

    #[Test]
    public function respond_not_found_accepts_custom_message()
    {
        /** Arrange */
        $customMessage = 'Resource not found';

        /** Act */
        $response = $this->controller->callRespondNotFound($customMessage);

        /** Assert */
        $this->assertEquals(404, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Resource not found', $data['errors']['message']);
    }

    #[Test]
    public function controller_extends_illuminate_routing_controller()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        // No action needed

        /** Assert */
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function respond_error_includes_status_code_in_error_body()
    {
        /** Arrange */
        $message = 'Not found';
        $statusCode = 404;

        /** Act */
        $response = $this->controller->callRespondError($message, $statusCode);

        /** Assert */
        $data = $response->getData(true);
        $this->assertEquals(404, $data['errors']['status_code']);
    }

    #[Test]
    public function controller_does_not_depend_on_dingo_api()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $uses = class_uses(ApiController::class);

        /** Assert */
        $this->assertNotContains('Dingo\Api\Routing\Helpers', $uses);
    }

    #[Test]
    public function respond_with_empty_array_returns_empty_json_object()
    {
        /** Arrange */
        $emptyData = [];

        /** Act */
        $response = $this->controller->callRespond($emptyData);

        /** Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    #[Test]
    public function respond_with_nested_data_preserves_structure()
    {
        /** Arrange */
        $data = ['user' => ['id' => 1, 'name' => 'Alice'], 'roles' => ['admin', 'editor']];

        /** Act */
        $response = $this->controller->callRespond($data);

        /** Assert */
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function respond_error_message_and_status_code_are_both_in_body()
    {
        /** Arrange */
        $message = 'Bad Request';
        $statusCode = 400;

        /** Act */
        $response = $this->controller->callRespondError($message, $statusCode);

        /** Assert */
        $data = $response->getData(true);
        $this->assertEquals('Bad Request', $data['errors']['message']);
        $this->assertEquals(400, $data['errors']['status_code']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    # endregion
}
