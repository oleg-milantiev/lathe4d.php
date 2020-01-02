<?php

/**
 * ���������
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
 * �����
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
	 * ���-�� �� ���� �� Y � ������ %% ���� � d�����
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
			# ����� �����, � �� ������ �����

			// @todo ������� ������. ��� ������� ����� ������ ����
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
	 * ������ ���������
	 */
	public function start()
	{
		if (!$this->blank) {
			die('��������� �� ������');
		}
		if (!$this->safe) {
			die('Safe Z �� �����');
		}

		$ret = '';

		$ret .= "( File created by Lathe4d.php )\n";
		$ret .= "( ". date('d-m-Y H:i') ." )\n";

		$ret .= $this->zToSafe();

		return $ret;
	}


	/**
	 * �������
	 *
	 * @params $yBegin float ��������� ������ �������� (�������, ex: 0)
	 * @params $yEnd float �������� ������ �������� (�������, ex: 10)
	 * @params $dBegin float ��������� ������� (�������, ex: 50)
	 * @params $dEnd float �������� ������� (�������, ex: 40)
	 * @todo ������� ����� ������� ��������� / �������� D � Y
	 */
	public function cylinder($yBegin, $yEnd, $dBegin, $dEnd)
	{
		if (!$this->cutter) {
			die('����� �� ������');
		}

		$ret = "( Cylinder Y[{$yBegin}..{$yEnd}] D[{$dBegin}..{$dEnd}]=R[". $dBegin / 2 ."..". $dEnd / 2 ."] )\n";

		# Y=3, ������ ��� � 0 + d����� / 2
		$this->y = $yBegin + $this->cutter->getRadius();
		$ret .= "G0 A0 X0 Y{$this->y}\n";

		# ������ �������
		$this->z = $dBegin/2;
		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;	# ��������� ������
			}

			# �������� �� �������� ������� �� 10�
			$this->a += 10;
			$ret .= "G1 Z{$this->z} A{$this->a}\n";

			# ��������� ������ �� �����
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

			if ($this->y == ($yBegin + $this->cutter->getRadius()) ) {
				# ������ ���
				# Y=7, ������ ��� �� 10 - r�����
				$this->y = $yEnd - $this->cutter->getRadius();
			}
			else {
				# �������� ���
				$this->y = $yBegin + $this->cutter->getRadius();
			}

			# A=670, ������ ��� ���� ������ �� d����� * %% = 6*0.8 = 4.8. ������ ���� 10 - 0 - 6 = 4, �� ���� 4 / 4.8 * 360 = 300 ��������
			$this->a += ($yEnd - $yBegin - $this->cutter->getDiameter()) / $this->cutter->getStepoverMm() * 360;
			$ret .= "G1 Y{$this->y} A{$this->a}\n";

			# ������ �� �����
			$this->a += 360;
			$ret .= "G1 A{$this->a}\n";

		} while ($this->z != ($dEnd/2));

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	/**
	 * ����������� ��� A �� �������� 360 �����
	 */
	private function aTo360()
	{
		$this->zToSafe();
		
		$this->a = ceil($this->a / 360) * 360;
		return "G0 A{$this->a}\n";
	}


	/**
	 * ���������� A ��� � ����, ���� ��� ������ 360
	 * ... ����� ��������� ��������� �� ������� ����� N ����������� ��������
	 */
	private function aReset()
	{
		if ($this->a == (ceil($this->a / 360) * 360) ) {
			$this->a = 0;

			return "G92 A0\n";
		}
	}


	/**
	 * ������ �������
	 * 
	 * @params $yBegin float ��������� ������ �������� (�������, ex: 0)
	 * @params $yEnd float �������� ������ �������� (�������, ex: 10)
	 * @params $yStep float|string ��� ���������� ������ ex: 'M15x1.5', ��� ��� ������
	 * @params $dBegin float|null ��� ��������� �������, ��� �����, ���� ������ ������ �������
	 * @params $dEnd null|float ��� �������� �������, ��� �����, ���� ������ ������ �������
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

		$ret .= "G1 Z{$this->z} F{$this->cutter->getFeed()}\n";	# ������ �������

		do {
			$this->z -= $this->cutter->getPassDepth();

			if ($this->z < ($dEnd/2)) {
				$this->z = $dEnd/2;		# ��������� ������
			}

			if ($this->y == $yEnd) {
				# �������� ���

				#1: y=end, a=360 + ������ + 10
				$this->a -= 10;
				#2: y=end, a=360 + ������
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# �������� �� �������� ������� �� A-=10

				$this->a -= 360;
				#2: y=end, a=������
				$ret .= "G1 A{$this->a}\n";	# ������ �� ����� ������

				$this->y = $yBegin;				# ������� ������ �����, ����� � ������
				$this->a = 0;
				#2: y=begin, a=0
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# ������ ������ ������

				$this->a -= 10;				# ������ 10� ��������� ������ ��� ���������� ��������
				#2: y=begin, a=-10
				$ret .= "G1 A{$this->a}\n";
			}
			else {
				# ������ ���

				#2: y=begin, a=-10
				$this->a += 10;
				#1: y=begin, a=0
				$ret .= "G1 Z{$this->z} A{$this->a}\n";	# �������� �� �������� ������� �� A+=10

				$this->a += 360;
				#1: y=begin, a=360
				$ret .= "G1 A{$this->a}\n";		# ������ �� �����

				#1: a=360
				$ret .= $this->aReset();
				#1: a=0

				$this->y = $yEnd;
				$this->a += ($yEnd - $yBegin) / $yStep * 360;
				#1: y=end, a=������
				$ret .= "G1 Y{$this->y} A{$this->a}\n";	# ������ ������

				$this->a += 370;
				#1: y=end, a=360 + ������ + 10
				$ret .= "G1 A{$this->a}\n";			# ������ �� ����� � ��� 10� ��� �������� �����
			}

		} while ($this->z != ($dEnd/2));

		# �����

		$ret .= $this->zToSafe();
		$ret .= $this->aTo360();
		$ret .= $this->aReset();

		return $ret;
	}


	public function end()
	{
	}


	/**
	 * ������� ����� �� Safe
	 *
	 * @return string
	 */
	private function zToSafe()
	{
		if ($this->isSafeZ()) {
			return '';
		}

		$this->z = $this->blank->getRadius() + $this->safe;

		return "G0 Z{$this->z}\n";				# ��������� 50/2 + safe 10
	}


	/**
	 * Z ������ �� safe?
	 * @return bool
	 */
	private function isSafeZ()
	{
		return !! ($this->z == ($this->blank->getRadius() + $this->safe));
	}

}