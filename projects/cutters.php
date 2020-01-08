<?php

# Библиотека моих фрез (с привязкой к материалу)
$cutters = [];

############# Набор фрез с режимами по дюралю
$cutters['d16t'] = [];

# Трёхпёрая 6мм фреза
$cutters['d16t']['end6'] = new Cutter([
	'diameter'  => 6,
	'passDepth' => 0.1,
	'stepover'  => 0.8,
	'feed'      => 1400,
	'sideDepth' => 2,
	'sideStep'  => 0.1,
	'sideForward' => true,
	'name'      => '6mm endmill 3-fluite alluminium',
	'tool'      => 1,
]);

# Гравёр 60°
$cutters['d16t']['engraver60'] = new Cutter([
	'diameter'  => 3.175,
	'passDepth' => 0.1,
	'stepover'  => 0.1 / 3.175,		# 0.1 mm
	'feed'      => 1400,
	'name'      => '1/8" Engraver 60 degree',
	'tool'      => 2,
]);
############# / d16t



################## Набор фрез с режимами по мягкому дереву
$cutters['wood'] = [];

# Кукуруза 6мм
$cutters['wood']['end6'] = new Cutter([
	'diameter'  => 6,
	'passDepth' => 3,
	'stepover'  => 0.8,
	'feed'      => 1400,
	'sideDepth' => 6,
	'sideStep'  => 2,
	'sideForward' => true,
	'name'      => '6mm corn endmill wood',
	'tool'      => 1,
]);
