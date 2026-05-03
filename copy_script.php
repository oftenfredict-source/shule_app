<?php
$content = file_get_contents('resources/views/Teacher/teacher_subjects.blade.php');
$pos = strpos($content, '<!-- View Results Modal -->');
if ($pos !== false) {
    $bottom = substr($content, $pos);
    file_put_contents('resources/views/Admin/admin_add_result.blade.php', "\n\n" . $bottom, FILE_APPEND);
    echo 'Success';
} else {
    echo 'Failed to find modal';
}
