<?php

namespace Tests\Unit\Environment;

use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class LanguageFilesTest extends AbstractTestCase
{
    /**
     * @return array<int, string>
     */
    public static function languageFiles(): array
    {
        return glob(resource_path('lang') . '/*.json') ?: [];
    }

    #[Test]
    public function it_has_at_least_one_language_file(): void
    {
        /* Arrange */
        $files = self::languageFiles();

        /* Act */
        $count = count($files);

        /* Assert */
        $this->assertGreaterThan(0, $count, 'resources/lang must contain at least one *.json translation file');
    }

    #[Test]
    public function it_parses_every_language_file_as_valid_json(): void
    {
        /* Arrange */
        $files = self::languageFiles();

        /* Act */
        $invalid = [];
        foreach ($files as $file) {
            json_decode(file_get_contents($file), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $invalid[] = basename($file) . ': ' . json_last_error_msg();
            }
        }

        /* Assert */
        $this->assertEmpty($invalid, 'Malformed language file(s): ' . implode(', ', $invalid));
    }

    #[Test]
    public function it_decodes_every_language_file_as_a_flat_string_keyed_map(): void
    {
        /* Arrange */
        $files = self::languageFiles();

        /* Act */
        $malformed = [];
        foreach ($files as $file) {
            $decoded         = json_decode(file_get_contents($file), true);
            $isFlatStringMap = is_array($decoded) && ! empty($decoded)
                && array_reduce(
                    array_keys($decoded),
                    static fn (bool $carry, $key) => $carry && is_string($key),
                    true
                )
                && array_reduce(
                    $decoded,
                    static fn (bool $carry, $value) => $carry && is_string($value),
                    true
                );

            if ( ! $isFlatStringMap) {
                $malformed[] = basename($file);
            }
        }

        /* Assert */
        $this->assertEmpty(
            $malformed,
            'Language file(s) not a flat string-keyed map of translations: ' . implode(', ', $malformed)
        );
    }
}
