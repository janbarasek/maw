<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
Copyright 2009-2013 Robert Marik

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


$scriptname = "graf";

require("../common/maw.php");

mendelu_detect();
$variables = 'x';

$fce = $_REQUEST["funkce"];
$xmin = check_decimal($_REQUEST["xmin"]);
$xmax = check_decimal($_REQUEST["xmax"]);
$ymin = check_decimal($_REQUEST["ymin"]);
$ymax = check_decimal($_REQUEST["ymax"]);
$out = $_REQUEST["out"];
$mrizka = $_REQUEST["mrizka"];

$logbase = $_REQUEST["logbase"];
$naturallog = $_REQUEST["naturallog"];

if (($out != "gif") and ($out != "pdf") and ($out != "png") and ($out != "svg")) {
	$out = "html";
}

if (($out != "png") and ($out != "svg")) {
	check_last_call();
}

function get_server_load()
{
	$load = explode(' ', `uptime`);

	return $load[count($load) - 3];
}

if (($out == "gif") or ($out == "pdf")) {
	$load = str_replace(",", "", get_server_load());
	if ($load > 1) {
		maw_html_head();
		echo "<h2 class='red'>" . __("Server load too large") . "</h2>";
		echo sprintf(__("Building animations is available only, if the server load in the last minute is smaller than %s. The current load %s. Return to the previous page and try it later, please. (Or reload the page after 1 minute.)"), 1, $load);
		save_log("server overladed: " . $load . " ", "overloaded_error");
		die();
	}
}


if (($out == "gif") || ($out == "pdf")) {
	if (file_exists("/tmp/maw_lock")) {
		maw_html_head();
		echo "\n<h2 class='red';>" . __("Too many requests") . "</h2>";
		echo sprintf(__("Server refused to build more animations simultaneously.") . " ");
		echo sprintf("\n<li>" . __("This message appears if somebody else is working with the same calculator - try again after 1 min.") . " ");
		echo sprintf(__("You can check is somebody else is working with the tool by checking %s history%s."), "<a href=\"$mawphphome/common/tail.php?dir=posun-grafu\">", "</a>");
		echo sprintf("\n<li>" . __("<b>This message usually appears also if you click link more times than once.</b>"));
		echo sprintf("\n<li><b>" . __("Wait a bit, return to the page with calculator form, fill again if necessary and resend the form. Then you will be allowed to build animation again. But click only once and be patient (about 20 sec)!") . "<b>");
		system("rm -r " . $maw_tempdir);

		$datcasip = "funkce: $fce, $xmin..$xmax, $ymin..$ymax, $out";
		save_log($datcasip, "posun-grafu-odmitnuto");
		die();
	}
}

system("touch /tmp/maw_lock");

check_for_security("$fce, $xmin, $xmax, $ymin, $ymax");

$kontrola = input_to_maxima($fce);
$fcenew = input_to_maxima($fce);
$fce = $fcenew;

if ((stristr($fcenew, 'log')) and (preg_match('~[^0-9/.]~', $logbase)) and ($naturallog == "0")) {
	maw_html_head();
	echo __("If you use another logarithm than natural, use logarithm with integer base.");
	die();
}


if ((stristr($fcenew, 'log')) and ($naturallog == "0")) {
	$logbasetex = formconv($logbase);
	$logbasegnuplot = chop(formconv_gnuplot($logbase));
}

if ($naturallog == "1") {
	$logbase = "exp(1)";
	$logbasegnuplot = "exp(1)";
}

if ($out == "html") {
	maw_html_head();


}

$vystup = `$mawtimeout $maxima --batch-string="fcenew:$fcenew;load(\"/var/www/maw/graf/graf.mac\");"`;

$vystupkopie = $vystup;
$vystup = str_replace("\n", "", $vystup);
$vystup = str_replace("##", "\n##", $vystup);
$vystup = str_replace("(%", "\n(%", $vystup);
$vystup = str_replace("\"\n#", "\"#", $vystup);
$vystup = str_replace("\"###", "\"##a#", $vystup);
$vystup = preg_replace("~(\(%i1.*\(%i2)~", "%i2", $vystup);


$vystupk = $vystup;
$vystupk = str_replace(" ", "", $vystupk);

if ($out == "html") {
	$printfunction = formconv($fcenew);

	if ((stristr($fcenew, 'log')) and ($naturallog == "0")) {
		$printfunction = str_replace("\\ln", "\\log_{{$logbasetex}}", $printfunction);
	}

	printf("<h4>%s : %s</h4>", __("Function grapher"), "\\( y=$printfunction\\)");

	$picture = "/maw/gnuplot/gnuplot.php?out=svg&funkce=" . rawurlencode($fce) . "&xmin=" . rawurlencode($xmin) . "&xmax=" . rawurlencode($xmax) . "&ymin=" . rawurlencode($ymin) . "&ymax=" . rawurlencode($ymax) . "&naturallog=" . $naturallog . "&mrizka=$mrizka&logbase=" . $logbase;
	echo "<div class=center><img class=centerimg alt=\"Loading ...\" src=\"" . $picture . "\"></div>";
}

if (!((stristr($vystupk, "kontrola10")) and (stristr($vystupk, "kontrola2[[0,0],[0,0]]")))) {


	die();
}

$deformace = 0;
if (stristr($vystupk, "nasobek")) {
	$deformace = 1;
}

$svas = "";
$vas = "";
$stylas = " lt -1";
if (preg_match("~##(operace *ln|operace: *log|deleni)~", $vystupk)) {
	preg_match("/### vodorovny posun (.*)/", $vystup, $matches);
	$svas = $matches[0];
	$svas = str_replace("### vodorovny posun ", "", $svas);
	$bodsvas = "-(1.0*(" . formconv_gnuplot($svas) . "))";
	$svas = "set arrow 1 from 1.0*$bodsvas,graph 0 to 1.0*$bodsvas,graph 1 nohead $stylas\n";
}

if (preg_match("~##(deleni|exponenciela|operace: *sin|operace: *cos)~", $vystupk)) {
	preg_match("/### svisly posun (.*)/", $vystup, $matches);
	$vas = $matches[0];
	$vas = str_replace("### svisly posun ", "", $vas);
	$bodas = formconv_gnuplot($vas);
	$vas = "set arrow 2 from graph 0,first 1.0*$bodas to graph 1, first 1.0*$bodas nohead $stylas\n";
}

function najdiretezec($klicoveslovo, $retezec)
{
	preg_match("/\#\#\# *" . $klicoveslovo . " (.*)/", $retezec, $matches);
	$vystup = $matches[0];
	$vystup = str_replace("### " . $klicoveslovo, "", $vystup);

	return ($vystup);
}

function najdiretezectex($klicoveslovo, $retezec)
{
	global $logbase, $logbasetex;
	$vystup = najdiretezec($klicoveslovo, $retezec);
	$vystup = str_replace("$", "", $vystup);
	$vystup = str_replace("log", "ln", $vystup);
	if ($logbase != "exp(1)") {
		$vystup = str_replace("ln", "log_{{$logbasetex}}", $vystup);
	}

	return ($vystup);
}

function math_to_GNUplot($vyraz)
{
	global $logbasegnuplot, $formconv_bin;
	$uprfunkceGNU = `echo "$vyraz" | $formconv_bin -r -O gnuplot`;
	$uprfunkceGNU = chop($uprfunkceGNU);
	$uprfunkceGNU = str_replace("log(", "mylog($logbasegnuplot,", $uprfunkceGNU);

	return ($uprfunkceGNU);
}

$animaceposunu = najdiretezec("animace posunu", $vystup);
$animacedeformace = najdiretezec("animace deformace", $vystup);
$zaklfunkce = najdiretezec("zakladni funkce je", $vystup);
$uprfunkce = najdiretezec("budu kreslit", $vystup);
$nasobenafunkce = najdiretezec("budu posunovat", $vystup);
$vodpos = najdiretezec("vodorovny posun", $vystup);
$svpos = najdiretezec("svisly posun", $vystup);

$funkcetex = `echo "$uprfunkce" | $formconv_bin`;
$funkcetex = chop($funkcetex);
$funkcetexorig = `echo "$zaklfunkce" | $formconv_bin`;
$funkcetexorig = chop($funkcetexorig);
$funkcetexnas = `echo "$nasobenafunkce" | $formconv_bin`;
$funkcetexnas = chop($funkcetexnas);


$uprfunkceGNU = math_to_GNUplot($uprfunkce);
$zaklfunkceGNU = math_to_GNUplot($zaklfunkce);
$nasobenafunkceGNU = math_to_GNUplot($nasobenafunkce);
$animaceposunuGNU = math_to_GNUplot($animaceposunu);
$animacedeformaceGNU = math_to_GNUplot($animacedeformace);

function myformconv_replacements($vstup)
{
	global $logbase, $logbasetex;
	$vystup = formconv_replacements($vstup);
	if ($logbase != "exp(1)") {
		$vystup = str_replace("ln", "log_{{$logbasetex}}", $vystup);
	}

	return ($vystup);
}

if (($out == "html") and ($funkcetex != $funkcetexorig)) {
	echo "<div class=logickyBlok>";
	echo "<h4>" . __("The graph can be drawn by shifting and resizing certain basic elementary function") . "</h4>";
	echo sprintf(__("We draw the function %s."), "&nbsp;&nbsp;\$ y=" . myformconv_replacements($funkcetex) . "\$&nbsp;&nbsp;") . '<br>';


	echo '<ul><li>' . sprintf(__("We start with the function %s."), "&nbsp;&nbsp;\$y=" . myformconv_replacements($funkcetexorig) . "\$&nbsp;&nbsp;") . '</li>';

	if (($vodpos == 0) && ($svpos == 0)) {
		$style = __("bold");
	} else {
		$style = __("red");
	}
	if ($deformace) {
		echo '<li>' . sprintf(__("Graph deformation: we draw function %s."), "&nbsp;&nbsp;\$y=" . myformconv_replacements($funkcetexnas) . "\$&nbsp;&nbsp;", $style) . '</li>';
	}

	echo '<li>' . sprintf(__("Shift: horizontally %s units and vertically  %s units (positive values mean move to the left or top, negative to the right or bottom)"), $vodpos, $svpos) . "</li>";


	echo '</ul>';
	echo '<div>';
}


$maw_tempdir = "/tmp/MAW_graf" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

define("NAZEV_SOUBORU_OBR", $maw_tempdir . "/vstup");
$souborobr = fopen(NAZEV_SOUBORU_OBR, "w");

fwrite($souborobr, "set zeroaxis lt -1 \n");

if ($mrizka == "on") {
	fwrite($souborobr, "set grid\n");
}
fwrite($souborobr, "set xtics axis nomirror\n");
fwrite($souborobr, "set ytics axis nomirror\n");
fwrite($souborobr, "mylog(a,b)=log(b)/log(a)\n");
fwrite($souborobr, "set samples 1000 \n");
fwrite($souborobr, "unset key\nset noborder \n");
if ($out == "png") {
	fwrite($souborobr, "set term png transparent size 800,500\n");
	fwrite($souborobr, 'set output "graf.png"' . "\n");
}
if ($out == "svg") {
	fwrite($souborobr, "set term svg\n");
	fwrite($souborobr, 'set output "graf.svg"' . "\n");
}
fwrite($souborobr, $svas . "\n");
fwrite($souborobr, $vas . "\n");
fwrite($souborobr, "set xrange [" . $xmin . ":" . $xmax . "]\n");
fwrite($souborobr, "set yrange [" . $ymin . ":" . $ymax . "]\n");
fwrite($souborobr, "set style function lines\n");

fwrite($souborobr, "plot $uprfunkceGNU linewidth 3 \n");

if ($vas != "") {
	fwrite($souborobr, "unset arrow 2\n");
}
if ($svas != "") {
	fwrite($souborobr, "unset arrow 1\n");
}

if (($out == "gif") or ($out == "pdf")) {
	$plot = "plot $zaklfunkceGNU";
	fwrite($souborobr, "unset key\n");
	if ($out == "gif") {
		fwrite($souborobr, "set term gif notransparent size 500,400\n");

		$pripona = "gif";
		$styl = "";
		$stylas = " lt -1";
		$konec = 20;
	} else {
		fwrite($souborobr, "set term postscript eps color solid \n");
		$pripona = "eps";
		$styl = " linewidth 5 ";
		$stylas = " lt -1 linewidth 0.1";
		$plot = $plot . $styl;
		$konec = 0;
	}
	$j = 0;

	$oldfunction = najdiretezectex("t0", $vystup);
	$texcommands = "\\nadpis{" . sprintf(__("The original function is %s."), "\$" . $oldfunction . "\$") . "}";
	$headline = "{" . $oldfunction . "} ";
	$cisloanimace = 0;

	for ($i = 0; $i <= $konec; $i++) {
		if ($out == gif) {
			fwrite($souborobr, "set title \"" . __("- Original function -") . "\"\n");
		}
		if ($j < 10) {
			$jj = "00" . $j;
		} elseif ($j < 100) {
			$jj = "0" . $j;
		} else {
			$jj = $j;
		}
		fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
		fwrite($souborobr, "$plot \n");
		$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
		$j = $j + 1;
	}

	$animace = najdiretezec("ADefX", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t1", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Transforming %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 20; $i++) {
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("- Horizontal deformation -") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$plotb = str_replace("krok", "(0.05*$i)", $plotb);
			fwrite($souborobr, "$plot ,$plotb \n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$plot = $plot . "," . $plotb;
	}

	$animace = najdiretezec("ADefY", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t2", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Transforming %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 20; $i++) {
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("- Vertical deformation -") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$plotb = str_replace("krok", "(0.05*$i)", $plotb);
			fwrite($souborobr, "$plot , $plotb \n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$plot = $plot . "," . $plotb;
	}

	$animace = najdiretezec("AZrcX", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t3", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Turning %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 21; $i++) {
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("- Turning about y -") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$iii = floor($i / 3);
			$plotb = str_replace("krok", "((-1)**$iii)", $plotb);
			fwrite($souborobr, "$plot, $plotb \n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$i = 1;
		$plotb = math_to_GNUplot($animace) . $styl;
		$plotb = str_replace("krok", "((-1)**$i)", $plotb);
		$plot = $plot . "," . $plotb;
	}

	$animace = najdiretezec("AZrcY", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t4", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Turning %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 21; $i++) {
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("- Turning about x -") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$iii = floor($i / 3);
			$plotb = str_replace("krok", "((-1)**$iii)", $plotb);
			fwrite($souborobr, "$plot, $plotb \n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$i = 1;
		$plotb = math_to_GNUplot($animace) . $styl;
		$plotb = str_replace("krok", "((-1)**$i)", $plotb);
		$plot = $plot . "," . $plotb;
	}

	$animace = najdiretezec("APosX", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t5", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Shifting %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 20; $i++) {
			if ($svas != "") {
				if ($i > 0) {
					fwrite($souborobr, "unset arrow 1\n");
				}
				$svas = "set arrow 1 from 1.0*0.05*$i*$bodsvas,graph 0 to 1.0*0.05*$i*$bodsvas,graph 1 nohead $stylas\n";
				fwrite($souborobr, $svas);
			}
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("- Horizontal shift -") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$plotb = str_replace("krok", "(0.05*$i)", $plotb);
			fwrite($souborobr, "$plot, $plotb \n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$plot = $plot . "," . $plotb;
	}

	$animace = najdiretezec("APosY", $vystup);

	if ($animace != "") {
		$newfunction = najdiretezectex("t6", $vystup);
		$headline = $headline . " \\to {" . $newfunction . "} ";
		$texcommands = $texcommands . "\\nadpis{" . sprintf(__("Shifting %s into %s."), "\$" . $oldfunction . "\$ ", "\$" . $newfunction . "\$") . "}";
		$oldfunction = $newfunction;
		for ($i = 0; $i <= 20; $i++) {
			if ($vas != "") {
				if ($i > 0) {
					fwrite($souborobr, "unset arrow 2\n");
				}
				$vas = "set arrow 2 from graph 0,first 1.0*$bodas*0.05*$i to graph 1, first 1.0*$bodas*0.05*$i nohead $stylas\n";
				fwrite($souborobr, $vas);
			}
			if ($out == gif) {
				fwrite($souborobr, "set title \"" . __("Vertical shift") . "\"\n");
			}
			if ($j < 10) {
				$jj = "00" . $j;
			} elseif ($j < 100) {
				$jj = "0" . $j;
			} else {
				$jj = $j;
			}
			fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
			$plotb = math_to_GNUplot($animace) . $styl;
			$plotb = str_replace("krok", "(0.05*$i)", $plotb);
			fwrite($souborobr, "$plot, $plotb\n");
			$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
			$j = $j + 1;
		}
		$plot = $plot . "," . $plotb;
	}


	for ($i = 0; $i <= $konec; $i++) {
		$newfunction = najdiretezectex("t7", $vystup);
		$oldfunction = najdiretezectex("t0", $vystup);
		$tempstr = __("The function %s has been transformed into %s.");
		$texcommands = $texcommands . "\\nadpis{" . sprintf($tempstr, "\$" . $oldfunction . "\$", "\$" . $newfunction . "\$") . "}";

		if ($out == gif) {
			fwrite($souborobr, "set title \"" . __("Final result") . "\"\n");
		}
		if ($j < 10) {
			$jj = "00" . $j;
		} elseif ($j < 100) {
			$jj = "0" . $j;
		} else {
			$jj = $j;
		}
		fwrite($souborobr, 'set output "graf' . $jj . '.' . $pripona . '"' . "\n");
		$plot = preg_replace("~linewidth 5 *$~", "", $plot);
		if ($out == "gif") {
			fwrite($souborobr, "$plot linewidth 3\n");
		} else {
			fwrite($souborobr, "$plot linewidth 10\n");
		}
		$texcommands = $texcommands . "\\obrazek{graf" . $jj . ".eps}\n";
		$j = $j + 1;
	}
	$plot = $plot . "," . $plotb;
}

fclose($souborobr);

$jmeno = getmypid();


if ($out == "gif") {
	system("cd $maw_tempdir; gnuplot vstup; gifsicle --loopcount graf*.gif> animace.gif");
	header("Content-Type: image/gif");
	readfile($maw_tempdir . "/animace.gif");
}

if ($out == "pdf") {
	define("NAZEV_SOUBORU_TEX", $maw_tempdir . "/graf.tex");
	$soubortex = fopen(NAZEV_SOUBORU_TEX, "w");
	$TeXfile = '\documentclass{article}
\usepackage{graphicx}

\usepackage[dvips]{web}
\screensize{10cm}{14cm}
\margins{0pt}{0pt}{10pt}{0pt}

\let\to\Rightarrow
' . $TeX_language . '
\def\nadpis#1{\def\nadpisA{#1}}
\def\thepage{}
\nadpis{}
\def\obrazek#1{\setbox0=\vbox{\begin{center}
    $\postup$ 
    \par\nadpisA
    \par\includegraphics{#1}
  \end{center}
}
\ifdim\ht0>\vsize
\resizebox{!}{0.98\vsize}{\box0}
\else
\box0
\fi
\newpage}
\let\rmdefault\sfdefault
\def\it{\rm}

\begin{document}
';
	fwrite($soubortex, $TeXfile . "\\def\postup{" . $headline . "}");
	fwrite($soubortex, $texcommands . "\n\\end{document}");
	fclose($soubortex);

	$TeXfileA = '
\usepackage[pdftex]{web}
\margins{.25in}{.15in}{14pt}{.15in} % left,right,top, bottom
\screensize{12cm}{15cm} % height, width

\pagestyle{empty}
\usepackage{graphicx}

\usepackage{multido}
\usepackage{pdfanim}
\input pdfanim-patch
\def\prvni{}
\def\posledni{}' . "\\def\\prvni{" . najdiretezectex("t0", $vystup) . "}" . "\\def\\posledni{" . najdiretezectex("t7", $vystup) . "}" . '

\immediate\pdfximage {graf.pdf}%
\edef\pocet{\the\pdflastximagepages}

\multido{\i=1+1}{\pocet}{%\immediate
  \pdfximage page \i{graf.pdf}
  \expandafter\xdef\csname pdf:trac\i\endcsname{\the\pdflastximage}} 

\PDFAnimLoad[]{a}{graf}{\pocet}

\usepackage[pdftex]{eforms}

\rightskip=0pt plus 1 fill
\let\rmdefault\sfdefault
\everymath{\displaystyle}
\parindent 0 pt';

	$TeXtempa = "";
	$TeXtempb = "";
	if ($lang == "zh") {
		$TeXtempa = '\begin{CJK*}{UTF8}{gbsn}';
		$TeXtempb = '\newpage\end{CJK*}';
	}
	$TeXfileB = '
\begin{document}
' . $TeXtempa . '
\MAWhead{%s}

%s

%s

\newpage

\begin{center}
  \PDFAnimation{a}
  \PDFAnimButtons[\BG{0.612 0.902 0.953} \BC{} ]{a}
\end{center}

' . $TeXtempb . '

\end{document}
';

	$soubortex = fopen("$maw_tempdir/animace.tex", "w");
	$tempstr = __("On the next page you can find an animation which describes, how the graph of %s is transformed into %s.");
	$tempstr = sprintf($tempstr, "$\prvni$", "$\posledni$");
	if ($lang == "zh") {
		$TeXheader = str_replace("\\AtBegin", "%\\AtBegin", $TeXheader);
	}
	fwrite($soubortex, str_replace($TeXhyperref, "", $TeXheader) . $TeXfileA . sprintf($TeXfileB, __("Graphs of basic elementary functions"), $tempstr, __("You have to use the PDF file in Adobe Reader or Adobe Acrobat (do not use xpdf, kpdf and other PDF viewers).")));
	fclose($soubortex);

	system("cd $maw_tempdir; cp $mawhome/graf/pdfanim.sty .; cp $mawhome/graf/pdfanim-patch.tex .; gnuplot vstup; $latex graf >> output; $dvips graf  >> output; $ps2pdf graf.ps graf.pdf >> output; $pdflatex animace.tex >> output");
	send_PDF_file_to_browser($maw_tempdir . "/animace.pdf");
}

$datcasip = "funkce: $fce, $xmin..$xmax, $ymin..$ymax, $out";
system("rm -r " . $maw_tempdir);
system("rm /tmp/maw_lock");
save_log($datcasip, "posun-grafu");

?>
