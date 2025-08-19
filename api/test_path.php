<?php
echo "Current __DIR__ from api folder: " . __DIR__ . "\n";
echo "Trying path: " . __DIR__ . '/../../config/bootstrap.php' . "\n";
echo "File exists: " . (file_exists(__DIR__ . '/../../config/bootstrap.php') ? 'YES' : 'NO') . "\n";
echo "Correct path should be: " . __DIR__ . '/../config/bootstrap.php' . "\n";
echo "Correct path exists: " . (file_exists(__DIR__ . '/../config/bootstrap.php') ? 'YES' : 'NO') . "\n";
?>
