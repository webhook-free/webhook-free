<?php
$count = 20;
header("Content-Type: application/json");

$file_name = "message.json";
$file_path = __DIR__ . "/" . $file_name;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$file_url = $protocol . "://" . $host . $script_dir . "/" . $file_name;
$input = file_get_contents("php://input");
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($input)) {
    echo json_encode([
        "status" => "no data received",
        "url" => $file_url
    ], JSON_PRETTY_PRINT);
    exit;
}
$data = json_decode($input, true);
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid JSON",
        "url" => $file_url
    ], JSON_PRETTY_PRINT);
    exit;
}

$messages = [];
if (file_exists($file_path)) {
    $content = @file_get_contents($file_path);
    $messages = json_decode($content, true);
    if (!is_array($messages)) {
        $messages = [];
    }
}
$messages[] = [
    "received_at" => date("Y-m-d H:i:s"),
    "data" => $data
];
if (count($messages) > $count) {
    $messages = array_slice($messages, -$count);
}
file_put_contents($file_path, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
echo json_encode([
    "status" => "ok",
    "received_at" => date("Y-m-d H:i:s"),
    "received_data" => $data,
    "url" => $file_url
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
