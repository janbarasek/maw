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

$scriptname = "banach";

require("../common/maw.php");

$funkce = $_REQUEST["funkce"];
$a = check_decimal($_REQUEST["a"]);
$b = check_decimal($_REQUEST["b"]);
$c = $_REQUEST["c"];
$n = $_REQUEST["n"];
$what = $_REQUEST["what"];
$method = $_REQUEST["method"];

if ($what == "") {
	if ($_REQUEST["output"] == "html") {
		$what = "";
	} else {
		$what = "pdf";
	}
}

$pdflink = 1;


if ((!($method == "banach")) && (!($method == "bisection")) && (!($method == "regula_falsi")) && (!($method == "newton"))) {
	$method = "bisection";
}

$parameters = ' ';
check_for_security("$funkce, $a, $b, $c, $n");

if ($c == "") {
	$c = $a;
}
$c = check_decimal($c);

function check_integer($vstup, $decfieldname = "")
{
	global $lang;
	$vstup = str_replace(" ", "", $vstup);
	if (!(preg_match('/^[0-9]*$/', $vstup))) {
		maw_html_head();
		echo("<h3>" . __("Incorrect input, use only integers to enter number of steps.") . "</h3>");
		die();
	}

	return ($vstup);
}

function my_number_format($var)
{
	$temp = 0.0 + (str_replace("E", "e", $var));
	if (abs($temp) < 0.001) {
		return (sprintf("%e", $temp));
	} else {
		return (round(100000 * $temp) / 100000);
	}
}

$variables = 'x';
$funkce = input_to_maxima($funkce, __("function"));

$n = check_integer($n);

$datcasip = "funkce: $funkce, interval:[$a,$b], init:$c, n=$n, $method";
function math_to_GNUplot($vyraz)
{
	global $formconv_bin;
	$uprfunkceGNU = `echo "$vyraz" | $formconv_bin -r -O gnuplot`;
	$uprfunkceGNU = chop($uprfunkceGNU);

	return ($uprfunkceGNU);
}


function najdiretezec($klicoveslovo, $retezec, $key = "###")
{
	$retezec = str_replace("\n", "", $retezec);
	preg_match("/\#\#\# *" . $klicoveslovo . " (.*?)(\#\#\# end)/", $retezec, $matches);
	$vystup = $matches[0];
	$vystup = str_replace("### " . $klicoveslovo, "", $vystup);
	$vystup = str_replace("### end", "", $vystup);
	$vystup = str_replace("###", "", $vystup);

	return ($vystup);
}

function remove_dollars($string)
{
	return (str_replace("$$", "", $string));
}

function removepercent($string)
{
	return (str_replace("%", "", $string));
}

function def_tex($string, $def)
{
	return ("\\def" . $string . "{" . $def . "}\n\n");
}


if ($method == "banach") {
	$command = "display2d:false\$ f(x):=$funkce\$ a:$a\$ b:$b\$ c:$c\$ n:$n\$ load(\\\"$mawhome/banach/banach.mac\\\")\$ ";
} elseif ($method == "newton") {
	$command = "display2d:false\$ f(x):=$funkce\$ a:$a\$ b:$b\$ c:$c\$ n:$n\$ load(\\\"$mawhome/banach/newton.mac\\\")\$ ";
} elseif ($method == "bisection") {
	$command = "display2d:false\$ f(x):=$funkce\$ a:$a\$ b:$b\$ c:$c\$ n:$n\$ load(\\\"$mawhome/banach/bisection.mac\\\")\$ ";
} else {
	$command = "display2d:false\$ f(x):=$funkce\$ a:$a\$ b:$b\$ c:$c\$ n:$n\$ load(\\\"$mawhome/banach/regula-falsi.mac\\\")\$ ";
}

if ($what == "") {
	maw_html_head();
	echo($maw_processing_msg);
	ob_flush();
	flush();
	if (function_exists("maw_after_flush")) {
		echo(maw_after_flush());
	}
}

function hide_message()
{
	return ('<script>document.getElementById("processing").style.display = "none";</script>');
}

$output = `$mawtimeout $maxima --batch-string="$command"`;

check_for_errors($output, $datcasip, $method);

$outputn = str_replace("\n", "", $output);

if (($what == "pdf") || ($what == "png") || ($what == "svg")) {

	preg_match_all("/### gnuplot (.*?) ### end/", $outputn, $matches, PREG_SET_ORDER);

	if ($what == "png") {
		$gnuplotcommands = "set xtics axis nomirror \n set ytics axis nomirror \n set terminal png size 700,700 transparent\n set output \"obrazek.png\"\n";
	} elseif ($what == "svg") {
		$gnuplotcommands = "set xtics axis nomirror \n set ytics axis nomirror \n set terminal svg size 700,700\n set output \"obrazek.svg\"\n";
	} else {
		$gnuplotcommands = "set xtics axis nomirror \n set ytics axis nomirror \n set term postscript eps color \n set output \"graf.eps\"\n";
	}

	foreach ($matches as $val) {
		$gnuplotcommands = $gnuplotcommands . "\n" . $val[1];
	}

	$funkcegnuplot = `echo "$funkce" | $formconv_bin -r -O gnuplot`;
	$funkcegnuplot = chop($funkcegnuplot);
	if (($method == "bisection") || ($method == "regula_falsi")) {
		$gnuplotcommands = $gnuplotcommands . "\n plot " . $funkcegnuplot . " linewidth 4,0 linewidth 2";
	} elseif ($method == "newton") {
		$gnuplotcommands = $gnuplotcommands . "\n plot " . $funkcegnuplot . " linewidth 4, 0 linewidth 2";
	} else {
		$gnuplotcommands = $gnuplotcommands . "\n plot " . $funkcegnuplot . " linewidth 4, x linewidth 2";
	}

	$maw_tempdir = "/tmp/MAW_banach" . getmypid() . "xx" . RandomName(6);
	system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);
	define("NAZEV_SOUBORU", $maw_tempdir . "/vstup");
	$soubor = fopen(NAZEV_SOUBORU, "w");
	fwrite($soubor, $gnuplotcommands);
	fclose($soubor);
	system("cd $maw_tempdir;  gnuplot vstup; gnuplot vstup; if [ ! -s obrazek.svg ]; then grep -v \"set arrow from\" vstup > vstup2; gnuplot vstup2; fi; cp * /tmp");

	if ($what == "png") {
		$file = $maw_tempdir . "/obrazek.png";
		header("Content-Type: image/png");
		header("Content-Disposition: attachment; filename=" . basename($file) . ";");
		header("Content-Transfer-Encoding: binary");
		readfile($file);
		system("rm -r " . $maw_tempdir);
		die();
	}
	if ($what == "svg") {
		$file = $maw_tempdir . "/obrazek.svg";
		header("Content-Type: image/svg+xml");
		header("Content-Disposition: attachment; filename=" . basename($file) . ";");
		readfile($file);
		system("rm -r " . $maw_tempdir);
		die();
	}
}


if ($what == "pdf") {

	$string = "\\def\\function{ " . remove_dollars(najdiretezec("function", $outputn)) . " }\n\\def\\interval{ " . remove_dollars(najdiretezec("interval", $outputn)) . " }" . "\\def\\initialguess{ " . remove_dollars(najdiretezec("initial", $outputn)) . " }";


	if (($method == "bisection") || ($method == "regula_falsi")) {

		if ($method == "bisection") {
			$formula = "\\frac{a+b}2";
		} else {
			$formula = "\\frac{af(b)-bf(a)}{f(b)-f(a)}";
		}

		$data = "\\begin{tabular}{|l||l|l|l||l|l|l|}\\hline $ n $ & $ a $ & $ c=" . $formula . " $ & $ b $ & $ f(a) $ & $ f(c) $ & $ f(b) $ \\\\ \\hline";

		preg_match_all("/### step (.*?) , (.*?) , (.*?) , (.*?) , (.*?) , (.*?) , (.*?) ### end/", $outputn, $matches, PREG_SET_ORDER);

		foreach ($matches as $val) {
			$data = $data . " " . $val[1] . " & " . $val[2] . " & " . $val[3] . " & " . $val[4] . " & " . my_number_format($val[5]) . " & " . my_number_format($val[6]) . " & " . my_number_format($val[7]) . " \\\\\n";
		}

		$data = $data . "\\hline\\end{tabular}";


		$TeXfile = '\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}
\rightskip 0 pt plus 1 fill

\MAWhead{%s}

\def\comment#1{\hbox{\qquad\textit{(#1)}}}
\lineskip 10pt
\lineskiplimit 10pt

%s 

{\small\tabulka

}

\begin{center}
  {\includegraphics[width=15cm]{graf.pdf}}
\end{center}


\end{document}
';

		if ($method == "bisection") {
			$tmp = sprintf(__("We solve %s on the interval %s using bisection of interval."), "$ \\function=0 $ ", " $ \\interval $ ");
			$TeXfile = "\\def\\tabulka { " . $data . " } \n" . $string . sprintf($TeXfile, __("Bisection of interval"), $tmp);
		} else {
			$tmp = sprintf(__("We solve %s on the interval %s using regula falsi."), "$ \\function=0 $ ", " $ \\interval $ ");
			$TeXfile = "\\def\\tabulka { " . $data . " } \n" . $string . sprintf($TeXfile, __("Method regula falsi"), $tmp);
		}

	} // bisection and regula falsi
	else {

		if ($method == "banach") {
			$data = "\\begin{tabular}{|l||l|}\\hline $ n $ & $ f^n(x_0) $ \\\\ \\hline";
		}
		{
			$data = "\\begin{tabular}{|l||l|}\\hline $ i $ & $ x_i $ \\\\ \\hline";
		}

		preg_match_all("/### iteration (.*?) value (.*?) ### end/", $outputn, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$data = $data . " " . $val[1] . " & " . $val[2] . "\\\\\n";
		}


		$data = $data . "\\hline\\end{tabular}";

		$TeXfile = "\\def\\tabulka { " . $data . " } \n" . $string . '
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}
\rightskip 0 pt plus 1 fill

\MAWhead{%s}

\def\comment#1{\hbox{\qquad\textit{(#1)}}}
\lineskip 10pt
\lineskiplimit 10pt


%s

{\scriptsize 

\tabulka
}

\begin{center}
  {\includegraphics[width=15cm]{graf.pdf}}
\end{center}


\end{document}
';

		if ($method == "banach") {
			$tmp = sprintf(__("We solve the equation %s using method of iterations on the interval %s with initial guess %s."), "$ \\function=x $ ", " $\\interval $ ", " $ x_0=\\initialguess $");
			$TeXfile = sprintf($TeXfile, __("Method of iterations"), $tmp);
		} else {
			$tmp = sprintf(__("We solve the equation %s using Newton--Raphson method with initial guess %s."), "$ \\function=0 $ ", " $ x_0=\\initialguess $");
			$tmp = $tmp . "\\par\n" . __("Function") . ": \$ f(x)=" . remove_dollars(najdiretezec("function", $outputn)) . "\$\\par\n" . __("Derivative") . ": \$ f^\\prime(x)=" . remove_dollars(najdiretezec("derivative", $outputn)) . "\$\\par\n" . __("Iteration scheme") . ": \$ x_{i+1}=" . remove_dollars(najdiretezec("scheme", $outputn)) . "\$\par";
			$TeXfile = sprintf($TeXfile, __("Newton--Raphson method"), $tmp);
		}

	}

	define("NAZEV_SOUBORU_TEX", $maw_tempdir . "/equation.tex");
	$soubortex = fopen(NAZEV_SOUBORU_TEX, "w");
	fwrite($soubortex, $TeXheader . "\\usepackage{graphicx}" . $TeXfile);
	fclose($soubortex);

	system("cd $maw_tempdir; $epstopdf graf.eps; $pdflatex equation.tex >> output ");

	/* Errors in compilation? We send PDF file or log of errors              */
	/* ---------------------------------------------------------------------*/

	$lastline = exec("cd " . $maw_tempdir . "; cat errors; cp * ..");

	if ($lastline != "") {
		maw_errmsgB("<pre>");
		system("cd " . $maw_tempdir . "; cat output");
		system("cp * ..; rm -r " . $maw_tempdir);
		save_log_err($datcasip . " PDF", $method);
		die("</pre></body></html>");
	} else {
		/* here we send PDF file into browser */
		send_PDF_file_to_browser("$maw_tempdir/equation.pdf");
		save_log($datcasip . " PDF", $method);
		system("rm -r " . $maw_tempdir);
		die();
	}
}

if (($method == "bisection") || ($method == "regula_falsi")) {
	if ($method == "bisection") {
		echo '<h2>', __("Bisection method"), '</h2>';
	} else {
		echo '<h2>', __("Regula falsi method"), '</h2>';
	}

	echo __("Equation"), ": \$" . remove_dollars(najdiretezec("function", $outputn)) . "=0\$" . "<br><br>";

	echo __("Interval"), ": \$" . remove_dollars(najdiretezec("interval", $outputn)) . "\$<br><br>";

	if (stristr($output, "no change of sign")) {
		echo "<h3 class='red'>" . __("ERROR: No change in sign between end points of the interval") . "</h3>";
		save_log_err($datcasip, $method);

		echo("<img alt=\"loading ...\" src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" />");
		echo hide_message();
		die();
	}


	if (stristr($output, "function is out of domain")) {
		echo "<h3 class='red'>" . __("ERROR: function is out of domain at some point of the interval") . "</h3>";
		save_log_err($datcasip, $method);

		echo("<img alt=\"loading ...\" src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" />");
		echo hide_message();
		die();
	}


	if (stristr($output, "more zeros")) {
		echo "<h3 class='red'>" . __("ERROR: No change in sign between end points of the interval") . "</h3>";
		save_log_err($datcasip, $method);

		echo("<img alt=\"loading ...\" src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" />");
		echo hide_message();
		die();
	}

	preg_match_all("/### step (.*?) , (.*?) , (.*?) , (.*?) , (.*?) , (.*?) , (.*?) ### end/", $outputn, $matches, PREG_SET_ORDER);
	foreach ($matches as $val) {
		$data = $data . "<tr><td>" . $val[1] . "</td><td></td><td>" . $val[2] . "</td><td>" . $val[3] . "</td><td>" . $val[4] . "</td><td></td><td>" . my_number_format($val[5]) . "</td><td>" . my_number_format($val[6]) . "</td><td>" . my_number_format($val[7]) . "</td></tr>\n";
	}

	echo '<style> table, th, td { border: 1px solid black; } 
table  { border-collapse:collapse; }
th  {background-color:green; color:white; } 
td {padding:10px;}
</style>';

	echo "<table><tr><th>k</th><th>&nbsp;&nbsp;&nbsp;</th><th>a</th><th>c</th><th>b</th><th>&nbsp;&nbsp;&nbsp;</th><th>f(a)</th><th>f(c)</th><th>f(b)</th></tr>", $data, "</table>";

	if (stristr($output, "FOUND ZERO")) {
		echo "<br>", __("Found zero") . " x =", najdiretezec("FOUND ZERO", $outputn), "<br><br>";
		$pdflink = 0;
	}

	echo("<img alt=\"loading ...\" src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" />");


} // regula falsi or bisection
else // 
{

	if ($method == "banach") {
		echo '<h2>', __("Method of iterations"), '</h2>';
		echo "<div class=logickyBlok>";
		echo "<p>" . __("Equation") . ": \$" . remove_dollars(najdiretezec("function", $outputn)) . "=x\$ </p>";
	} else {
		echo '<h2>', __("Newton-Raphson method"), '</h2>';
		echo "<div class=logickyBlok>";
		echo "<p>" . __("Equation") . ": \$" . remove_dollars(najdiretezec("function", $outputn)) . "=0\$ </p>";
	}

	echo "<p>" . __("Interval") . ": \$" . remove_dollars(najdiretezec("interval", $outputn)) . "\$</p>";

	echo "<p>" . __("Initial guess") . ": \$ x_0=" . remove_dollars(najdiretezec("initial", $outputn)) . "\$</p>";
	echo "</div>";


	if (stristr($output, "function outside interval")) {
		echo "<h3 class='red'>" . __("ERROR: Function is not bijection on the given interval.") . "</h3>";
		save_log_err($datcasip, $method);
		echo("<img src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" />");
		echo hide_message();
		die();
	}

	if ($method == "newton") {
		echo "<div class=logickyBlok>";
		echo "<p>" . __("Function") . ": \$ f(x)=" . remove_dollars(najdiretezec("function", $outputn)) . "\$</p>";
		echo "<p>" . __("Derivative") . ": \$ f^\\prime(x)=" . remove_dollars(najdiretezec("derivative", $outputn)) . "\$</p>";
		echo "<p>" . __("Iteration scheme") . ": \$ x_{i+1}=" . remove_dollars(najdiretezec("scheme", $outputn)) . "\$</p>";
		echo "</div>";
	}

	preg_match_all("/### iteration (.*?) value (.*?) ### end/", $outputn, $matches, PREG_SET_ORDER);
	foreach ($matches as $val) {
		$data = $data . "<tr><th>" . $val[1] . "</th><td>" . $val[2] . "</td></tr>\n";
	}


	echo '<style> table, th, td { border: 1px solid black; }
table  { border-collapse:collapse; }
th  {background-color:green; color:white; } 
td {padding:10px;}
</style>';

	if ($method == "newton") {
		echo "<div class=inlinediv><table><tr><th>\$ i \$</th><th>\$ x_i \$</th></tr>", $data, "</table></div>";
	} else {
		echo "<div class=inlinediv><table><tr><th>\$ i \$</th><th>\$ x_i = f(x_{i-1})\$</th></tr>", $data, "</table></div>";
	}

	echo("<div class=inlinediv><img alt=\"loading ...\" src=\"$mawphphome" . "/.." . $maw_URI . "&what=svg\" /></div>");


}

//if ($pdflink==1)
//{
//echo ("<hr><a href=\"$mawphphome"."/..".$maw_URI."&what=pdf\">PDF</a>");
//}

//echo ("<pre>".$output);

echo hide_message() . '</body></html>';

save_log($datcasip, $method);

?>



