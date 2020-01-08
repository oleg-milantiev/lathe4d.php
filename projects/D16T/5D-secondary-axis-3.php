<?php

include "../../lathe4d.php";
include "../cutters.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);
$lathe->setFile('5D-secondary-axis-3.nc');

$lathe->setBlank(new Blank(29));
$lathe->setSafe(10);

$lathe->start();

$lathe->setCutter($cutters['d16t']['end6']);

#!!!!!!!!!! Начало в 5D-secondary-axis-1 !!!!!!!!

# Подшипник шириной 11мм. Под ним ~d15.2. А дальше цилиндр под резьбу d14.9
$lathe->cylinder([
	'yBegin' => 40,
	'yEnd'   => 48,
	'dBegin' => 15.2,
	'dEnd'   => 14.9,
]);
###### / Справа от болта



##### Слева от болта - резьба с канавкой
# Цилиндр под резьбу
# Оптимизация медленной поворотки предварительным резом восьмигранника в sideMill
$lathe->octagon([
	'yBegin' => -6,
	'yEnd'   => 18,
	'dEnd'   => 15.2,
	'sideMill' => true,
]);
$lathe->cylinder([
	'yBegin' => -6,
	'yEnd'   => 18,
	'dBegin' => 15.2 * Lathe4d::$OCTAGON_SOFT,
	'dEnd'   => 14.9,
	// @todo параметр (через восьмигранник)
]);
# заглубление для завершения резьбы (надо было б фрезой поуже, но и так норм)
$lathe->cutLeft([
	'y'         => 18,
	'dBegin'    => 14.9,
	'dEnd'      => 13.6,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);
####### / Слева




# Резьба
$lathe->setCutter($cutters['d16t']['engraver60']);
# ... слева короткая (т.к. часть цилиндра под подшипником - там резьба не нужна)
$lathe->thread(40, 48, 'M15x0.75');
# ... справа длинная
$lathe->thread(0, 12, 'M15x0.75');


# Отрез детали
$lathe->setCutter($cutters['d16t']['end6']);
$lathe->cutLeft([
	'y'         => 0,
	'dBegin'    => 14.9,
	'dEnd'      => 0,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

$lathe->end();
