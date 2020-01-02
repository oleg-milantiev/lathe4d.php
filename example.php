<?php

include "lathe4d.php";

$lathe = new Lathe4d();

$lathe->setBlank(
	new Blank(36)			# çàãîòîâêà D 36, L íå çàäàí (è ïîêà íå èñïîëüçóåòñÿ)
							# X,Y [0,0] â öåíòğå áëèç ïàòğîíà. Z [0] íà îñè öèëèíäğà
);
$lathe->setSafe(10);		# Áåçîïàñíàÿ âûñîòà 10ìì

$cutter = new cutter();
$cutter->setDiameter(6);	# Äèàìåòğ ôğåçû 6ìì
$cutter->setPassDepth(3);	# Çàãëóáëåíèå çà ğàç 3ìì
$cutter->setStepover(0.8);	# 80% øàã ïî Y îò äèàìåòğà ôğåçû
$cutter->setFeed(1400);		# Ïîäà÷à 1400 ìì/ìèí
$cutter->setName('6ìì êîíöåâàÿ òğ¸õï¸ğàÿ ïî äşğàëş');		# Èìÿ ôğåçû

echo $lathe->start();		# Íà÷àëüíûå g-code êîìàíäû

echo $lathe->setCutter($cutter);

# òî÷èì íàğóæíèé öèëèíäğ îò D36 äî D17 ñ 0 ïî 48 ïî Y
echo $lathe->cylinder(0, 48, 36, 17);
# åù¸ ïàğà L 18 öèëèíäğîâ ïîä ğåçüáû Ì15 íà êîíöàõ
echo $lathe->cylinder(0, 18, 17, 14.9);
echo $lathe->cylinder(30, 48, 17, 14.9);

# ğåæåì M15x1.5 ğåçüáû ãğàâ¸ğîì. Íåòîğîïÿñü
$engraver = new cutter();
$engraver->setPassDepth(0.1);
$engraver->setFeed(1400);
$engraver->setName('Ãğàâ¸ğ 60°');		# Èìÿ ôğåçû

echo $lathe->setCutter($engraver);

#echo $lathe->thread(0, 17, 1.5, 29.9, 29.9 - 1.5 * 1.3); ñèíîíèì ñòğîêè íèæå
echo $lathe->thread(0, 17, 'M15x1.5');		# 0-17, ò.ê. ïîìíèì î øèğèíå ãğàâ¸ğà. Äî 18 íå äîâåä¸ò
echo $lathe->thread(31, 48, 'M15x1.5');

echo $lathe->end();
