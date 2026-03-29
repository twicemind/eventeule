<?php
/**
 * Local Configuration for EventEule
 * 
 * This file is NOT committed to Git!
 * Copy config-local.example.php to config-local.php and adjust the values.
 */

return [
    // GitHub Personal Access Token for updates from private repositories
    // Create a token at: https://github.com/settings/tokens
    // Required Permissions: repo (all)
    // Leave this field empty ('') if your repository is public
    'github_token' => '',
    
    // Production database credentials (if import desired)
    'prod_db' => [
        'host' => '',
        'name' => '',
        'user' => '',
        'password' => '',
    ],
    
    // Add additional local configurations here
];
