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

    # region happy_path

    // -------------------------------------------------------------------------
    // .env.ci
    // -------------------------------------------------------------------------

    #[Test]
    public function it_env_ci_uses_cache_store_not_cache_driver(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.ci');

        /** Act */
        $hasCacheStore = str_contains($content, 'CACHE_STORE=');
        $hasCacheDriver = str_contains($content, 'CACHE_DRIVER=');

        /** Assert */
        $this->assertTrue(
            $hasCacheStore,
            '.env.ci must define CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertFalse(
            $hasCacheDriver,
            '.env.ci must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function it_env_ci_cache_store_is_set_to_array(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.ci');

        /** Act */
        $cacheStore = $vars['CACHE_STORE'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals(
            'array',
            $cacheStore,
            'CACHE_STORE in .env.ci must be "array" for CI test isolation'
        );
    }

    #[Test]
    public function it_env_ci_contains_session_domain(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.ci');

        /** Act */
        // Check for key existence

        /** Assert */
        $this->assertArrayHasKey(
            'SESSION_DOMAIN',
            $vars,
            '.env.ci must define SESSION_DOMAIN (added in this PR)'
        );
    }

    #[Test]
    public function it_env_ci_app_env_is_testing(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.ci');

        /** Act */
        $appEnv = $vars['APP_ENV'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('APP_ENV', $vars);
        $this->assertEquals('testing', $appEnv);
    }

    #[Test]
    public function it_env_ci_contains_required_keys(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.ci');
        $required = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_KEY',
            'CACHE_STORE',
            'SESSION_DRIVER',
            'QUEUE_DRIVER',
        ];

        /** Act */
        // Check each required key

        /** Assert */
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.ci is missing required key: {$key}");
        }
    }

    // -------------------------------------------------------------------------
    // .env.dusk.local
    // -------------------------------------------------------------------------

    #[Test]
    public function it_env_dusk_local_uses_cache_store_not_cache_driver(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.dusk.local');

        /** Act */
        $hasCacheStore = str_contains($content, 'CACHE_STORE=');
        $hasCacheDriver = str_contains($content, 'CACHE_DRIVER=');

        /** Assert */
        $this->assertTrue(
            $hasCacheStore,
            '.env.dusk.local must define CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertFalse(
            $hasCacheDriver,
            '.env.dusk.local must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function it_env_dusk_local_cache_store_is_set_to_array(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.dusk.local');

        /** Act */
        $cacheStore = $vars['CACHE_STORE'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals('array', $cacheStore);
    }

    // -------------------------------------------------------------------------
    // .env.testing
    // -------------------------------------------------------------------------

    #[Test]
    public function it_env_testing_file_exists(): void
    {
        /** Arrange */
        $filePath = $this->rootPath.'/.env.testing';

        /** Act */
        $fileExists = file_exists($filePath);

        /** Assert */
        $this->assertTrue(
            $fileExists,
            '.env.testing must exist so PHPUnit can load test-specific environment values'
        );
    }

    #[Test]
    public function it_env_testing_app_env_is_testing(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');

        /** Act */
        $appEnv = $vars['APP_ENV'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('APP_ENV', $vars);
        $this->assertEquals('testing', $appEnv);
    }

    #[Test]
    public function it_env_testing_uses_cache_store_not_cache_driver(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.testing');

        /** Act */
        $hasCacheStore = str_contains($content, 'CACHE_STORE=');
        $hasCacheDriver = str_contains($content, 'CACHE_DRIVER=');

        /** Assert */
        $this->assertTrue(
            $hasCacheStore,
            '.env.testing must use the CACHE_STORE key'
        );
        $this->assertFalse(
            $hasCacheDriver,
            '.env.testing must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function it_env_testing_cache_store_is_array(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');

        /** Act */
        $cacheStore = $vars['CACHE_STORE'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('CACHE_STORE', $vars);
        $this->assertEquals('array', $cacheStore);
    }

    #[Test]
    public function it_env_testing_contains_required_database_keys(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');
        $required = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];

        /** Act */
        // Check each required key

        /** Assert */
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.testing is missing required DB key: {$key}");
        }
    }

    #[Test]
    public function it_env_testing_database_name_is_test_database(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');

        /** Act */
        $dbDatabase = $vars['DB_DATABASE'] ?? '';

        /** Assert */
        $this->assertArrayHasKey('DB_DATABASE', $vars);
        $this->assertStringContainsString(
            'test',
            strtolower($dbDatabase),
            'The test DB_DATABASE name should indicate it is a test database to avoid accidental data loss'
        );
    }

    #[Test]
    public function it_env_testing_contains_required_keys(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');
        $required = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_KEY',
            'CACHE_STORE',
            'SESSION_DRIVER',
            'QUEUE_DRIVER',
        ];

        /** Act */
        // Check each required key

        /** Assert */
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $vars, ".env.testing is missing required key: {$key}");
        }
    }

    #[Test]
    public function it_env_testing_queue_driver_is_sync(): void
    {
        /** Arrange */
        $vars = $this->parseEnvFile('.env.testing');

        /** Act */
        $queueDriver = $vars['QUEUE_DRIVER'] ?? null;

        /** Assert */
        $this->assertArrayHasKey('QUEUE_DRIVER', $vars);
        $this->assertEquals(
            'sync',
            $queueDriver,
            'QUEUE_DRIVER must be sync in testing to execute jobs inline'
        );
    }

    // -------------------------------------------------------------------------
    // .env.example
    // -------------------------------------------------------------------------

    #[Test]
    public function it_env_example_contains_mail_scheme_key(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.example');

        /** Act */
        $hasMailScheme = str_contains($content, 'MAIL_SCHEME=');

        /** Assert */
        $this->assertTrue(
            $hasMailScheme,
            '.env.example must include MAIL_SCHEME (added in this PR for Laravel 11+ compatibility)'
        );
    }

    #[Test]
    public function it_env_example_uses_cache_store_not_cache_driver(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.example');

        /** Act */
        $hasCacheStore = str_contains($content, 'CACHE_STORE=');
        $hasCacheDriver = str_contains($content, 'CACHE_DRIVER=');

        /** Assert */
        $this->assertTrue(
            $hasCacheStore,
            '.env.example must use CACHE_STORE (not the deprecated CACHE_DRIVER)'
        );
        $this->assertFalse(
            $hasCacheDriver,
            '.env.example must not use the deprecated CACHE_DRIVER key'
        );
    }

    #[Test]
    public function it_env_example_php_cli_server_workers_is_commented_out(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.example');

        /** Act */
        $isCommented = preg_match('/^#\s*PHP_CLI_SERVER_WORKERS=/m', $content);
        $isUncommented = preg_match('/^PHP_CLI_SERVER_WORKERS=/m', $content);

        /** Assert */
        $this->assertEquals(
            1,
            $isCommented,
            'PHP_CLI_SERVER_WORKERS should be commented out in .env.example'
        );
        $this->assertEquals(
            0,
            $isUncommented,
            'PHP_CLI_SERVER_WORKERS must not appear as an uncommented key in .env.example'
        );
    }

    #[Test]
    public function it_env_example_ends_with_newline(): void
    {
        /** Arrange */
        $content = $this->readFile('.env.example');

        /** Act */
        $endsWithNewline = str_ends_with($content, "\n");

        /** Assert */
        $this->assertTrue(
            $endsWithNewline,
            '.env.example must end with a trailing newline (fixed in this PR)'
        );
    }

    // -------------------------------------------------------------------------
    // .gitignore
    // -------------------------------------------------------------------------

    #[Test]
    public function it_gitignore_excludes_vendor_directory(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasVendor = str_contains($content, '/vendor/');

        /** Assert */
        $this->assertTrue(
            $hasVendor,
            '.gitignore must exclude the /vendor/ directory'
        );
    }

    #[Test]
    public function it_gitignore_excludes_env_files(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasEnv = str_contains($content, '.env');
        $hasEnvProduction = str_contains($content, '.env.production');

        /** Assert */
        $this->assertTrue($hasEnv, '.gitignore must exclude .env files');
        $this->assertTrue($hasEnvProduction, '.gitignore must exclude .env.production');
    }

    #[Test]
    public function it_gitignore_excludes_node_modules(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasNodeModules = str_contains($content, '/node_modules/');

        /** Assert */
        $this->assertTrue(
            $hasNodeModules,
            '.gitignore must exclude /node_modules/'
        );
    }

    #[Test]
    public function it_gitignore_excludes_phpunit_result_cache(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasPhpunitCache = str_contains($content, '.phpunit.result.cache');

        /** Assert */
        $this->assertTrue(
            $hasPhpunitCache,
            '.gitignore must exclude the PHPUnit result cache file'
        );
    }

    #[Test]
    public function it_gitignore_excludes_ide_directories(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasIdea = str_contains($content, '.idea/');
        $hasVscode = str_contains($content, '.vscode/');

        /** Assert */
        $this->assertTrue($hasIdea, '.gitignore must exclude .idea/ (JetBrains IDE)');
        $this->assertTrue($hasVscode, '.gitignore must exclude .vscode/ (VS Code)');
    }

    #[Test]
    public function it_gitignore_excludes_log_files(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasLogFiles = str_contains($content, '*.log');

        /** Assert */
        $this->assertTrue(
            $hasLogFiles,
            '.gitignore must exclude log files'
        );
    }

    #[Test]
    public function it_gitignore_excludes_build_artifacts(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasBuildArtifacts = str_contains($content, 'public/build/');

        /** Assert */
        $this->assertTrue(
            $hasBuildArtifacts,
            '.gitignore must exclude public/build/ (compiled assets)'
        );
    }

    #[Test]
    public function it_gitignore_excludes_ds_store_and_thumbs_db(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitignore');

        /** Act */
        $hasDsStore = str_contains($content, '.DS_Store');
        $hasThumbsDb = str_contains($content, 'Thumbs.db');

        /** Assert */
        $this->assertTrue($hasDsStore, '.gitignore must exclude .DS_Store (macOS metadata)');
        $this->assertTrue($hasThumbsDb, '.gitignore must exclude Thumbs.db (Windows thumbnails)');
    }

    # endregion

    # region edge_cases

    // -------------------------------------------------------------------------
    // .gitattributes
    // -------------------------------------------------------------------------

    #[Test]
    public function it_gitattributes_enforces_lf_line_endings(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasLfEnforcement = preg_match('/\*\s+text=auto\s+eol=lf/', $content);

        /** Assert */
        $this->assertEquals(
            1,
            $hasLfEnforcement,
            '.gitattributes must enforce LF line endings for all files'
        );
    }

    #[Test]
    public function it_gitattributes_sets_diff_driver_for_php_files(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasPhpDiff = str_contains($content, '*.php diff=php');

        /** Assert */
        $this->assertTrue(
            $hasPhpDiff,
            '.gitattributes must configure PHP diff driver for .php files'
        );
    }

    #[Test]
    public function it_gitattributes_sets_diff_driver_for_blade_files(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasBladeDiff = str_contains($content, '*.blade.php diff=html');

        /** Assert */
        $this->assertTrue(
            $hasBladeDiff,
            '.gitattributes must configure HTML diff driver for Blade template files'
        );
    }

    #[Test]
    public function it_gitattributes_excludes_github_directory_from_exports(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasGithubExportIgnore = str_contains($content, '/.github export-ignore');

        /** Assert */
        $this->assertTrue(
            $hasGithubExportIgnore,
            '.gitattributes must mark /.github as export-ignore to omit it from git archives'
        );
    }

    #[Test]
    public function it_gitattributes_excludes_changelog_from_exports(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasChangelogExportIgnore = str_contains($content, 'CHANGELOG.md export-ignore');

        /** Assert */
        $this->assertTrue(
            $hasChangelogExportIgnore,
            '.gitattributes must mark CHANGELOG.md as export-ignore'
        );
    }

    #[Test]
    public function it_gitattributes_sets_diff_driver_for_css_and_html(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasCssDiff = str_contains($content, '*.css diff=css');
        $hasHtmlDiff = str_contains($content, '*.html diff=html');

        /** Assert */
        $this->assertTrue($hasCssDiff, '.gitattributes must configure CSS diff driver for .css files');
        $this->assertTrue($hasHtmlDiff, '.gitattributes must configure HTML diff driver for .html files');
    }

    #[Test]
    public function it_gitattributes_does_not_contain_old_linguist_settings(): void
    {
        /** Arrange */
        $content = $this->readFile('.gitattributes');

        /** Act */
        $hasLinguistVendored = str_contains($content, 'linguist-vendored');
        $hasLinguistLanguage = str_contains($content, 'linguist-language=Php');

        /** Assert */
        $this->assertFalse(
            $hasLinguistVendored,
            '.gitattributes must not contain old linguist-vendored entries'
        );
        $this->assertFalse(
            $hasLinguistLanguage,
            '.gitattributes must not contain old linguist-language overrides'
        );
    }

    # endregion

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
