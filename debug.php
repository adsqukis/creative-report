<?php
// Debug - dump what we received
header('Content-Type: text/plain');
echo "GET: " . print_r($_GET, true) . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
