<?php

/**
 * Пример нарезки болта L10 на M30x1.5
 * @todo шестигранник под ключ, как будет реализован в библиотеке
 */

include "lathe4d.php";

$lathe = new Lathe4d();

$lathe->setBlank(
	new Blank(36)			# заготовка D 36, L не задан (и пока не используется)
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

echo $lathe->cutRight(15, 36);			# отрезать справа всё, что y>13
echo $lathe->cutLeft(5, 36, 28);		# уменьшаем до d28 слева. Для нарезки резьбы на все 5..15
echo $lathe->cylinder(5, 15, 36, 29.9);	# под резьбу М30, Y[5..15]

# режем M30x1.5 резьбы гравёром. Неторопясь
$engraver = new cutter([
	'passDepth' => 0.1,
	'feed'      => 1400,
	'name'      => '3.175mm engraver 60*',
	'tool'      => 2,
]);

echo $lathe->setCutter($engraver);

echo $lathe->thread(5, 15, 'M30x1.5');	# Резьба M30х1.5, Y[5..15]

// @todo Шестигранником голову болта

echo $lathe->end();
