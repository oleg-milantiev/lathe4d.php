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
# Длиной резьбы 13.4 (1.6мм на полугравёр = 15).
# Голова шестигранником на ключ 31 длиной 6. Фаски

echo $lathe->cutRight(20, 36);			# отрезать справа всё, что L>20
echo $lathe->cutLeft(0, 36, 28);	# уменьшаем до d28 слева. Для нарезки резьбы с начала
												# помни, что фреза дойдёт до -6! Опасность столкновения

# Цилиндр под шестигранник 31 с фасками. Но он вызовет
echo $lathe->cylinder(15, 21, 36, 31 * Lathe4d::$HEXAGON_CYLINDER_SOFT);
echo $lathe->hexagon(15, 21, 31 * Lathe4d::$HEXAGON_CYLINDER_SOFT, 31);

# Резьба
echo $lathe->cylinder(0, 15, 36, 29.9);	# под резьбу М30, Y[0..15]

# режем M30x1.5 резьбы гравёром. Неторопясь
$engraver = new cutter([
	'passDepth' => 0.1,
	'feed'      => 1400,
	'name'      => '3.175mm engraver 60*',
	'tool'      => 2,
]);

echo $lathe->setCutter($engraver);

echo $lathe->thread(0, 15 - 1.6, 'M30x1.5');	# Резьба M30х1.5, Y[0..15-1.6]

echo $lathe->end();
