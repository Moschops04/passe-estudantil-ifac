<?php
require 'src/bootstrap.php';
try {
    $db = db();
    echo "DB OK\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
