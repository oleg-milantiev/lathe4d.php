<?php

include "lathe4d.php";

$lathe = new Lathe4d();

$lathe->setBlank(
	new Blank(36)			# заготовка D 36, L не задан (и пока не используется)
							# X,Y [0,0] в центре близ патрона. Z [0] на оси цилиндра
);
$lathe->setSafe(10);		# Безопасная высота 10мм

$cutter = new Cutter();
$cutter->setDiameter(6);	# Диаметр фрезы 6мм
$cutter->setPassDepth(3);	# Заглубление за раз 3мм
$cutter->setStepover(0.8);	# 80% шаг по Y от диаметра фрезы
$cutter->setFeed(1400);		# Подача 1400 мм/мин
$cutter->setName('6mm endmill 3fluite alluminium');		# Имя фрезы
$cutter->setTool(1);		# Номер инструмента (фрезы)

/* альтернативная запись (можно включать не все параметры)
$cutter = new Cutter([
	'diameter'  => 6,
	'passDepth' => 3,
	'stepover'  => 0.8,
	'feed'      => 1400,
	'name'      => '6mm endmill 3fluite alluminium',
	'tool'      => 1,
]);
*/

echo $lathe->start();		# Начальные g-code команды

echo $lathe->setCutter($cutter);

# точим наружний цилиндр от D36 до D17 с 0 по 48 по Y
echo $lathe->cylinder(0, 48, 36, 17);
# ещё пара L 18 цилиндров под резьбы М15 на концах
echo $lathe->cylinder(0, 18, 17, 14.9);
echo $lathe->cylinder(30, 48, 17, 14.9);

echo $lathe->cutRight(48, 36);		# отрезать справа всё, что y>48

# режем M15x1.5 резьбы гравёром. Неторопясь
$engraver = new cutter();
$engraver->setPassDepth(0.1);
$engraver->setFeed(1400);
$engraver->setName('Гравёр 60°');		# Имя фрезы
$engraver->setTool(2);					# Номер фрезы

echo $lathe->setCutter($engraver);

#echo $lathe->thread(0, 17, 1.5, 29.9, 29.9 - 1.5 * 1.3); синоним строки ниже
echo $lathe->thread(0, 17, 'M15x1.5');		# 0-17, т.к. помним о ширине гравёра. До 18 не доведёт
echo $lathe->thread(31, 48, 'M15x1.5');

# отрезаем той же 6мм фрезой
echo $lathe->setCutter($cutter);
echo $lathe->cutLeft(0, 36, 2);		# отрезать слева всё, что y<0. То есть отрезать деталь
									# режет до заданного диаметра (2мм).
									# стоит придерживать деталь бабкой или чем-то ещё.
									# не забудь учесть, что cutLeft заденет тут аж до y=-6!
									# то есть чтобы с патроном не столкнуться.


echo $lathe->end();
