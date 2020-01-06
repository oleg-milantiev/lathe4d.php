<?php

include "../../lathe4d.php";
include "../cutters.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);

$lathe->setBlank(new Blank(29));
$lathe->setSafe(10);

echo $lathe->start();

echo $lathe->setCutter($cutters['d16t']['end6']);

# Обрез лишнего справа
echo $lathe->cutRight([
	'y'         => 48,
	'dBegin'    => 29,
	'dEnd'      => 0,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

# Болт посредине
echo $lathe->cylinder([
	'yBegin' => 18,
	'yEnd'   => 30,
	'dBegin' => 29,
	'dEnd'   => 19.8 * Lathe4d::$HEXAGON_SOFT,		# Цилиндр чуть захватит края HEX. Будет фаска
]);
echo $lathe->hexagon([
	'yBegin' => 18,
	'yEnd'   => 30,
	'yStepoverMm' => 3,								# Переназначение перекрытия, заданного фрезой
	'dBegin' => 20 * Lathe4d::$HEXAGON_SOFT,
	'dEnd'   => 19.8,
]);

### ПодРезьбы справа и слева
# Справа
echo $lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 29,
	'dEnd'   => 14.9,
]);
# заглубление для завершения резьбы (надо было б фрезой поменьше, но и так норм)
echo $lathe->cutRight([
	'y'         => 30,
	'dBegin'    => 14.9,
	'dEnd'      => 13.6,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);
# Слева на dФрезы дальше. Чтобы гравёр влез. Всё равно срежется потом
echo $lathe->cylinder([
	'yBegin' => -6,
	'yEnd'   => 18,
	'dBegin' => 29,
	'dEnd'   => 14.9,		# И под резьбу, и под посадку подшипника
							# (потренируюсь на внешнем цилиндре)
]);

# Резьба
echo $lathe->setCutter($cutters['d16t']['engraver60']);
# ... слева короткая (т.к. часть цилиндра под подшипником - там резьба не нужна)
echo $lathe->thread(0, 8, 'M15x0.75');
# ... справа длинная
echo $lathe->thread(36, 48, 'M15x0.75');


# Отрез детали
echo $lathe->setCutter($cutters['d16t']['end6']);
echo $lathe->cutLeft([
	'y'         => 0,
	'dBegin'    => 15,
	'dEnd'      => 0,
	'direction' => Lathe4d::$CUT_DIR_RIGHT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

echo $lathe->end();
