<?php

namespace Tests\Unit\Environment;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for project configuration files changed in this PR:
 * - .env.ci       : CACHE_DRIVER → CACHE_STORE, SESSION_DOMAIN=null added
 * - .env.dusk.local: CACHE_DRIVER → CACHE_STORE
 * - .env.testing  : new file with required test environment keys
 * - .env.example  : MAIL_SCHEME=null added, PHP_CLI_SERVER_WORKERS commented out, EOF newline
 * - phpunit.yml   : PHP version 8.3, yarn build steps removed
 * - .gitignore    : expanded with categorised sections
 * - .gitattributes: rewrote with eol=lf, diff drivers, export-ignore
 *
 * These tests use plain PHPUnit (not the Laravel TestCase) because they only
 * inspect file contents and do not need the application container.
 */
#[Group('project-files-configuration')]
class ProjectFilesConfigurationTest extends TestCase
{
    private string $rootPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootPath = realpath(__DIR__.'/../../..');
    }

    // -------------------------------------------------------------------------
    // .env.ci
    // -------------------------------------------------------------------------

    #[Test]
    public function env_ci_uses_cache_store_not_cache_driver(): void
    {
        $content = $this->readFile('.env.ci');

        $this->assertStringContainsString(
            'CACHE_STORE=',
            $content,
            '.env.ci must define CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertStringNotContainsString(
            'CACHE_DRIVER=',
            $content,
            '.env.ci must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function env_ci_cache_store_is_set_to_array(): void
    {
        $vars = $this->parseEnvFile('.env.ci');

        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals(
            'array',
            $vars['CACHE_STORE'],
            'CACHE_STORE in .env.ci must be "array" for CI test isolation'
        );
    }

    #[Test]
    public function env_ci_contains_session_domain(): void
    {
        $vars = $this->parseEnvFile('.env.ci');

        $this->assertArrayHasKey(
            'SESSION_DOMAIN',
            $vars,
            '.env.ci must define SESSION_DOMAIN (added in this PR)'
        );
    }

    #[Test]
    public function env_ci_app_env_is_testing(): void
    {
        $vars = $this->parseEnvFile('.env.ci');

        $this->assertArrayHasKey('APP_ENV', $vars);
        $this->assertEquals('testing', $vars['APP_ENV']);
    }

    #[Test]
    public function env_ci_contains_required_keys(): void
    {
        $vars = $this->parseEnvFile('.env.ci');
        $required = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_KEY',
            'CACHE_STORE',
            'SESSION_DRIVER',
            'QUEUE_DRIVER',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.ci is missing required key: {$key}");
        }
    }

    // -------------------------------------------------------------------------
    // .env.dusk.local
    // -------------------------------------------------------------------------

    #[Test]
    public function env_dusk_local_uses_cache_store_not_cache_driver(): void
    {
        $content = $this->readFile('.env.dusk.local');

        $this->assertStringContainsString(
            'CACHE_STORE=',
            $content,
            '.env.dusk.local must define CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertStringNotContainsString(
            'CACHE_DRIVER=',
            $content,
            '.env.dusk.local must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function env_dusk_local_cache_store_is_set_to_array(): void
    {
        $vars = $this->parseEnvFile('.env.dusk.local');

        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals('array', $vars['CACHE_STORE']);
    }

    // -------------------------------------------------------------------------
    // .env.testing
    // -------------------------------------------------------------------------

    #[Test]
    public function env_testing_file_exists(): void
    {
        $this->assertFileExists(
            $this->rootPath.'/.env.testing',
            '.env.testing must exist so PHPUnit can load test-specific environment values'
        );
    }

    #[Test]
    public function env_testing_app_env_is_testing(): void
    {
        $vars = $this->parseEnvFile('.env.testing');

        $this->assertArrayHasKey('APP_ENV', $vars);
        $this->assertEquals('testing', $vars['APP_ENV']);
    }

    #[Test]
    public function env_testing_uses_cache_store_not_cache_driver(): void
    {
        $content = $this->readFile('.env.testing');

        $this->assertStringContainsString(
            'CACHE_STORE=',
            $content,
            '.env.testing must use the CACHE_STORE key'
        );
        $this->assertStringNotContainsString(
            'CACHE_DRIVER=',
            $content,
            '.env.testing must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function env_testing_cache_store_is_array(): void
    {
        $vars = $this->parseEnvFile('.env.testing');

        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals('array', $vars['CACHE_STORE']);
    }

    #[Test]
    public function env_testing_contains_required_database_keys(): void
    {
        $vars = $this->parseEnvFile('.env.testing');
        $required = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.testing is missing required DB key: {$key}");
        }
    }

    #[Test]
    public function env_testing_database_name_is_test_database(): void
    {
        $vars = $this->parseEnvFile('.env.testing');

        $this->assertArrayHasKey('DB_DATABASE', $vars);
        $this->assertStringContainsString(
            'test',
            strtolower($vars['DB_DATABASE']),
            'The test DB_DATABASE name should indicate it is a test database to avoid accidental data loss'
        );
    }

    #[Test]
    public function env_testing_contains_required_keys(): void
    {
        $vars = $this->parseEnvFile('.env.testing');
        $required = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_KEY',
            'CACHE_STORE',
            'SESSION_DRIVER',
            'QUEUE_DRIVER',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.testing is missing required key: {$key}");
        }
    }

    #[Test]
    public function env_testing_queue_driver_is_sync(): void
    {
        $vars = $this->parseEnvFile('.env.testing');

        $this->assertArrayHasKey('QUEUE_DRIVER', $vars);
        $this->assertEquals(
            'sync',
            $vars['QUEUE_DRIVER'],
            'QUEUE_DRIVER must be sync in testing to execute jobs inline'
        );
    }

    // -------------------------------------------------------------------------
    // .env.example
    // -------------------------------------------------------------------------

    #[Test]
    public function env_example_contains_mail_scheme_key(): void
    {
        $content = $this->readFile('.env.example');

        $this->assertStringContainsString(
            'MAIL_SCHEME=',
            $content,
            '.env.example must include MAIL_SCHEME (added in this PR for Laravel 11+ compatibility)'
        );
    }

    #[Test]
    public function env_example_uses_cache_store_not_cache_driver(): void
    {
        $content = $this->readFile('.env.example');

        $this->assertStringContainsString(
            'CACHE_STORE=',
            $content,
            '.env.example must use CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertStringNotContainsString(
            'CACHE_DRIVER=',
            $content,
            '.env.example must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function env_example_php_cli_server_workers_is_commented_out(): void
    {
        $content = $this->readFile('.env.example');

        $this->assertMatchesRegularExpression(
            '/^#\s*PHP_CLI_SERVER_WORKERS=/m',
            $content,
            'PHP_CLI_SERVER_WORKERS should be commented out in .env.example'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/^PHP_CLI_SERVER_WORKERS=/m',
            $content,
            'PHP_CLI_SERVER_WORKERS must not appear as an uncommented key in .env.example'
        );
    }

    #[Test]
    public function env_example_ends_with_newline(): void
    {
        $content = $this->readFile('.env.example');

        $this->assertStringEndsWith(
            "\n",
            $content,
            '.env.example must end with a trailing newline (fixed in this PR)'
        );
    }

    // -------------------------------------------------------------------------
    // phpunit.yml (CI workflow)
    // -------------------------------------------------------------------------

    #[Test]
    public function phpunit_workflow_uses_php_83(): void
    {
        $content = $this->readFile('.github/workflows/phpunit.yml');

        $this->assertStringContainsString(
            "php-version: '8.3'",
            $content,
            'CI workflow must use PHP 8.3 (upgraded from 8.2 in this PR)'
        );
        $this->assertStringNotContainsString(
            "php-version: '8.2'",
            $content,
            'CI workflow must not reference the old PHP 8.2'
        );
    }

    #[Test]
    public function phpunit_workflow_does_not_run_yarn_install(): void
    {
        $content = $this->readFile('.github/workflows/phpunit.yml');

        $this->assertStringNotContainsString(
            'yarn install',
            $content,
            'CI workflow must not run yarn install (frontend build step removed in this PR)'
        );
        $this->assertStringNotContainsString(
            'yarn run dev',
            $content,
            'CI workflow must not run yarn run dev (frontend build step removed in this PR)'
        );
    }

    #[Test]
    public function phpunit_workflow_uses_env_ci_for_configuration(): void
    {
        $content = $this->readFile('.github/workflows/phpunit.yml');

        $this->assertStringContainsString(
            '.env.ci',
            $content,
            'CI workflow must copy .env.ci as the application configuration'
        );
    }

    #[Test]
    public function phpunit_workflow_triggers_on_push_and_pull_request(): void
    {
        $content = $this->readFile('.github/workflows/phpunit.yml');

        $this->assertStringContainsString(
            'push',
            $content,
            'CI workflow must trigger on push events'
        );
        $this->assertStringContainsString(
            'pull_request',
            $content,
            'CI workflow must trigger on pull_request events'
        );
    }

    #[Test]
    public function phpunit_workflow_runs_database_migrations_before_tests(): void
    {
        $content = $this->readFile('.github/workflows/phpunit.yml');

        $this->assertStringContainsString(
            'migrate',
            $content,
            'CI workflow must run database migrations before executing tests'
        );
    }

    // -------------------------------------------------------------------------
    // .gitignore
    // -------------------------------------------------------------------------

    #[Test]
    public function gitignore_excludes_vendor_directory(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '/vendor/',
            $content,
            '.gitignore must exclude the /vendor/ directory'
        );
    }

    #[Test]
    public function gitignore_excludes_env_files(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '.env',
            $content,
            '.gitignore must exclude .env files'
        );
        $this->assertStringContainsString(
            '.env.production',
            $content,
            '.gitignore must exclude .env.production'
        );
    }

    #[Test]
    public function gitignore_excludes_node_modules(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '/node_modules/',
            $content,
            '.gitignore must exclude /node_modules/'
        );
    }

    #[Test]
    public function gitignore_excludes_phpunit_result_cache(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '.phpunit.result.cache',
            $content,
            '.gitignore must exclude the PHPUnit result cache file'
        );
    }

    #[Test]
    public function gitignore_excludes_ide_directories(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '.idea/',
            $content,
            '.gitignore must exclude .idea/ (JetBrains IDE)'
        );
        $this->assertStringContainsString(
            '.vscode/',
            $content,
            '.gitignore must exclude .vscode/ (VS Code)'
        );
    }

    #[Test]
    public function gitignore_excludes_log_files(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '*.log',
            $content,
            '.gitignore must exclude log files'
        );
    }

    #[Test]
    public function gitignore_excludes_build_artifacts(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            'public/build/',
            $content,
            '.gitignore must exclude public/build/ (compiled assets)'
        );
    }

    #[Test]
    public function gitignore_excludes_ds_store_and_thumbs_db(): void
    {
        $content = $this->readFile('.gitignore');

        $this->assertStringContainsString(
            '.DS_Store',
            $content,
            '.gitignore must exclude .DS_Store (macOS metadata)'
        );
        $this->assertStringContainsString(
            'Thumbs.db',
            $content,
            '.gitignore must exclude Thumbs.db (Windows thumbnails)'
        );
    }

    // -------------------------------------------------------------------------
    // .gitattributes
    // -------------------------------------------------------------------------

    #[Test]
    public function gitattributes_enforces_lf_line_endings(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertMatchesRegularExpression(
            '/\*\s+text=auto\s+eol=lf/',
            $content,
            '.gitattributes must enforce LF line endings for all files'
        );
    }

    #[Test]
    public function gitattributes_sets_diff_driver_for_php_files(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringContainsString(
            '*.php diff=php',
            $content,
            '.gitattributes must configure PHP diff driver for .php files'
        );
    }

    #[Test]
    public function gitattributes_sets_diff_driver_for_blade_files(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringContainsString(
            '*.blade.php diff=html',
            $content,
            '.gitattributes must configure HTML diff driver for Blade template files'
        );
    }

    #[Test]
    public function gitattributes_excludes_github_directory_from_exports(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringContainsString(
            '/.github export-ignore',
            $content,
            '.gitattributes must mark /.github as export-ignore to omit it from git archives'
        );
    }

    #[Test]
    public function gitattributes_excludes_changelog_from_exports(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringContainsString(
            'CHANGELOG.md export-ignore',
            $content,
            '.gitattributes must mark CHANGELOG.md as export-ignore'
        );
    }

    #[Test]
    public function gitattributes_sets_diff_driver_for_css_and_html(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringContainsString(
            '*.css diff=css',
            $content,
            '.gitattributes must configure CSS diff driver for .css files'
        );
        $this->assertStringContainsString(
            '*.html diff=html',
            $content,
            '.gitattributes must configure HTML diff driver for .html files'
        );
    }

    #[Test]
    public function gitattributes_does_not_contain_old_linguist_settings(): void
    {
        $content = $this->readFile('.gitattributes');

        $this->assertStringNotContainsString(
            'linguist-vendored',
            $content,
            '.gitattributes must not contain old linguist-vendored entries'
        );
        $this->assertStringNotContainsString(
            'linguist-language=Php',
            $content,
            '.gitattributes must not contain old linguist-language overrides'
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Read a file relative to the project root and return its contents.
     */
    private function readFile(string $relativePath): string
    {
        $fullPath = $this->rootPath.'/'.$relativePath;
        $this->assertFileExists($fullPath, "Expected project file not found: {$relativePath}");

        return file_get_contents($fullPath);
    }

    /**
     * Parse a .env-format file into a key→value map.
     * Skips blank lines and comments. Does not expand variable references.
     */
    private function parseEnvFile(string $relativePath): array
    {
        $content = $this->readFile($relativePath);
        $lines = explode("\n", $content);
        $vars = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip blanks and comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // KEY=VALUE  (value may be quoted or unquoted)
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\"'");
                $vars[$key] = $value;
            }
        }

        return $vars;
    }
}
