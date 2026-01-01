#!/usr/bin/env php
<?php
/**
 * generate_experience_replacement.php
 *
 * Scans README.md for an existing "X+ years" token and generates a JSON
 * replacement file replacing that token with a computed value based on
 * a provided start-year. Does NOT modify files by itself.
 *
 * Usage:
 *   php scripts/generate_experience_replacement.php --start-year=2018
 *   php scripts/generate_experience_replacement.php --start-year=2018 > replacements/experience_replacement.json
 */

$opts = [];
foreach ($argv as $arg) {
    if (strpos($arg, '--start-year=') === 0) {
        $opts['start-year'] = (int)substr($arg, strlen('--start-year='));
    }
    if (strpos($arg, '--start-month=') === 0) {
        $opts['start-month'] = (int)substr($arg, strlen('--start-month='));
    }
}
$startYear = $opts['start-year'] ?? 2018;
$startMonth = $opts['start-month'] ?? 1;

$repoReadme = __DIR__ . '/../README.md';
if (!file_exists($repoReadme)) {
    fwrite(STDERR, "README.md not found at $repoReadme\n");
    exit(1);
}

$content = file_get_contents($repoReadme);
$found = null;
// match either "8+ years" or "8+ years (since Jul 2017)" pattern
if (preg_match('/(\d+\+ years(?: \(since [^)]+\))?)/', $content, $m)) {
    $found = $m[1];
}
if ($found === null) {
    // fallback
    $found = '5+ years';
}

// Compute precise number of full years since start month/year
try {
    $startDate = new DateTime(sprintf('%04d-%02d-01', $startYear, $startMonth));
    $now = new DateTime();
    $interval = $startDate->diff($now);
    $years = $interval->y;
    if ($years < 0) $years = 0;
    $sinceLabel = $startDate->format('M Y');
    $replacement = $years . '+ years (since ' . $sinceLabel . ')';
} catch (Exception $e) {
    $years = (int)date('Y') - $startYear;
    if ($years < 0) $years = 0;
    $replacement = $years . '+ years (since ' . $startYear . ')';
}
$pair = [
    [
        'old' => $found,
        'new' => $replacement
    ]
];

// write to replacements file
$outFile = __DIR__ . '/../replacements/experience_replacement.json';
if (file_put_contents($outFile, json_encode($pair, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    fwrite(STDERR, "Failed to write $outFile\n");
    exit(1);
}

echo json_encode($pair, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
file_put_contents('php://stderr', "Wrote experience replacement: {$found} => {$replacement} to $outFile\n");
exit(0);
