<?php
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name         = trim($_POST['name']         ?? '');
$phone        = trim($_POST['phone']        ?? '');
$booking_date = trim($_POST['booking_date'] ?? '');
$booking_time = trim($_POST['booking_time'] ?? '');
$persons      = (int)($_POST['persons']     ?? 0);
$note         = trim($_POST['note']         ?? '');

if (!$name || !$phone || !$booking_date || !$booking_time || !$persons) {
    header('Location: index.php?status=error#reservation');
    exit;
}

// Load data
$file = __DIR__ . '/data.json';
$data = [];
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true) ?: [];
}
if (!isset($data['bookings'])) $data['bookings'] = [];
if (!isset($data['settings'])) $data['settings'] = [];

$booking = [
    'id'           => time(),
    'name'         => $name,
    'phone'        => $phone,
    'booking_date' => $booking_date,
    'booking_time' => $booking_time,
    'persons'      => $persons,
    'note'         => $note,
    'date'         => date('d.m.Y H:i:s'),
    'status'       => 'new',
];

$data['bookings'][] = $booking;
file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Telegram notification
$token   = $data['settings']['telegram_token']   ?? '';
$chat_id = $data['settings']['telegram_chat_id'] ?? '';
if ($token && $chat_id) {
    $text = "🍕 *Нова резервація — Піцерія Travel*\n\n"
          . "👤 Ім'я: " . $name . "\n"
          . "📞 Телефон: " . $phone . "\n"
          . "📅 Дата: " . $booking_date . " о " . $booking_time . "\n"
          . "👥 Гостей: " . $persons . "\n"
          . ($note ? "💬 Побажання: " . $note . "\n" : "");
    @file_get_contents(
        "https://api.telegram.org/bot{$token}/sendMessage?"
        . http_build_query(['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'Markdown'])
    );
}

header('Location: index.php?status=ok#reservation');
exit;
