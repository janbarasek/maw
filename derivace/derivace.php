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

$scriptname = "derivace";

require("../common/maw.php");
$funkce = $_REQUEST["funkce"];
$akce = $_REQUEST["akce"];
$suppose_linear = $_REQUEST["linear"];
$differentiate_constants = $_REQUEST["constants"];

if ($funkce == "") {
	$akce = 0;
	$funkce = rawurldecode($_SERVER['QUERY_STRING']);
}
if (stristr($funkce, "tlacitko")) {
	$funkce = "";
}

check_for_security($funkce);

if ($akce == "0") {
	check_for_y($funkce);
	$variables = 'x';
}

$parameters = " ";
$funkce = input_to_maxima($funkce);

$trigsimp = "";
if (preg_match("~sin|cos|tan|cot|asin|acos|atan~", $funkce)) {
	$trigsimp = "trigsimp";
}

$maw_tempdir = "/tmp/MAW_derivace" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);


define("NAZEV_SOUBORU", $maw_tempdir . "/prikazy");
$soubor = fopen(NAZEV_SOUBORU, "w");

fwrite($soubor, "soubor:\"" . $maw_tempdir . "/soubor.tex\";\n");
fwrite($soubor, "negsumdispflag:false;\n display2d:false;\n");

if ($akce == "2") {
	fwrite($soubor, "load(\"$mawhome/derivace/derivace2y.mac\");\n");
} else {
	fwrite($soubor, "load(\"$mawhome/derivace/derivace2.mac\");\n");
	if ($akce == "1") {


		fwrite($soubor, 'texput(derivace,["\\\\frac{\\\\partial}{\\\partial x}\\\\left(","\\\\right)"],matchfix)$ ' . "\n");
		fwrite($soubor, 'texput(derivaceb,["\\\\frac{\\\\partial}{\\\\partial x}\\\\left(","\\\\right)"],matchfix)$' . "\n");

	}
}

fwrite($soubor, "simp:false;\n formula_to_tex($funkce,\"\\\\zadani y=\");\n simp:true;\n");

if ($suppose_linear == "on") {
	fwrite($soubor, "trylinear(expr):=expr;");
	fwrite($soubor, "declare(derivace,linear);");
} else {
	fwrite($soubor, "trylinear(expr):=(oldexpr:expr,ev(ev(exprlin:expr,declare(derivace,additive)), remove(derivace,additive)),remove(derivace,additive),if (exprlin#oldexpr) then formula_to_tex(exprlin,\"\\\\linearity\"), exprlin)$ ");
}

if ($differentiate_constants == "on") {
	fwrite($soubor, "matchdeclare(const,freeofx);tellsimpafter(derivace(const),0);");
}

fwrite($soubor, "diff_in_steps($funkce);\n");

if ($suppose_linear == "on") {
	fwrite($soubor, "remove(derivace,linear);");
}

fwrite($soubor, "kill(a,b,c,d,x,y,z);");

if ($akce == "2") {
	fwrite($soubor, "remove(x,constant);maxapplydepth:10000;newfce2:radcan(newfce);a2:zeroequiv(ratsimp(radcan(trigsimp(diff($funkce,y)-(newfce2)))),y);\n b2:zeroequiv(ratsimp(trigsimp(diff($funkce,y)-expand(newfce))),y);\n if not(derivacep(newfce)) and (a2 or b2) and (a2#dontknow or b2#dontknow) then 1 else diff_failed(diff($funkce,y));\n");
} else {
	fwrite($soubor, "remove(y,constant);maxapplydepth:10000;newfce2:radcan(newfce);a2:zeroequiv(ratsimp(radcan(trigsimp(diff($funkce,x)-(newfce2)))),x);\n b2:zeroequiv(ratsimp(trigsimp(diff($funkce,x)-expand(newfce))),x);\n if  not(derivacep(newfce)) and (a2 or b2) and (a2#dontknow or b2#dontknow) then 1 else diff_failed(diff($funkce,x));\n");
}

fwrite($soubor, "a3:factor(ratsimp($trigsimp(newfce2)));\n");
fwrite($soubor, "errcatch(map(fullratsimp,newfce2));\n bb2:%;\n if bb2=[] then b3:a3 else b3:bb2[1] ;\n");
fwrite($soubor, "c3:combine(expandwrt(($trigsimp(newfce2)),x));\n");
fwrite($soubor, "if freeof(%i,a3) then formula_to_tex(a3,\"\\\\uprava {y}^\\\\prime=\");\n");
fwrite($soubor, "if freeof(%i,b3) then if (str(a3)#str(b3)) then formula_to_tex(b3,\"\\\\uprava {y}^\\\\prime=\");\n");
fwrite($soubor, "if freeof(%i,c3) then if (str(c3)#str(b3)) and (str(a3)#str(c3)) then formula_to_tex(c3,\"\\\\uprava {y}^\\\\prime=\");\n");

fwrite($soubor, "a2;b2;A:ratsimp(radcan(trigsimp(diff($funkce,x))));B:(newfce2);zeroequiv(A-B,x);finished();");
fclose($soubor);

$out = `cd $maw_tempdir; $mawtimeout -t 10 $maxima -b prikazy >> output`;

$tempout = file_get_contents($maw_tempdir . "/output");


if (preg_match("~[E|e]rror|[Ii]ncorrect~", $tempout)) {
	maw_errmsgB("");
	if (stristr($tempout, "Error - differentiation in steps failed")) {
		echo("<h3 class='red'>" . __("Something failed - report this to the authors of MAW, please.") . "</h3>");
		save_log_err("funkce: $funkce : failed diff in steps", "derivace");
		save_log("funkce: $funkce : failed diff in steps", "derivace_error");
	} else {
		save_log_err("funkce: $funkce", "derivace");
	}
	echo("<pre>");
	system("cd $maw_tempdir; cat output|$mawcat");
	system("rm -r " . $maw_tempdir);
	die("</pre></body></html>");
}

if (!(stristr($tempout, "Finished OK"))) {
	maw_errmsgB("");
	echo("<h3 class='red'>" . __("Something failed - report this to the authors of MAW, please.") . "</h3>");
	echo("<h3 class='red'>" . sprintf(__("You probably reached the time limit, try simpler problem or %s."), "<a href=\"http://www.wolframalpha.com/input/?i=derivative\">Wolfram Alpha</a>") . "</h3>");
	save_log_err("funkce: $funkce : failed diff in steps", "derivace");
	save_log("funkce: $funkce : failed diff in steps", "derivace_error");
	echo("<pre>");
	system("cd $maw_tempdir; cat output|$mawcat");
	system("rm -r " . $maw_tempdir);
	die("</pre></body></html>");
}

define("SOUBA", $maw_tempdir . "/out.tex");
$soubor = fopen(SOUBA, "w");

if ($akce == "0") {
	fwrite($soubor, "\\def\\co#1{" . __("Differentiate function #1") . "}\\def\\jak{\\frac{d}{dx}}");
	fclose($soubor);
	$outb = `cd $maw_tempdir; sh $mawhome/derivace/filtr.sh >> out.tex`;
} elseif ($akce == "1") {
	fwrite($soubor, "\\def\\co#1{" . sprintf(__("Differentiate #1 with respect to %s"), "\$ x\$") . " \\ignorespaces}\\def\\jak{\\frac{\\partial}{\\partial x}}");
	fclose($soubor);
	$outb = `cd $maw_tempdir; sh $mawhome/derivace/filtr1.sh >> out.tex`;
} elseif ($akce == "2") {
	fwrite($soubor, "\\def\\co#1{" . sprintf(__("Differentiate #1 with respect to %s"), "\$ y\$") . " \\ignorespaces}\\def\\jak{\\frac{\\partial}{\\partial y}}");
	fclose($soubor);
	$outb = `cd $maw_tempdir; sh $mawhome/derivace/filtr2.sh >> out.tex`;
}


if ($_REQUEST["output"] != "pdf"):

	printf("<h2>%s</h2>", __("Derivative"));

	if ($akce == "0") {
		$problem = sprintf("%s", __("Differentiate function #1"));
	} elseif ($akce == "1") {
		$problem = sprintf("%s", sprintf(__("Differentiate #1 with respect to %s"), "\$ x\$"));
	} elseif ($akce == "2") {
		$problem = sprintf("%s", sprintf(__("Differentiate #1 with respect to %s"), "\$ y\$"));
	}

	$outb = `cd $maw_tempdir; cat soubor.tex`;
	$outb = str_replace("\n", " ", $outb);


	preg_match_all('/\"\\\\.*?konec/', $outb, $matches);

	function fixderivative($str)
	{
		global $formconv_repl_input, $formconv_repl_output, $akce;

		$arrsearch = ["\"\\\\krok", "\"\\\\uprava", "\"\\\\constantrule", "\"\\\\constantmultrule", "\"\\\\construle", "\"\\\\productrule", "\"\\\\quotientrule", "\"\\\\powerrulex", "\"\\\\powerrule", "\"\\\\sinrule", "\"\\\\cosrule", "\"\\\\tanrule", "\"\\\\cotrule", "\"\\\\sinhrule", "\"\\\\coshrule", "\"\\\\tanhrule", "\"\\\\cothrule", "\"\\\\arcsinrule", "\"\\\\arccosrule", "\"\\\\arctanrule", "\"\\\\arccotrule", "\"\\\\arcsinhrule", "\"\\\\arccoshrule", "\"\\\\arctanhrule", "\"\\\\arccothrule", "\"\\\\exprule", "\"\\\\genexprule", "\"\\\\logrule", "\"\\\\genlogrule", "\"\\\\linearity", "\"\\\\expvarexprule", "\"\\\\absrule", "\"\\\\secrule", "\"\\\\cscrule"];

		$arrreplace = [" ", __("Simplification"), __("Constant rule"), __("Constant multiple rule"), __("Constant rule"), __("Product rule"), __("Quotient rule"), sprintf(__("Derivative of %s"), "\$ x\$"), __("Derivative of power function"), __("Derivative of sine function"), __("Derivative of cosine function"), __("Derivative of tangent function"), __("Derivative of contangent function"), __("Derivative of hyperbolic sine function"), __("Derivative of hyperbolic cosine function"), __("Derivative of hyperbolic tangent function"), __("Derivative of hyperbolic contangent function"), __("Derivative of arcsine function"), __("Derivative of arccosine function"), __("Derivative of arctan function"), __("Derivative of acrcotan function"), __("Derivative of hyperbolic arcsine function"), __("Derivative of hyperbolic arccosine function"), __("Derivative of hyperbolic arctan function"), __("Derivative of hyperbolic acrcotan function"), __("Derivative of exponential function"), __("Derivative of exponential function"), __("Derivative of logarithm"), __("Derivative of logarithm"), __("Sum rule"), __("Conversion into exponential function"), __("Derivative of absolute value"), __("Derivative of sec function"), __("Derivative of csc function")];

		$arrreplace2 = array_map(function ($val) {
			return $val . ": \\\\(";
		}, $arrreplace);
		$arrreplace2[0] = "\\\\(";

		$str = str_replace("\\\\konec", "\\\\)", $str);
		if ($akce == 1) {
			$arrsearchB = ["\"\\\\krok {y}^\\\\prime", "\"\\\\uprava {y}^\\\\prime", "\"\\\\zadani y="];
			$arrreplaceB = ["\\\\(\\\\frac{\\\\partial z}{\\\\partial x}", __("Simplification") . ": \\\\(\\\\frac{\\\\partial z}{\\\\partial x}", " z="];
			$str = str_replace($arrsearchB, $arrreplaceB, $str);
		}
		if ($akce == 2) {
			$arrsearchB = ["\"\\\\krok {y}^\\\\prime", "\"\\\\uprava {y}^\\\\prime", "\"\\\\zadani y="];
			$arrreplaceB = ["\\\\(\\\\frac{\\\\partial z}{\\\\partial y}", __("Simplification") . ": \\\\(\\\\frac{\\\\partial z}{\\\\partial y}", " z="];
			$str = str_replace($arrsearchB, $arrreplaceB, $str);
		}
		$str = str_replace($arrsearch, $arrreplace2, $str);
		$str = str_replace("\\\\", "\\", $str);
		$str = str_replace("\\log ", "\\ln ", $str);
		$str = str_replace($formconv_repl_input, $formconv_repl_output, $str);

		return ($str);
	}

	if ($mawISAjax == 0) {
		maw_html_head();
	}

	$items = array_map("fixderivative", $matches[0]);

	echo("<div class=logickyBlok>");
	echo(str_replace("\"\\zadani", "", str_replace("#1", "\(" . $items[0], $problem)));

	array_shift($items);
	foreach ($items as $value) {


		if (substr($value, 0, 2) == "\\(") {
			echo "<br>$value </div><div class=logickyBlok>\n";
		} else {
			echo "\n<li>$value";
		}
	}

	echo("</div>");

	$datcasip = "funkce: $funkce";
	save_log($datcasip, "derivace");
	system("cp $maw_tempdir/* /tmp/ ;rm -r " . $maw_tempdir);

	if ($mawISAjax == 0) {
		echo('</body></html>');
	}


	die();

endif;

define("SOUBB", $maw_tempdir . "/derivace.tex");
$soubor = fopen(SOUBB, "w");
$TeXfile = '
\fboxsep 0 pt

\usepackage{fancybox}

\def\init{\footnotesize\qquad}
\newif\ifjeden

\newcount\uprcount
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{%s}


\fboxsep=5pt
\def\formatuj#1{\setbox0=\hbox{#1} \ifdim\wd0<\hsize \fbox{#1} \else #1\fi}
\everymath{\displaystyle}
\def\krok#1\konec{\normalsize\par\leavevmode\formatuj{$#1$}\par\smallskip\hrule\bigskip}
\def\uprava#1\konec{\advance \uprcount by 1\relax \ifnum\uprcount=1 \footnotesize 
%s
\par\bigskip\fi\par\normalsize\leavevmode %s \the\uprcount: \formatuj{$#1$}\par\smallskip\hrule\bigskip}
\def\zadani#1\konec{\normalsize %s\footnote{
%s}: 
\co{${#1}$}\par\hbox to \hsize{\hss %s:\quad
$(\cdot)^\prime=\jak$}\smallskip\hrule\bigskip}
\def\constantrule#1\konec{\par \init %s: $#1$}
\def\constantmultrule#1\konec{\par \init %s: $#1$}
\def\construle#1\konec{\par\init  %s: $#1$}
\def\productrule#1\konec{\par\init %s: $#1$}
\def\quotientrule#1\konec{\par\init  %s: $#1$}
\def\powerrulex#1\konec{\par\init  ' . sprintf(__("Derivative of %s"), "\$ x\$") . ': $#1$}
\def\powerrule#1\konec{\par\init  %s: $#1$}
\def\sinrule#1\konec{\par\init  %s: $#1$}
\def\cosrule#1\konec{\par\init  %s: $#1$}
\def\tanrule#1\konec{\par\init  %s: $#1$}
\def\cotrule#1\konec{\par\init  %s: $#1$}
\def\sinhrule#1\konec{\par\init  %s: $#1$}
\def\coshrule#1\konec{\par\init  %s: $#1$}
\def\tanhrule#1\konec{\par\init  %s: $#1$}
\def\cothrule#1\konec{\par\init  %s: $#1$}
\def\arcsinrule#1\konec{\par\init  %s: $#1$}
\def\arccosrule#1\konec{\par\init  %s: $#1$}
\def\arctanrule#1\konec{\par\init  %s: $#1$}
\def\arccotrule#1\konec{\par\init  %s: $#1$}
\def\arcsinhrule#1\konec{\par\init  %s: $#1$}
\def\arccoshrule#1\konec{\par\init  %s: $#1$}
\def\arctanhrule#1\konec{\par\init  %s: $#1$}
\def\arccothrule#1\konec{\par\init  %s: $#1$}
\def\exprule#1\konec{\par\init  %s: $#1$}
\def\genexprule#1\konec{\par\init %s: $#1$}
\def\logrule#1\konec{\par\init  %s: $#1$}
\def\genlogrule#1\konec{\par\init %s: $#1$}
\def\linearity#1\konec{\par\init  %s}
\def\expvarexprule#1\konec{\par\init %s: $#1$}
\def\absrule#1\konec{\par\init  %s: $#1$}
\def\secrule#1\konec{\par\init %s: $#1$}
\def\cscrule#1\konec{\par\init  %s: $#1$}

\input out.tex
\end{document}
';

$TeXfile = $TeXheader . sprintf($TeXfile, __("Derivative"), __("We try to simplify the answer. (This is rather tricky step, sometimes it simplifies the answer and sometimes not.)"), __("Simplification"), __("Problem"), __("If the given function differs from the function which is actually differentiated, then Maxima performed an automatical simplification first."), __("notation"), __("Constant rule"), __("Constant multiple rule"), __("Constant rule"), __("Product rule"), __("Quotient rule"), __("Derivative of power function"), __("Derivative of sine function"), __("Derivative of cosine function"), __("Derivative of tangent function"), __("Derivative of contangent function"), __("Derivative of hyperbolic sine function"), __("Derivative of hyperbolic cosine function"), __("Derivative of hyperbolic tangent function"), __("Derivative of hyperbolic contangent function"), __("Derivative of arcsine function"), __("Derivative of arccosine function"), __("Derivative of arctan function"), __("Derivative of acrcotan function"), __("Derivative of hyperbolic arcsine function"), __("Derivative of hyperbolic arccosine function"), __("Derivative of hyperbolic arctan function"), __("Derivative of hyperbolic acrcotan function"), __("Derivative of exponential function"), __("Derivative of exponential function"), __("Derivative of logarithm"), __("Derivative of logarithm"), __("Sum rule"), __("Conversion into exponential function"), __("Derivative of absolute value"), __("Derivative of sec function"), __("Derivative of csc function"));
fwrite($soubor, $TeXfile);
fclose($soubor);

$outc = `cd $maw_tempdir; cat out.tex >> output; sed -i 's/^[\t ]*$/%/' out.tex; $pdflatex derivace.tex >> output; $catchmawerrors; grep Exiting output >>errors; cat soubor.tex; cat out.tex`;

$out = $out . "\n" . $outb . "\n" . $outc;

$lastline = exec("cd $maw_tempdir; cat errors");


if ($lastline != "") {
	save_log_err("funkce: $funkce", "derivace");

	maw_errmsgB("<pre>");
	system("cd $maw_tempdir; cat output|$mawcat; cat soubor.tex; cat funkce.tex");
	system("rm -r " . $maw_tempdir);
	die("</pre></body></html>");
}

send_PDF_file_to_browser("$maw_tempdir/derivace.pdf");

$datcasip = "funkce: $funkce";
save_log($datcasip, "derivace");


system("rm -r " . $maw_tempdir);

?>



