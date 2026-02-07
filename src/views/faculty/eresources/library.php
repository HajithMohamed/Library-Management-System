<?php
/**
 * Faculty Saved E-Resources Library (alias for main library view)
 * Route: GET /faculty/eresources/library
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// Reuse the main library view
include APP_ROOT . '/views/eresources/library.php';
