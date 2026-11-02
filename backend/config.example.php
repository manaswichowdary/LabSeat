<?php
/**
 * Example database settings for LabSeat (safe to commit to Git).
 *
 * SETUP (every developer):
 * 1) Copy this file to config.php in the same folder.
 * 2) Edit config.php with your real PostgreSQL username and password.
 * 3) Never commit config.php. It is listed in .gitignore.
 */

return [
    'DB_HOST' => 'localhost',
    'DB_PORT' => '5432',
    'DB_NAME' => 'labseat',
    'DB_USER' => 'your_postgres_username',
    // Leave empty in the example; set your real password only in local config.php.
    'DB_PASS' => '',
];
