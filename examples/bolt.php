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

# Болт общей длиной 21.
# Голова шестигранником на ключ 31 (именно 31, не ~30.8 под реальный ключ) длиной 6. Фаски
# Длиной резьбы 13.4 (c щелью в 1.6мм на полугравёр = 15).

echo $lathe->cutRight(21, 36);			# отрезать справа всё, что L>21

# Цилиндр под шестигранник 31 с фасками. Но он вызовет cutRight для оптимизации
echo $lathe->cylinder(0, 6, 36, 31 * Lathe4d::$HEXAGON_CYLINDER_SOFT);
echo $lathe->hexagon(0, 6, 31 * Lathe4d::$HEXAGON_CYLINDER_SOFT, 31);

# Резьба
echo $lathe->cylinder(6, 21, 36, 29.9);	# под резьбу М30, Y[6..21]

# режем M30x1.5 резьбы гравёром. Неторопясь
$engraver = new cutter([
	'passDepth' => 0.1,
	'feed'      => 1400,
	'name'      => '3.175mm engraver 60*',
	'tool'      => 2,
]);

echo $lathe->setCutter($engraver);

# Резьба M30х1.5, Y[6+1.6 - 21]
# Используется 3.175мм гравёр. Отступ от головы болта радиусом гравёра плюс чуть-чуть = 1.6
echo $lathe->thread(6 + 1.6, 21, 'M30x1.5');

# Отрез слева - срезаем деталь
# !! Помни, что фреза дойдёт до -6! Опасность столкновения c токарным патроном
echo $lathe->setCutter($cutter);
echo $lathe->cutLeft(0, 36);

echo $lathe->end();
