<?php

namespace Tests\Unit\Api;

use App\Api\v1\Controllers\ApiController;

/**
 * Concrete subclass to expose protected methods for testing.
 */
class ConcreteApiController extends ApiController
{
    public function callRespond($data, $statusCode = 200, $headers = [])
    {
        return $this->respond($data, $statusCode, $headers);
    }

    public function callRespondSuccess()
    {
        return $this->respondSuccess();
    }

    public function callRespondCreated($data)
    {
        return $this->respondCreated($data);
    }

    public function callRespondNoContent()
    {
        return $this->respondNoContent();
    }

    public function callRespondError($message, $statusCode)
    {
        return $this->respondError($message, $statusCode);
    }

    public function callRespondUnauthorized($message = 'Unauthorized')
    {
        return $this->respondUnauthorized($message);
    }

    public function callRespondForbidden($message = 'Forbidden')
    {
        return $this->respondForbidden($message);
    }

    public function callRespondNotFound($message = 'Not Found')
    {
        return $this->respondNotFound($message);
    }
}
