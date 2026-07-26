<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\AbstractTestCase;

class HandlerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_handler_dont_report_list_contains_expected_exceptions()
    {
        /* Arrange */
        $handler    = new Handler(app());
        $reflection = new ReflectionClass($handler);
        $property   = $reflection->getProperty('dontReport');
        $property->setAccessible(true);

        /* Act */
        $dontReport = $property->getValue($handler);

        /* Assert */
        $this->assertContains(AuthenticationException::class, $dontReport);
        $this->assertContains(AuthorizationException::class, $dontReport);
        $this->assertContains(ValidationException::class, $dontReport);
        $this->assertContains(ModelNotFoundException::class, $dontReport);
        $this->assertContains(HttpException::class, $dontReport);
    }

    #[Test]
    public function it_extends_the_laravel_exception_handler()
    {
        /* Arrange */

        /* Act */
        $handler = app(Handler::class);

        /* Assert */
        $this->assertInstanceOf(ExceptionHandler::class, $handler);
    }

    #[Test]
    public function it_returns_json_for_unauthenticated_json_request()
    {
        /* Arrange */

        /* Act */
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/api/users');

        /* Assert */
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_redirects_unauthenticated_web_request_to_login()
    {
        /* Arrange */
        auth()->logout();

        /* Act */
        $response = $this->get('/dashboard');

        /* Assert */
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_returns_json_403_for_authorization_exception_on_json_request(): void
    {
        /* Arrange: a plain (non-owner) user has no CLIENT_CREATE permission, so
         * StoreClientRequest::authorize() throws AuthorizationException. */
        $this->actingAs(\App\Models\User::factory()->create());

        /* Act */
        $response = $this->postJson(route('clients.store'), []);

        /* Assert */
        $response->assertStatus(403);
        $response->assertJson(['message' => 'This action is unauthorized.']);
    }

    #[Test]
    public function it_redirects_with_flash_message_for_authorization_exception_on_web_request(): void
    {
        /* Arrange */
        $this->actingAs(\App\Models\User::factory()->create());

        /* Act */
        $response = $this->from(route('clients.create'))->post(route('clients.store'), []);

        /* Assert */
        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
    }
}
