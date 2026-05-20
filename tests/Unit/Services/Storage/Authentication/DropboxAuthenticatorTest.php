<?php

namespace Tests\Unit\Services\Storage\Authentication;

use App\Models\Integration;
use App\Services\Storage\Authentication\DropboxAuthenticator;
use App\Services\Storage\Dropbox;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use RuntimeException;
use Tests\AbstractTestCase;

#[Group('storage')]
#[Group('dropbox')]
#[Group('authentication')]
class DropboxAuthenticatorTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.dropbox.client_id' => 'test-client-id']);
        config(['services.dropbox.client_secret' => 'test-client-secret']);
    }

    #[Test]
    public function it_throws_exception_when_credentials_not_configured()
    {
        // Arrange
        config(['services.dropbox.client_id' => null]);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dropbox credentials are not configured');

        // Act
        new DropboxAuthenticator();
    }

    #[Test]
    public function it_throws_exception_when_client_secret_not_configured()
    {
        // Arrange
        config(['services.dropbox.client_secret' => null]);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dropbox credentials are not configured');

        // Act
        new DropboxAuthenticator();
    }

    #[Test]
    public function it_generates_valid_auth_url()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        // Act
        $authUrl = $authenticator->authUrl();

        // Assert
        $this->assertStringContainsString('https://www.dropbox.com/oauth2/authorize', $authUrl);
        $this->assertStringContainsString('client_id=test-client-id', $authUrl);
        $this->assertStringContainsString('response_type=code', $authUrl);
        $this->assertStringContainsString('redirect_uri=', $authUrl);
    }

    #[Test]
    public function it_successfully_exchanges_authorization_code_for_token()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'sl.BrQlNr7e5mPow_test',
            'token_type'   => 'bearer',
            'expires_in'   => 3600,
        ]));

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient->expects($this->once())
            ->method('post')
            ->with(
                'https://api.dropboxapi.com/oauth2/token',
                $this->callback(function ($options) {
                    return isset($options['form_params']['code'])
                        && $options['form_params']['grant_type'] === 'authorization_code'
                        && $options['form_params']['client_id'] === 'test-client-id'
                        && $options['form_params']['client_secret'] === 'test-client-secret';
                })
            )
            ->willReturn($mockResponse);

        // Use reflection to inject the mock
        $reflection = new ReflectionClass($authenticator);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($authenticator, $mockHttpClient);

        // Act
        $token = $authenticator->token('test-auth-code');

        // Assert
        $this->assertIsArray($token);
        $this->assertArrayHasKey('access_token', $token);
        $this->assertEquals('sl.BrQlNr7e5mPow_test', $token['access_token']);
        $this->assertEquals('bearer', $token['token_type']);
    }

    #[Test]
    public function it_throws_exception_on_failed_token_exchange()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient->expects($this->once())
            ->method('post')
            ->willThrowException(new Exception('Connection failed'));

        $reflection = new ReflectionClass($authenticator);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($authenticator, $mockHttpClient);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to exchange Dropbox authorization code');

        // Act
        $authenticator->token('invalid-code');
    }

    #[Test]
    public function it_handles_revoke_access_when_integration_not_found()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dropbox integration not found');

        // Act
        $authenticator->revokeAccess();
    }

    #[Test]
    public function it_successfully_revokes_access()
    {
        // Arrange
        Integration::factory()->create([
            'name'     => Dropbox::class,
            'api_type' => 'file',
            'api_key'  => 'test-token',
        ]);

        $authenticator = new DropboxAuthenticator();

        // Act & Assert - should not throw exception
        try {
            $authenticator->revokeAccess();
            $this->assertTrue(true);
        } catch (RuntimeException $e) {
            $this->fail('revokeAccess should not throw exception when integration exists');
        }
    }

    #[Test]
    public function it_includes_redirect_uri_in_auth_url()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        // Act
        $authUrl = $authenticator->authUrl();
        $query   = parse_url($authUrl, PHP_URL_QUERY);
        parse_str($query, $params);

        // Assert
        $this->assertArrayHasKey('redirect_uri', $params);
        $this->assertEquals(route('dropbox.callback'), $params['redirect_uri']);
    }

    #[Test]
    public function it_uses_correct_oauth_endpoint()
    {
        // Arrange
        $authenticator = new DropboxAuthenticator();

        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'test-token',
        ]));

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient->expects($this->once())
            ->method('post')
            ->with('https://api.dropboxapi.com/oauth2/token', $this->anything())
            ->willReturn($mockResponse);

        $reflection = new ReflectionClass($authenticator);
        $property   = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($authenticator, $mockHttpClient);

        // Act
        $authenticator->token('test-code');

        // Assert - if we got here, correct endpoint was used
        $this->assertTrue(true);
    }
}
