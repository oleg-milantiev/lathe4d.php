( File created by Lathe4d.php )
( 05-01-2020 23:47 )
G00G21G17G90G40G49G80
G71G91.1
S18000M3
G94
G0 Z28
T1
(Current cutter 6mm endmill 3fluite alluminium)
M6      (Tool change.)
( CutRight Y[40] D[36..0]=R[18..0] )
G0 A0 Y43
G1 Z18 F1400
G1 A2160 Z0
G1 A2520
G0 Z28
G92 A 0
( Cylinder Y[0..10] D[36..34.565]=R[18..17.2825] )
G0 A0 X0 Y3
G1 Z18 F1400
G1 Z17.2825 A10
G1 A370
G1 Y7 A670
G1 A1030
G0 Z28
G0 A1080
G92 A 0
( Hexagon Y[0..10] D[34.565..31]=R[17.2825..15.5] )
( Hexagon - Face #1 )
( Hexagon - Face #1 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A60
( Hexagon - Face #2 )
( Hexagon - Face #2 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A120
( Hexagon - Face #3 )
( Hexagon - Face #3 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A180
( Hexagon - Face #4 )
( Hexagon - Face #4 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A240
( Hexagon - Face #5 )
( Hexagon - Face #5 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A300
( Hexagon - Face #6 )
( Hexagon - Face #6 - Square X[-7.6442662335897..7.6442662335897] Y[0..10] Z[15.5] )
G0 X10.64426623359 Y3
G1 Z15.5 F1400
G1 Y3
G1 X-7.6442662335897
G1 Y7
G1 X7.6442662335897
G0 Z28
G0 A360
G92 A 0
( Cylinder Y[10..40] D[36..29.9]=R[18..14.95] )
G0 A0 X0 Y13
G1 Z18 F1400
G1 Z15 A10
G1 A370
G1 Y37 A2170
G1 A2530
G1 Z14.95 A2540
G1 A2900
G1 Y13 A4700
G1 A5060
G0 Z28
G0 A5400
G92 A 0
( Thread Y[13..37 by 8] D[29.9..19.5]=R[14.95..9.75] )
G0 A10 X0 Y13
G1 Z14.95 F1400
G1 Z11.95 A0
G1 A-360
G1 Y37 A-1440
G1 A-1810
G1 Z9.75 A-1800
G1 A-1440
G1 Y13 A-360
G1 A0
G0 Z28
( CutLeft Y[0] D[36..0]=R[18..0] )
G0 A0 Y-3
G1 Z18 F1400
G1 A2160 Z0
G1 A2520
G0 Z28
G92 A 0
M09
M30
