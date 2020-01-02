<?php

/**
 * Çàãîòîâêà
 */
class Blank
{
	private $diameter;
	private $length;

	public function Blank($diameter, $length = -1)
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
 * Ôğåçà
 */
class Cutter
{
	private $diameter;
	private $passDepth;
	private $stepover;
	private $feed;
	private $name;

	public function Cutter($diameter = null, $passDepth = null, $stepover = null, $feed = null, $name = null)
	{
		$this->diameter  = $diameter;
		$this->passDepth = $passDepth;
		$this->stepover  = $stepover;
		$this->feed      = $feed;
		$this->name      = $name;
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

	public function setStepover($stepover)
	{
		$this->stepover = $stepover;
	}

	/**
	 * Êîë-âî ìì øàãà ïî Y ñ ó÷¸òîì %% øàãà è dÔğåçû
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

	private $blank;
	private $cutter;
	private $safe;

	private $x = 0;
	private $y = 0;
	private $z = 0;
	private $a = 0;

	public function setBlank(Blank $blank)
	{
		$this->blank = $blank;
	}

	public function setCutter(Cutter $cutter)
	{
		$ret = '';

		if ($this->cutter) {
			# Ñìåíà ôğåçû, à íå ïåğâàÿ ôğåçà

			// @todo Ïîäíÿòü ïîâûøå. Øîá óäîáíåé ôğåçó ìåíÿòü áûëî
			$ret .= "T1\n";
			$ret .= "M5      (Spindle stop.)\n";
			$ret .= "(MSG, Change tool to {$cutter->getName()})\n";
			$ret .= "M6      (Tool change.)\n";
			$ret .= "M0      (Temporary machine stop.)\n";
			$ret .= "M3      (Spindle on clockwise.)\n";
		}
		else {
			$ret .= "(Current cutter {$cutter->getName()})\n";
		}

		$this->cutter = $cutter;

		return $ret;
	}

	public function setSafe($safe)
	{
		$this->safe = $safe;
	}

	/**
	 * Íà÷àëî ïğîãğàììû
	 */
	public function start()
	{
		if (!$this->blank) {
			die('Çàãîòîâêà íå çàäàíà');
		}
		if (!$this->safe) {
			die('Safe Z íå çàäàí');
		}

		$ret = '';

		$ret .= "( File created by Lathe4d.php )\n";
		$ret .= "( ". date('d-m-Y H:i') ." )\n";

		$ret .= $this->zToSafe();

		return $ret;
	}


	/**
	 * Öèëèíäğ
	 *
	 * @params $yBegin float Íà÷àëüíûé ğàçìåğ öèëèíäğà (ìåíüøèé, ex: 0)
	 * @params $yEnd float Êîíå÷íûé ğàçìåğ öèëèíäğà (áîëüøèé, ex: 10)
	 * @params $dBegin float Íà÷àëüíûé äèàìåòğ (áîëüøèé, ex: 50)
	 * @params $dEnd float Êîíå÷íûé äèàìåòğ (ìåíüøèé, ex: 40)
	 * @todo ñäåëàòü ïîôèê ïîğÿäîê íà÷àëüíûõ / êîíå÷íûõ D è Y
	 */
	public function cylinder($yBegin, $yEnd, $dBegin, $dEnd)
	{
		if (!$this->cutter) {
			die('Ôğåçà íå çàäàíà');
		}

		$ret = "( Cylinder Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		# Y=3, ïîòîìó êàê ñ 0 + dÔğåçû / 2
		$this->y = $yBegin + $this->cutter->getRadius();
		$ret .= "G0 A0 X0 Y{$this->y}\n";

		# íà÷àëî ïğîõîäà
		$this->z = $dBegin/2;
		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;	# Ôèíàëüíûé ïğîõîä
			}

			# âğåçàíèå äî çàäàííîé ãëóáèíû çà 10°
			$this->a += 10;
			$ret .= "G1 Z{$this->z} A{$this->a}\n";

			# íà÷àëüíûé êğóæîê íà ìåñòå
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

			if ($this->y == ($yBegin + $this->cutter->getRadius()) ) {
				# ïğÿìîé õîä
				# Y=7, ïîòîìó êàê äî 10 - rÔğåçû
				$this->y = $yEnd - $this->cutter->getRadius();
			}
			else {
				# îáğàòíûé õîä
				$this->y = $yBegin + $this->cutter->getRadius();
			}

			# A=670, ïîòîìó êàê îäèí îáîğîò çà dÔğåçû * %% = 6*0.8 = 4.8. Ïğîéòè íàäî 10 - 0 - 6 = 4, òî åñòü 4 / 4.8 * 360 = 300 ãğàäóñîâ
			$this->a += ($yEnd - $yBegin - $this->cutter->getDiameter()) / $this->cutter->getStepoverMm() * 360;
			$ret .= "G1 Y{$this->y} A{$this->a}\n";

			# êğóæîê íà ìåñòå
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

		} while ($this->z != ($dEnd/2));

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	/**
	 * Äîêğó÷èâàåò îñü A äî êğàòíîãî 360 âïåğ¸ä
	 */
	private function aTo360()
	{
		$this->zToSafe();
		
		$this->a = ceil($this->a / 360) * 360;
		return "G0 A{$this->a}\n";
	}


	/**
	 * Ñáğàñûâàåò A îñü â íîëü, åñëè îíà êğàòíà 360
	 * ... ÷òîáû ñëåäóşùàÿ ïğîãğàììà íå êğóòèëà íàçàä N íàêğó÷åííûõ îáîğîòîâ
	 */
	private function aReset()
	{
		if ($this->a == (ceil($this->a / 360) * 360) ) {
			$this->a = 0;

			return "G92 A0\n";
		}
	}


	/**
	 * Ğåçüáà ãğàâ¸ğîì
	 * 
	 * @params $yBegin float Íà÷àëüíûé ğàçìåğ öèëèíäğà (ìåíüøèé, ex: 0)
	 * @params $yEnd float Êîíå÷íûé ğàçìåğ öèëèíäğà (áîëüøèé, ex: 10)
	 * @params $yStep float|string Èëè èìåíîâàíèå ğåçüáû ex: 'M15x1.5', èëè øàã ğåçüáû
	 * @params $dBegin float|null Èëè íà÷àëüíûé äèàìåòğ, èëè ïóñòî, åñëè ğåçüáà çàäàíà ñòğîêîé
	 * @params $dEnd null|float Èëè êîíå÷íûé äèàìåòğ, èëè ïóñòî, åñëè ğåçüáà çàäàíà ñòğîêîé
	 */
	public function thread($yBegin, $yEnd, $yStep, $dBegin = null, $dEnd = null)
	{
		if ( (gettype($yStep) == 'string') and preg_match('#M([0-9.]+)x([0-9.]+)#', $yStep, $out) ) {
			$dBegin = $out[1] * 2 - 0.1;			# M15x1.5 -> 14.9
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

		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";	# íà÷àëî ïğîõîäà

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;		# Ôèíàëüíûé ïğîõîä
			}

			if ($this->y == $yEnd) {
				# îáğàòíûé õîä

				#1: y=end, a=360 + ğåçüáà + 10
				$this->a -= 10;
				#2: y=end, a=360 + ğåçüáà
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# âğåçàíèå äî çàäàííîé ãëóáèíû íà A-=10

				$this->a -= 360;
				#2: y=end, a=ğåçüáà
				$ret .= "G1 A{$this->a}\n";	# êğóæîê íà ìåñòå íàëåâî

				$this->y = $yBegin;				# ïîãíàëè íàëåâî êğóòÿ, åõàòü ê íà÷àëó
				$this->a = 0;
				#2: y=begin, a=0
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# êğóòèì ğåçüáó íàëåâî

				$this->a -= 10;				# ëèøíèå 10° ïğîñêî÷èì íàëåâî äëÿ ñëåäóşùåãî âğåçàíèÿ
				#2: y=begin, a=-10
				$ret .= "G1 A{$this->a}\n";
			}
			else {
				# ïğÿìîé õîä

				#2: y=begin, a=-10
				$this->a += 10;
				#1: y=begin, a=0
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# âğåçàíèå äî çàäàííîé ãëóáèíû íà A+=10

				$this->a += 360;
				#1: y=begin, a=360
				$ret .= "G1 A{$this->a}\n";		# êğóæîê íà ìåñòå

				#1: a=360
				$ret .= $this->aReset();
				#1: a=0

				$this->y = $yEnd;
				$this->a += ($yEnd - $yBegin) / $yStep * 360;
				#1: y=end, a=ğåçüáà
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# êğóòèì ğåçüáó

				$this->a += 370;
				#1: y=end, a=360 + ğåçüáà + 10
				$ret .= "G1 A{$this->a}\n";			# êğóæîê íà ìåñòå è åù¸ 10° äëÿ âğåçàíèÿ ïîòîì
			}

		} while ($this->z != ($dEnd/2));

		# Êîíåö

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	public function end()
	{
	}


	/**
	 * Ïîäíÿòü ôğåçó íà Safe
	 *
	 * @return string
	 */
	private function zToSafe()
	{
		if ($this->isSafeZ()) {
			return '';
		}

		$this->z = $this->blank->getRadius() + $this->safe;

		return "G0 Z{$this->z}\n";				# çàãîòîâêà 50/2 + safe 10
	}


	/**
	 * Z ñåé÷àñ íà safe?
	 * @return bool
	 */
	private function isSafeZ()
	{
		return !! ($this->z == ($this->blank->getRadius() + $this->safe));
	}

}