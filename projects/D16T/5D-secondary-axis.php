<?php

include "../../lathe4d.php";
include "../cutters.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);
$lathe->setFile('5D-secondary-axis.nc');

$lathe->setBlank(new Blank(29));
$lathe->setSafe(10);

$lathe->start();

$lathe->setCutter($cutters['d16t']['end6']);

# Обрез лишнего справа
$lathe->cutRight([
	'y'         => 48,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

#### Шестигранный болт посредине
$lathe->hexagon([
	'yBegin' => 18,
	'yEnd'   => 30,
	'dEnd'   => 19.8,
	'sideMill' => true,
	// @todo параметр "фаска". Пусть сам цилиндр точит
]);
# Фаска болта
# Уменьшу шаг по Y до 0.5 мм - чище обработка
$lathe->setCutter($cutters['d16t']['end6-clean']);

$lathe->cylinder([
	'yBegin' => 18,
	'yEnd'   => 30,
	'dBegin' => 19.8 * Lathe4d::$HEXAGON_SHARP,
	'dEnd'   => 19.8 * Lathe4d::$HEXAGON_SOFT,		# Цилиндр чуть захватит края HEX. Будет фаска
]);
# Верну шаг по Y на преждние 80% = 4.8 мм
$lathe->setCutter($cutters['d16t']['end6']);

#### / Шестигранный болт посредине



###### Справа от болта - резьба с посадкой под подшипник
# Оптимизация медленной поворотки предварительным резом восьмигранника в sideMill
$lathe->octagon([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dEnd'   => 15.5,
	'sideMill' => true,
]);
# Цилиндр под подшипник
$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 40,
	'dBegin' => 15.5 * Lathe4d::$OCTAGON_SOFT,
	'dEnd'   => 15.1,
]);
# Цилиндр под резьбу
$lathe->cylinder([
	'yBegin' => 40,
	'yEnd'   => 48,
	'dBegin' => 15.5 * Lathe4d::$OCTAGON_SOFT,
	'dEnd'   => 14.9,
]);

# Чистка цилиндра под подшипник
$lathe->setCutter($cutters['d16t']['end6-clean']);

$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 40,
	'dBegin' => 15.1,   // Возможно, понадобится несколько проходов, подбирая посадку подшипника
	'dEnd'   => 15.1,
]);

# Чистка цилиндра под резьбу
$lathe->cylinder([
	'yBegin' => 40,
	'yEnd'   => 48,
	'dBegin' => 14.9,
	'dEnd'   => 14.9,
]);

# Верну шаг по Y на преждние 80% = 4.8 мм
$lathe->setCutter($cutters['d16t']['end6']);


# Слева от болта цилиндр с резьбой. Через квадрат (оптимизация)
$lathe->square([
	'yBegin' => -6,
	'yEnd'   => 18,
	'dEnd'   => 14.9,
]);
$lathe->cutLeft([
	'y'         => 18,
	'dBegin'    => 14.9 * Lathe4d::$SQUARE_SOFT,
	'dEnd'      => 13.6,	// Углубление за резьбой
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);
$lathe->cutLeft([
	'y'         => 12,
	'dBegin'    => 14.9 * Lathe4d::$SQUARE_SOFT,
	'dEnd'      => 14.9,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);
$lathe->cutLeft([
	'y'         => 6,
	'dBegin'    => 14.9 * Lathe4d::$SQUARE_SOFT,
	'dEnd'      => 14.9,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);
$lathe->cutLeft([
	'y'         => 0,
	'dBegin'    => 14.9 * Lathe4d::$SQUARE_SOFT,
	'dEnd'      => 14.9,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

# Чистка цилиндра под левую резьбу
$lathe->setCutter($cutters['d16t']['end6-clean']);

$lathe->cylinder([
	'yBegin' => 0,
	'yEnd'   => 12,
	'dBegin' => 14.9,
	'dEnd'   => 14.9,
]);



# Резьбы
$lathe->setCutter($cutters['d16t']['engraver60']);
# ... справа короткая (т.к. часть цилиндра под подшипником - там резьба не нужна)
$lathe->thread(40, 48, 'M15x0.75');
# ... слева длинная
$lathe->thread(0, 12, 'M15x0.75');



# Отрез детали
$lathe->setCutter($cutters['d16t']['end6']);
$lathe->cutLeft([
	'y'         => 0,
	'dBegin'    => 14.9,
	'direction' => Lathe4d::$CUT_DIR_LEFT,
	'zPassMode' => Lathe4d::$CUT_ZPASS_75,
]);

$lathe->end();
