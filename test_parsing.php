<?php

$commands = [
    'git checkout -b "test1"',
    'git commit -m "initial commit"',
    'git config --global user.name "John Doe"',
];

foreach ($commands as $cmd) {
    echo "Command: $cmd\n";
    
    $exploded = explode(' ', $cmd);
    echo "Exploded (Current behavior): ";
    print_r($exploded);
    
    $parsed = str_getcsv($cmd, ' ');
    echo "Parsed (Proposed fix): ";
    print_r($parsed);
    
    echo "----------------------------------------\n";
}
