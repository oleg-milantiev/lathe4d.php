<?php

/**
 * Заготовка
 */
class Blank
{

	/** @var float */
	private $diameter;

	/** @var float */
	private $length;

	public function __construct($diameter, $length = -1)
	{
		$this->diameter = $diameter;
		$this->length   = $length;
	}

	/**
	 * @return float
	 */
	public function getDiameter()
	{
		return $this->diameter;
	}

	/**
	 * @param float $diameter
	 */
	public function setDiameter($diameter)
	{
		$this->diameter = $diameter;
	}

	/**
	 * @return float
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * @param float $length
	 */
	public function setLength($length)
	{
		$this->length = $length;
	}

	public function getRadius()
	{
		return $this->diameter / 2;
	}

}


/**
 * Фреза
 */
class Cutter
{
	private $diameter;
	private $passDepth;

	/** @var float аля "iMaching". Глубина выборки сбоку, мм */
	private $sideDepth;
	/** @var float Ширина выборки сбоку, мм */
	private $sideStep;
	/** @var bool Попутное (да) или встречное (нет) боковое фрезерование */
	private $sideForward;

	private $stepover;
	private $feed;
	private $plunge; // @todo Пока не используется
	private $name;
	private $tool;

	public function __construct($params = [])
	{
		foreach (['diameter', 'passDepth', 'stepover', 'feed', 'name', 'tool', 'plunge', 'sideDepth', 'sideStep', 'sideForward'] as $field) {
			if (isset($params[$field])) {
				$this->{$field} = $params[$field];
			}
		}
	}

	public function setDiameter($diameter)
	{
		$this->diameter = $diameter;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return float
	 */
	public function getSideDepth()
	{
		return $this->sideDepth;
	}

	/**
	 * @param float $sideDepth
	 */
	public function setSideDepth($sideDepth)
	{
		$this->sideDepth = $sideDepth;
	}

	/**
	 * @return float
	 */
	public function getSideStep()
	{
		return $this->sideStep;
	}

	/**
	 * @param float $sideStep
	 */
	public function setSideStep($sideStep)
	{
		$this->sideStep = $sideStep;
	}

	/**
	 * @return bool
	 */
	public function isSideForward()
	{
		return $this->sideForward;
	}

	/**
	 * @param bool $sideForward
	 */
	public function setSideForward($sideForward)
	{
		$this->sideForward = $sideForward;
	}

	public function getDiameter()
	{
		return $this->diameter;
	}

	public function getRadius()
	{
		return $this->diameter / 2;
	}

	public function setFeed($feed)
	{
		$this->feed = $feed;
	}

	public function getFeed()
	{
		return $this->feed;
	}

	public function setPlunge($plunge)
	{
		$this->plunge = $plunge;
	}

	public function getPlunge()
	{
		return $this->plunge;
	}

	public function setTool($tool)
	{
		$this->tool = $tool;
	}

	public function getTool()
	{
		return $this->tool;
	}

	public function setStepover($stepover)
	{
		$this->stepover = $stepover;
	}

	/**
	 * Кол-во мм шага по Y с учётом %% шага и dФрезы
	 */
	public function getStepoverMm()
	{
		return $this->stepover * $this->diameter;
	}

	public function setPassDepth($passDepth)
	{
		$this->passDepth = $passDepth;
	}

	public function getPassDepth()
	{
		return $this->passDepth;
	}

}


class Lathe4d
{

	/** Файл-дескриптор, куда пишем кот */
	private $fd;

	/** @var Blank */
	private $blank;

	/** @var Cutter */
	private $cutter;
	private $safe;

	private $x = 0;
	private $y = 0;
	private $z = 0;
	private $a = 0;


	/** @var string Уровень подробности логов */
	private $verbose;

	public static $VERBOSE_QUIET = 'quiet';
	public static $VERBOSE_INFO  = 'info';
	public static $VERBOSE_DEBUG = 'debug';


	public function __construct($verbose = null)
	{
		$this->verbose = $verbose ? $verbose : self::$VERBOSE_QUIET;
		$this->fd      = fopen('php://output', 'w');
	}


	/**
	 * Смена файла записи
	 *
	 * @param $filename
	 */
	public function setFile($filename)
	{
		fclose($this->fd);
		$this->fd = fopen($filename, 'w');
	}


	private function isDebug()
	{
		return !! ($this->verbose == self::$VERBOSE_DEBUG);
	}

	private function isInfo()
	{
		return in_array($this->verbose, [self::$VERBOSE_DEBUG, self::$VERBOSE_INFO]);
	}


	public function setBlank(Blank $blank)
	{
		$this->blank = $blank;
	}

	public function getCutter()
	{
		return $this->cutter;
	}

	public function setCutter(Cutter $cutter)
	{
		if ($this->cutter) {
			# Не менять шило на мыло (фрезу с тем же именем)
			if ($this->cutter->getName() == $cutter->getName()) {
				return;
			}

			# Смена фрезы, а не первая фреза

			// @todo Поднять повыше. Шоб удобней фрезу менять было
			fputs($this->fd, "T{$cutter->getTool()}\n");
			fputs($this->fd, "M5      (Spindle stop.)\n");
			fputs($this->fd, "(MSG, Change tool to {$cutter->getName()})\n");
			fputs($this->fd, "M6      (Tool change.)\n");
			fputs($this->fd, "M0      (Temporary machine stop.)\n");
			fputs($this->fd, "M3      (Spindle on clockwise.)\n");
		}
		else {
			fputs($this->fd, "T{$cutter->getTool()}\n");
			fputs($this->fd, "( Current cutter: {$cutter->getName()} )\n");
			fputs($this->fd, "M6      (Tool change.)\n");
		}

		$this->cutter = $cutter;
	}

	public function setSafe($safe)
	{
		$this->safe = $safe;
	}

	/**
	 * Начало программы
	 */
	public function start()
	{
		if (!$this->blank) {
			die('ERROR: Blank not defined');
		}
		if (!$this->safe) {
			die('ERROR: Safe Z not defined');
		}

		fputs($this->fd, "( File created by Lathe4d.php: https://github.com/oleg-milantiev/lathe4d.php/wiki )\n");
		fputs($this->fd, "( ". date('d-m-Y H:i') ." )\n");

		# @todo пока взял шапку из aspire for mach3. Потом вынесу наружу
		fputs($this->fd, "G00G21G17G90G40G49G80\n");
		fputs($this->fd, "G71G91.1\n");
		fputs($this->fd, "S18000M3\n"); // @todo скорость шпинделя в cutter
		fputs($this->fd, "G94\n");

		$this->zToSafe();
	}


	public function pause()
	{
		$this->zToSafe();

		fputs($this->fd, "M0      (Temporary machine stop.)\n");
	}


	public static $SQUARE_SHARP = 1.4142135623730950488016887242097;	# sqrt(2) - диагональ квадрата
	public static $SQUARE_SOFT  = 1.35;

	/**
	 * Квадратная голова болта
	 * Основа болта цилиндр:
	 *	- dSize*$SQUARE_SHARP для острых граней;
	 *	- dSize*$SQUARE_SOFT - с фасками;
	 *	- можно и больше цилиндр. Схавает справа так, как надо, без остатка и без жадности.
	 *
	 * @params array Параметры: yBegin, yEnd, dBegin, End, [aStart], [sideMill]
	 */
	public function square($params)
	{
		$params['figure'] = 'Square';
		$params['aStep']  = 90;

		return $this->polygon($params);
	}


	public static $HEXAGON_SHARP = 1.155;
	public static $HEXAGON_SOFT  = 1.115;

	/**
	 * Шестигранная голова болта
	 * Основа болта цилиндр:
	 *	- dSize*$HEXAGON_SHARP для острых граней;
	 *	- dSize*$HEXAGON_SOFT - с фасками;
	 *	- можно и больше цилиндр. Схавает справа так, как надо, без остатка и без жадности.
	 *
	 * @params array Параметры: yBegin, yEnd, dBegin, End, [aStart], [sideMill]
	 */
	public function hexagon($params)
	{
		$params['figure'] = 'Hexagon';
		$params['aStep']  = 60;

		return $this->polygon($params);
	}

	public static $OCTAGON_SHARP = 1.0825;
	public static $OCTAGON_SOFT  = 1.08;

	/**
	 * Восьмигранная голова болта
	 * Основа болта цилиндр:
	 *	- dSize*$OCTAGON_SHARP для острых граней;
	 *	- dSize*$OCTAGON_SOFT - с фасками;
	 *	- можно и больше цилиндр. Схавает справа так, как надо, без остатка и без жадности.
	 *
	 * @params array Параметры: yBegin, yEnd, dBegin, End, [aStart], [sideMill]
	 */
	public function octagon($params)
	{
		$params['figure'] = 'Octagon';
		$params['aStep']  = 45;

		return $this->polygon($params);
	}

	/**
	 * Основа для hexagon и square
	 *
	 * @param $params
	 * @return string
	 */
	private function polygon($params)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['yBegin']) or !isset($params['yEnd']) or !isset($params['dEnd'])) {
			die('ERROR: Mandatory parameters are not defined: yBegin, yEnd, dEnd');
		}

		if (!isset($params['dBegin'])) {
			$params['dBegin'] = $this->blank->getDiameter();
		}

		# Делаем yBegin < $yEnd, как бы их не задали
		$yBegin = ($params['yBegin'] < $params['yEnd']) ? $params['yBegin'] : $params['yEnd'];
		$yEnd   = ($params['yBegin'] < $params['yEnd']) ? $params['yEnd'] : $params['yBegin'];

		if (($yEnd - $yBegin) < $this->cutter->getDiameter()) {
			die('ERROR: Cutter cant fit into '. $params['figure'] .' length');
		}

		# Аналогично и dBegin всегда больше dEnd
		$dBegin = ($params['dBegin'] < $params['dEnd']) ? $params['dEnd'] : $params['dBegin'];
		$dEnd   = ($params['dBegin'] < $params['dEnd']) ? $params['dBegin'] : $params['dEnd'];

		$aStart = isset($params['aStart']) ? $params['aStart'] : 0;

		if (isset($params['sideMill'])) {
			if (!$this->cutter->getSideDepth() or !$this->cutter->getSideStep()) {
				die('Error: Cant sideMill with this cutter (not defined side* properties)');
			}
		}

		fputs($this->fd, "\n( {$params['figure']} Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] ". (($aStart) ? ('A['. $aStart .'] '): '') .")\n");

		$this->zToSafe();

		if ($this->a != $aStart) {
			$this->a = $aStart;

			$this->G0('a');
		}

		# Слева режем точно до грани болта
		$xLeftBolt  = - $dEnd/2 * tan(pi() / (360 / $params['aStep']));

		# 8 - octagon, 6 граней для hexagon и 4 грани для square
		for ($face = 1; $face <= 360 / $params['aStep']; $face ++) {

			if ($this->isInfo()) {
				fputs($this->fd, "( {$params['figure']} - Face #{$face} )\n");
			}

			$this->z = $dBegin / 2;
			$zLast = false;

			do {

				$this->z -= isset($params['sideMill'])
					? $this->cutter->getSideDepth()
					: $this->cutter->getPassDepth();

				if ($this->z < ($dEnd / 2)) {
					# последний проход
					$this->z = $dEnd / 2;
					$zLast = true;
				}

				# определю граничные условия цилиндра dBegin по X на этой высоте
				$xLeft = -sqrt($dBegin / 2 * $dBegin / 2 - $this->z * $this->z);

				# Слева не режу дальше грани болта
				if ($xLeft < $xLeftBolt) {
					$xLeft = $xLeftBolt;
				}

				# Для шестигранника (и, тем более, для более "гранников") левую границу нужно сдвигать
				# правее, чтобы не резать воздух
				if ( ($face > 1) and ($params['aStep'] < 90) and ($this->z != ($dEnd/2))) {
					$xLeft += ($this->z - $dEnd/2) * tan(pi() / 180 * (90 - $params['aStep']) );
				}

				# Правая граница цилиндра dBegin по X на этой высоте
				$xRight = sqrt($dBegin / 2 * $dBegin / 2 - $this->z * $this->z);

				if ($xLeft < $xRight) {
					if ($this->isDebug()) {
						fputs($this->fd, "( {$params['figure']} - Face #{$face} - Square X[{$xLeft}..{$xRight}] Y[{$yBegin}..{$yEnd}] Z[{$this->z}] )\n");
					}

					# Врезание справа, правее цилиндра подвести и погнали налево
					$this->x = $xRight + $this->cutter->getRadius();
					$this->y = $yEnd - $this->cutter->getRadius();

					if (isset($params['sideMill'])) {
						$this->squareLevelSideMill($xLeft, $xRight, $yBegin, $yEnd);
					}
					else {
						$this->squareLevelSnake(   $xLeft, $xRight, $yBegin, $yEnd);
					}
				}

			} while (!$zLast);

			$this->zToSafe();

			$this->a -= $params['aStep'];
			$this->G0('a');
		}

		$this->aTo360();
		$this->aReset();
	}


	/**
	 * Срез одного уровня прямоугольника змейкой от yEnd до yBegin налево-направо
	 * Предполагается, что this->x, y и z содержат координаты перехода к XYZ[safe] начала справа-сверху
	 * Режет с заходом за xLeft / xRight на радиус фрезы. Но держа себя в рамках по Y
	 */
	private function squareLevelSnake($xLeft, $xRight, $yBegin, $yEnd)
	{
		$this->G0('xy');
		$this->G1('zf');

		# При дробном кол-ве шагов yStepoverMm усредним
		$yRange = $yEnd - $yBegin - $this->cutter->getDiameter();
		$yStepoverMm = $yRange
			? $yRange / ceil( $yRange / $this->cutter->getStepoverMm() )
			: 1;

		$this->y += $yStepoverMm;
		$yLast = false;

		# Цикл по Y
		do {
			$this->y -= $yStepoverMm;

			if ($this->y <= ($yBegin + $this->cutter->getRadius())) {
				# Последний проход
				$this->y = $yBegin + $this->cutter->getRadius();
				$yLast = true;
			}

			// @todo за чистоту кода просится wasY и анализ его изменений (лишний кот)
			$this->G1('y');

			if ($this->x == $xLeft) {
				# девочки направо
				$this->x = $xRight;
			}
			else {
				# мальчики налево
				$this->x = $xLeft;
			}

			$this->G1('x');

		} while (!$yLast);
	}

	/**
	 * Срез одного уровня прямоугольника боковым резом.
	 * То есть, достаточно серьёзное заглубление и фрезеровка боком фрезы на заданную ширину захвата
	 * Предполагается, что this->x, y и z содержат координаты перехода к XYZ[safe] начала справа-сверху
	 * Режет с заходом за xLeft / xRight на радиус фрезы. Но держа себя в рамках по Y
	 *
	 * Предполагается, что sideStep меньше радиуса фрезы. Иначе нижний отвод надо делить на G1 / G0
	 * @todo пока реализовано только попутное фрезерование
	 */
	private function squareLevelSideMill($xLeft, $xRight, $yBegin, $yEnd)
	{
		$this->G0('xy');
		$this->G1('zf');

		$xLast = false;

		# Цикл по X налево
		do {
			$this->x -= $this->cutter->getSideStep();

			// @todo хорошо б сделать адаптивный по ширине рез. Оставляя при этом тот же объём
			// То есть, в начале (наверху) цилиндра можно брать увереннее при той же нагрузке на станок

			if ($this->x < $xLeft) {
				# Последний проход
				$this->x = $xLeft;
				$xLast = true;
			}

			# "Врезались" в деталь на sideStep (0.1 мм, например)
			$this->G1('x');

			# Основной режущий проход
			$this->y = $yBegin + $this->cutter->getRadius();
			$this->G1('y');

			# Дорезка нижнего угла
			$this->x += $this->cutter->getSideStep();
			$this->G1('x');

			# Чуть отведу фрезу (компенсация отгиба при резе)
			$this->x += $this->cutter->getSideStep();
			$this->G0('x');

			# Холостой ход к началу реза
			$this->y = $yEnd - $this->cutter->getRadius();
			$this->G0('y');
			$this->x -= $this->cutter->getSideStep() * 2;
			$this->G0('x');
		} while (!$xLast);
	}


	/**
	 * Был квадрат, размером params['d']. Нужно сделать цилиндр того же диаметра
	 * @todo Сейчас поддерживает только sideMill
	 *
	 * @param $params array Массив параметров: yBegin, yEnd, d, [aStart]
	 */
	public function squareToCylinder($params)
	{
		die('@todo');
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['yBegin']) or !isset($params['yEnd']) or !isset($params['d'])) {
			die('ERROR: Mandatory parameters are not defined: yBegin, yEnd, d');
		}

		# Делаем yBegin < $yEnd, как бы их не задали
		$yBegin = ($params['yBegin'] < $params['yEnd']) ? $params['yBegin'] : $params['yEnd'];
		$yEnd   = ($params['yBegin'] < $params['yEnd']) ? $params['yEnd'] : $params['yBegin'];

		if (($yEnd - $yBegin) < $this->cutter->getDiameter()) {
			die('ERROR: Cutter cant fit into SquareToCylinder length');
		}

		if (isset($params['sideMill'])) {
			if (!$this->cutter->getSideDepth() or !$this->cutter->getSideStep()) {
				die('ERROR: Cant sideMill with this cutter (not defined side* properties)');
			}
		}
		else {
			die('ERROR: now support only sideMill, sorry');
		}

		$d = $params['d'];
		$aStart = isset($params['aStart']) ? $params['aStart'] : 0;

		fputs($this->fd, "\n( SquareToCylinder Y[{$yBegin}..{$yEnd}] D[{$d}]=R[". $d / 2 ."] ". (($aStart) ? ('A['. $aStart .'] '): '') .")\n");

		$this->zToSafe();

		$this->x = 0;
		$this->G0('x');

		for ($face = 1; $face <= 4; $face ++) {

			if ($this->isInfo()) {
				fputs($this->fd, "( SquareToCylinder - Face #{$face} )\n");
			}

			$this->z = ($d * sqrt(2)) / 2;
			$zLast = false;

			do {

				$this->z -= isset($params['sideMill'])
					? $this->cutter->getSideDepth()
					: $this->cutter->getPassDepth();

				if ($this->z < ($d / 2)) {
					# последний проход
					$this->z = $d / 2;
					$zLast = true;
				}

				$a = (pi() / 2 - 2 * acos($d/2 / $this->z)) / 2 / pi() * 180;
				$aRight = 45 + $aStart + ($face-1) * 90 + $a;
				$aLeft  = 45 + $aStart + ($face-1) * 90 - $a;

				if ($this->isDebug()) {
					fputs($this->fd, "( SquareToCylinder - Face #{$face} - CylinderPart A[{$aLeft}..{$aRight}] Y[{$yBegin}..{$yEnd}] Z[{$this->z}] )\n");
				}

				# Длина окружности на этом радиусе = pi * d
				$circleLength = pi() * 2 * $this->z;

				# Врезание справа, правее цилиндра подвести и погнали налево
				$this->a = $aRight + $this->cutter->getRadius() / $circleLength * 360;
				$this->y = $yEnd - $this->cutter->getRadius();

				$this->G0('ay');
				$this->G1('zf');

				if (isset($params['sideMill'])) {
					$this->CylinderPartLevelSideMill($aLeft, $aRight, $yBegin, $yEnd);
				}
				else {
					$this->cylinderPartLevelSnake(   $aLeft, $aRight, $yBegin, $yEnd);
				}

				$this->a = $aRight + $this->cutter->getRadius() / $circleLength * 360;
				$this->G0('a');

			} while (!$zLast);

			$this->zToSafe();
		}

		$this->aTo360();
		$this->aReset();
	}

	/**
	 * @todo implement
	 * @param $aLeft
	 * @param $aRight
	 * @param $yBegin
	 * @param $yEnd
	 */
	private function CylinderPartLevelSideMill($aLeft, $aRight, $yBegin, $yEnd)
	{
		die('ERROR: not implemented yet. Use without sideMill yet');
	}

	/**
	 * Срез куска цилиндра от aLeft до aRight (X = 0 при этом). От yBegin до yEnd
	 * Изначально фреза находится правее (по A) среза. Уже на нужной Z. В Y близко к yEnd
	 *
	 * @param $aLeft
	 * @param $aRight
	 * @param $yBegin
	 * @param $yEnd
	 */
	private function CylinderPartLevelSnake($aLeft, $aRight, $yBegin, $yEnd)
	{
		$yLast = false;

		$this->y += $this->cutter->getStepoverMm();

		do {
			$this->y -= $this->cutter->getStepoverMm();

			if ($this->y <= ($yBegin + $this->cutter->getRadius()) ) {
				$this->y = $yBegin + $this->cutter->getRadius();
				$yLast = true;
			}

			$this->G1('y');

			if ($this->a == $aLeft) {
				# Режем направо
				$this->a = $aRight;
			}
			else {
				# Режем налево
				$this->a = $aLeft;
			}

			$this->G1('a');

		} while (!$yLast);
	}


	/**
	 * Цилиндр
	 *
	 * @param $params array Массив параметров: dBegin, dEnd, yBegin, yEnd
	 */
	public function cylinder($params)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['yBegin']) or !isset($params['yEnd']) or !isset($params['dEnd'])) {
			die('ERROR: Mandatory parameters are not defined: yBegin, yEnd, dEnd');
		}

		if (!isset($params['dBegin'])) {
			$params['dBegin'] = $this->blank->getDiameter();
		}

		# Делаем yBegin < $yEnd, как бы их не задали
		$yBegin = ($params['yBegin'] < $params['yEnd']) ? $params['yBegin'] : $params['yEnd'];
		$yEnd   = ($params['yBegin'] < $params['yEnd']) ? $params['yEnd'] : $params['yBegin'];

		if (($yEnd - $yBegin) < $this->cutter->getDiameter()) {
			die('ERROR: Cutter cant fit into cylinder length');
		}

		# Аналогично и dBegin всегда больше dEnd
		$dBegin = ($params['dBegin'] < $params['dEnd']) ? $params['dEnd'] : $params['dBegin'];
		$dEnd   = ($params['dBegin'] < $params['dEnd']) ? $params['dBegin'] : $params['dEnd'];

		fputs($this->fd, "\n( Cylinder Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n");

		if (($yEnd - $yBegin) == $this->cutter->getDiameter()) {
			# Цилиндр через cutRight, а не cylinder из-за совпадения yRange с dCutter - так быстрее
			if ($this->isInfo()) {
				fputs($this->fd, "( Cylinder - Optimized to CutRight )\n");
			}

			return $this->cutRight($yBegin, $dBegin, $dEnd);
		}

		$this->zToSafe();

		$this->x = 0;
		$this->a = 0;
		# Y=3, потому как с 0 + dФрезы / 2
		$this->y = $yBegin + $this->cutter->getRadius();
		$this->G0('axy');

		# начало прохода
		$this->z = $dBegin/2;
		$this->G1('zf');
		$zLast = false;

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;	# Финальный проход
				$zLast = true;
			}

			# врезание до заданной глубины за 10°
			$this->a += 10;
			$this->G1('za');

			# начальный кружок на месте
			$this->a += 360;
			$this->G1('a');

			if ($this->y == ($yBegin + $this->cutter->getRadius()) ) {
				# прямой ход
				# Y=7, потому как до 10 - rФрезы
				$this->y = $yEnd - $this->cutter->getRadius();
			}
			else {
				# обратный ход
				$this->y = $yBegin + $this->cutter->getRadius();
			}

			# A=670, потому как один оборот за dФрезы * %% = 6*0.8 = 4.8. Пройти надо 10 - 0 - 6 = 4, то есть 4 / 4.8 * 360 = 300 градусов
			$this->a += ($yEnd - $yBegin - $this->cutter->getDiameter()) / $this->cutter->getStepoverMm() * 360;
			$this->G1('ya');

			# кружок на месте
			$this->a += 360;
			$this->G1('a');

		} while (!$zLast);

		$this->zToSafe();
		$this->aTo360();
		$this->timeEstimateByA();
		$this->aReset();
	}


	/**
	 * Докручивает ось A до кратного 360 вперёд
	 * @todo Параметр - докручивать направо, налево или куда ближе
	 */
	private function aTo360()
	{
		$this->zToSafe();

		if ($this->a != (ceil($this->a / 360) * 360)) {
			$this->a = ceil($this->a / 360) * 360;

			$this->G0('a');
		}
	}


	/**
	 * Сбрасывает A ось в ноль, если она кратна 360
	 * ... чтобы следующая программа не крутила назад N накрученных оборотов
	 */
	private function aReset()
	{
		if ($this->a == (ceil($this->a / 360) * 360) ) {
			fputs($this->fd, "G92 A 0".
				($this->isDebug()
					? "			( [rev=". abs(ceil($this->a / 360)) ."] )"
					: ''
				)
				."\n");

			$this->a = 0;
		}
	}


	public static $THREAD_RIGHT = -1;
	public static $THREAD_LEFT  = 1;


	/**
	 * Резьба гравёром
	 * @todo !!! перевести на $params
	 * @todo !!! необязательный dBegin
	 * @todo адаптивное заглубление. В начале лишь царапаем гравёром и можно увеличить подачу
	 *
	 * @param $yBegin float Начальный размер цилиндра (меньший, ex: 0)
	 * @param $yEnd float Конечный размер цилиндра (больший, ex: 10)
	 * @param $yStep float|string Или именование резьбы ex: 'M15x1.5', или шаг резьбы
	 * @param $dBegin float|null Или начальный диаметр, или пусто, если резьба задана строкой
	 * @param $dEnd null|float Или конечный диаметр, или пусто, если резьба задана строкой
	 */
	public function thread($yBegin, $yEnd, $yStep, $direction = -1, $dBegin = null, $dEnd = null)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if ( (gettype($yStep) == 'string') and preg_match('#M([0-9.]+)x([0-9.]+)#', $yStep, $out) ) {
			$dBegin = $out[1] - 0.1;			# M15x1.5 -> 14.9
			$dEnd   = $dBegin - $out[2] * 1.3;		# M15x1.5 -> 27.95
			$yStep  = $out[2];
		}

		fputs($this->fd, "\n( Thread Y[{$yBegin}..{$yEnd} by {$yStep}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n");

		$this->zToSafe();

		$this->a = -10 * $direction;
		$this->x = 0;
		$this->y = $yBegin;
		#1:
		$this->G0('axy');

		$this->z = $dBegin/2;
		$zLast = false;

		# начало прохода
		$this->G1('zf');

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;		# Финальный проход
				$zLast = true;
			}

			if ($this->y == $yEnd) {
				# обратный ход

				#1: y=end, a=2x360 + резьба + 10
				$this->a -= 10 * $direction;
				#2: y=end, a=2x360 + резьба
				# врезание до заданной глубины на A-=10
				$this->G1('za');

				$this->a -= 360 * $direction;
				#2: y=end, a=360 + резьба
				# кружок на месте налево
				$this->G1('a');

				$this->y = $yBegin;				# погнали налево крутя, ехать к началу

				$this->a = 360 * $direction;
				#2: y=begin, a=360
				# крутим резьбу налево
				$this->G1('ya');

				# круг на месте и лишние 10° - для следующего врезания, если НЕ финальный проход
				$this->a = ($this->z == ($dEnd/2)) ? 0 : (-10 * $direction);
				#2: y=begin, a=-10 или a=0 при финальном
				$this->G1('a');
			}
			else {
				# прямой ход

				#2: y=begin, a=-10
				$this->a += 10 * $direction;
				#1: y=begin, a=0
				# врезание до заданной глубины на A+=10
				$this->G1('za');

				$this->a += 360 * $direction;
				#1: y=begin, a=360
				# кружок на месте
				$this->G1('a');

				$this->y = $yEnd;
				$this->a += ($yEnd - $yBegin) / $yStep * 360 * $direction;
				#1: y=end, a=360 + резьба
				# крутим резьбу
				$this->G1('ya');

				$this->a += (($this->z == ($dEnd/2)) ? 360 : 370) * $direction;
				#1: y=end, a=2x360 + резьба + 10 (если НЕ финальный проход)
				# кружок на месте
				$this->G1('a');
			}

		} while (!$zLast);

		# Конец
		$this->zToSafe();
		$this->aTo360();
		$this->aReset();
	}


	// Направление вращения оси при резе
	public static $CUT_DIR_RIGHT = 1;
	public static $CUT_DIR_LEFT  = -1;

	/**
	 * Режим вычисления шага по Z:
	 * - center: простой и точный. На каждый оборот спирали заглубление на cutter->passDepth
	 * - half: см. wiki/figure-cut-zpass. Точка врезания половины радиуса фрезы
	 * - 75: Точка врезания 3/4 радиуса фрезы
	 * - diameter: Точка врезания края фрезы соответствует срезу заготовки
	 */
	public static $CUT_ZPASS_CENTER   = 'center';
	public static $CUT_ZPASS_HALF     = 'half';
	public static $CUT_ZPASS_75       = '75';
	public static $CUT_ZPASS_DIAMETER = 'diameter';


	/**
	 * Обрезать мусор справа. Режет до dEnd. Если не задал, то до нуля
	 *
	 * @param $params array Массив параметров: y, dBegin, [dEnd], [direction], [zPassMode]
	 * @return string g-code
	 */
	public function cutRight($params)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['y'])) {
			die('ERROR: Mandatory parameter "Y" is not defined');
		}

		$y         = $params['y'];
		$dBegin    = isset($params['dBegin']) ? $params['dBegin'] : $this->blank->getDiameter();
		$dEnd      = isset($params['dEnd']) ? $params['dEnd'] : 0;
		$direction = isset($params['direction']) ? $params['direction'] : self::$CUT_DIR_RIGHT;
		$zPassMode = isset($params['zPassMode']) ? $params['zPassMode'] : self::$CUT_ZPASS_CENTER;

		fputs($this->fd, "\n( CutRight Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] Direction=". (($direction == self::$CUT_DIR_RIGHT) ? 'RIGHT' : 'LEFT') ." zPassMode={$zPassMode} )\n");

		$this->cut($y + $this->cutter->getRadius(), $dBegin, $dEnd, $direction, $zPassMode);
	}

	/**
	 * Обрезать деталь слева. Режет до dEnd. Если не задал, то до нуля
	 *
	 * @param $params array Массив параметров: y, dBegin, [dEnd], [direction]
	 * @return string g-code
	 */
	public function cutLeft($params)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['y'])) {
			die('ERROR: Mandatory parameter "Y" is not defined');
		}

		$y         = $params['y'];
		$dBegin    = isset($params['dBegin']) ? $params['dBegin'] : $this->blank->getDiameter();
		$dEnd      = isset($params['dEnd']) ? $params['dEnd'] : 0;
		$direction = isset($params['direction']) ? $params['direction'] : self::$CUT_DIR_RIGHT;
		$zPassMode = isset($params['zPassMode']) ? $params['zPassMode'] : self::$CUT_ZPASS_CENTER;

		fputs($this->fd, "\n( CutLeft Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] Direction=". (($direction == self::$CUT_DIR_RIGHT) ? 'RIGHT' : 'LEFT') ." )\n");

		$this->cut($y - $this->cutter->getRadius(), $dBegin, $dEnd, $direction, $zPassMode);
	}

	/**
	 * Отрезать по заданному Y (+- радиус фрезы, конечно)
	 * Режет до dEnd. Если не задал, то до нуля
	 *
	 * @param $params array Массив параметров: y, dBegin, [dEnd], [direction]
	 * @return string g-code
	 */
	public function cutCenter($params)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (!isset($params['y'])) {
			die('ERROR: Mandatory parameter "Y" is not defined');
		}

		$y         = $params['y'];
		$dBegin    = isset($params['dBegin']) ? $params['dBegin'] : $this->blank->getDiameter();
		$dEnd      = isset($params['dEnd']) ? $params['dEnd'] : 0;
		$direction = isset($params['direction']) ? $params['direction'] : self::$CUT_DIR_RIGHT;
		$zPassMode = isset($params['zPassMode']) ? $params['zPassMode'] : self::$CUT_ZPASS_CENTER;

		fputs($this->fd, "\n( CutCenter Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] Direction=". (($direction == self::$CUT_DIR_RIGHT) ? 'RIGHT' : 'LEFT') ." )\n");

		$this->cut($y, $dBegin, $dEnd, $direction, $zPassMode);
	}


	/**
	 * DRY для семейства public function cut*()
	 *
	 * @param $y float Y-координата фрезы
	 * @param $dBegin float начальный диаметр
	 * @param $dEnd float конечный диаметр
	 * @param $direction int enum Направление реза - один из self::$CUT_DIR_*)
	 * @param $zPassMode string enum Режим заглубления (см. self::$CUT_ZPASS_*)
	 *
	 * @return string g-code
	 */
	private function cut($y, $dBegin, $dEnd, $direction, $zPassMode)
	{
		$this->zToSafe();

		$this->x = 0;
		$this->a = 0;
		$this->y = $y;
		$this->G0('axy');

		$this->z = $dBegin/2;
		$this->G1('zf');
		# Фрезу подвели к началу

		switch ($zPassMode) {
			case self::$CUT_ZPASS_CENTER:
				# Спиралью опустил до $dEnd
				$this->z = $dEnd/2;
				$this->a = ($dBegin/2 - $dEnd/2) / $this->cutter->getPassDepth() * 360 * $direction;
				$this->G1('az');
				break;

			case self::$CUT_ZPASS_HALF:
				$this->cutZPass($dBegin/2, $dEnd/2, $direction, $this->cutter->getRadius() / 2);
				break;

			case self::$CUT_ZPASS_75:
				$this->cutZPass($dBegin/2, $dEnd/2, $direction, $this->cutter->getRadius() * 0.75);
				break;

			case self::$CUT_ZPASS_DIAMETER:
				$this->cutZPass($dBegin/2, $dEnd/2, $direction, $this->cutter->getRadius());
				break;
		}

		if ($this->z > 0) {
			# Круг почёта на месте (не нужен, если уже r=0)
			$this->a += 360 * $direction;
			$this->G1('a');
		}

		$this->zToSafe();
		$this->aTo360();
		$this->timeEstimateByA();
		$this->aReset();
	}


	/**
	 * Сколько времени займёт, судя по кол-ву оборотов и feed
	 */
	private function timeEstimateByA()
	{
		if ($this->isDebug() and ($this->a != 0)) {
			$time = round(abs($this->a) / $this->cutter->getFeed() * 60);

			$hour = floor($time / 3600);
			$time -= $hour * 3600;
			$min  = floor($time / 60);
			$sec  = $time - $min * 60;

			fputs($this->fd, "( time estimate: ". sprintf('%02d:%02d:%02d', $hour, $min, $sec) ." )\n");
		}
	}


	private function cutZPass($rBegin, $rEnd, $direction, $xRange)
	{
		// Найти до какого z подойдёт обычный режим. Точка пересечения dФрезы с dОстатка
		$rEndStandart = ($this->cutter->getPassDepth() * $this->cutter->getPassDepth() + $xRange * $xRange) / (2 * $this->cutter->getPassDepth());

		if ($rEndStandart < $rBegin) {
			# Стандартный режим
			$this->z = $rEndStandart;
			$this->a = ($rBegin - $rEndStandart) / $this->cutter->getPassDepth() * 360 * $direction;
			$this->G1('az');
		}

		while ($this->z > $rEnd) {
			# Стандартный режим не дорезал до требуемой глубины. Добавим драйва!

			# На полный оборот нужно заглубиться на $passDepth
			$passDepth = ($xRange < $this->z) ?
				($this->z - sqrt($this->z * $this->z - $xRange * $xRange))
				: $this->z
			;

			if (($this->z - $passDepth) < $rEnd) {
				# Но с таким заглублением прорежем глубже желаемого!
				// @todo лучше уменьшить угол, а не заглубление
				$passDepth = $this->z - $rEnd;
			}

			$this->a += 360 * $direction; 	# Один оборот дальше
			$this->z -= $passDepth;			# Вычисленное заглубление
			$this->G1('az');
		}
	}


	public function end()
	{
		fputs($this->fd, "M09\n");
		fputs($this->fd, "M30\n");

		fclose($this->fd);
	}


	/**
	 * Поднять фрезу на Safe
	 *
	 * @return string
	 */
	private function zToSafe()
	{
		if ($this->isSafeZ()) {
			return;
		}

		$this->z = $this->blank->getRadius() + $this->safe;
		# ex: заготовка 50/2 + safe 10 = 35

		$this->G0('z');
	}


	/**
	 * Z сейчас на safe?
	 * @return bool
	 */
	private function isSafeZ()
	{
		return !! ($this->z == ($this->blank->getRadius() + $this->safe));
	}


	private function G0($axis)
	{
		return $this->GoGo('0', $axis);
	}
	private function G1($axis)
	{
		return $this->GoGo('1', $axis);
	}
	private function GoGo($mode, $axis)
	{
		$ret = ['G'. $mode];

		foreach (str_split($axis) as $item) {
			switch ($item) {
				case 'x':
				case 'y':
				case 'z':
				case 'a':
					$ret[] = strtoupper($item) . round($this->{$item}, 4);
					break;

				case 'f':
					$ret[] = 'F'. $this->cutter->getFeed();
					break;
			}
		}

		fputs($this->fd, join(' ', $ret) ."\n");
	}

}