( File created by Lathe4d.php: https://github.com/oleg-milantiev/lathe4d.php/wiki )
( 08-01-2020 19:08 )
G00G21G17G90G40G49G80
G71G91.1
S18000M3
G94
G0 Z24.5
T1
( Current cutter: 6mm endmill 3-fluite alluminium )
M6      (Tool change.)

( Cylinder Y[30..48] D[15.2..15.1]=R[7.6..7.55] )
G0 A0 X0 Y33
G1 Z7.6 F1400
G1 Z7.55 A10
G1 A370
G1 Y45 A1270
G1 A1630
G0 Z24.5
G0 A1800
( time estimate: 00:01:17 )
G92 A 0			( [rev=5] )
M0      (Temporary machine stop.)

( Cylinder Y[30..48] D[15.1..15]=R[7.55..7.5] )
G0 A0 X0 Y33
G1 Z7.55 F1400
G1 Z7.5 A10
G1 A370
G1 Y45 A1270
G1 A1630
G0 Z24.5
G0 A1800
( time estimate: 00:01:17 )
G92 A 0			( [rev=5] )
M0      (Temporary machine stop.)

( Cylinder Y[30..48] D[15..14.95]=R[7.5..7.475] )
G0 A0 X0 Y33
G1 Z7.5 F1400
G1 Z7.475 A10
G1 A370
G1 Y45 A1270
G1 A1630
G0 Z24.5
G0 A1800
( time estimate: 00:01:17 )
G92 A 0			( [rev=5] )
M0      (Temporary machine stop.)

( Cylinder Y[30..48] D[14.95..14.9]=R[7.475..7.45] )
G0 A0 X0 Y33
G1 Z7.475 F1400
G1 Z7.45 A10
G1 A370
G1 Y45 A1270
G1 A1630
G0 Z24.5
G0 A1800
( time estimate: 00:01:17 )
G92 A 0			( [rev=5] )
M09
M30
