<?php

// Подтокариваю посадку под подшипник

include "../../lathe4d.php";
include "../cutters.php";

$lathe = new Lathe4d(Lathe4d::$VERBOSE_DEBUG);
$lathe->setFile('5D-secondary-axis-2-bearing.nc');

$lathe->setBlank(new Blank(29));
$lathe->setSafe(10);

$lathe->start();

$lathe->setCutter($cutters['d16t']['end6']);

$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 15.2,
	'dEnd'   => 15.1,
]);

$lathe->pause();

$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 15.1,
	'dEnd'   => 15.0,
]);

$lathe->pause();

$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 15.0,
	'dEnd'   => 14.95,
]);

$lathe->pause();

$lathe->cylinder([
	'yBegin' => 30,
	'yEnd'   => 48,
	'dBegin' => 14.95,
	'dEnd'   => 14.9,
]);

$lathe->end();
