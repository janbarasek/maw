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

$scriptname = "integral2";

require("../common/maw.php");

$requestedoutput = "html";
if ($_REQUEST["output"] == "pdf") {
	$requestedoutput = "pdf";
}

$funkce = $_REQUEST["funkce"];
$a = $_REQUEST["a"];
$b = $_REQUEST["b"];
$c = $_REQUEST["c"];
$d = $_REQUEST["d"];
$vars = $_REQUEST["vars"];
$logarcswitch = $_REQUEST["logarc"];

if ($logarcswitch == "on") {
	$logarc = "load(\\\"$mawhome/integral/simpinvtrigh.mac\\\")\$";
} else {
	$logarc = "";
}
$parameters = ' ';
check_for_security("$funkce, $a, $b, $c, $d");

if (($vars == "r dr dphi") || ($vars == "r dphi dr")) {
	$variables = "r|phi";
}

$funkce = input_to_maxima($funkce, __("function"));

$xmin = check_decimal($_REQUEST["xmin"], "xmin");
$xmax = check_decimal($_REQUEST["xmax"], "xmax");
$ymin = check_decimal($_REQUEST["ymin"], "ymin");
$ymax = check_decimal($_REQUEST["ymax"], "ymax");
check_for_security("$xmin, $xmax, $ymin, $ymax");

$coords = "coord1min:$xmin\$ coord1max:$xmax\$ coord2min:$ymin\$ coord2max:$ymax\$ ";

//if ($vars=="dx dy") {$variables="y";} 
//elseif ($vars=="dy dx") {$variables="x";}
//elseif ($vars=="dr dphi") {$variables="phi";}
//else {$variables="r";}
$c = input_to_maxima($c, __("lower limit for inside integral"));
$d = input_to_maxima($d, __("upper limit for inside integral"));

//$variables=' ';
$a = input_to_maxima($a, __("lower limit for outside integral"));
$b = input_to_maxima($b, __("upper limit for outside integral"));

$datcasip = "funkce: $funkce, meze:$a..$b,  $c..$d, $vars";

$xmul = "x";
$ymul = "y";
if ($vars == "dx dy") {
	$varint = "vnejsi:y\$ vnitrni:x\$ coor1(m,n):=m\$ coor2(m,n):=n\$";
	$vnejsi = "y";
	$vnitrni = "x";
} elseif ($vars == "dy dx") {
	$varint = "vnejsi:x\$ vnitrni:y\$ coor1(m,n):=n\$ coor2(m,n):=m\$";
	$vnejsi = "x";
	$vnitrni = "y";
} elseif ($vars == "r dr dphi") {
	$varint = "vnejsi:phi\$ vnitrni:r\$ coor1(m,n):=m*cos(n)\$ coor2(m,n):=m*sin(n)\$";
	$vnejsi = "phi";
	$vnitrni = "r";
	$xmul = "r*cos(phi)";
	$ymul = "r*sin(phi)";
} else {
	$varint = "vnejsi:r\$ vnitrni:phi\$ coor1(m,n):=n*cos(m)\$ coor2(m,n):=n*sin(m)\$";
	$vnejsi = "r";
	$vnitrni = "phi";
	$xmul = "r*cos(phi)";
	$ymul = "r*sin(phi)";
}


function najdiretezec($klicoveslovo, $retezec, $key = "###")
{
	$retezec = str_replace("\n", "", $retezec);
	preg_match("/\#\#\# *" . $klicoveslovo . " (.*?)(\#\#\#)/", $retezec, $matches);
	$vystup = $matches[0];
	$vystup = str_replace("### " . $klicoveslovo, "", $vystup);
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

$vzorce = [];
function def_tex($string, $def)
{
	global $vzorce;
	$temp = substr($string, 1);
	$vzorce[$temp] = $def;

	return ("\\def" . $string . "{" . $def . "}\n\n");
}


if (ereg("sin|cos|tan|cot|asin|acos|atan", $funkce)) {
	$opttrigsimp = "opttrigsimp(f):=trigsimp(f)";
} else {
	$opttrigsimp = "opttrigsimp(f):=f";
}


$maw_tempdir = "/tmp/MAW_double" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

function rundoubleintegration($funkce, $I, $checkon)
{
	global $opttrigsimp, $varint, $a, $b, $c, $d, $coords, $logarc, $mawhome, $mawtimeout, $maxima, $err_msg, $print_errmsg, $outputHTML, $maw_tempdir, $vnitrni, $vnejsi, $vars, $vzorce, $TeXheader, $TeXfile, $requestedoutput, $pdflatex, $epstopdf, $catchmawerrors, $bash, $xmin, $xmax, $ymin, $ymax, $swap, $lang, $mawphphome, $mawhtmlhome;

	$command = "display2d:false\$ checkon:$checkon$ f:$funkce\$ $opttrigsimp\$ $varint a:$a\$ b:$b\$ c:$c\$ d:$d\$ $coords $logarc load(\\\"$mawhome/integral2/integral2.mac\\\")\$ ";

	$output = `$mawtimeout $maxima --batch-string="$command"`;
	$err_msg = "";
	$print_errmsg = true;
	$toutput = str_replace("\n", "", $output);
	if (ereg("invalid outside limits", $toutput)) {
		$err_msg = __("Invalid limits for integration. Limits for the outside integral must be constants and the lower limit has to be smaller than upper limit.");
		$problem = "vnejsi meze";
	} elseif (ereg("invalid inside limits - bad variable", $toutput)) {
		$err_msg = sprintf(__("Invalid limits for integration. Limits for the inside integral cannot contain the variable %s, which is used for inside integration.<br>Bad limits or bad order of variables for integration."), "<b>$vnitrni</b>");
		$problem = "spatna promenna vnitrni meze";
	} elseif (ereg("undefined inside limit", $toutput)) {
		$err_msg = sprintf(__("Invalid limits for integration. One of the limits is becomes undefined on the region of integration."), "<b>$vnitrni</b>");
		$params = sprintf("xmin=%s&xmax=%s&ymin=%s&ymax=%s&a=%s&b=%s&f=%s&g=%s&vars=%s", rawurlencode($xmin), rawurlencode($xmax), rawurlencode($ymin), rawurlencode($ymax), rawurlencode($a), rawurlencode($b), rawurlencode($c), rawurlencode($d), rawurlencode($vars));
		$err_msg = $err_msg . "<br><img src=\"../gnuplot/gnuplot_region.php?$params\">";
		$problem = "spatna promenna vnitrni meze";
	} elseif (ereg("upper and lower limits have intersections", $toutput)) {
		$err_msg = __("Invalid limits for integration. The lower limit for the inside integral has to be smaller than the upper limit.");
		$problem = "spatne vnitrni meze: prusecik";
		$params = sprintf("xmin=%s&xmax=%s&ymin=%s&ymax=%s&a=%s&b=%s&f=%s&g=%s&vars=%s", rawurlencode($xmin), rawurlencode($xmax), rawurlencode($ymin), rawurlencode($ymax), rawurlencode($a), rawurlencode($b), rawurlencode($c), rawurlencode($d), rawurlencode($vars));
		$err_msg = $err_msg . "<br><img src=\"../gnuplot/gnuplot_region.php?$params\">";
		if ($vars == "r dr dphi") {
			$params = sprintf("xmin=%s&xmax=%s&funkce=%s,%s&dummy=phi&xlabel=phi&ylabel=r&border=1", rawurlencode($a), rawurlencode($b), rawurlencode($c), rawurlencode($d));
			$err_msg = $err_msg . "<br><br><img src=\"../gnuplot/gnuplot?$params\">";
		}
	} elseif (ereg("upper and lower limits are interchanged", $toutput)) {
		$linkparams = "output=" . $requestedoutput;
		$linkparams = $linkparams . "&funkce=" . rawurlencode($funkce);
		$linkparams = $linkparams . "&a=" . rawurlencode($a);
		$linkparams = $linkparams . "&b=" . rawurlencode($b);
		$linkparams = $linkparams . "&c=" . rawurlencode($d);
		$linkparams = $linkparams . "&d=" . rawurlencode($c);
		$linkparams = $linkparams . "&xmin=" . rawurlencode($xmin);
		$linkparams = $linkparams . "&xmax=" . rawurlencode($xmax);
		$linkparams = $linkparams . "&ymin=" . rawurlencode($ymin);
		$linkparams = $linkparams . "&ymax=" . rawurlencode($ymax);
		$linkparams = $linkparams . "&vars=" . rawurlencode($vars);
		$linkparams = $linkparams . "&logarc=" . rawurlencode($logarcswitch);
		$link = "<a href=\"$mawhtmlhome/index.php?form=integral2&auto=1&$linkparams\">" . __("link") . "</a>";
		$err_msg = sprintf(__("Invalid limits for integration. The lower limit for the inside integral has to be smaller than the upper limit. If you want to interchange limits, you can try the following %s."), $link);
		$problem = "spatne vnitrni meze: prohozeno";
	} elseif (ereg("variable r is negative", $toutput)) {
		$err_msg = __("Invalid limits for integration. The radial variable r must be nonnegative.");
		if ($vars == "r dr dphi") {
			$params = sprintf("xmin=%s&xmax=%s&funkce=%s,%s&dummy=phi&xlabel=phi&ylabel=r&border=1", rawurlencode($a), rawurlencode($b), rawurlencode($c), rawurlencode($d));
			$err_msg = $err_msg . "<br><img src=\"../gnuplot/gnuplot?$params\">";
		}
		$problem = "r zaporne";
	} elseif ((str_replace(" ", "", remove_dollars(najdiretezec("vnejsi2", $output))) == "")) {
		$print_errmsg = false;
		$err_msg = __("Sorry, the evaluation of the integral failed.");
		if (ereg("abs", $output)) {
			$err_msg = $err_msg . "<br><br>" . __("As a subproblem we have to integrate function with absolute value, which is not supported well in our version of Maxima.");
		}
		$err_msg = $err_msg . "<br>" . __("You may try to interchange the order of integration and submit again. (Remember also to adjust limits for integration properly.)");
		$err_msg = $err_msg . "<br><br>" . __("The problem either does not make any sense or it is too difficult for Maxima. Check also, that the integral on the top matches your problem.");
		$problem = "integrace ztroskotala";
		save_log($datcasip . " " . $problem, "failed_integral2");
	}

	if ($err_msg != "") {
		maw_html_head();
		$meza = remove_dollars(najdiretezec("vnejsimezea", $output));
		$mezb = remove_dollars(najdiretezec("vnejsimezeb", $output));
		$mezc = remove_dollars(najdiretezec("vnitrnimezec", $output));
		$mezd = remove_dollars(najdiretezec("vnitrnimezed", $output));
		$optjac = "";
		if ($vnitrni == "phi") {
			$vnitrni = "\\varphi";
			$optjac = "r";
		}
		if ($vnejsi == "phi") {
			$vnejsi = "\\varphi";
			$optjac = "r";
		}
		$funkce = "";
		if (ereg("funkce", $output)) {
			$funkce = "$$\\int_" . "{" . $meza . "}" . "^" . "{" . $mezb . "}\\left[\\;" . "\\int_" . "{" . $mezc . "}^" . "{" . $mezd . "}\\left(" . remove_dollars(najdiretezec("funkce", $output)) . "\\right)$optjac\\,d$vnitrni \\;\\right]\\;d$vnejsi $$";
		}
		$vnitrni = "$$" . $mezc . "\\leq $vnitrni \\leq " . $mezd . "$$";
		$vnejsi = "$$" . $meza . "\\leq $vnejsi \\leq " . $mezb . "$$";
		if ($print_errmsg) {
			echo(show_TeX("<h3 class='red'>" . __("Ill posed problem, incorrect formulation of the problem or missing informations") . "</h3>" .
				__("<ul><li>the function has to be function in two variables, <li>the limits for outside integral have to be numbers,<li>the limits for the inside integral numbers or functions of variable which is used for outside integration,<li>he lower limit must be smaller or equal to the upper limit for integration for both integrals.</ul> One of these conditions has been violated or Maxima failed to verify these conditions (in most cases due to a typing error or bad mathematical formula on input).")));
		}

		if ($funkce != "") {
			echo(show_TeX(" <h4>" .
				__("Integral") . ": </h4><b>$funkce</b><h4>" .
				__("Variable for outside integration and limits for this variable") . ": </h4><b>$vnejsi</b><h4>" . __("Variable for inside integration and limits for this variable") . ": </h4><b>$vnitrni</b>"));
		}
		echo("<hr><span style='font-weight:bold'><span class='red'>" . $err_msg . "</span></span>");
		save_log_err($datcasip . " " . $problem, "integral2");
		die("<hr><pre>" . highlight_errors(show_TeX(substr($output, 0, 20000))));
	}

	if ($vars == "dx dy") {
		$swap = 1;
	} else {
		$swap = "";
	}
	if ($vars == "r dr dphi") {
		$polar = 1;
	} else {
		$polar = "";
	}
	if ($vars == "r dphi dr") {
		$polar = 1;
		$swap = 1;
	}
	$a_ = formconv_gnuplot($a);
	$b_ = formconv_gnuplot($b);
	$f_ = formconv_gnuplot($c);
	$g_ = formconv_gnuplot($d);
	$terminal = "postscript eps size 5in,5in";


	if ($requestedoutput == "pdf") {
		if ($polar == "") {
			system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_region.bash \"$f_\" \"$g_\" \"$a_\" \"$b_\" \"$xmin\" \"$xmax\" \"$ymin\" \"$ymax\" \"$swap\" \"$terminal\" \"a.eps\" ");
		} else {
			system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_region_polar.bash \"$f_\" \"$g_\" \"$a_\" \"$b_\" \"$xmin\" \"$xmax\" \"$ymin\" \"$ymax\" \"$swap\" \"$terminal\" \"a.eps\" ");
		}
	}

	$output = str_replace("\\\n", "", $output);

	$texoutput = def_tex("\\exprA", remove_dollars(najdiretezec("vnitrni1", $output))) . def_tex("\\exprB", remove_dollars(najdiretezec("dosazeniuvnitr", $output))) . def_tex("\\exprC", remove_dollars(najdiretezec("vnitrni2", $output)));

	$texoutput = $texoutput . def_tex("\\exprD", remove_dollars(najdiretezec("vnejsi1", $output))) . def_tex("\\exprE", remove_dollars(najdiretezec("dosazenivne", $output))) . def_tex("\\exprF", remove_dollars(najdiretezec("vnejsi2", $output))) . def_tex("\\exprG", remove_dollars(najdiretezec("funkce", $output))) . def_tex("\\exprH", remove_dollars(najdiretezec("limita", $output))) . def_tex("\\exprI", remove_dollars(najdiretezec("limitb", $output))) . def_tex("\\exprJ", remove_dollars(najdiretezec("limitc", $output))) . def_tex("\\exprK", remove_dollars(najdiretezec("limitd", $output)));

	$jac = "\\exprG";
	if ($vars == "dx dy") {

		$texoutput = $texoutput . def_tex("\\ins", "x") . def_tex("\out", "y") . def_tex("\insideintegral", "\href{" . $mawphphome . "/integral/integral2x.php?" . removepercent(remove_dollars(najdiretezec("int2", $output)) . ";lang=$lang}")) . def_tex("\outsideintegral", "\href{" . $mawphphome . "/integral/integraly.php?" . removepercent(remove_dollars(najdiretezec("int1", $output))) . ";lang=$lang}");
		$ins = 'x';
		$out = 'y';
		$insideintegralH = $mawphphome . "/integral/integral2x.php?" . removepercent(remove_dollars(najdiretezec("int2", $output))) . ";lang=$lang";
		$outsideintegralH = $mawphphome . "/integral/integraly.php?" . removepercent(remove_dollars(najdiretezec("int1", $output))) . ";lang=$lang";
	} elseif ($vars == "dy dx") {
		$texoutput = $texoutput . def_tex("\\ins", "y") . def_tex("\out", "x") . def_tex("\insideintegral", "\href{" . $mawphphome . "/integral/integral2y.php?" . removepercent(remove_dollars(najdiretezec("int2", $output))) . ";lang=$lang}") . def_tex("\outsideintegral", "\href{" . $mawphphome . "/integral/integralx.php?" . removepercent(remove_dollars(najdiretezec("int1", $output))) . ";lang=$lang}");
		$ins = 'y';
		$out = 'x';
		$insideintegralH = $mawphphome . "/integral/integral2y.php?" . removepercent(remove_dollars(najdiretezec("int2", $output))) . ";lang=$lang";
		$outsideintegralH = $mawphphome . "/integral/integralx.php?" . removepercent(remove_dollars(najdiretezec("int1", $output))) . ";lang=$lang";
	} elseif ($vars == "r dr dphi") {
		$texoutput = $texoutput . def_tex("\\ins", "r") . def_tex("\out", "\\varphi") . def_tex("\insideintegral", "{}") . def_tex("\outsideintegral", "{}");
		$jac = "\\left(\\exprG\\right) r ";
		$ins = 'r';
		$out = '\\varphi';
		$insideintegralH = '';
		$outsideintegralH = '';
	} else {
		$texoutput = $texoutput . def_tex("\\ins", "\\varphi") . def_tex("\out", "r") . def_tex("\insideintegral", "{}") . def_tex("\outsideintegral", "{}");
		$jac = "\\left(\\exprG\\right) r ";
		$ins = '\varphi';
		$out = 'r';
		$insideintegralH = '';
		$outsideintegralH = '';
	}


	$TeXfile = '
\usepackage{graphicx}
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}
\rightskip 0 pt plus 1 fill

\MAWhead{%s}

%s 

\def\comment#1{\hbox{\qquad\textit{(#1)}}}
\lineskip 10pt
\lineskiplimit 10pt


%s

\parindent = -0.5 in
\leftskip = 0.5 in
$I=\int_{\exprH}^{\exprI}\insideintegral{\int_{\exprJ}^{\exprK} %s \;\textrm{d}\ins}\,\textrm{d}\out$ 

$\phantom{I}=\int_{\exprH}^{\exprI}\left[\exprA\right]_{\exprJ}^{\exprK} \;\textrm{d}\out $ \comment{%s}

$\phantom{I}=\int_{\exprH}^{\exprI} \exprB\;\textrm{d}\out $ \comment{%s}

$\phantom{I}=\outsideintegral{\int_{\exprH}^{\exprI} \exprC\;\textrm{d}\out} $ \comment{%s}

$\phantom{I}=\left[\exprD\right]_{\exprH}^{\exprI} $ \comment{%s}

$\phantom{I}=\exprE$ \comment{%s}

$\phantom{I}=\exprF$ \comment{%s}

\bigskip  
\vfill
\begin{center}
  \includegraphics[width=10cm]{a.pdf}
\end{center}

\end{document}
';

	foreach ($vzorce as $key => $value) {
		$$key = $value;
	}

	$jacHTML = $exprG;

	if ($vars == "r dr dphi") {
		if ($exprG == " 1 ") {
			$jacHTML = "r";
		} else {
			$jacHTML = "\\left( $exprG \\right) r ";
		}
	} elseif ($vars == "r dphi dr") {
		if ($exprG == " 1 ") {
			$jacHTML = "r";
		} else {
			$jacHTML = "\\left( $exprG \\right) r ";
		}
	}


	$HTMLskeletonA = '
<h2>%s</h2>

<p>%s </p>

<div class=logickyBlok>
<p>%s %s</p>
</div>';

	$HTMLskeletonB = '
<div class=inlinediv>
<div class=logickyBlok>
<p>$\displaystyle ' . $I . '=\int_{' . $exprH . '}^{' . $exprI . '}\int_{' . $exprJ . '}^{' . $exprK . '} %s \;\textrm{d}' . $ins . '\,\textrm{d}' . $out . '$ 

<p>$\displaystyle \phantom{' . $I . '}=\int_{' . $exprH . '}^{' . $exprI . '}\left[' . $exprA . '\right]_{' . $exprJ . '}^{' . $exprK . '} \;\textrm{d}' . $out . ' $  <span class=komentar>(%s)</span>

<p>$\displaystyle \phantom{' . $I . '}=\int_{' . $exprH . '}^{' . $exprI . '} ' . $exprB . '\;\textrm{d}' . $out . ' $ <span class=komentar>(%s)</span>

<p>$\displaystyle \phantom{' . $I . '}={\int_{' . $exprH . '}^{' . $exprI . '} ' . $exprC . '\;\textrm{d}' . $out . '} $ <span class=komentar>(%s)</span>

<p>$\displaystyle \phantom{' . $I . '}=\left[' . $exprD . '\right]_{' . $exprH . '}^{' . $exprI . '} $ <span class=komentar>(%s)</span>

<p>$\displaystyle \phantom{' . $I . '}=' . $exprE . '$ <span class=komentar>(%s)</span>

<p>$\displaystyle \phantom{' . $I . '}=' . $exprF . '$ <span class=komentar>(%s)</span>
</div></div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
';

	$TeXfile = $TeXheader . sprintf($TeXfile, __("Double integral"), $texoutput, sprintf(__("We integrate the function %s on the set given by %s and %s."), '$ \exprG $', ' $ \exprH\leq \out\leq \\exprI $ ', ' $ \\exprJ\leq \ins\leq \exprK $ '), $jac, __("inside integration"), __("substituting limits"), __("simplification"), __("integration"), __("substituting limits"), __("simplification"));

	if ($outputHTML == "") {
		$outputHTML = $outputHTML . sprintf($HTMLskeletonA, __("Double integral"), "", sprintf(__("We integrate the function %s on the set given by %s and %s."), "$ $exprG $", "$ $exprH \\leq $out \\leq $exprI $ ", " $ $exprJ \leq $ins \\leq $exprK $ "), "\$\$I=\\iint_{M}f\\,\\mathrm{d}x\\mathrm{d}y\\ ,\\qquad\\qquad f=$exprG\\ , \\qquad\\qquad M=\\begin{cases}$exprH \\leq $out \\leq $exprI \\\\  $exprJ \leq $ins \\leq $exprK\\end{cases}\$\$");
	}


	$insideintegrallink = ($insideintegralH == '') ? __("inside integration") : "<a href=\"$insideintegralH\">" . __("inside integration") . "</a>";
	$outsideintegrallink = ($outsideintegralH == '') ? __("integration") : "<a href=\"$outsideintegralH\">" . __("integration") . "</a>";
	$outputHTML = $outputHTML . sprintf($HTMLskeletonB, $jacHTML, $insideintegrallink, __("substituting limits"), __("simplification"), $outsideintegrallink, __("substituting limits"), __("simplification"));

	define("NAZEV_SOUBORUB", $maw_tempdir . "/output");
	$souborb = fopen(NAZEV_SOUBORUB, "w");
	fwrite($souborb, "**** Maxima output ****");
	fwrite($souborb, $output);
	fclose($souborb);

	if ($requestedoutput == "pdf") {
		define("SOUBB", $maw_tempdir . "/doubleint.tex");
		$soubor = fopen(SOUBB, "w");
		fwrite($soubor, $TeXfile);
		fclose($soubor);
		if (function_exists("doubleint_before_latex")) {
			doubleint_before_latex();
		}
		system("cd $maw_tempdir; $epstopdf a.eps; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex doubleint.tex>>output; cp * .. ; $catchmawerrors");
	}

}

rundoubleintegration($funkce, "I", 1);
$outputHTML1 = $outputHTML;
$outputHTML = "  ";
if ($_REQUEST["f1"] == "on") {
	rundoubleintegration("1", "\\iint \\mathrm{d}x\\mathrm{d}y", 0);
}
if ($_REQUEST["fx"] == "on") {
	rundoubleintegration("$xmul*($funkce)", "\\iint xf(x,y)\\mathrm{d}x\\mathrm{d}y", 0);
}
if ($_REQUEST["fy"] == "on") {
	rundoubleintegration("$ymul*($funkce)", "\\iint yf(x,y)\\mathrm{d}x\\mathrm{d}y", 0);
}
if ($_REQUEST["fxx"] == "on") {
	rundoubleintegration("($xmul)^2*($funkce)", "\\iint x^2f(x,y)\\mathrm{d}x\\mathrm{d}y", 0);
}
if ($_REQUEST["fyy"] == "on") {
	rundoubleintegration("($ymul)^2*($funkce)", "\\iint y^2f(x,y)\\mathrm{d}x\\mathrm{d}y", 0);
}

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline = exec("cd " . $maw_tempdir . "; cat errors");

if ($lastline != "") {
	maw_errmsgB("<pre>");
	system("cd " . $maw_tempdir . "; cat output|$mawcat ; cat *.tex");
	system("rm -r " . $maw_tempdir);
	save_log_err($datcasip, "integral2");
	die("</pre></body></html>");
} else {
	if ($requestedoutput == "html") {
		if ($mawISAjax == 0) {
			maw_html_head();
		}
		echo '<style> .komentar {color:gray; margin-left:20px;}</style>';
		$outputHTML1 = str_replace($formconv_repl_input, $formconv_repl_output, $outputHTML1);
		$outputHTML = str_replace($formconv_repl_input, $formconv_repl_output, $outputHTML);
		echo $outputHTML1;
		require("htmlregion.php");
		echo $outputHTML;
		if ($mawISAjax == 0) {
			echo('</body></html>');
		}
	} else {
		/* here we send PDF file into browser */
		send_PDF_file_to_browser("$maw_tempdir/doubleint.pdf");
	}
	save_log($datcasip, "integral2");
	system("rm -r " . $maw_tempdir);
}

?>



