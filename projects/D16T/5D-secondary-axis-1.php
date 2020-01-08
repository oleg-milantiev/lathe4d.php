<?php

include "../../lathe4d.php";
include "../cutters.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);
$lathe->setFile('5D-secondary-axis-1.nc');

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
$lathe->cylinder([
	'yBegin' => 18,
	'yEnd'   => 30,
	'dBegin' => 19.8 * Lathe4d::$HEXAGON_SHARP,
	'dEnd'   => 19.8 * Lathe4d::$HEXAGON_SOFT,		# Цилиндр чуть захватит края HEX. Будет фаска
]);
#### / Шестигранный болт посредине



###### Справа от болта - резьба с посадкой под подшипник
# Оптимизация медленной поворотки предварительным резом восьмигранника в sideMill
$lathe->octagon([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dEnd'   => 15.5,
	'sideMill' => true,
]);
$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 15.5 * Lathe4d::$OCTAGON_SOFT,
	'dEnd'   => 15.2,
]);

# !! Подбор посадки подшипника, см. 5D-secondary-axis-2-bearing.php
# !! Окончание в 5D-secondary-axis-3.php

$lathe->end();
