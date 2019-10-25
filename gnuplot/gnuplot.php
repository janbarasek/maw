<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
Copyright 2009-2012 Robert Marik

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
$xmin = $_REQUEST["xmin"];
$xmax = $_REQUEST["xmax"];
$ymin = $_REQUEST["ymin"];
$ymax = $_REQUEST["ymax"];
$logbase = $_REQUEST["logbase"];
$naturallog = $_REQUEST["naturallog"];
$xlabel = $_REQUEST["xlabel"];
$ylabel = $_REQUEST["ylabel"];
$xvar = $_REQUEST["xvar"];
$yvar = $_REQUEST["yvar"];
$dummy = $_REQUEST["dummy"];
$border = $_REQUEST["border"];
$out = $_REQUEST["out"];
$mrizka = $_REQUEST["mrizka"];

if ($out == "") {
	$out = "png";
}
//$out="png";

check_for_security($fce . $xmin . $xmax . $ymin . $ymax . $naturallog . $logbase . $xvar . $yvar . $dummy . $border);

if ($naturallog == "0") {
	$logbasegnuplot = chop(formconv_gnuplot($logbase));
} else {
	$logbasegnuplot = "exp(1)";
}

function math_to_GNUplot($vyraz)
{
	global $logbasegnuplot, $formconv_bin;
	$uprfunkceGNU = `echo "$vyraz" | $formconv_bin -r -O gnuplot`;
	$uprfunkceGNU = chop($uprfunkceGNU);
	$uprfunkceGNU = str_replace("log(", "mylog($logbasegnuplot,", $uprfunkceGNU);
	$uprfunkceGNU = str_replace("sqrt", "mysqrt", $uprfunkceGNU);

	return ($uprfunkceGNU);
}

list($f1, $f2, $f3, $f4, $f5) = explode(',', $fce);
$funkce = math_to_GNUplot($f1);
if (($f2 != "")) {
	$funkce = $funkce . " linewidth 3," . math_to_GNUplot($f2);
}
if (($f3 != "")) {
	$funkce = $funkce . " linewidth 3," . math_to_GNUplot($f3);
}
if (($f4 != "")) {
	$funkce = $funkce . " linewidth 3," . math_to_GNUplot($f4);
}
if (($f5 != "")) {
	$funkce = $funkce . " linewidth 3," . math_to_GNUplot($f5);
}

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
if ($mrizka == "on") {
	fwrite($souborobr, "set grid\n");
}
fwrite($souborobr, "set xtics axis nomirror \n");
fwrite($souborobr, "set ytics axis nomirror \n");
fwrite($souborobr, "set samples 1000 \n");
if ($border == "") {
	fwrite($souborobr, "set noborder \n");
}
if ($out == "png") {
	fwrite($souborobr, "set term png transparent size 600,600\n");
	fwrite($souborobr, 'set output "graf.png"' . "\n");
}
if ($out == "svg") {
	fwrite($souborobr, "set term svg font 'Verdana,9' rounded solid\n");
	fwrite($souborobr, 'set output "graf.svg"' . "\n");
	//fwrite($souborobr,"jiggle(x) = x+($ymax-$ymin)*(2*(rand(0)-0.5)*0.005)\n");
}
//  fwrite($souborobr,"unset key \n");
fwrite($souborobr, "set xrange [" . math_to_GNUplot($xmin) . ":" . math_to_GNUplot($xmax) . "]\n");
fwrite($souborobr, "set yrange [" . $ymin . ":" . $ymax . "]\n");
if ($dummy != "") {
	fwrite($souborobr, "set dummy " . $dummy . "\n");
}
if ($xlabel != "") {
	fwrite($souborobr, "set xlabel \" " . $xlabel . "\"\n");
}
if ($ylabel != "") {
	fwrite($souborobr, "set ylabel \" " . $ylabel . "\"\n");
}
fwrite($souborobr, "set style function lines\n");
//  $funkcegnuplot=`$mawtimeout echo "$funkce" | $formconv_bin -r -O gnuplot`;
//  $funkcegnuplot=chop($funkcegnuplot);	
fwrite($souborobr, "plot " . $funkce . " linewidth 3\n");
fclose($souborobr);


/* Here we run all programms                                            */
/* ---------------------------------------------------------------------*/

system("cd $maw_tempdir;  gnuplot vstup; cp * /tmp");

if ($out == "png") {
	$file = $maw_tempdir . "/graf.png";
	header("Content-Type: image/png");
	header("Content-Disposition: attachment; filename=" . basename($file) . ";");
	header("Content-Transfer-Encoding: binary");
	readfile($file);
}
if ($out == "svg") {
	$file = $maw_tempdir . "/graf.svg";
	header('Content-Type: image/svg+xml');
	header("Content-Disposition: attachment; filename=" . basename($file) . ";");
	readfile($file);
}
//echo("<img src=\"file://$maw_tempdir/obrazek.png\"></img>");

$datcasip = "funkce: $fce, format: $out ";

system("rm -r " . $maw_tempdir);

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

save_log($datcasip, "gnuplot");

?>



