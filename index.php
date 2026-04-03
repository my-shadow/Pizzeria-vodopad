<?php
$data = [];
$settings = [];
if (file_exists('data.json')) {
    $data = json_decode(file_get_contents('data.json'), true) ?: [];
    $settings = $data['settings'] ?? [];
}
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$business_name = e($settings['business_name'] ?? '') ?: 'Піцерія Travel';
$phone         = e($settings['phone']         ?? '') ?: '096 890 40 55';
$address       = e($settings['address']       ?? '') ?: 'вул. Шевченка 89';
$meta_title    = e($settings['meta_title']    ?? '') ?: 'Піцерія Travel — Хмельницький';
$meta_desc     = e($settings['meta_desc']     ?? '') ?: 'Піцерія Travel у центрі Хмельницького. Великий вибір піц, м\'ясних, рибних та овочевих страв. Доставка та самовивіз.';

// Landing photos from images/ (about + gallery sections)
$landing_photos = array_values(array_map('basename', array_filter(
    glob(__DIR__ . '/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [],
    fn($f) => is_file($f)
)));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $meta_title ?></title>
<meta name="description" content="<?= $meta_desc ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Play:wght@400;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://unpkg.com/imask"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                play:     ['Play', 'sans-serif'],
                playfair: ['"Playfair Display"', 'serif'],
            }
        }
    }
}
</script>
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-stone-950 text-stone-200">

<!-- ═══════════════════════════════ NAV ═══════════════════════════════ -->
<nav id="nav" class="fixed top-0 left-0 right-0 z-50 bg-transparent">
    <div class="max-w-7xl mx-auto px-5 flex items-center justify-between h-16 lg:h-18">
        <!-- Logo -->
        <a href="#hero" class="font-playfair text-xl font-bold text-white tracking-wide">
            <span class="text-amber-400">Travel</span><span class="text-stone-400 font-normal text-base ml-1">піцерія</span>
        </a>

        <!-- Desktop nav -->
        <div class="hidden lg:flex items-center gap-8 text-stone-300 text-[16px]">
            <a href="#menu"        class="hover:text-amber-400 transition-colors">Меню</a>
            <a href="#promos"      class="hover:text-amber-400 transition-colors">Акції</a>
            <a href="#gallery"     class="hover:text-amber-400 transition-colors">Галерея</a>
            <a href="#reservation" class="hover:text-amber-400 transition-colors">Резервація</a>
            <a href="#contacts"    class="hover:text-amber-400 transition-colors">Контакти</a>
        </div>

        <!-- Phone CTA -->
        <a href="tel:+380968904055" class="hidden lg:flex items-center gap-2 btn-cta px-5 py-2.5 text-[16px]">
            <i class="fa-solid fa-phone text-sm"></i> <?= $phone ?>
        </a>

        <!-- Burger -->
        <button id="burger" class="lg:hidden text-white p-2" aria-label="Меню">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="lg:hidden bg-stone-950/98 backdrop-blur-xl border-t border-white/5">
        <div class="max-w-7xl mx-auto px-5 py-4 flex flex-col gap-1">
            <a href="#menu"        class="nav-link py-3 text-stone-300 hover:text-amber-400 transition-colors text-[16px] border-b border-white/5">Меню</a>
            <a href="#promos"      class="nav-link py-3 text-stone-300 hover:text-amber-400 transition-colors text-[16px] border-b border-white/5">Акції</a>
            <a href="#gallery"     class="nav-link py-3 text-stone-300 hover:text-amber-400 transition-colors text-[16px] border-b border-white/5">Галерея</a>
            <a href="#reservation" class="nav-link py-3 text-stone-300 hover:text-amber-400 transition-colors text-[16px] border-b border-white/5">Резервація</a>
            <a href="#contacts"    class="nav-link py-3 text-stone-300 hover:text-amber-400 transition-colors text-[16px]">Контакти</a>
            <a href="tel:+380968904055" class="mt-3 btn-cta text-center py-3 text-[16px] block">
                <i class="fa-solid fa-phone mr-2"></i><?= $phone ?>
            </a>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
<?php $hero_bg = 'images/photo_2_2026-03-28_15-43-15.jpg'; ?>
<section id="hero" class="relative min-h-screen flex items-center" style="background-image:url('<?= $hero_bg ?>');background-size:cover;background-position:top;">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative max-w-7xl mx-auto px-5 pt-24 pb-16 w-full">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-amber-400/10 border border-amber-400/25 rounded-full px-4 py-2 mb-6">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span class="text-amber-400 text-[16px]">з/д вокзал · вул. Шевченка 89</span>
            </div>

            <h1 class="font-playfair text-5xl sm:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6">
                Піцерія<br>
                <em class="text-amber-400 not-italic">Travel</em>
            </h1>

            <p class="text-stone-300 text-xl leading-relaxed mb-4 max-w-lg">
                Справжня піца, м'ясні, рибні та овочеві страви — все для чудового відпочинку в центрі міста.
            </p>
            <p class="text-stone-400 text-[16px] mb-10">
                <i class="fa-solid fa-clock text-amber-400 mr-2"></i>Щодня 9:00–24:00
                <span class="mx-3 text-stone-700">·</span>
                <i class="fa-solid fa-music text-amber-400 mr-2"></i>Музика та DJ щодня
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="#menu" class="btn-cta px-8 py-4 text-[16px] inline-block">
                    Переглянути меню
                </a>
                <a href="#reservation" class="btn-outline px-8 py-4 text-[16px] inline-block">
                    Зарезервувати стіл
                </a>
            </div>

        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-stone-500 text-[16px] flex flex-col items-center gap-2 animate-bounce">
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</section>

<!-- ═══════════════════════════════ STATS ═══════════════════════════════ -->
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, #111009 0%, #1a1208 50%, #110c09 100%);">
    <!-- Decorative glow -->
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 70% 60% at 50% 50%, rgba(245,158,11,.07) 0%, transparent 70%);"></div>

    <div class="relative max-w-5xl mx-auto px-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-white/6 rounded-3xl overflow-hidden border border-white/8">

            <div class="stat-item anim anim-up bg-stone-950/80 px-8 py-10 text-center flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/12 border border-amber-400/20 flex items-center justify-center text-amber-400 text-xl mb-1">
                    <i class="fa-solid fa-pizza-slice"></i>
                </div>
                <div class="font-playfair text-5xl lg:text-6xl font-bold text-white leading-none"><span class="count-up" data-to="15">0</span><span class="text-amber-400">+</span></div>
                <div class="text-stone-400 text-[16px] leading-snug">Видів<br>піци</div>
            </div>

            <div class="stat-item anim anim-up bg-stone-950/80 px-8 py-10 text-center flex flex-col items-center gap-3" style="transition-delay:80ms">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/12 border border-amber-400/20 flex items-center justify-center text-amber-400 text-xl mb-1">
                    <i class="fa-solid fa-ruler-horizontal"></i>
                </div>
                <div class="font-playfair text-5xl lg:text-6xl font-bold text-white leading-none"><span class="text-amber-400 count-up" data-to="60">0</span></div>
                <div class="text-stone-400 text-[16px] leading-snug">Мега-піца<br>в сантиметрах</div>
            </div>

            <div class="stat-item anim anim-up bg-stone-950/80 px-8 py-10 text-center flex flex-col items-center gap-3" style="transition-delay:160ms">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/12 border border-amber-400/20 flex items-center justify-center text-amber-400 text-xl mb-1">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="font-playfair text-5xl lg:text-6xl font-bold text-white leading-none"><span class="count-up" data-to="9" data-from="1">1</span><span class="text-amber-400">–</span><span class="count-up" data-to="24" data-from="10">10</span></div>
                <div class="text-stone-400 text-[16px] leading-snug">Щодня<br>без вихідних</div>
            </div>

            <div class="stat-item anim anim-up bg-stone-950/80 px-8 py-10 text-center flex flex-col items-center gap-3" style="transition-delay:240ms">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/12 border border-amber-400/20 flex items-center justify-center text-amber-400 text-xl mb-1">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="font-playfair text-5xl lg:text-6xl font-bold text-white leading-none"><span class="count-up" data-to="15">0</span><span class="text-amber-400">р.</span></div>
                <div class="text-stone-400 text-[16px] leading-snug">Досвіду<br>та традицій</div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════ ABOUT ═══════════════════════════════ -->
<section class="py-20 lg:py-28 bg-stone-900/40">
    <div class="max-w-7xl mx-auto px-5">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <div class="anim anim-left">
                <span class="accent-bar mb-5 block"></span>
                <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-6">
                    Місце, де смак<br>
                    <em class="text-amber-400">зустрічає традиції</em>
                </h2>
                <p class="text-stone-300 text-[17px] leading-relaxed mb-6">
                    Піцерія Travel — це затишне місце в самому серці Хмельницького, де кожен гість почуває себе бажаним. Ми готуємо з любов'ю: класичні піци на тонкому тісті, ситні м'ясні страви та свіжі рибні делікатеси.
                </p>
                <p class="text-stone-400 text-[16px] leading-relaxed mb-8">
                    Ідеально для корпоративів, днів народження та будь-яких свят. Музика та DJ щодня створюють неповторну атмосферу.
                </p>
                <div class="flex flex-wrap gap-3">
                    <span class="flex items-center gap-2 text-[16px] text-stone-300 bg-stone-800/60 px-4 py-2.5 rounded-full border border-white/6">
                        <i class="fa-solid fa-motorcycle text-amber-400"></i> Доставка
                    </span>
                    <span class="flex items-center gap-2 text-[16px] text-stone-300 bg-stone-800/60 px-4 py-2.5 rounded-full border border-white/6">
                        <i class="fa-solid fa-bag-shopping text-amber-400"></i> Самовивіз
                    </span>
                    <span class="flex items-center gap-2 text-[16px] text-stone-300 bg-stone-800/60 px-4 py-2.5 rounded-full border border-white/6">
                        <i class="fa-solid fa-champagne-glasses text-amber-400"></i> Заходи
                    </span>
                    <span class="flex items-center gap-2 text-[16px] text-stone-300 bg-stone-800/60 px-4 py-2.5 rounded-full border border-white/6">
                        <i class="fa-solid fa-music text-amber-400"></i> DJ щодня
                    </span>
                </div>
            </div>

            <div class="anim anim-right">
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $about_ratios = ['aspect-square', 'style="aspect-ratio:1/1.4;"', 'style="aspect-ratio:1/1.4;"', 'aspect-square'];
                    for ($ai = 0; $ai < 4; $ai++):
                        $af = $landing_photos[$ai] ?? ($landing_photos[0] ?? '');
                        $ar = $about_ratios[$ai];
                        $cls = strpos($ar, 'aspect-square') !== false ? 'aspect-square' : '';
                    ?>
                    <div class="gallery-item <?= $cls ?> rounded-2xl" <?= $cls ? '' : $ar ?>>
                        <?php if ($af): ?><img src="images/<?= e($af) ?>" alt="Піцерія Travel" loading="lazy"><?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════ MENU: PIZZA ═══════════════════════════════ -->
<section id="menu" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-5">

        <!-- Section header -->
        <div class="text-center mb-14 anim anim-up">
            <span class="accent-bar mx-auto mb-5 block"></span>
            <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-3">Наше меню</h2>
            <p class="text-stone-400 text-[17px]">Всі піци доступні у трьох розмірах: <span class="text-amber-400 font-bold">30 см</span> · <span class="text-amber-400 font-bold">45 см</span> · <span class="text-amber-400 font-bold">60 см мега</span></p>
        </div>

        <!-- Pizza section label -->
        <div class="flex items-center gap-4 mb-8 anim anim-up">
            <div class="w-10 h-10 rounded-full bg-amber-400/15 border border-amber-400/30 flex items-center justify-center text-xl">🍕</div>
            <h3 class="font-playfair text-2xl lg:text-3xl font-bold text-white">Піца</h3>
            <div class="flex-1 h-px bg-gradient-to-r from-amber-400/20 to-transparent"></div>
        </div>

        <?php
        $hidden_pizza = $settings['hidden_pizza_photos'] ?? [];
        $pizza_photos = array_values(array_map('basename', array_filter(
            glob(__DIR__ . '/images/pizza-photo/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [],
            fn($f) => is_file($f) && !in_array(basename($f), $hidden_pizza)
        )));
        $pizza_photos_json = json_encode(array_map(fn($f) => 'images/pizza-photo/' . $f, $pizza_photos));
        ?>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 gap-3 anim anim-up">
        <?php foreach ($pizza_photos as $i => $photo): ?>
            <div class="pizza-gallery-item cursor-pointer" data-gallery="pizza" style="overflow:hidden; border-radius:14px;" data-index="<?= $i ?>">
                <img src="images/pizza-photo/<?= e($photo) ?>" alt="Піца" loading="lazy" style="width:100%;height:auto;object-fit:cover;display:block;transition:transform .4s ease;">
            </div>
        <?php endforeach; ?>
        </div>

        <script>var PIZZA_PHOTOS = <?= $pizza_photos_json ?>;</script>
    </div>
</section>

<!-- ═══════════════════════════════ MENU: OTHER ═══════════════════════════════ -->
<section class="pb-20 lg:pb-28">
    <div class="max-w-7xl mx-auto px-5">

        <!-- Label -->
        <div class="flex items-center gap-4 mb-8 anim anim-up">
            <div class="w-10 h-10 rounded-full bg-amber-400/15 border border-amber-400/30 flex items-center justify-center text-xl">🍽️</div>
            <h3 class="font-playfair text-2xl lg:text-3xl font-bold text-white">Інші страви</h3>
            <div class="flex-1 h-px bg-gradient-to-r from-amber-400/20 to-transparent"></div>
        </div>

        <?php
        $hidden_other = $settings['hidden_other_photos'] ?? [];
        $other_photos = array_values(array_map('basename', array_filter(
            glob(__DIR__ . '/images/meals-photo/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [],
            fn($f) => is_file($f) && !in_array(basename($f), $hidden_other)
        )));
        $other_photos_json = json_encode(array_map(fn($f) => 'images/meals-photo/' . $f, $other_photos));
        ?>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 gap-3 anim anim-up">
        <?php foreach ($other_photos as $i => $photo): ?>
            <div class="pizza-gallery-item cursor-pointer" data-gallery="other" style="overflow:hidden; border-radius:14px;" data-index="<?= $i ?>">
                <img src="images/meals-photo/<?= e($photo) ?>" alt="Страва" loading="lazy" style="width:100%;height:auto;object-fit:cover;display:block;transition:transform .4s ease;">
            </div>
        <?php endforeach; ?>
        </div>

        <script>var OTHER_PHOTOS = <?= $other_photos_json ?>;</script>
    </div>
</section>

<!-- ═══════════════════════════════ PROMOS ═══════════════════════════════ -->
<section id="promos" class="py-20 lg:py-28 bg-stone-900/40">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12 anim anim-up">
            <span class="accent-bar mx-auto mb-5 block"></span>
            <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-3">Наші акції</h2>
            <p class="text-stone-400 text-[17px]">Вигідні пропозиції для наших гостей</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto" style="overflow:visible; padding-top:3rem;">
            <div class="promo-card p-8 anim anim-left">
                <img src="/images/other/pizza-slice-reversed.png" class="promo-img" style="width:150px; top:-40px; right:-25px;" alt="Кусок піцци">
                <h3 class="font-playfair text-3xl md:text-4xl font-bold text-white mb-7">Мега-комбо</h3>
                <p class="text-stone-300 text-[17px] leading-relaxed mb-4">
                    Замовте <span class="text-amber-400 font-bold">2 мега-піци 60 см</span> — і отримайте <span class="text-amber-400 font-bold">третю 30 см в подарунок!</span>
                </p>
                <div class="inline-flex items-center gap-2 text-green-400 text-[16px] font-bold">
                    <i class="fa-solid fa-circle-check"></i> Без обмежень за смаком
                </div>
            </div>

            <div class="promo-card p-8 anim anim-right">
                <img src="/images/other/three-beers.png" class="promo-img" style="width:160px; top:-50px; right:-25px;" alt="3 бокали пива">
                <h3 class="font-playfair text-3xl md:text-4xl font-bold text-white mb-7">Мега-пиво</h3>
                <p class="text-stone-300 text-[17px] leading-relaxed mb-4">
                    При замовленні <span class="text-amber-400 font-bold">2 мега-піц</span> — <br><span class="text-amber-400 font-bold">3 бокали пива безкоштовно!</span>
                </p>
                <div class="inline-flex items-center gap-2 text-green-400 text-[16px] font-bold">
                    <i class="fa-solid fa-circle-check"></i> Лагер, темне або пшеничне
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════ GALLERY ═══════════════════════════════ -->
<section id="gallery" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12 anim anim-up">
            <span class="accent-bar mx-auto mb-5 block"></span>
            <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-3">Наш заклад</h2>
            <p class="text-stone-400 text-[17px]">Затишна атмосфера для відпочинку та свят</p>
        </div>

        <!-- Masonry-style grid -->
        <?php
        $gallery_heights = ['280px', '577px', '280px', '280px', '280px', '280px'];
        $gallery_spans   = [false, true, false, false, false, false];
        $gallery_slice   = array_slice($landing_photos, 0, 6);
        $gallery_photos_json = json_encode(array_map(fn($f) => 'images/' . $f, $gallery_slice));
        ?>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 anim anim-up">
            <?php foreach ($gallery_slice as $gi => $gf): ?>
            <div class="gallery-item rounded-2xl cursor-pointer <?= $gallery_spans[$gi] ? 'row-span-2' : '' ?>" style="height:<?= $gallery_heights[$gi] ?>" data-gallery="landing" data-index="<?= $gi ?>">
                <img src="images/<?= e($gf) ?>" alt="Піцерія Travel" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
        <script>var GALLERY_PHOTOS = <?= $gallery_photos_json ?>;</script>
    </div>
</section>

<!-- ═══════════════════════════════ RESERVATION ═══════════════════════════════ -->
<section id="reservation" class="py-20 lg:py-28 bg-stone-900/40">
    <div class="max-w-7xl mx-auto px-5">
        <div class="grid lg:grid-cols-2 gap-14 lg:gap-20 items-start">

            <!-- Left: info -->
            <div class="anim anim-left">
                <span class="accent-bar mb-6 block"></span>
                <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-5">
                    Зарезервуйте<br><em class="text-amber-400">стіл</em>
                </h2>
                <p class="text-stone-300 text-[17px] leading-relaxed mb-10">
                    Забронюйте місце для особливої події або просто вечері з близькими. Ми підготуємо все для вашого комфорту.
                </p>

                <!-- Contact cards -->
                <div class="space-y-4">
                    <a href="tel:+380968904055" class="flex items-center gap-4 p-5 bg-stone-900/60 border border-white/6 rounded-2xl hover:border-amber-400/30 transition-colors group">
                        <div class="w-12 h-12 rounded-xl bg-amber-400/15 border border-amber-400/25 flex items-center justify-center text-amber-400 group-hover:bg-amber-400/20 transition-colors">
                            <i class="fa-solid fa-phone text-lg"></i>
                        </div>
                        <div>
                            <div class="text-stone-400 text-[15px]">Телефон</div>
                            <div class="text-white font-bold text-[18px]"><?= $phone ?></div>
                        </div>
                    </a>
                    <div class="flex items-center gap-4 p-5 bg-stone-900/60 border border-white/6 rounded-2xl">
                        <div class="w-12 h-12 rounded-xl bg-amber-400/15 border border-amber-400/25 flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-location-dot text-lg"></i>
                        </div>
                        <div>
                            <div class="text-stone-400 text-[15px]">Адреса</div>
                            <div class="text-white font-bold text-[17px]"><?= $address ?>, Хмельницький</div>
                            <div class="text-stone-400 text-[15px]">Коло залізничного вокзалу</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5 bg-stone-900/60 border border-white/6 rounded-2xl">
                        <div class="w-12 h-12 rounded-xl bg-amber-400/15 border border-amber-400/25 flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                        <div>
                            <div class="text-stone-400 text-[15px]">Години роботи</div>
                            <div class="text-white font-bold text-[17px]">Щодня 9:00 — 24:00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: form -->
            <div class="anim anim-right">
                <form id="booking-form" action="submit.php" method="POST" class="bg-stone-900/60 border border-white/6 rounded-2xl p-6 lg:p-8 space-y-5">
                    <h3 class="font-playfair text-2xl font-bold text-white mb-2">Форма бронювання</h3>

                    <div>
                        <label class="block text-stone-400 text-[15px] mb-2">Ваше ім'я *</label>
                        <input type="text" name="name" required placeholder="Олександр" class="f-input">
                    </div>

                    <div>
                        <label class="block text-stone-400 text-[15px] mb-2">Номер телефону *</label>
                        <input type="tel" name="phone" id="phone" required placeholder="+38 (0__) ___ __ __" class="f-input">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-stone-400 text-[15px] mb-2">Дата *</label>
                            <input type="date" name="booking_date" required class="f-input" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="block text-stone-400 text-[15px] mb-2">Час *</label>
                            <select name="booking_time" required class="f-input">
                                <option value="">Оберіть час</option>
                                <?php
                                for ($h = 9; $h <= 22; $h++) {
                                    $t = sprintf('%02d:00', $h);
                                    echo "<option value=\"$t\">$t</option>";
                                    $t2 = sprintf('%02d:30', $h);
                                    echo "<option value=\"$t2\">$t2</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-stone-400 text-[15px] mb-2">Кількість гостей *</label>
                        <select name="persons" required class="f-input">
                            <option value="">Оберіть кількість</option>
                            <?php for ($i = 1; $i <= 20; $i++) echo "<option value=\"$i\">$i " . ($i === 1 ? 'особа' : ($i < 5 ? 'особи' : 'осіб')) . "</option>"; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-stone-400 text-[15px] mb-2">Побажання</label>
                        <textarea name="note" rows="3" placeholder="Привід, особливі побажання, алергії..." class="f-input resize-none"></textarea>
                    </div>

                    <button type="submit" class="btn-cta w-full py-4 text-[16px] rounded-xl">
                        <i class="fa-solid fa-calendar-check mr-2"></i>Підтвердити резервацію
                    </button>
                    <p class="text-stone-500 text-[14px] text-center">Ми зателефонуємо для підтвердження протягом 30 хвилин</p>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════ CONTACTS ═══════════════════════════════ -->
<section id="contacts" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12 anim anim-up">
            <span class="accent-bar mx-auto mb-5 block"></span>
            <h2 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-3">Як нас знайти</h2>
        </div>

        <!-- Map placeholder + info row -->
        <div class="grid lg:grid-cols-3 gap-6 anim anim-up">
            <!-- Map embed area (iframe) -->
            <div class="lg:col-span-2 rounded-2xl overflow-hidden" style="height:360px; background:#1c1917; border:1px solid rgba(255,255,255,.06);">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=26.96,49.41,26.99,49.43&layer=mapnik&marker=49.42,26.975"
                    style="width:100%;height:100%;border:0;filter:invert(0.9) hue-rotate(180deg) brightness(0.85) contrast(0.9);"
                    loading="lazy"
                    title="Карта — Піцерія Travel"></iframe>
            </div>

            <!-- Info -->
            <div class="space-y-4">
                <div class="bg-stone-900/60 border border-white/6 rounded-2xl p-6">
                    <div class="text-amber-400 mb-2"><i class="fa-solid fa-location-dot mr-2"></i><strong>Адреса</strong></div>
                    <p class="text-white text-[16px]"><?= $address ?>, Хмельницький</p>
                    <p class="text-stone-400 text-[15px] mt-1">Коло залізничного вокзалу</p>
                </div>
                <div class="bg-stone-900/60 border border-white/6 rounded-2xl p-6">
                    <div class="text-amber-400 mb-2"><i class="fa-solid fa-clock mr-2"></i><strong>Режим роботи</strong></div>
                    <p class="text-white text-[16px]">Щодня: 9:00 — 24:00</p>
                </div>
                <div class="bg-stone-900/60 border border-white/6 rounded-2xl p-6">
                    <div class="text-amber-400 mb-2"><i class="fa-solid fa-phone mr-2"></i><strong>Телефон</strong></div>
                    <a href="tel:+380968904055" class="text-white text-[18px] font-bold hover:text-amber-400 transition-colors"><?= $phone ?></a>
                </div>
                <a href="tel:+380968904055" class="btn-cta w-full py-4 text-[16px] block text-center rounded-xl">
                    <i class="fa-solid fa-phone mr-2"></i>Зателефонувати
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════ FOOTER ═══════════════════════════════ -->
<footer class="border-t border-white/6 py-10">
    <div class="max-w-7xl mx-auto px-5 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="font-playfair text-xl font-bold text-white mb-1">
                <span class="text-amber-400">Travel</span> піцерія
            </div>
            <div class="text-stone-500 text-[15px]"><?= $address ?>, Хмельницький</div>
        </div>
        <div class="flex flex-wrap gap-6 text-stone-500 text-[15px]">
            <a href="#menu"        class="hover:text-amber-400 transition-colors">Меню</a>
            <a href="#promos"      class="hover:text-amber-400 transition-colors">Акції</a>
            <a href="#gallery"     class="hover:text-amber-400 transition-colors">Галерея</a>
            <a href="#reservation" class="hover:text-amber-400 transition-colors">Резервація</a>
        </div>
        <div class="text-stone-500 text-[15px]">© <?= date('Y') ?> Піцерія Travel</div>
    </div>
</footer>

<!-- Toast -->
<div id="toast"></div>

<!-- Lightbox -->
<div id="lb" role="dialog" aria-modal="true">
    <button id="lb-close" aria-label="Закрити"><i class="fa-solid fa-xmark"></i></button>
    <button class="lb-arrow" id="lb-prev" aria-label="Попереднє"><i class="fa-solid fa-chevron-left"></i></button>
    <img id="lb-img" src="" alt="Піца">
    <button class="lb-arrow" id="lb-next" aria-label="Наступне"><i class="fa-solid fa-chevron-right"></i></button>
    <div id="lb-counter"></div>
</div>

<!-- ═══════════════════════════════ JS ═══════════════════════════════ -->
<script>
// ── Nav scroll ──────────────────────────────────────────────
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// ── Burger ──────────────────────────────────────────────────
const burger = document.getElementById('burger');
const mobileMenu = document.getElementById('mobile-menu');
burger.addEventListener('click', () => {
    mobileMenu.classList.toggle('open');
    burger.querySelector('i').className = mobileMenu.classList.contains('open')
        ? 'fa-solid fa-xmark text-xl'
        : 'fa-solid fa-bars text-xl';
});
document.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', () => {
    mobileMenu.classList.remove('open');
    burger.querySelector('i').className = 'fa-solid fa-bars text-xl';
}));

// ── Scroll animations ────────────────────────────────────────
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.anim').forEach(el => observer.observe(el));

// ── Stats counter ────────────────────────────────────────────
(function() {
    function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

    function animateCount(el) {
        const from     = +(el.dataset.from ?? 0);
        const to       = +el.dataset.to;
        const duration = 1600;
        const start    = performance.now();
        function tick(now) {
            const t   = Math.min((now - start) / duration, 1);
            const val = Math.round(from + (to - from) * easeOutQuart(t));
            el.textContent = val;
            if (t < 1) requestAnimationFrame(tick);
            else el.textContent = to;
        }
        requestAnimationFrame(tick);
    }

    const statsSection = document.querySelector('.grid.grid-cols-2.lg\\:grid-cols-4');
    if (!statsSection) return;

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            statsObserver.unobserve(entry.target);
            entry.target.querySelectorAll('.count-up').forEach((el, i) => {
                setTimeout(() => animateCount(el), i * 80);
            });
        });
    }, { threshold: 0.4 });

    statsObserver.observe(statsSection);
})();

// ── Phone mask ───────────────────────────────────────────────
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    IMask(phoneInput, { mask: '+38 (000) 000-00-00' });
}

// ── Booking form ─────────────────────────────────────────────
const form = document.getElementById('booking-form');
form.addEventListener('submit', function(e) {
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Надсилаємо...';
});

// ── Toast helper ─────────────────────────────────────────────
function showToast(msg, duration = 3500) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
}

// Show success/error from URL params (after form submit redirect)
const params = new URLSearchParams(location.search);
if (params.get('status') === 'ok')    { showToast('✅ Бронювання прийнято! Очікуйте дзвінка.'); history.replaceState({}, '', location.pathname); }
if (params.get('status') === 'error') { showToast('❌ Помилка. Спробуйте ще або зателефонуйте.'); history.replaceState({}, '', location.pathname); }

// ── Lightbox ─────────────────────────────────────────────────
(function() {
    const lb      = document.getElementById('lb');
    const lbImg   = document.getElementById('lb-img');
    const lbClose = document.getElementById('lb-close');
    const lbPrev  = document.getElementById('lb-prev');
    const lbNext  = document.getElementById('lb-next');
    const lbCount = document.getElementById('lb-counter');
    let photos  = [];
    let current = 0;

    function show(index) {
        current = (index + photos.length) % photos.length;
        lbImg.classList.add('fade');
        setTimeout(() => {
            lbImg.src = photos[current];
            lbImg.classList.remove('fade');
        }, 180);
        lbCount.textContent = (current + 1) + ' / ' + photos.length;
    }

    function open(gallery, index) {
        photos  = gallery === 'pizza' ? PIZZA_PHOTOS : gallery === 'landing' ? GALLERY_PHOTOS : OTHER_PHOTOS;
        current = index;
        lbImg.src = photos[current];
        lbCount.textContent = (current + 1) + ' / ' + photos.length;
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.pizza-gallery-item, .gallery-item').forEach(el => {
        el.addEventListener('click', () => open(el.dataset.gallery, +el.dataset.index));
    });

    lbClose.addEventListener('click', close);
    lbPrev.addEventListener('click',  () => show(current - 1));
    lbNext.addEventListener('click',  () => show(current + 1));

    lb.addEventListener('click', e => { if (e.target === lb) close(); });

    document.addEventListener('keydown', e => {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
    });

    // Touch swipe
    let tx = 0;
    lb.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 50) show(dx < 0 ? current + 1 : current - 1);
    });
})();
</script>
</body>
</html>
