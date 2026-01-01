#!/usr/bin/env php
<?php
/**
 * apply_json_replacements.php
 *
 * Generic replacement applier for any JSON replacement file.
 * Supports multiple files and includes validation/preview.
 *
 * Usage:
 *   php scripts/apply_json_replacements.php replacements/professional_enhancements.json --dry-run
 *   php scripts/apply_json_replacements.php replacements/professional_enhancements.json --apply
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php apply_json_replacements.php <json_file> [--dry-run|--apply]\n");
    exit(1);
}

$jsonFile = $argv[1];
$options = array_slice($argv, 2);
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

$repoDir = realpath(dirname($jsonFile) . '/..');
$repoReadme = $repoDir . '/README.md';

if (!file_exists($repoReadme)) {
    fwrite(STDERR, "README.md not found at $repoReadme\n");
    exit(1);
}

$orig = file_get_contents($repoReadme);
$modified = $orig;
$stats = ['replaced' => 0, 'not_found' => 0];

echo "📋 Processing " . count($replacements) . " replacement(s)...\n\n";

foreach ($replacements as $idx => $r) {
    $old = $r['old'];
    $new = $r['new'];

    if (strpos($modified, $old) === false) {
        echo "⚠️  [{$idx}] NOT FOUND: " . substr($old, 0, 60) . "...\n";
        $stats['not_found']++;
        continue;
    }

    $modified = str_replace($old, $new, $modified);
    echo "✅ [{$idx}] REPLACED: " . substr($old, 0, 60) . "...\n";
    $stats['replaced']++;
}

echo "\n📊 Summary: {$stats['replaced']} replaced, {$stats['not_found']} not found\n\n";

if ($dryRun && !$apply) {
    echo "--- DRY RUN: Preview (first 5000 chars) ---\n\n";
    echo substr($modified, 0, 5000), "\n\n--- (truncated) ---\n";
    echo "\n💡 Run with --apply to write changes (backup will be created).\n";
    exit(0);
}

// Apply with backup
$timestamp = date('Ymd_His');
$backup = dirname($repoReadme) . '/README.md.bak.' . $timestamp;

if (file_put_contents($backup, $orig) === false) {
    fwrite(STDERR, "❌ Failed to create backup at $backup\n");
    exit(1);
}

if (file_put_contents($repoReadme, $modified) === false) {
    fwrite(STDERR, "❌ Failed to write updated README.md\n");
    exit(1);
}

echo "✅ Applied changes and backed up original.\n";
echo "📁 Backup: $backup\n";
echo "📄 Updated: $repoReadme\n";
exit(0);
