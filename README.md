# lathe4d.php

CNC 4D (rotary axis) pseudo-lathe G-Code generator

**DISCLAMER: U can use this library AS IS without any type garanty. Produced G-Code could damage your cutter, CNC machine or whole Earth! Use it only on your risk!**

This package give G-Code generator ability to use your 4 axis CNC milling machine as some kind of lathe machine.

At moment we have only russian description. So use translator if needed. Sorry. Will fix it ASAP.

----

(Russian below)

**ВНИМАНИЕ!
Сгенерённый этой библиотекой g-code может уничтожить заготовку, фрезу, станок и весь земной шар. Использовать на свой страх и риск (as is). Снимаю с себя всякую ответственность за возможный причинённый ущерб от использования этого софта.**

Генератор G-кода для ЧПУ фрезерного станка с поворотной осью. 
Проект в начальной стадии разработки. Доделывается по мере возникновения у меня новых потребностей "токарки".
Подробное описание в вики проекта: https://github.com/oleg-milantiev/lathe4d.php/wiki

Написан на PHP. Я его запускаю на винде, генерю G-Code, тот идёт в Mach3 на станке.
