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

$scriptname = "ineq2d";

require("../common/maw.php");

$nerovnice = $_REQUEST["nerovnice"];
$akce = $_REQUEST["akce"];
$funkce = $_REQUEST["funkce"];
$podmnez = $_REQUEST["podmnez"];
$onevar = $_REQUEST["onevar"];
$axislabels = $_REQUEST["axislabels"];
$pdfformat = $_REQUEST["pdf"];


$xmin = $_REQUEST["xmin"];
$xmax = $_REQUEST["xmax"];
$ymin = $_REQUEST["ymin"];
$ymax = $_REQUEST["ymax"];

check_for_security("$nerovnice, $xmin, $xmax, $ymin, $ymax");


////// the output is a picture in png format
if ($akce == "1") {
	$maw_tempdir = "/tmp/MAW_ineq2d" . getmypid() . "xx" . RandomName(6);
	system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

	$fcef = $funkce;


	if (($podmnez == "on")) {
		$fcef = $fcef . "+1.231456*(sqrt(x))+1.4217681*sqrt(y)";
	}

	$funkcegnuplot = `echo "$fcef" | $formconv_bin -r -O gnuplot`;
	$funkcegnuplot = chop($funkcegnuplot);

	define("NAZEV_SOUBORU", $maw_tempdir . "/vstup");
	$soubor = fopen(NAZEV_SOUBORU, "w");
	if ($onevar == 1) {
		fwrite($soubor, "unset key\n unset ztics\n unset ytics\nset terminal png size 600,70 transparent\n set output \"obrazek.png\"\n set isosample 500\n set xrange [$xmin:$xmax] \n set yrange [0:1]\n set view 0,0\n splot $funkcegnuplot, 'a' with dots\n");
	} else {
		fwrite($soubor, "set zeroaxis\n unset key\nunset ztics\n");
		if ($axislabels == "on") {
			fwrite($soubor, "unset border\n unset ytics\n unset xtics\nset zeroaxis lt -1\nset tics axis\n unset ztics\n set ytics offset graph 0-0.11,0\n");
		}
		fwrite($soubor, "\nset terminal png size 600,600 transparent\n set output \"obrazek.png\"\n set isosample 500\n set xrange [$xmin:$xmax] \n set yrange [$ymin:$ymax]\n set view 0,0.00\n splot $funkcegnuplot, 'a' with dots \n");
	}
	fclose($soubor);

	$file = $maw_tempdir . "/obrazek.png";

	if ($onevar == 1) {
		system("cd $maw_tempdir; echo \"$xmin 0 0\">a; gnuplot vstup; convert -shave 100x10 obrazek.png obrazek.png");
	} else {
		system("cd $maw_tempdir; echo \"$xmin $ymin 0\">a; gnuplot vstup; convert -shave 100x100 obrazek.png obrazek.png");
	}

	if (filesize($file) == 0) {
		system("cp ../common/nosol-$lang.png $file");
	}


	header("Content-Type: image/png");
	header("Content-Disposition: attachment; filename=" . basename($file) . ";");
	header("Content-Transfer-Encoding: binary");
	readfile($file);
	system("rm -r " . $maw_tempdir);
	die();
}


///// test if the system is a system in one variable (x)
if (stristr($nerovnice, "y")) {
	$onevar = 0;
} else {
	$onevar = 1;
}

if ($onevar == 1) {
	$podmnez = "";
}

$errmsg_ineq = "";
$funkce = "8";
$fce_array = [];

///// the output is html or pdf page, we parse inequalities

function find_inequalities($nerovnice)
{
	global $errmsg_ineq;
	global $funkce, $fce_array;
	$nerovnice = str_replace("\n", ";", $nerovnice);
	$nerovnice = str_replace(" ", "", $nerovnice);
	$rozd_nerovnice = explode(";", $nerovnice);
	$pocet = count($rozd_nerovnice);

	$output = [];
	$numb_ineq = 0;
	for ($i = 0; $i <= $pocet; $i = $i + 1) {
		$a = 1;
		$b = 1;
		$ttemp = chop($rozd_nerovnice[$i]);
		$ner = preg_split("~>|<~", $ttemp);
		if (strlen($ner[1]) * strlen($ner[0]) > 0) {
			$a = input_to_maxima(chop($ner[0]));
			$b = input_to_maxima(chop($ner[1]));
			if (stristr($ttemp, ">")) {
				$funkce = $funkce . "+log(($a)-($b))";
				$tempfce = "log(($a)-($b))";
			} else {
				$funkce = $funkce . "+log(($b)-($a))";
				$tempfce = "log(($b)-($a))";
			}
			$fce_array[$numb_ineq] = $tempfce;
			$output[$numb_ineq] = $ttemp;
			$numb_ineq = $numb_ineq + 1;
		} elseif ($ner[0] . $ner[1] != "") {
			$errmsg_ineq = $errmsg_ineq . sprintf(__("Bad input in row %s"), ($i + 1)) . ": &nbsp;&nbsp;&nbsp; $ttemp<br>";
			if (stristr($ner[0] . $ner[1], "=")) {
				$errmsg_ineq = $errmsg_ineq . "&nbsp;&nbsp;&nbsp; " . __("Do not use equations. The application cannot draw such an input.") . "<br>";
			}
		}

	}

	return ($output);
}


$ineq = find_inequalities($nerovnice);

$numb_of_ineq = sizeof($ineq);

$textable = "";
if ($onevar == "1") {
	$textable = $textable . "\\def\\insertpicture#1{\\includegraphics[width=\\hsize, clip, bb = 50 0 310 50]{#1.pdf}}";
	//    $textable=$textable."\\def\\insertpicture#1{\\includegraphics[width=\\hsize]{#1.pdf}}";
}
$gnuplot_commands = "";
$gnuplot_all = "splot 8";


$gnuplot_file = "";
$command_after_gnuplot = "";

if ($onevar == 1) {
	$ymin = 0;
	$gnuplot_file = "unset key\n unset ztics\n unset ytics\nset term postscript eps color size 5in, 0.7in\n set isosample 500\n set xrange [$xmin:$xmax] \n set yrange [0:1]\n set view 0,0\n";
} else {
	if ($pdfformat == "on") {
		$square = $_REQUEST["square"];
		if ($square == "on") {
			$xlength = (($ymax) - ($ymin)) * 5 / 3.5;
			$xdiff = $xlength - ($xmax) + ($xmin);
			if ($xdiff > 0)  // axis x is short
			{
				$xmax = $xmax + ($xdiff / 2);
				$xmin = $xmin - ($xdiff / 2);
			} else  // axis y is short
			{
				$ylength = (($xmax) - ($xmin)) * 3.5 / 5;
				$ydiff = $ylength - ($ymax) + ($ymin);
				$ymax = $ymax + ($ydiff / 2);
				$ymin = $ymin - ($ydiff / 2);
			}
		}
	}
	$gnuplot_file = "set zeroaxis\n unset key\nunset ztics\n";
	if ($axislabels == "on") {
		$gnuplot_file = $gnuplot_file . "unset border\n unset ytics\n unset xtics\nset zeroaxis lt -1\nset tics axis\n unset ztics\n set ytics offset graph 0-0.11,0\n";
	}
	$gnuplot_file = $gnuplot_file . "\n set term postscript eps color \n  set isosample 500\n set xrange [$xmin:$xmax] \n set yrange [$ymin:$ymax]\n set view 0,0.00\n";
}


$allinequalitites = "";

foreach ($ineq as $i => $value) {
	$oneinequality = chop(formconv($value));
	if ($numb_of_ineq > 1) {
		$textable = $textable . " \\oneineq" . '{' . $oneinequality . "}" . '{graph' . $i . '}' . "\n";
	}
	if ($allinequalitites != "") {
		$allinequalitites = $allinequalitites . "\\\\" . $oneinequality;
	} else {
		$allinequalitites = $oneinequality;
	}
	$fcef = "$fce_array[$i]";

	if (($podmnez == "on")) {
		$fcef = $fcef . "+1.231456*(sqrt(x))+1.4217681*sqrt(y)";
	}
	$funkcegnuplot = `echo "$fcef" | $formconv_bin -r -O gnuplot`;
	$funkcegnuplot = chop($funkcegnuplot);
	if ($numb_of_ineq > 1) {
		$gnuplot_file = $gnuplot_file . "\n set output \"graph$i.eps\"\n splot $funkcegnuplot, 'a' with dots\n\n";
		$command_after_gnuplot = $command_after_gnuplot . "; $epstopdf graph$i.eps";
	}
	$gnuplot_all = $gnuplot_all . "+$funkcegnuplot";

}


$gnuplot_file = $gnuplot_file . "\nset output \"all.eps\"\n" . $gnuplot_all . ", 'a' with dots\n";


$all = $allinequalitites;

if ($errmsg_ineq != "") {
	maw_html_head();
	echo '<h2>';
	echo(__("Error in input data"));
	echo '</h2>';
	echo $errmsg_ineq;
	save_log_err($nerovnice, "ineq2d");
	$nerovniceB = str_replace(" ", "", $nerovnice);
	$nerovniceB = str_replace("x1", "x", $nerovniceB);
	$nerovniceB = str_replace("x2", "y", $nerovniceB);
	$nerovniceB = str_replace("≥", ">", $nerovniceB);
	$nerovniceB = str_replace("≤", "<", $nerovniceB);
	$nerovniceB = str_replace("x,y>0", "x>0\ny>0", $nerovniceB);
	$nerovniceB = preg_replace('/\n(.*,.*)\n/', "\n", $nerovniceB);
	$nerovniceB = preg_replace('/\n(.*,.*)/', "\n", $nerovniceB);
	$nerovniceB = preg_replace("~([0-9]) *(x|y)~", "\\1*\\2", $nerovniceB);
	$nerovniceB = str_replace("=", "<", $nerovniceB);
	if ($nerovniceB != $nerovnice) {
		echo("<br><br>" . __("Can't guess, what you actually mean. You may try to enter the following inequalities"));
		echo '<pre>', $nerovniceB, '</pre>';
	}
	die(__("<br><b>Instructions</b><br>Use only sharp inequalities, one inequality per row and use variables <i>x</i> and <i>y</i>."));
}


$tex_all_ineq = "\\begin{cases}$all \\end{cases}";
$textable = $textable . "\\allineq{" . $tex_all_ineq . "}";

//echo '<pre>';


//echo $textable,$gnuplot_file;

if ($pdfformat == "on") {
	$maw_tempdir = "/tmp/MAW_ineq2d" . getmypid() . "xx" . RandomName(6);
	system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

	define("NAZEV_SOUBORUB", $maw_tempdir . "/pics");
	$soubor = fopen(NAZEV_SOUBORUB, "w");
	fwrite($soubor, $gnuplot_file);
	fclose($soubor);

	$soubor = fopen("$maw_tempdir/inequality.tex", "w");
	$TeXfile = '
\fboxsep=3pt
\usepackage{multicol}
\usepackage{graphicx}
\parindent 0 pt


\relpenalty =10000         
\binoppenalty =10000
\exhyphenpenalty =1000
                      
\def\OpakujZnak #1#2{\mathchardef #2=\mathcode`#1
  \activedef #1{#2\nobreak\discretionary{}{\hbox{\qquad$#2$}}{}}
  \uccode`\~=0 \mathcode`#1="8000 }
\def\activedef #1{\uccode`\~=`#1 \uppercase{\def~}}

\def\noneg{}
\newif\iflast
\lastfalse
\newcount \mycount
\def\oneineq#1#2{\global\advance\mycount by 1 \par \fbox{\vbox{
    \begin{center} \iflast \textbf{' . __("Solution") . '} \else \textbf{' . sprintf(__("Inequality %s"), '\the\mycount') . ': }\fbox{$#1$}\fi
\par\includegraphics[width=\hsize %s]{#2.pdf}
\end{center}
}}}

\def\allineq#1{\lasttrue\oneineq{#1}{all}}
 
\begin{document}

\OpakujZnak ={\eqORI} 
\OpakujZnak +{\plusORI}
\OpakujZnak -{\minusORI}

\pagestyle{empty}

\MAWhead{%s}
';

	if ($onevar == "1") {
		$tempstr = "";
	} else {
		$tempstr = ", clip, bb = 50 50 310 202";
	}

	$TeXfile = sprintf($TeXfile, $tempstr, __("Inequalities"));
	fwrite($soubor, $TeXheader . $TeXfile);
	fwrite($soubor, "\\begin{center}" . __("Inequalities") . ": \$ $tex_all_ineq \$");
	if ($podmnez == "on") {
		fwrite($soubor, __("\\def\\nonneg{We solve the system in the first quadrant (for nonnegative variables)}"));
	}

	fwrite($soubor, "\\par\\noneg\\end{center}\\begin{multicols}{2}");
	fwrite($soubor, $textable);
	fwrite($soubor, "\\end{multicols}\\end{document}");

	fclose($soubor);

	$temp = `cd $maw_tempdir; echo "$xmin $ymin 0">a; gnuplot pics $command_after_gnuplot; $epstopdf all.eps; $pdflatex inequality>>output; $catchmawerrors`;

	$lastline = exec("cd $maw_tempdir; cat errors");


	if ($lastline != "") {
		maw_errmsgB("<pre>");
		system("cd " . $maw_tempdir . "; cat output|$mawcat");
		system("rm -r " . $maw_tempdir);
		save_log_err($nerovnice, "ineq2d");
		die("</pre></body></html>");
	} else {
		/* here we send PDF file into browser */
		send_PDF_file_to_browser("$maw_tempdir/inequality.pdf");
		save_log($nerovnice, "ineq2d");
	}

	/* We clean the temp directory and write log information                */
	/* ---------------------------------------------------------------------*/

	system("rm -r " . $maw_tempdir);
	die();

}


maw_html_head();

echo '<h3>', __("Inequalities and systems of inequalities");
echo '</h3>';

if ($podmnez == "on") {
	echo __("We solve the system in the first quadrant (for nonnegative variables)");
}

echo("<br>");
//echo __("No images in the table? The system probably possesses no solution (or we have problems with server).");
//echo ("<br>");


$funkce = "8";

$nerovnice = str_replace("\n", ";", $nerovnice);
$nerovnice = str_replace(" ", "", $nerovnice);
$rozd_nerovnice = explode(";", $nerovnice);

$pocet = count($rozd_nerovnice);

echo '<br><table><tr><th>' . __("Inequality") . "</th><th>" . __("Graph") . "</th></tr>";
$pocet_nerovnic = 0;

for ($i = 0; $i <= $pocet; $i = $i + 1) {
	$a = 1;
	$b = 1;
	$ttemp = chop($rozd_nerovnice[$i]);
	$ner = preg_split("~>|<~", $ttemp);
	if (strlen($ner[1]) * strlen($ner[0]) > 0) {
		$a = input_to_maxima(chop($ner[0]));
		$b = input_to_maxima(chop($ner[1]));
		if (stristr($ttemp, ">")) {
			$funkce = $funkce . "+log(($a)-($b))";
			$tempfce = "log(($a)-($b))";
		} else {
			$funkce = $funkce . "+log(($b)-($a))";
			$tempfce = "log(($b)-($a))";
		}
		$pocet_nerovnic = $pocet_nerovnic + 1;
		echo '<tr><td>';
		$rozd_nerovnice[$i] = formconv($ttemp);
		echo put_tex_to_html($rozd_nerovnice[$i]);
		echo '</td><td><img alt="loading ..." src="../../maw/ineq2d/ineq2d.php?onevar=', $onevar, '&podmnez=', $podmnez, '&axislabels=', $axislabels, '&xmin=', $xmin, '&xmax=', $xmax, '&ymin=', $ymin, '&ymax=', $ymax, '&akce=1&funkce=', rawurlencode("$tempfce"), '"></td></tr>';
	} elseif ($ner[0] . $ner[1] != "") {
		echo("<tr><td></td><td>Bad input in row " . ($i + 1) . "</td></tr>");
		$rozd_nerovnice[$i] = "bad\\ input\\ in\\ row\\ " . ($i + 1);
	}

}

$fcef = $funkce;

if ($pocet_nerovnic > 1) {
	echo '<tr><td>', __("System of inequalities"), '<br>';

	$allinequalitites = "";
	for ($i = 0; $i <= $pocet; $i = $i + 1) {
		if ($rozd_nerovnice[$i] != "") {
			if ($allinequalitites != "") {
				$allinequalitites = $allinequalitites . "\\\\";
			}
			$allinequalitites = $allinequalitites . " " . $rozd_nerovnice[$i];
		}
	}

	echo put_tex_to_html("\\begin{cases}" . $allinequalitites . "\\end{cases}");
	echo '</td><td><img alt="loading ..." src="../../maw/ineq2d/ineq2d.php?onevar=', $onevar, '&podmnez=', $podmnez, '&axislabels=', $axislabels, '&xmin=', $xmin, '&xmax=', $xmax, '&ymin=', $ymin, '&ymax=', $ymax, '&akce=1&funkce=', rawurlencode("$fcef"), '"></td></tr>';

}

echo '</table><br><br>';
save_log($nerovnice, "ineq2d");
die("</body></html>");
?>
