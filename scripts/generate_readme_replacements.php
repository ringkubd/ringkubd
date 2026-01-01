#!/usr/bin/env php
<?php
/**
 * generate_readme_replacements.php
 *
 * Outputs JSON replacement pairs for README badge fixes (does NOT modify files).
 * Usage:
 *   php scripts/generate_readme_replacements.php > replacements/readme_replacements.json
 *   php scripts/generate_readme_replacements.php --sed  # prints suggested sed commands (preview only)
 */

$replacements = [
    [
        'old' => '[![trophy](https://github-profile-trophy.vercel.app/?username=ringkubd&theme=onedark&column=7&margin-w=15&margin-h=15)](https://github.com/ryo-ma/github-profile-trophy)',
        'new' => '[![trophy](https://github-profile-trophy.vercel.app/?username=ringkubd&theme=onedark)](https://github.com/ryo-ma/github-profile-trophy)'
    ],
    [
        'old' => '![GitHub Stats](https://readme-stats-q22wxmhvd-ringkubd.vercel.app/api?username=ringkubd&count_private=true&show_icons=true&theme=radical&hide_border=true)',
        'new' => '![GitHub Stats](https://github-readme-stats.vercel.app/api?username=ringkubd&show_icons=true&count_private=true&theme=radical&hide_border=true)'
    ],
    [
        'old' => '![Top Languages](https://github-readme-stats.vercel.app/api/top-langs/?username=ringkubd&layout=compact&theme=radical&hide_border=true)',
        'new' => '![Top Languages](https://github-readme-stats.vercel.app/api/top-langs/?username=ringkubd&layout=compact&theme=radical&hide_border=true&langs_count=8)'
    ],
    [
        'old' => '![GitHub Streak](https://github-readme-streak-stats.herokuapp.com/?user=ringkubd&theme=radical&hide_border=true)',
        'new' => '![GitHub Streak](https://streak-stats.demolab.com?user=ringkubd&theme=radical&hide_border=true)'
    ],
    [
        'old' => '- 🌱 Currently learning **React Native**',
        'new' => '- 🌟 **Expert in React Native, Laravel, JavaScript, and more**'
    ]
];

if (in_array('--sed', $argv, true)) {
    // Print suggested sed commands for manual preview/apply (be careful: test before running)
    foreach ($replacements as $r) {
        $old = addcslashes($r['old'], "'\\");
        $new = addcslashes($r['new'], "'\\");
        // Use a safe delimiter (@) to avoid conflicts with URL slashes
        echo "# Replace first occurrence in README.md (preview first):\n";
        echo "# grep -nF \"{$r['old']}\" README.md || true\n";
        echo "sed -i " . "'0,/@" . str_replace("@","\\@",addcslashes($r['old'], "'\\")) . "@/s@" . str_replace("@","\\@",addcslashes($r['old'], "'\\")) . "@" . str_replace("@","\\@",addcslashes($r['new'], "'\\")) . "@' README.md\n\n";
    }
    exit(0);
}

// Default: print JSON to stdout (unescaped slashes for readability)
echo json_encode($replacements, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;

// Helpful message to stderr
fwrite(STDERR, "Wrote replacement pairs to stdout. Run with --sed to see suggested sed commands (preview before applying).\n");

exit(0);
