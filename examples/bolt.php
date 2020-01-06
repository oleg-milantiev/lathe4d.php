<?php

/**
 * Пример нарезки болта L10 на M30x1.5
 * @todo шестигранник под ключ, как будет реализован в библиотеке
 */

include "../lathe4d.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);

$lathe->setBlank(
	new Blank(36)		# заготовка D 36, L не задан (и пока не используется)
								# X,Y [0,0] в центре близ патрона. Z [0] на оси цилиндра
);
$lathe->setSafe(10);		# Безопасная высота 10мм

$cutter = new Cutter([
	'diameter'  => 6,
	'passDepth' => 3,
	'stepover'  => 0.8,
	'feed'      => 1400,
	'name'      => '6mm endmill 3fluite alluminium',
	'tool'      => 1,
]);

echo $lathe->start();					# Начальные g-code команды

echo $lathe->setCutter($cutter);

# Болт общей длиной 40.
# Голова шестигранником на ключ 31 (именно 31, не ~30.8 под реальный ключ) длиной 10. Фаски
# Длиной резьбы 30

echo $lathe->cutRight(['y' => 40, 'dBegin' => 36]);			# отрезать справа всё, что L>25

# Цилиндр под шестигранник 31 с фасками (31 * SOFT).
echo $lathe->cylinder(0, 10, 36, 31 * Lathe4d::$HEXAGON_SOFT);
echo $lathe->hexagon(0, 10, 31 * Lathe4d::$HEXAGON_SOFT, 31);

# Резьба
echo $lathe->cylinder(10, 40, 36, 29.9);	# под резьбу М30, Y[10..30]


# По уму, мелкую резьбу надо было резать гравёром, но для теста я фрезернул крупную резьбу 6мм фрезой
/*
# режем M30x1.5 резьбы гравёром. Неторопясь
$engraver = new cutter([
	'passDepth' => 0.1,
	'feed'      => 1400,
	'name'      => '3.175mm engraver 60*',
	'tool'      => 2,
]);

echo $lathe->setCutter($engraver);

# Резьба M30х1.5, Y[10+1.6 - 30]
# Используется 3.175мм гравёр. Отступ от головы болта радиусом гравёра плюс чуть-чуть = 1.6
echo $lathe->thread(10 + 1.6, 30, 'M30x1.5');
*/

# Крупная резьба 6мм фрезой
echo $lathe->thread(10 + 3, 40 - 3, 'M30x8');

# Отрез слева - срезаем деталь
# !! Помни, что фреза дойдёт до -6! Опасность столкновения c токарным патроном
//echo $lathe->setCutter($cutter);
echo $lathe->cutLeft(['y' => 0, 'dBegin' => 36]);

echo $lathe->end();
