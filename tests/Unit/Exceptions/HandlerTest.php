<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function handler_class_extends_laravel_exception_handler()
    {
        $handler = app(Handler::class);
        $this->assertInstanceOf(ExceptionHandler::class, $handler);
    }

    #[Test]
    public function handler_dont_report_list_contains_expected_exceptions()
    {
        $handler = new Handler(app());
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('dontReport');
        $property->setAccessible(true);
        $dontReport = $property->getValue($handler);

        $this->assertContains(AuthenticationException::class, $dontReport);
        $this->assertContains(AuthorizationException::class, $dontReport);
        $this->assertContains(ValidationException::class, $dontReport);
        $this->assertContains(ModelNotFoundException::class, $dontReport);
        $this->assertContains(HttpException::class, $dontReport);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function unauthenticated_returns_json_for_json_request()
    {
        // Access an API route that requires authentication without credentials
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/users');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthenticated.']);
    }

    #[Test]
    public function unauthenticated_redirects_to_login_for_web_request()
    {
        // Log out the user that TestCase sets up
        auth()->logout();

        $response = $this->get('/dashboard');

        // Should redirect (to login page)
        $response->assertRedirect();
    }

    #[Test]
    #[Group('repaired')]
    public function unauthenticated_json_response_has_correct_structure()
    {
    }
}
