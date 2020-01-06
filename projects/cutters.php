<?php

# Библиотека моих фрез (с привязкой к материалу)
$cutters = [];

# Набор фрез с режимами по дюралю
$cutters['d16t'] = [];

# Трёхпёрая 6мм фреза
$cutters['d16t']['end6'] = new Cutter([
	'diameter'  => 6,
	'passDepth' => 0.1,
	'stepover'  => 0.8,
	'feed'      => 1400,
	'name'      => '6mm endmill 3-fluite alluminium',
	'tool'      => 1,
]);

# Гравёр 60°
$cutters['d16t']['engraver60'] = new Cutter([
	'diameter'  => 3.175,
	'passDepth' => 0.1,
	'stepover'  => 0.1 / 3.175,		# 0.1 mm
	'feed'      => 1400,
	'name'      => '1/8" Engraver 60°',
	'tool'      => 2,
]);
