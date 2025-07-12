<?php
// Stores daily unique IPs (used to calculate the visitor count)
$db_dir = __DIR__ . '/../db';
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}
$file = $db_dir . '/counter_data.json';
$today = date('Y-m-d');
$ip = $_SERVER['REMOTE_ADDR'];

// Open the counter file with an exclusive lock for safe concurrent updates
$fp = fopen($file, 'c+');
if ($fp === false) {
    http_response_code(500);
    exit;
}
flock($fp, LOCK_EX);
rewind($fp);

// Load or initialize data
$contents = stream_get_contents($fp);
$data = json_decode($contents, true);
if (!is_array($data)) {
    $data = [];
}

// Remove old entries (not today)
foreach ($data as $stored_ip => $last_date) {
    if ($last_date !== $today) {
        unset($data[$stored_ip]);
    }
}

// Add/update today's visit for this IP
$data[$ip] = $today;


// Return DAU count
header('Content-Type: application/json');
echo json_encode(['dau' => count($data)]);