#!/usr/bin/env php
<?php

/**
 * PHPUnit Results Cleaner & Parser
 *
 * Reads a full PHPUnit log from stdin (or a file), strips successful test
 * lines (✔) and noisy stack-trace frames, and prints only:
 *   - Failing/erroring test entries  (✘ …)
 *   - The final summary line(s)
 *
 * Full raw output is preserved in the log file that was piped through tee;
 * this script only controls what is shown on the terminal.
 *
 * Usage:  php parse-phpunit.php < phpunit-output.log
 *         phpunit ... 2>&1 | tee phpunit-output.log | php parse-phpunit.php
 */

$input = file_get_contents('php://stdin');

function hasTestFailures(string $output): bool
{
    return preg_match('/\b(FAILURES!|ERRORS!)\b/i', $output) === 1
        || preg_match('/\b(Failures|Errors|Warnings|Risky|Incomplete):\s*[1-9]\d*/i', $output) === 1
        || preg_match('/There (was 1|were \d+) (failure|error|warning|risky test|incomplete test)s?:/i', $output) === 1;
}

// 1. Remove ANSI escape codes (colors) and CI timestamps
$clean = preg_replace('#\x1B\[[0-?]*[ -/]*[@-~]|[\x07\x08\x0c\x0e\x0f]#', '', $input);
$clean = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+Z\s*/', '', $clean);

$lines  = explode("\n", $clean);
$output = [];

$isBufferingTrace = false;
$currentTrace     = [];

foreach ($lines as $line) {
    $trimmedLine = trim($line);

    // Drop successful test lines (✔ …)
    if (str_starts_with($trimmedLine, '✔')) {
        continue;
    }

    // While inside an active trace block, consume every line until the block ends.
    // Doing this first ensures no app-code frames get silently dropped between
    // vendor frames (which would happen if we only buffered lines that matched
    // the trace-start pattern below).
    if ($isBufferingTrace) {
        // End of trace: blank line or the "N) TestClass::method" header of the next failure
        if ($trimmedLine === '' || preg_match('/^\d+\)/', $trimmedLine)) {
            // Flush only the last 5 frames — #0 often starts deep inside vendor,
            // so the interesting app/test code is toward the bottom of the trace.
            foreach (array_slice($currentTrace, -5) as $traceLine) {
                $output[] = $traceLine;
            }
            $currentTrace     = [];
            $isBufferingTrace = false;
            $output[]         = $line; // keep the blank line / next-failure header
        } else {
            $currentTrace[] = $line; // buffer every trace line, vendor or app
        }
        continue;
    }

    // Identify the start of a verbose stack-trace block.
    // Match numbered frames (#N /path.php(line):) regardless of any leading
    // decoration (e.g. testdox's "│ " prefix), and also catch un-numbered
    // phpunit-vendor lines that appear at the top of some trace formats.
    if (
        preg_match('/#\d+\s+.*\.php\(\d+\):/', $trimmedLine)
        || str_contains($trimmedLine, 'vendor/phpunit/phpunit')
    ) {
        $isBufferingTrace = true;
        $currentTrace[]   = $line;
        continue;
    }

    $output[] = $line;
}

// Flush any trace that ran to the very end of the file
if ( ! empty($currentTrace)) {
    foreach (array_slice($currentTrace, -5) as $traceLine) {
        $output[] = $traceLine;
    }
}

// Final cleanup: collapse 3+ consecutive blank lines into 2
$result = implode("\n", $output);
$result = preg_replace("/\n{3,}/", "\n\n", $result);

$hasErrors = hasTestFailures($clean);

// Always print the filtered view (no ✔ lines).
// The full log is already on disk — no need to echo it here.
echo rtrim($result) . "\n";

exit($hasErrors ? 1 : 0);
