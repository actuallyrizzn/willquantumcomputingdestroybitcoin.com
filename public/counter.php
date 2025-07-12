<?php
// File to store daily unique IPs and count
$db_dir = __DIR__ . '/../db';
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}
$file = $db_dir . '/counter_data.json';
$today = date('Y-m-d');
$ip = $_SERVER['REMOTE_ADDR'];

// Load or initialize data
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) $data = [];
} else {
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

// Save updated data with an exclusive lock
$result = file_put_contents($file, json_encode($data), LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update counter']);
    exit;
}

// Return DAU count
header('Content-Type: application/json');
echo json_encode(['dau' => count($data)]); 