<?php

include "lathe4d.php";

$lathe = new Lathe4d();

$lathe->setBlank(
	new Blank(36)			# ��������� D 36, L �� ����� (� ���� �� ������������)
							# X,Y [0,0] � ������ ���� �������. Z [0] �� ��� ��������
);
$lathe->setSafe(10);		# ���������� ������ 10��

$cutter = new cutter();
$cutter->setDiameter(6);	# ������� ����� 6��
$cutter->setPassDepth(3);	# ����������� �� ��� 3��
$cutter->setStepover(0.8);	# 80% ��� �� Y �� �������� �����
$cutter->setFeed(1400);		# ������ 1400 ��/���
$cutter->setName('6�� �������� ������� �� ������');		# ��� �����

echo $lathe->start();		# ��������� g-code �������

echo $lathe->setCutter($cutter);

# ����� �������� ������� �� D36 �� D17 � 0 �� 48 �� Y
echo $lathe->cylinder(0, 48, 36, 17);
# ��� ���� L 18 ��������� ��� ������ �15 �� ������
echo $lathe->cylinder(0, 18, 17, 14.9);
echo $lathe->cylinder(30, 48, 17, 14.9);

# ����� M15x1.5 ������ �������. ����������
$engraver = new cutter();
$engraver->setPassDepth(0.1);
$engraver->setFeed(1400);
$engraver->setName('����� 60�');		# ��� �����

echo $lathe->setCutter($engraver);

#echo $lathe->thread(0, 17, 1.5, 29.9, 29.9 - 1.5 * 1.3); ������� ������ ����
echo $lathe->thread(0, 17, 'M15x1.5');		# 0-17, �.�. ������ � ������ ������. �� 18 �� ������
echo $lathe->thread(31, 48, 'M15x1.5');

echo $lathe->end();
