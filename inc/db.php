<?php
/** PDO connection singleton. */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // emulation on: allows reusing a named placeholder (e.g. :me) in one query
                PDO::ATTR_EMULATE_PREPARES   => true,
            ]
        );
    }
    return $pdo;
}
