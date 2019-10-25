<?php

/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova

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

$scriptname = "solid";


require("../common/maw.php");


$maw_tempdir = "/tmp/MAW_solid" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

$fcef = $_REQUEST["fcef"];
$fceg = $_REQUEST["fceg"];
$meza = $_REQUEST["meza"];
$mezb = $_REQUEST["mezb"];
$xmin = $_REQUEST["xmin"];
$xmax = $_REQUEST["xmax"];
$ymin = $_REQUEST["ymin"];
$ymax = $_REQUEST["ymax"];
$viewa = $_REQUEST["viewa"];
$viewb = $_REQUEST["viewb"];
$colors = $_REQUEST["colors"];
$hidden = $_REQUEST["hidden"];

if ($colors == "on") {
	$colorizegraph = "set pm3d";
}
if ($hidden == "on") {
	$hidden = "set hidden";
}

check_for_security($fcef . " " . $fceg . $xmin . $xmax . $meza . $mezb);


$fcef = input_to_maxima($fcef);
$fceg = input_to_maxima($fceg);

if ($xmin = "") {
	$xmin = input_to_maxima($xmin);
	$xmin = chop(formconv_gnuplot($xmin));
}

if ($xmax = "") {
	$xmax = input_to_maxima($xmax);
	$xmax = chop(formconv_gnuplot($xmax));
}

if ($ymin = "") {
	$ymin = input_to_maxima($ymin);
	$ymin = chop(formconv_gnuplot($ymin));
}

if ($ymax = "") {
	$ymax = input_to_maxima($ymax);
	$ymax = chop(formconv_gnuplot($ymax));
}

$meza = input_to_maxima($meza);
$mezb = input_to_maxima($mezb);


$meza = chop(formconv_gnuplot($meza));
$mezb = chop(formconv_gnuplot($mezb));


/* Command for gnuplot                                                  */
/* ---------------------------------------------------------------------*/
define("NAZEV_SOUBORU_OBR", $maw_tempdir . "/solid");
$souborobr = fopen(NAZEV_SOUBORU_OBR, "w");

fwrite($souborobr, "set isosamples 30 \n");
fwrite($souborobr, "set view $viewa," . "$viewb \n");

fwrite($souborobr, "unset title \n");
fwrite($souborobr, "unset key \n");
fwrite($souborobr, "set size square \n" . $colorizegraph . "\n");


fwrite($souborobr, "set term svg size 600,600 \n");
fwrite($souborobr, 'set output "solid.svg"' . "\n");


fwrite($souborobr, "set vrange [" . $meza . ":" . $mezb . "]\n");
fwrite($souborobr, "set urange [0:6.28]\n");
fwrite($souborobr, "$hidden\nset parametric\n");

$vystup = formconv_gnuplot($fcef);
$tempfcef = chop($vystup);
$tempfcef = str_replace("exp", "EXP", $tempfcef);
$tempfcef = str_replace("x", "v", $tempfcef);
$tempfcef = str_replace("EXP", "exp", $tempfcef);

$vystup = formconv_gnuplot($fceg);
$tempfceg = chop($vystup);
$tempfceg = str_replace("exp", "EXP", $tempfceg);
$tempfceg = str_replace("x", "v", $tempfceg);
$tempfceg = str_replace("EXP", "exp", $tempfceg);

fwrite($souborobr, "splot v,cos(u)*(" . $tempfceg . "),sin(u)*(" . $tempfceg . "),v,cos(u)*(" . $tempfcef . "),sin(u)*(" . $tempfcef . ") lc rgb \"blue\"\n");
fclose($souborobr);


/* Here we run all programms                                            */
/* ---------------------------------------------------------------------*/


$file = $maw_tempdir . "/solid.svg";
system("cd $maw_tempdir; gnuplot solid");


header("Content-Type: image/svg+xml");
header("Content-Disposition: attachment; filename=" . basename($file) . ";");
readfile($file);

$datcasip = "funkce: $fce, ";

system("rm -r " . $maw_tempdir);

?>

