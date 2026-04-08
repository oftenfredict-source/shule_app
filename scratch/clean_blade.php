<?php
$filePath = 'resources/views/Teacher/view_scheme_of_work.blade.php';
$content = file_get_contents($filePath);

$startMarker = 'function downloadPDF() {';
$endMarker = 'function downloadExcel() {';

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker);

if ($startPos !== false && $endPos !== false) {
    // Search for the closing brace of downloadPDF after its start
    // It should be followed by a newline and possibly empty lines or comments
    // But we know we just added it.
    
    // Let's find "Swal.fire" then the next "});" then the next "}"
    $swalPos = strpos($content, 'title: \'Export Failed\',', $startPos);
    if ($swalPos !== false) {
        $catchEnd = strpos($content, '});', $swalPos);
        $funcEnd = strpos($content, '}', $catchEnd + 3);
        
        if ($funcEnd !== false) {
            $funcEnd += 1; // Include the brace
            
            $newContent = substr($content, 0, $funcEnd) . "\n\n        " . substr($content, $endPos);
            file_put_contents($filePath, $newContent);
            echo "Cleaned successfully at pos $funcEnd\n";
        } else {
            echo "Could not find func end\n";
        }
    } else {
        echo "Could not find Swal\n";
    }
} else {
    echo "Markers not found\n";
}
