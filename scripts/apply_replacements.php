#!/usr/bin/env php
<?php
/**
 * apply_replacements.php
 *
 * Reads replacements/readme_replacements.json and safely applies each replacement to README.md
 * Usage:
 *   php scripts/apply_replacements.php --dry-run   # show changes, do not write
 *   php scripts/apply_replacements.php --apply     # apply changes (backup made)
 */

$repoReadme = __DIR__ . '/../README.md';
$jsonFile = __DIR__ . '/../replacements/readme_replacements.json';

$options = array_slice($argv, 1);
$dryRun = in_array('--dry-run', $options, true) || empty($options);
$apply = in_array('--apply', $options, true);

if (!file_exists($jsonFile)) {
    fwrite(STDERR, "Replacements file not found: $jsonFile\n");
    exit(1);
}

$replacements = json_decode(file_get_contents($jsonFile), true);
if (!is_array($replacements)) {
    fwrite(STDERR, "Invalid JSON in $jsonFile\n");
    exit(1);
}

if (!file_exists($repoReadme)) {
    fwrite(STDERR, "README.md not found at $repoReadme\n");
    exit(1);
}

$orig = file_get_contents($repoReadme);
$modified = $orig;
$applied = [];

foreach ($replacements as $r) {
    $old = $r['old'];
    $new = $r['new'];

    // Find position of first occurrence
    $pos = strpos($modified, $old);
    if ($pos === false) {
        $applied[] = ['old' => $old, 'status' => 'not found'];
        continue;
    }

    // Replace only first occurrence
    $modified = substr_replace($modified, $new, $pos, strlen($old));
    $applied[] = ['old' => $old, 'status' => 'replaced'];
}

// Show summary
foreach ($applied as $a) {
    echo ($a['status'] === 'replaced' ? "✅ " : "⚠️ ") . "{$a['status']}: {$a['old']}\n";
}

if ($dryRun && !$apply) {
    echo "\n--- DRY RUN: preview of changed README.md (first 4000 chars) ---\n\n";
    echo substr($modified, 0, 4000), PHP_EOL;
    exit(0);
}

// Apply with backup
$timestamp = date('Ymd_His');
$backup = $repoReadme . ".bak.$timestamp";
if (file_put_contents($backup, $orig) === false) {
    fwrite(STDERR, "Failed to write backup to $backup\n");
    exit(1);
}

if (file_put_contents($repoReadme, $modified) === false) {
    fwrite(STDERR, "Failed to write updated README.md\n");
    // attempt to restore
    copy($backup, $repoReadme);
    exit(1);
}

echo "\nApplied replacements and backed up original to: $backup\n";
exit(0);
