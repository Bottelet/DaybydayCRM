<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function handlerClassExtendsLaravelExceptionHandler()
    {
        $handler = app(Handler::class);
        $this->assertInstanceOf(ExceptionHandler::class, $handler);
    }

    /** @test */
    public function handlerDontReportListContainsExpectedExceptions()
    {
        $handler = new Handler(app());
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('dontReport');
        $property->setAccessible(true);
        $dontReport = $property->getValue($handler);

        $this->assertContains(\Illuminate\Auth\AuthenticationException::class, $dontReport);
        $this->assertContains(\Illuminate\Auth\Access\AuthorizationException::class, $dontReport);
        $this->assertContains(\Illuminate\Validation\ValidationException::class, $dontReport);
        $this->assertContains(\Illuminate\Database\Eloquent\ModelNotFoundException::class, $dontReport);
        $this->assertContains(\Symfony\Component\HttpKernel\Exception\HttpException::class, $dontReport);
    }

    /** @test */
    public function unauthenticatedReturnsJsonForJsonRequest()
    {
        // Access an API route that requires authentication without credentials
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/users');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthenticated.']);
    }

    /** @test */
    public function unauthenticatedRedirectsToLoginForWebRequest()
    {
        // Log out the user that TestCase sets up
        auth()->logout();

        $response = $this->get('/dashboard');

        // Should redirect (to login page)
        $response->assertRedirect();
    }

    /** @test */
    public function unauthenticatedJsonResponseHasCorrectStructure()
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/users');

        $response->assertStatus(401);
        $data = $response->json();
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Unauthenticated.', $data['error']);
    }
}