<?php
$file = 'c:\laragon\www\ShuleLinkWeb\resources\views\Admin\manage_sms_updates.blade.php';
$lines = file($file);
file_put_contents($file, implode('', array_slice($lines, 0, 610)));
echo "Truncated to 610 lines\n";
