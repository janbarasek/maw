<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2013 Robert Marik

This file is part of Mathematical Assistant on Web.

Mathematical Assistant on Web is free software: you can
redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software
Foundation, either version 3 of the License, or
(at your option) any later version.

Mathematical Assistant on Web is distributed in the hope that it
will be useful, but WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Mathematical Assistant o Web.  If not, see 
<http://www.gnu.org/licenses/>.
*/


$scriptname = "gnuplot";

require("../common/maw.php");

$fce = rawurldecode($_REQUEST["funkce"]);
$xmin = $_REQUEST["a"];
$xmax = $_REQUEST["b"];
$mean = $_REQUEST["mean"];
$logbase = "e";

check_for_security($fce . $xmin . $xmax . $mean);

function math_to_GNUplot($vyraz)
{
	global $logbasegnuplot, $formconv_bin;
	$uprfunkceGNU = `echo "$vyraz" | $formconv_bin -r -O gnuplot`;
	$uprfunkceGNU = chop($uprfunkceGNU);

	$uprfunkceGNU = str_replace("sqrt", "mysqrt", $uprfunkceGNU);

	return ($uprfunkceGNU);
}

$f1 = math_to_GNUplot($fce);
$f2 = math_to_GNUplot($mean);
$f2 = $mean;

/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir = "/tmp/MAW_gnuplot" . getmypid();
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

/* Command for gnuplot                                                  */
/* ---------------------------------------------------------------------*/
define("NAZEV_SOUBORU_OBR", $maw_tempdir . "/vstup");
$souborobr = fopen(NAZEV_SOUBORU_OBR, "w");

fwrite($souborobr, "mylog(a,b)=(b>0)? log(b)/log(a):0/0\n");
fwrite($souborobr, "mysqrt(x)=(x>=0)? sqrt(x):0/0 \n");
fwrite($souborobr, "set zeroaxis lt -1 \nunset key\n");
fwrite($souborobr, "set xtics axis nomirror \n");
fwrite($souborobr, "set ytics axis nomirror \n");
fwrite($souborobr, 'set style fill pattern 4 bo' . "\n");
fwrite($souborobr, "set samples 100 \n");
if ($border == "") {
	fwrite($souborobr, "set noborder \n");
}
fwrite($souborobr, "set xrange [" . math_to_GNUplot($xmin) . ":" . math_to_GNUplot($xmax) . "]\n");
fwrite($souborobr, "set table 'a.dat'\nplot " . $f1 . "\nmin(a,b) = (a < b) ? a : b\nstats 'a.dat' u 1:2 nooutput\nunset table\n");
fwrite($souborobr, "set term svg font 'Verdana,9' rounded solid\n");
fwrite($souborobr, 'set output "graf.svg"' . "\n");


fwrite($souborobr, "set yrange [min(0,STATS_min_y):]\n");


fwrite($souborobr, "set style function lines\n");


fwrite($souborobr, "plot 'a.dat' with filledcurves x1 , " . $f2 . " with filledcurves x1 \n");
fclose($souborobr);


/* Here we run all programms                                            */
/* ---------------------------------------------------------------------*/

system("cd $maw_tempdir;  gnuplot vstup; cp * /tmp ");

$file = $maw_tempdir . "/graf.svg";
header('Content-Type: image/svg+xml');
header("Content-Disposition: attachment; filename=" . basename($file) . ";");
readfile($file);

$datcasip = "funkce: $fce, format: $out ";

system("rm -r " . $maw_tempdir);

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

save_log($datcasip, "definiteimg");

?>



