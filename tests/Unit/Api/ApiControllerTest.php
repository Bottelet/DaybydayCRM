<?php

namespace Tests\Unit\Api;

use App\Api\v1\Controllers\ApiController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

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
    public function it_returns_a_json_response_with_data()
    {
        /** Arrange */
        $data = ['key' => 'value'];

        /** Act */
        $response = $this->controller->callRespond($data);

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function it_returns_a_json_response_with_a_custom_status_code()
    {
        /** Arrange */
        $data       = ['test' => true];
        $statusCode = 201;

        /** Act */
        $response = $this->controller->callRespond($data, $statusCode);

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_a_json_response_with_custom_headers()
    {
        /** Arrange */
        $headers = ['X-Custom-Header' => 'custom-value'];

        /** Act */
        $response = $this->controller->callRespond(['data' => true], 200, $headers);

        /* Assert */
        $this->assertEquals('custom-value', $response->headers->get('X-Custom-Header'));
    }

    #[Test]
    public function it_returns_status_200_with_an_empty_body_on_success()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondSuccess();

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }

    #[Test]
    public function it_returns_status_201_with_data_when_a_resource_is_created()
    {
        /** Arrange */
        $data = ['id' => 42, 'name' => 'Test'];

        /** Act */
        $response = $this->controller->callRespondCreated($data);

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function it_returns_status_204_with_no_content()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondNoContent();

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_an_error_response_with_the_correct_structure()
    {
        /** Arrange */
        $message    = 'Something went wrong';
        $statusCode = 500;

        /** Act */
        $response = $this->controller->callRespondError($message, $statusCode);

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertEquals('Something went wrong', $data['errors']['message']);
        $this->assertEquals(500, $data['errors']['status_code']);
    }

    #[Test]
    public function it_returns_status_401_when_unauthorized()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondUnauthorized();

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Unauthorized', $data['errors']['message']);
    }

    #[Test]
    public function it_uses_a_custom_message_for_unauthorized_responses()
    {
        /** Arrange */
        $customMessage = 'Custom unauthorized message';

        /** Act */
        $response = $this->controller->callRespondUnauthorized($customMessage);

        /* Assert */
        $this->assertEquals(401, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Custom unauthorized message', $data['errors']['message']);
    }

    #[Test]
    public function it_returns_status_403_when_forbidden()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondForbidden();

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Forbidden', $data['errors']['message']);
    }

    #[Test]
    public function it_uses_a_custom_message_for_forbidden_responses()
    {
        /** Arrange */
        $customMessage = 'Access denied to this resource';

        /** Act */
        $response = $this->controller->callRespondForbidden($customMessage);

        /* Assert */
        $this->assertEquals(403, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Access denied to this resource', $data['errors']['message']);
    }

    #[Test]
    public function it_returns_status_404_when_a_resource_is_not_found()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $response = $this->controller->callRespondNotFound();

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Not Found', $data['errors']['message']);
    }

    #[Test]
    public function it_uses_a_custom_message_for_not_found_responses()
    {
        /** Arrange */
        $customMessage = 'Resource not found';

        /** Act */
        $response = $this->controller->callRespondNotFound($customMessage);

        /* Assert */
        $this->assertEquals(404, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Resource not found', $data['errors']['message']);
    }

    #[Test]
    public function it_controller_extends_illuminate_routing_controller()
    {
        /* Arrange */
        // Already arranged in setUp()

        /* Act */
        // No action needed

        /* Assert */
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_includes_the_status_code_in_the_error_response_body()
    {
        /** Arrange */
        $message    = 'Not found';
        $statusCode = 404;

        /** Act */
        $response = $this->controller->callRespondError($message, $statusCode);

        /** Assert */
        $data = $response->getData(true);
        $this->assertEquals(404, $data['errors']['status_code']);
    }

    #[Test]
    public function it_controller_does_not_depend_on_dingo_api()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $uses = class_uses(ApiController::class);

        /* Assert */
        $this->assertNotContains('Dingo\Api\Routing\Helpers', $uses);
    }

    #[Test]
    public function it_returns_an_empty_json_object_when_data_is_an_empty_array()
    {
        /** Arrange */
        $emptyData = [];

        /** Act */
        $response = $this->controller->callRespond($emptyData);

        /* Assert */
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    #[Test]
    public function it_preserves_nested_data_structure_in_the_response()
    {
        /** Arrange */
        $data = ['user' => ['id' => 1, 'name' => 'Alice'], 'roles' => ['admin', 'editor']];

        /** Act */
        $response = $this->controller->callRespond($data);

        /* Assert */
        $this->assertEquals($data, $response->getData(true));
    }

    #[Test]
    public function it_includes_both_message_and_status_code_in_the_error_body()
    {
        /** Arrange */
        $message    = 'Bad Request';
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
