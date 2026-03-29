<?php
session_start();
$file = 'data.json';

$data = json_decode(file_exists($file) ? file_get_contents($file) : '{"settings":{},"bookings":[]}', true);
if (!isset($data['bookings'])) $data['bookings'] = [];
if (!isset($data['settings'])) $data['settings'] = [];

$admin_password = $data['settings']['admin_password'] ?? 'pizza';

// ── Вхід / вихід ────────────────────────────────────────────
if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $login_error = 'Невірний пароль!';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true):
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вхід — Піцерія Travel</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 min-h-screen flex items-center justify-center p-4">
    <div class="bg-stone-900 border border-white/8 p-8 rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="text-center mb-7">
            <div class="text-4xl mb-3">🍕</div>
            <h2 class="text-2xl font-bold text-amber-400">Піцерія Travel</h2>
            <p class="text-stone-500 text-sm mt-1">Панель керування</p>
        </div>
        <?php if (isset($login_error)) echo "<p class='text-red-400 mb-4 text-sm text-center font-bold'>{$login_error}</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Пароль" required autofocus
                class="w-full px-4 py-3 bg-stone-800 border border-white/10 text-white rounded-xl mb-4 outline-none focus:ring-2 focus:ring-amber-500 transition text-base">
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold py-3 rounded-xl transition text-base">Увійти</button>
        </form>
    </div>
</body>
</html>
<?php
exit;
endif;

// Reload after login
$data = json_decode(file_exists($file) ? file_get_contents($file) : '{"settings":{},"bookings":[]}', true);
if (!isset($data['bookings'])) $data['bookings'] = [];
if (!isset($data['settings'])) $data['settings'] = [];

$tab = $_GET['tab'] ?? 'bookings';

// ── CSV Експорт ──────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bookings_pizza_travel.csv');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Дата заявки', 'Клієнт', 'Телефон', 'Дата резервації', 'Час', 'Гостей', 'Статус', 'Нотатка']);
    foreach ($data['bookings'] as $row) {
        $bd = !empty($row['booking_date']) ? date('d.m.Y', strtotime($row['booking_date'])) : '';
        fputcsv($out, [
            $row['date'] ?? '',
            $row['name'] ?? '',
            $row['phone'] ?? '',
            $bd,
            $row['booking_time'] ?? '',
            $row['persons'] ?? '',
            ($row['status'] ?? 'new') === 'new' ? 'Нова' : 'Оброблена',
            $row['note'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

// ── Дії із заявками ──────────────────────────────────────────
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] === 'delete') {
        $data['bookings'] = array_values(array_filter($data['bookings'], fn($b) => $b['id'] != $id));
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: admin.php?tab=bookings&msg=deleted');
        exit;
    }
    if ($_GET['action'] === 'toggle') {
        foreach ($data['bookings'] as &$b) {
            if ($b['id'] == $id) { $b['status'] = ($b['status'] ?? 'new') === 'new' ? 'processed' : 'new'; break; }
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: admin.php?tab=bookings');
        exit;
    }
}

// ── Нотатка ──────────────────────────────────────────────────
if (isset($_POST['save_note'])) {
    foreach ($data['bookings'] as &$b) {
        if ($b['id'] == $_POST['booking_id']) { $b['note'] = trim($_POST['note'] ?? ''); break; }
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php?tab=bookings');
    exit;
}

// ── Контент ──────────────────────────────────────────────────
if (isset($_POST['save_content'])) {
    foreach (['meta_title', 'meta_desc', 'og_image', 'business_desc', 'promo_text', 'form_title', 'footer_text'] as $f) {
        $data['settings'][$f] = $_POST[$f] ?? '';
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php?tab=content&saved=1');
    exit;
}

// ── Налаштування ─────────────────────────────────────────────
if (isset($_POST['save_settings'])) {
    foreach (['business_name', 'phone', 'address', 'analytics_id', 'telegram_token', 'telegram_chat_id'] as $f) {
        $data['settings'][$f] = $_POST[$f] ?? '';
    }
    $new_pass = trim($_POST['admin_password'] ?? '');
    if ($new_pass !== '') $data['settings']['admin_password'] = $new_pass;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $success_msg = 'Налаштування збережено!';
}

// ── Завантаження фото (pizza gallery) ────────────────────────
if (isset($_POST['upload_pizza'])) {
    $dir = __DIR__ . '/images/pizza-photo/';
    $uploaded = 0;
    $errors   = [];
    foreach ($_FILES['pizza_images']['tmp_name'] as $i => $tmp) {
        if ($_FILES['pizza_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) { $errors[] = 'Невірний формат файлу.'; continue; }
        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = 'photo_' . time() . '_' . $i . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . $name)) $uploaded++;
    }
    $success_msg = "Завантажено: {$uploaded} фото." . ($errors ? ' Помилки: ' . implode(', ', $errors) : '');
    $tab = 'content';
}

// ── Видалення фото (pizza gallery) ───────────────────────────
if (isset($_POST['delete_pizza_photo'])) {
    $f = basename($_POST['delete_pizza_photo']);
    $hidden = $data['settings']['hidden_pizza_photos'] ?? [];
    if (!in_array($f, $hidden)) { $hidden[] = $f; }
    $data['settings']['hidden_pizza_photos'] = array_values($hidden);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo json_encode(['ok' => true]); exit; }
    header('Location: admin.php?tab=content');
    exit;
}

// ── Завантаження фото (other gallery) ────────────────────────
if (isset($_POST['upload_other'])) {
    $dir = __DIR__ . '/images/meals-photo/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $uploaded = 0;
    $errors   = [];
    foreach ($_FILES['other_images']['tmp_name'] as $i => $tmp) {
        if ($_FILES['other_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) { $errors[] = 'Невірний формат.'; continue; }
        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $name = 'photo_' . time() . '_' . $i . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . $name)) $uploaded++;
    }
    $success_msg = "Завантажено: {$uploaded} фото." . ($errors ? ' Помилки: ' . implode(', ', $errors) : '');
    $tab = 'content';
}

// ── Видалення фото (other gallery) ───────────────────────────
if (isset($_POST['delete_other_photo'])) {
    $f = basename($_POST['delete_other_photo']);
    $hidden = $data['settings']['hidden_other_photos'] ?? [];
    if (!in_array($f, $hidden)) { $hidden[] = $f; }
    $data['settings']['hidden_other_photos'] = array_values($hidden);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo json_encode(['ok' => true]); exit; }
    header('Location: admin.php?tab=content');
    exit;
}

function val($key) {
    global $data;
    return htmlspecialchars($data['settings'][$key] ?? '');
}

$total_bookings     = count($data['bookings']);
$new_bookings       = count(array_filter($data['bookings'], fn($b) => ($b['status'] ?? 'new') === 'new'));
$processed_bookings = $total_bookings - $new_bookings;

// Photos lists (files on disk, excluding hidden)
$hidden_pizza = $data['settings']['hidden_pizza_photos'] ?? [];
$hidden_other = $data['settings']['hidden_other_photos'] ?? [];

$pizza_photos = array_values(array_filter(
    glob(__DIR__ . '/images/pizza-photo/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [],
    fn($f) => is_file($f) && !in_array(basename($f), $hidden_pizza)
));
$other_photos = array_values(array_filter(
    glob(__DIR__ . '/images/meals-photo/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [],
    fn($f) => is_file($f) && !in_array(basename($f), $hidden_other)
));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Адмінка — Піцерія Travel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { font-family: system-ui, sans-serif; }
.thumb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
.thumb-item { position: relative; aspect-ratio: 3/4; border-radius: 10px; overflow: hidden; background: #1c1917; }
.thumb-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-item .del-btn { position: absolute; top: 5px; right: 5px; opacity: 0; transition: opacity .2s; }
.thumb-item:hover .del-btn { opacity: 1; }
.drop-zone { border: 2px dashed #78716c; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; }
.drop-zone:hover, .drop-zone.dragover { border-color: #f59e0b; background: rgba(245,158,11,.05); }
</style>
</head>
<body class="bg-gray-950 text-gray-200 min-h-screen">

<!-- Nav -->
<nav class="bg-gray-900 border-b border-gray-800 shadow-md sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-5 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🍕</span>
            <h1 class="text-lg font-bold text-amber-400">Піцерія Travel — Панель керування</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="index.php" target="_blank" class="text-gray-400 hover:text-amber-400 text-sm font-bold transition">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>Сайт
            </a>
            <a href="?logout=1" class="bg-red-700 hover:bg-red-600 px-4 py-2 rounded-lg font-bold text-sm transition">Вийти</a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-5 py-7">

    <?php if (isset($success_msg) || ($_GET['saved'] ?? '') === '1'): ?>
    <div class="bg-amber-900/40 border-l-4 border-amber-500 text-amber-300 p-4 mb-6 rounded-lg font-bold">
        <i class="fa-solid fa-circle-check mr-2"></i><?= isset($success_msg) ? htmlspecialchars($success_msg) : 'Контент збережено!' ?>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="bg-red-900/30 border-l-4 border-red-500 text-red-300 p-4 mb-6 rounded-lg">
        <i class="fa-solid fa-trash mr-2"></i>Заявку видалено.
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-1 mb-7 border-b border-gray-700 pb-0">
        <a href="?tab=bookings" class="px-5 py-3 font-bold text-sm transition rounded-t-lg <?= $tab === 'bookings' ? 'text-amber-400 border-b-2 border-amber-400' : 'text-gray-500 hover:text-amber-300' ?>">
            <i class="fa-solid fa-bell mr-1.5"></i>Бронювання
            <?php if ($new_bookings > 0): ?>
            <span class="bg-red-600 text-white text-[11px] px-2 py-0.5 rounded-full ml-1"><?= $new_bookings ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=content" class="px-5 py-3 font-bold text-sm transition rounded-t-lg <?= $tab === 'content' ? 'text-amber-400 border-b-2 border-amber-400' : 'text-gray-500 hover:text-amber-300' ?>">
            <i class="fa-solid fa-image mr-1.5"></i>Контент та фото
        </a>
        <a href="?tab=settings" class="px-5 py-3 font-bold text-sm transition rounded-t-lg <?= $tab === 'settings' ? 'text-amber-400 border-b-2 border-amber-400' : 'text-gray-500 hover:text-amber-300' ?>">
            <i class="fa-solid fa-gear mr-1.5"></i>Налаштування
        </a>
    </div>

    <!-- ═══════════════ TAB: BOOKINGS ═══════════════ -->
    <?php if ($tab === 'bookings'): ?>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-7">
        <div class="bg-gray-800 p-5 rounded-xl border-l-4 border-blue-500 flex justify-between items-center">
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Всього заявок</div>
                <div class="text-3xl font-black text-white"><?= $total_bookings ?></div>
            </div>
            <i class="fa-solid fa-layer-group text-4xl text-blue-900"></i>
        </div>
        <div class="bg-gray-800 p-5 rounded-xl border-l-4 border-green-500 flex justify-between items-center">
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Нових</div>
                <div class="text-3xl font-black text-green-400"><?= $new_bookings ?></div>
            </div>
            <i class="fa-solid fa-bell text-4xl text-green-900"></i>
        </div>
        <div class="bg-gray-800 p-5 rounded-xl border-l-4 border-gray-500 flex justify-between items-center">
            <div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider">Оброблених</div>
                <div class="text-3xl font-black text-gray-400"><?= $processed_bookings ?></div>
            </div>
            <i class="fa-solid fa-check-double text-4xl text-gray-700"></i>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-amber-400">Список бронювань</h2>
        <a href="?action=export" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i>Завантажити (Excel)
        </a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-700 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="p-4 border-b border-gray-600 w-36">Дата заявки</th>
                        <th class="p-4 border-b border-gray-600">Клієнт / Телефон</th>
                        <th class="p-4 border-b border-gray-600 w-48">Деталі</th>
                        <th class="p-4 border-b border-gray-600">Нотатка</th>
                        <th class="p-4 border-b border-gray-600 text-center w-32">Статус</th>
                        <th class="p-4 border-b border-gray-600 text-right w-36">Дії</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($data['bookings'])): ?>
                    <?php
                    $sorted = $data['bookings'];
                    usort($sorted, fn($a, $b) => ($b['id'] ?? 0) - ($a['id'] ?? 0));
                    foreach ($sorted as $row):
                        $isNew = ($row['status'] ?? 'new') === 'new';
                    ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-700/30 transition <?= $isNew ? '' : 'opacity-60' ?>">
                        <td class="p-4 text-xs text-gray-500 align-top whitespace-nowrap"><?= htmlspecialchars($row['date'] ?? '') ?></td>
                        <td class="p-4 align-top">
                            <div class="font-bold text-white text-base mb-1"><?= htmlspecialchars($row['name'] ?? '') ?></div>
                            <a href="tel:<?= htmlspecialchars($row['phone'] ?? '') ?>" class="text-amber-400 font-bold hover:underline inline-flex items-center gap-1 bg-amber-900/20 px-2 py-1 rounded text-sm">
                                <i class="fa-solid fa-phone text-xs"></i><?= htmlspecialchars($row['phone'] ?? '') ?>
                            </a>
                        </td>
                        <td class="p-4 align-top text-sm">
                            <?php if (!empty($row['booking_date'])): ?>
                            <div class="text-white font-bold mb-1">
                                <i class="fa-regular fa-calendar text-amber-500 mr-1"></i>
                                <?= date('d.m.Y', strtotime($row['booking_date'])) ?>
                                <?php if (!empty($row['booking_time'])): ?>
                                <span class="text-amber-400 ml-1"><?= htmlspecialchars($row['booking_time']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($row['persons'])): ?>
                            <div class="text-gray-400">
                                <i class="fa-solid fa-users text-amber-600 mr-1"></i><?= (int)$row['persons'] ?> гостей
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 align-top">
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                <input type="text" name="note" value="<?= htmlspecialchars($row['note'] ?? '') ?>" placeholder="Додати коментар..."
                                    class="text-sm p-2 bg-gray-700 border border-gray-600 text-white rounded-lg w-full focus:ring-2 focus:ring-amber-500 outline-none transition placeholder-gray-600">
                                <button type="submit" name="save_note" class="bg-amber-800/50 text-amber-300 px-3 rounded-lg hover:bg-amber-700 transition" title="Зберегти">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                </button>
                            </form>
                        </td>
                        <td class="p-4 text-center align-top pt-5">
                            <?php if ($isNew): ?>
                            <span class="bg-green-900/50 text-green-400 px-3 py-1 rounded-full text-xs font-bold border border-green-700/50">Нова</span>
                            <?php else: ?>
                            <span class="bg-gray-700 text-gray-400 px-3 py-1 rounded-full text-xs font-bold border border-gray-600">Оброблена</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right align-top pt-4">
                            <div class="flex justify-end gap-2">
                                <a href="?action=toggle&id=<?= $row['id'] ?>&tab=bookings"
                                   class="<?= $isNew ? 'bg-green-700 hover:bg-green-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300' ?> px-3 py-2 rounded-lg text-xs font-bold transition"
                                   title="<?= $isNew ? 'Відмітити оброблено' : 'Повернути в нові' ?>">
                                    <i class="fa-solid <?= $isNew ? 'fa-check' : 'fa-rotate-left' ?>"></i>
                                </a>
                                <a href="?action=delete&id=<?= $row['id'] ?>&tab=bookings"
                                   onclick="return confirm('Видалити заявку?')"
                                   class="bg-red-900/50 text-red-400 hover:bg-red-700 hover:text-white px-3 py-2 rounded-lg text-xs font-bold transition">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="p-16 text-center text-gray-600">
                            <i class="fa-solid fa-inbox text-5xl mb-4 block opacity-20"></i>
                            <span class="text-lg">Заявок поки немає.</span>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>


    <!-- ═══════════════ TAB: CONTENT ═══════════════ -->
    <?php if ($tab === 'content'): ?>

    <!-- SEO / Meta -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mb-6">
        <form method="POST" class="space-y-5">
            <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-purple-400"></i> SEO / Meta
            </h3>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        Meta Title <span id="title-count" class="text-gray-600 font-normal normal-case ml-1"></span>
                    </label>
                    <input id="f-meta-title" type="text" name="meta_title" value="<?= val('meta_title') ?>"
                           placeholder="Піцерія Travel — Хмельницький" maxlength="70"
                           class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">OG Image URL</label>
                    <input id="f-og-image" type="url" name="og_image" value="<?= val('og_image') ?>"
                           placeholder="https://pizza.ddev.site/images/photo_1_....jpg"
                           class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                    Meta Description <span id="desc-count" class="text-gray-600 font-normal normal-case ml-1"></span>
                </label>
                <textarea id="f-meta-desc" name="meta_desc" rows="2" maxlength="160"
                          placeholder="Піцерія Travel у центрі Хмельницького..."
                          class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500 resize-none"><?= val('meta_desc') ?></textarea>
            </div>

            <!-- Google SERP preview -->
            <div class="flex flex-wrap gap-4 items-start">
                <div class="bg-white rounded-xl p-4 max-w-xl flex-1 min-w-60">
                    <div class="text-xs text-gray-400 mb-1">pizza.ddev.site</div>
                    <div id="prev-google-title" class="text-[#1a0dab] text-xl leading-snug mb-1 font-normal" style="font-family:arial,sans-serif">
                        <?= val('meta_title') ?: 'Піцерія Travel — Хмельницький' ?>
                    </div>
                    <div id="prev-google-desc" class="text-[#4d5156] text-sm" style="font-family:arial,sans-serif">
                        <?= val('meta_desc') ?: 'Великий вибір піц, м\'ясних, рибних та овочевих страв. Доставка та самовивіз.' ?>
                    </div>
                </div>

                <!-- OG / Social card preview -->
                <div class="rounded-xl overflow-hidden border border-gray-600 w-72 flex-shrink-0">
                    <div id="prev-og-img-wrap" class="bg-gray-700 w-full" style="aspect-ratio:1.91/1;overflow:hidden;">
                        <?php $ogVal = val('og_image'); ?>
                        <img id="prev-og-img" src="<?= $ogVal ?>" alt="OG Image"
                             class="w-full h-full object-cover <?= $ogVal ? '' : 'hidden' ?>">
                        <div id="prev-og-placeholder" class="w-full h-full flex items-center justify-center text-gray-500 text-sm <?= $ogVal ? 'hidden' : '' ?>">
                            <i class="fa-regular fa-image text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-gray-800 px-3 py-2 border-t border-gray-600">
                        <div class="text-gray-500 text-xs uppercase mb-0.5">pizza.ddev.site</div>
                        <div id="prev-og-title" class="text-white text-sm font-bold truncate">
                            <?= val('meta_title') ?: 'Піцерія Travel — Хмельницький' ?>
                        </div>
                        <div id="prev-og-desc" class="text-gray-400 text-xs truncate">
                            <?= val('meta_desc') ?: 'Великий вибір піц, м\'ясних, рибних та овочевих страв.' ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-700">

            <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-400"></i> Тексти сайту
            </h3>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Опис закладу (About секція)</label>
                <textarea name="business_desc" rows="3"
                          placeholder="Піцерія Travel — це затишне місце..."
                          class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?= val('business_desc') ?></textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Текст акцій</label>
                    <textarea name="promo_text" rows="3"
                              placeholder="2 мега-піци 60 см — третя 30 см в подарунок..."
                              class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?= val('promo_text') ?></textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Заголовок форми бронювання</label>
                        <input type="text" name="form_title" value="<?= val('form_title') ?>"
                               placeholder="Зарезервуйте стіл"
                               class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Текст футера</label>
                        <input type="text" name="footer_text" value="<?= val('footer_text') ?>"
                               placeholder="© 2025 Піцерія Travel"
                               class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <button type="submit" name="save_content" class="bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold px-6 py-2.5 rounded-lg transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Зберегти контент
            </button>
        </form>
    </div>

    <!-- GALLERY: PIZZA -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mb-6">
        <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-5 flex items-center gap-2">
            <span class="text-xl">🍕</span> Галерея — Піца
            <span class="text-gray-600 font-normal text-sm ml-auto"><?= count($pizza_photos) ?> фото</span>
        </h3>

        <!-- Upload -->
        <form method="POST" enctype="multipart/form-data" class="mb-6">
            <div class="drop-zone" id="drop-pizza" onclick="document.getElementById('inp-pizza').click()">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-500 mb-2 block"></i>
                <p class="text-gray-400 text-sm mb-1">Перетягніть фото або <span class="text-amber-400 font-bold">натисніть для вибору</span></p>
                <p class="text-gray-600 text-xs">JPG, PNG, WebP · кілька файлів одночасно</p>
                <input id="inp-pizza" type="file" name="pizza_images[]" multiple accept="image/*" class="hidden">
                <div id="pizza-preview" class="mt-4 flex flex-wrap gap-2 justify-center"></div>
            </div>
            <button type="submit" name="upload_pizza" id="btn-pizza" class="mt-4 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold px-6 py-2.5 rounded-lg transition hidden">
                <i class="fa-solid fa-upload mr-2"></i>Завантажити
            </button>
        </form>

        <!-- Existing photos -->
        <?php if (!empty($pizza_photos)): ?>
        <div class="thumb-grid">
            <?php foreach ($pizza_photos as $path):
                $fname = basename($path);
            ?>
            <div class="thumb-item">
                <img src="images/pizza-photo/<?= htmlspecialchars($fname) ?>" alt="" loading="lazy">
                <form method="POST" class="del-btn">
                    <input type="hidden" name="delete_pizza_photo" value="<?= htmlspecialchars($fname) ?>">
                    <button type="submit"
                        class="bg-red-700 hover:bg-red-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs shadow-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-600 text-sm italic">Фото ще не додано.</p>
        <?php endif; ?>
    </div>

    <!-- GALLERY: OTHER -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
        <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-5 flex items-center gap-2">
            <span class="text-xl">🍽️</span> Галерея — Інші страви
            <span class="text-gray-600 font-normal text-sm ml-auto"><?= count($other_photos) ?> фото</span>
        </h3>

        <!-- Upload -->
        <form method="POST" enctype="multipart/form-data" class="mb-6">
            <div class="drop-zone" id="drop-other" onclick="document.getElementById('inp-other').click()">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-500 mb-2 block"></i>
                <p class="text-gray-400 text-sm mb-1">Перетягніть фото або <span class="text-amber-400 font-bold">натисніть для вибору</span></p>
                <p class="text-gray-600 text-xs">JPG, PNG, WebP · кілька файлів одночасно</p>
                <input id="inp-other" type="file" name="other_images[]" multiple accept="image/*" class="hidden">
                <div id="other-preview" class="mt-4 flex flex-wrap gap-2 justify-center"></div>
            </div>
            <button type="submit" name="upload_other" id="btn-other" class="mt-4 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold px-6 py-2.5 rounded-lg transition hidden">
                <i class="fa-solid fa-upload mr-2"></i>Завантажити
            </button>
        </form>

        <!-- Existing photos -->
        <?php if (!empty($other_photos)): ?>
        <div class="thumb-grid">
            <?php foreach ($other_photos as $path):
                $fname = basename($path);
            ?>
            <div class="thumb-item">
                <img src="images/meals-photo/<?= htmlspecialchars($fname) ?>" alt="" loading="lazy">
                <form method="POST" class="del-btn">
                    <input type="hidden" name="delete_other_photo" value="<?= htmlspecialchars($fname) ?>">
                    <button type="submit"
                        class="bg-red-700 hover:bg-red-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs shadow-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-600 text-sm italic">Фото ще не додано.</p>
        <?php endif; ?>
    </div>

    <?php endif; ?>


    <!-- ═══════════════ TAB: SETTINGS ═══════════════ -->
    <?php if ($tab === 'settings'): ?>

    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
        <form method="POST" class="space-y-6 max-w-2xl">

            <div>
                <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-store text-amber-400"></i> Контактна інформація
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Назва закладу</label>
                        <input type="text" name="business_name" value="<?= val('business_name') ?>"
                               placeholder="Піцерія Travel"
                               class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Телефон</label>
                            <input type="text" name="phone" value="<?= val('phone') ?>"
                                   placeholder="096 890 40 55"
                                   class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Адреса</label>
                            <input type="text" name="address" value="<?= val('address') ?>"
                                   placeholder="вул. Шевченка 89"
                                   class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-brands fa-telegram text-blue-400"></i> Telegram сповіщення
                </h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bot Token</label>
                        <input type="text" name="telegram_token" value="<?= val('telegram_token') ?>"
                               placeholder="123456789:AAH..."
                               class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Chat ID</label>
                        <input type="text" name="telegram_chat_id" value="<?= val('telegram_chat_id') ?>"
                               placeholder="-100123456789"
                               class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-brands fa-google text-yellow-400"></i> Google Analytics
                </h3>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Measurement ID</label>
                    <input type="text" name="analytics_id" value="<?= val('analytics_id') ?>"
                           placeholder="G-XXXXXXXXXX"
                           class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-yellow-500 font-mono">
                </div>
            </div>

            <div>
                <h3 class="font-bold text-gray-300 border-b border-gray-700 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-red-400"></i> Безпека
                </h3>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Новий пароль адміна <span class="text-gray-600 font-normal normal-case">(залиште порожнім, щоб не змінювати)</span></label>
                    <input type="password" name="admin_password" placeholder="Новий пароль"
                           class="w-full p-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <button type="submit" name="save_settings" class="bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold px-6 py-2.5 rounded-lg transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Зберегти налаштування
            </button>
        </form>
    </div>

    <?php endif; ?>

</div><!-- /max-w -->

<script>
// ── SEO live preview ─────────────────────────────────────────
(function() {
    const title    = document.getElementById('f-meta-title');
    const desc     = document.getElementById('f-meta-desc');
    const ogInput  = document.getElementById('f-og-image');
    const titlePrv = document.getElementById('prev-google-title');
    const descPrv  = document.getElementById('prev-google-desc');
    const ogImg    = document.getElementById('prev-og-img');
    const ogPh     = document.getElementById('prev-og-placeholder');
    const ogTitle  = document.getElementById('prev-og-title');
    const ogDesc   = document.getElementById('prev-og-desc');
    const titleCnt = document.getElementById('title-count');
    const descCnt  = document.getElementById('desc-count');
    if (!title) return;

    function update() {
        const tv = title.value || title.placeholder;
        const dv = desc.value  || desc.placeholder;
        if (titlePrv) titlePrv.textContent = tv;
        if (descPrv)  descPrv.textContent  = dv;
        if (ogTitle)  ogTitle.textContent  = tv;
        if (ogDesc)   ogDesc.textContent   = dv;
        if (titleCnt) titleCnt.textContent = title.value.length + '/70';
        if (descCnt)  descCnt.textContent  = desc.value.length  + '/160';
        if (titleCnt) titleCnt.className   = title.value.length > 60 ? 'text-amber-400' : 'text-gray-600';
        if (descCnt)  descCnt.className    = desc.value.length  > 150 ? 'text-amber-400' : 'text-gray-600';
    }

    function updateOgImg() {
        const url = ogInput.value.trim();
        if (url && ogImg) {
            ogImg.src = url;
            ogImg.classList.remove('hidden');
            if (ogPh) ogPh.classList.add('hidden');
        } else if (ogImg) {
            ogImg.classList.add('hidden');
            if (ogPh) ogPh.classList.remove('hidden');
        }
    }

    title.addEventListener('input', update);
    desc.addEventListener('input', update);
    if (ogInput) ogInput.addEventListener('input', updateOgImg);
    update();
    updateOgImg();
})();

// ── File upload preview ──────────────────────────────────────
function initUpload(inputId, previewId, btnId, dropId) {
    const inp  = document.getElementById(inputId);
    const prv  = document.getElementById(previewId);
    const btn  = document.getElementById(btnId);
    const drop = document.getElementById(dropId);
    if (!inp) return;

    function showPreviews(files) {
        prv.innerHTML = '';
        if (!files.length) { btn.classList.add('hidden'); return; }
        btn.classList.remove('hidden');
        Array.from(files).slice(0, 12).forEach(f => {
            const img = document.createElement('img');
            img.style.cssText = 'width:72px;height:96px;object-fit:cover;border-radius:8px;border:2px solid #f59e0b';
            const reader = new FileReader();
            reader.onload = e => img.src = e.target.result;
            reader.readAsDataURL(f);
            prv.appendChild(img);
        });
        if (files.length > 12) {
            const more = document.createElement('div');
            more.textContent = '+' + (files.length - 12);
            more.style.cssText = 'width:72px;height:96px;border-radius:8px;background:#374151;display:flex;align-items:center;justify-content:center;font-weight:700;color:#f59e0b;font-size:18px;';
            prv.appendChild(more);
        }
    }

    inp.addEventListener('change', () => showPreviews(inp.files));

    drop.addEventListener('dragover',  e => { e.preventDefault(); drop.classList.add('dragover'); });
    drop.addEventListener('dragleave', ()  => drop.classList.remove('dragover'));
    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.classList.remove('dragover');
        const dt = new DataTransfer();
        Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')).forEach(f => dt.items.add(f));
        inp.files = dt.files;
        showPreviews(inp.files);
    });
}

initUpload('inp-pizza', 'pizza-preview', 'btn-pizza', 'drop-pizza');
initUpload('inp-other', 'other-preview', 'btn-other', 'drop-other');

// ── AJAX photo delete ────────────────────────────────────────
document.querySelectorAll('.del-btn').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const thumb = form.closest('.thumb-item');
        thumb.style.transition = 'opacity .25s, transform .25s';
        thumb.style.opacity = '0';
        thumb.style.transform = 'scale(.85)';
        const body = new FormData(form);
        await fetch('admin.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body
        });
        thumb.remove();
    });
});
</script>
</body>
</html>
