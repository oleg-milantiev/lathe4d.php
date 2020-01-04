<?php

/**
 * Заготовка
 */
class Blank
{
	private $diameter;
	private $length;

	public function __construct($diameter, $length = -1)
	{
		$this->diameter = $diameter;
		$this->length   = $length;
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
	private $stepover;
	private $feed;
	private $plunge; // @todo Пока не используется
	private $name;
	private $tool;

	public function __construct($params = [])
	{
		foreach (['diameter', 'passDepth', 'stepover', 'feed', 'name', 'tool', 'plunge'] as $field) {
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

	public function setCutter(Cutter $cutter)
	{
		$ret = '';

		if ($this->cutter) {
			# Смена фрезы, а не первая фреза

			// @todo Поднять повыше. Шоб удобней фрезу менять было
			$ret .= "T{$cutter->getTool()}\n";
			$ret .= "M5      (Spindle stop.)\n";
			$ret .= "(MSG, Change tool to {$cutter->getName()})\n";
			$ret .= "M6      (Tool change.)\n";
			$ret .= "M0      (Temporary machine stop.)\n";
			$ret .= "M3      (Spindle on clockwise.)\n";
		}
		else {
			$ret .= "T{$cutter->getTool()}\n";
			$ret .= "(Current cutter {$cutter->getName()})\n";
			$ret .= "M6      (Tool change.)\n";
		}

		$this->cutter = $cutter;

		return $ret;
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

		$ret = '';

		$ret .= "( File created by Lathe4d.php )\n";
		$ret .= "( ". date('d-m-Y H:i') ." )\n";

		# @todo пока взял шапку из aspire for mach3. Потом вынесу наружу
		$ret .= "G00G21G17G90G40G49G80\n";
		$ret .= "G71G91.1\n";
		$ret .= "S18000M3\n"; // @todo скорость шпинделя в cutter
		$ret .= "G94\n";

		$ret .= $this->zToSafe();

		return $ret;
	}


	public static $HEXAGON_CYLINDER_SHARP = 1.155;
	public static $HEXAGON_CYLINDER_SOFT  = 1.115;

	/**
	 * Шестигранная голова болта
	 * Основа болта - цилиндр диаметра dSize*1.155 для острых граней. Или ~*1.115 с фасками
	 * 				- можно и больше цилиндр. Схавает так, как надо, без остатка и без жадности
	 *
	 * @param $yBegin float Начальная координата Y площадки
	 * @param $yEnd float Конечная координата Y площадки
	 * @param $dBegin float Начальный диаметр реза
	 * @param $dEnd float Размер ключа, например, 12.8 под 13й
	 * @param $aStart float Угол первой плоскости (0 по умолчанию)
	 *
	 * @todo далёкие планы - переделка на iMaching справа. Попутное или встречное (щас вперемешку)
	 */
	public function hexagon($yBegin, $yEnd, $dBegin, $dEnd, $aStart = 0)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (($yEnd - $yBegin) < $this->cutter->getDiameter()) {
			die('ERROR: Cutter cant fit into hexagon length');
		}

		$ret = "( Hexagon Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] ". (($aStart) ? ('A['. $aStart .'] '): '') .")\n";

		$ret .= $this->zToSafe();

		if ($this->a != $aStart) {
			$this->a = $aStart;

			$ret .= "G0 A{$aStart}\n";
		}

		# Слева режем точно до грани болта
		# Правая и левая координаты грани болта на уровне dEnd
		$xRightBolt = $dEnd/2 * tan(pi() / 6);
		$xLeftBolt  = -$xRightBolt;

		# 6 граней
		for ($face = 1; $face <= 6; $face ++) {

			if ($this->isInfo()) {
				$ret .= "( Hexagon - Face #{$face} )\n";
			}

			$this->z = $dBegin / 2;

			do {

				$this->z -= $this->cutter->getPassDepth();

				if ($this->z < ($dEnd / 2)) {
					# последний проход
					$this->z = $dEnd / 2;
				}

				# определю граничные условия цилиндра dBegin по X на этой высоте
				$xLeft = -sqrt($dBegin / 2 * $dBegin / 2 - $this->z * $this->z);

				# Слева не режу дальше грани болта
				if ($xLeft < $xLeftBolt) {
					$xLeft = $xLeftBolt;
				}

				# Правая граница цилиндра dBegin по X на этой высоте
				$xRight = sqrt($dBegin / 2 * $dBegin / 2 - $this->z * $this->z);

//				# Левый срез следующей грани
//				$xRightNext = $xRightBolt + tan(pi() / 3) * ($this->z - $dEnd / 2);
//
//				# Не нужно резать дальше следующей грани.
//				if ($xRight > $xRightNext) {
//					$xRight = $xRightNext;
//				}

				if ($this->isDebug()) {
					$ret .= "( Hexagon - Face #{$face} - Square X[{$xLeft}..{$xRight}] Y[{$yBegin}..{$yEnd}] Z[{$this->z}] )\n";
				}

				# Врезание справа, правее цилиндра подвести и погнали налево
				$this->x = $xRight + $this->cutter->getRadius();
				$this->y = $yBegin + $this->cutter->getRadius();

				$ret .= "G0 X{$this->x} Y{$this->y}\n";
				$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";

				$this->y -= $this->cutter->getStepoverMm();

				# Цикл по Y
				do {
					$this->y += $this->cutter->getStepoverMm();

					if ($this->y > ($yEnd - $this->cutter->getRadius())) {
						# Последний проход
						$this->y = $yEnd - $this->cutter->getRadius();
					}

					// @todo за чистоту кода просится wasY и анализ его изменений (лишний кот)
					$ret .= "G1 Y{$this->y}\n";

					if ($this->x == ($xLeft + $this->cutter->getRadius())) {
						# девочки направо
						$this->x = $xRight - $this->cutter->getRadius();
					}
					else {
						# мальчики налево
						$this->x = $xLeft + $this->cutter->getRadius();
					}

					$ret .= "G1 X{$this->x}\n";

				} while ($this->y != ($yEnd - $this->cutter->getRadius()));

			} while ($this->z != ($dEnd/2));

			$ret .= $this->zToSafe();

			$this->a += 60;
			$ret .= "G0 A{$this->a}\n";
		}

		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	/**
	 * Цилиндр
	 *
	 * @param $yBegin float Начальный размер цилиндра (меньший, ex: 0)
	 * @param $yEnd float Конечный размер цилиндра (больший, ex: 10)
	 * @param $dBegin float Начальный диаметр (больший, ex: 50)
	 * @param $dEnd float Конечный диаметр (меньший, ex: 40)
	 * @todo сделать пофик порядок начальных / конечных D и Y
	 */
	public function cylinder($yBegin, $yEnd, $dBegin, $dEnd)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if (($yEnd - $yBegin) < $this->cutter->getDiameter()) {
			die('ERROR: Cutter cant fit into cylinder length');
		}

		$ret = "( Cylinder Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		if (($yEnd - $yBegin) == $this->cutter->getDiameter()) {
			# Цилиндр через cutRight, а не cylinder из-за совпадения yRange с dCutter - так быстрее
			if ($this->isInfo()) {
				$ret .= "( Cylinder - Optimized to CutRight )\n";
			}

			$ret .= $this->cutRight($yBegin, $dBegin, $dEnd);

			return $ret;
		}

		$ret .= $this->zToSafe();

		# Y=3, потому как с 0 + dФрезы / 2
		$this->y = $yBegin + $this->cutter->getRadius();
		$ret .= "G0 A0 X0 Y{$this->y}\n";

		# начало прохода
		$this->z = $dBegin/2;
		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;	# Финальный проход
			}

			# врезание до заданной глубины за 10°
			$this->a += 10;
			$ret .= "G1 Z{$this->z} A{$this->a}\n";

			# начальный кружок на месте
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

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
			$ret .= "G1 Y{$this->y} A{$this->a}\n";

			# кружок на месте
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

		} while ($this->z != ($dEnd/2));

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	/**
	 * Докручивает ось A до кратного 360 вперёд
	 * @todo Параметр - докручивать направо, налево или куда ближе
	 */
	private function aTo360()
	{
		$ret = $this->zToSafe();

		if ($this->a != (ceil($this->a / 360) * 360)) {
			$this->a = ceil($this->a / 360) * 360;

			$ret .= "G0 A{$this->a}\n";
		}

		return $ret;
	}


	/**
	 * Сбрасывает A ось в ноль, если она кратна 360
	 * ... чтобы следующая программа не крутила назад N накрученных оборотов
	 */
	private function aReset()
	{
		if ($this->a == (ceil($this->a / 360) * 360) ) {
			$this->a = 0;

			return "G92 A 0\n";
		}
	}


	/**
	 * Резьба гравёром
	 * 
	 * @param $yBegin float Начальный размер цилиндра (меньший, ex: 0)
	 * @param $yEnd float Конечный размер цилиндра (больший, ex: 10)
	 * @param $yStep float|string Или именование резьбы ex: 'M15x1.5', или шаг резьбы
	 * @param $dBegin float|null Или начальный диаметр, или пусто, если резьба задана строкой
	 * @param $dEnd null|float Или конечный диаметр, или пусто, если резьба задана строкой
	 */
	public function thread($yBegin, $yEnd, $yStep, $dBegin = null, $dEnd = null)
	{
		if (!$this->cutter) {
			die('ERROR: Cutter not defined');
		}

		if ( (gettype($yStep) == 'string') and preg_match('#M([0-9.]+)x([0-9.]+)#', $yStep, $out) ) {
			$dBegin = $out[1] - 0.1;			# M15x1.5 -> 14.9
			$dEnd   = $dBegin - $out[2] * 1.3;		# M15x1.5 -> 27.95
			$yStep  = $out[2];
		}

		$ret = "( Thread Y[{$yBegin}..{$yEnd} by {$yStep}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		$ret .= $this->zToSafe();

		$this->a = -10;
		$this->y = $yBegin;
		#1:
		$ret .= "G0 A{$this->a} X0 Y{$this->y}\n";

		$this->z = $dBegin/2;

		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";	# начало прохода

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;		# Финальный проход
			}

			if ($this->y == $yEnd) {
				# обратный ход

				#1: y=end, a=2x360 + резьба + 10
				$this->a -= 10;
				#2: y=end, a=2x360 + резьба
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# врезание до заданной глубины на A-=10

				$this->a -= 360;
				#2: y=end, a=360 + резьба
				$ret .= "G1 A{$this->a}\n";	# кружок на месте налево

				$this->y = $yBegin;				# погнали налево крутя, ехать к началу
				$this->a = 360;
				#2: y=begin, a=360
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# крутим резьбу налево

				$this->a = -10;				# круг на месте и лишние 10° - для следующего врезания
				#2: y=begin, a=-10
				$ret .= "G1 A{$this->a}\n";
			}
			else {
				# прямой ход

				#2: y=begin, a=-10
				$this->a += 10;
				#1: y=begin, a=0
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# врезание до заданной глубины на A+=10

				$this->a += 360;
				#1: y=begin, a=360
				$ret .= "G1 A{$this->a}\n";		# кружок на месте

				$this->y = $yEnd;
				$this->a += ($yEnd - $yBegin) / $yStep * 360;
				#1: y=end, a=360 + резьба
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# крутим резьбу

				$this->a += 370;
				#1: y=end, a=2x360 + резьба + 10
				$ret .= "G1 A{$this->a}\n";			# кружок на месте и ещё 10° для врезания потом
			}

		} while ($this->z != ($dEnd/2));

		# Конец

		$ret .= $this->zToSafe();

		return $ret;
	}


	/**
	 * Обрезать мусор справа. Режет до dEnd. Если не задал, то до нуля
	 */
	public function cutRight($y, $dBegin, $dEnd = 0)
	{
		$ret = "( CutRight Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		$ret .= $this->cut($y + $this->cutter->getRadius(), $dBegin, $dEnd);

		return $ret;
	}

	/**
	 * Обрезать деталь слева. Режет до dEnd. Если не задал, то до нуля
	 */
	public function cutLeft($y, $dBegin, $dEnd = 0)
	{
		$ret = "( CutLeft Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		$ret .= $this->cut($y - $this->cutter->getRadius(), $dBegin, $dEnd);

		return $ret;
	}

	public function cutCenter($y, $dBegin, $dEnd = 0)
	{
		$ret = "( CutCenter Y[{$y}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		$ret .= $this->cut($y, $dBegin, $dEnd);

		return $ret;
	}

	private function cut($y, $dBegin, $dEnd)
	{
		$ret = '';

		$ret .= $this->zToSafe();

		$this->y = $y;
		$ret .= "G0 A0 Y{$this->y}\n";

		$this->z = $dBegin/2;
		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";
		# Фрезу подвели к началу

		$this->z = $dEnd/2;
		$this->a = ($dBegin/2 - $dEnd/2) / $this->cutter->getPassDepth() * 360;
		$ret .= "G1 A{$this->a} Z{$this->z}\n";
		# Спиралью опустил до $dEnd

		$this->a += 360;
		$ret .= "G1 A{$this->a}\n";
		# Круг почёта на месте

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	public function end()
	{
		$ret = "M09\n";
		$ret .= "M30\n";

		return $ret;
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

		return "G0 Z{$this->z}\n";
	}


	/**
	 * Z сейчас на safe?
	 * @return bool
	 */
	private function isSafeZ()
	{
		return !! ($this->z == ($this->blank->getRadius() + $this->safe));
	}


	// Для G0 / G1 предыдущие значения переходов
	private $wasX;
	private $wasY;
	private $wasZ;
	private $wasA;

	private function G($mode = '0')
	{
		$ret = 'G'. $mode;

		if ($this->wasX != $this->x) {
			$this->wasX = $this->x;
			$ret .= ' X'. $this->x;
		}

		if ($this->wasY != $this->y) {
			$this->wasY = $this->y;
			$ret .= ' Y'. $this->y;
		}

		if ($this->wasZ != $this->z) {
			$this->wasZ = $this->z;
			$ret .= ' Z'. $this->z;
		}

		if ($this->wasA != $this->a) {
			$this->wasA = $this->a;
			$ret .= ' A'. $this->a;
		}

		return $ret ."\n";
	}

}