<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2012-2013 Robert Marik

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

$scriptname = "lineintegral";

require("../common/maw.php");

$function = $_REQUEST["function"];
$fx = $_REQUEST["fx"];
$fy = $_REQUEST["fy"];
$fz = $_REQUEST["fz"];
$x = $_REQUEST["x"];
$y = $_REQUEST["y"];
$z = $_REQUEST["z"];
$tmin = $_REQUEST["tmin"];
$tmax = $_REQUEST["tmax"];
$kind = $_REQUEST["kind"];
$dimension = $_REQUEST["dimension"];

check_for_security("$fx, $fx, $fz, $x, $y, $z, $tmin, $tmax, $kind");

if ($kind != "2") {
	$kind = "1";
}
//if ($dimension!="3") {$dimension="2";}

$variables = "t";
$parameters = " ";
$x = input_to_maxima($x);
$y = input_to_maxima($y);
if ($z == "") {
	$dimension = 2;
} else {
	$dimension = 3;
}
if ($dimension == 3) {
	$z = input_to_maxima($z);
}

$variables = "x|y|z";
$fx = input_to_maxima($fx);
if ($kind == "2") {
	$fx = input_to_maxima($fx);
	$fy = input_to_maxima($fy);
	if ($dimension == "3") {
		$fz = input_to_maxima($fz);
	}
} else {
	$function = input_to_maxima($function);
}

$variables = " ";
$tmin = input_to_maxima($tmin);
$tmax = input_to_maxima($tmax);


if ($kind == "1") {
	if ($dimension == 2) {
		$fz = 0;
		$z = 0;
	}
	$datcasip = "function: $function; curve:$x,$y,$z; t=$tmin..$tmax";
	$command = "display2d:false\$ fx:$function \$ x:$x \$ y:$y \$  z:$z \$ tmin:$tmin \$ tmax:$tmax \$ load(\\\"$mawhome/lineintegral/lineintegral1.mac\\\")\$ ";
	$output = `$mawtimeout $maxima2 --batch-string="$command"`;
	$keywords = ["fx", "x", "y", "z", "dx", "dy", "dz", "ds", "tmin", "tmax", "integrand", "primitive", "result", "ok", "integrand-math", "Fa", "Fb"];
} else {
	if ($dimension == 2) {
		$fz = 0;
		$z = 0;
	}
	$datcasip = "function: $fx,$fy,$fz; curve:$x,$y,$z; t=$tmin..$tmax";
	$command = "display2d:false\$ fx:$fx \$ fy:$fy \$ fz:$fz \$ x:$x \$ y:$y \$  z:$z \$ tmin:$tmin \$ tmax:$tmax \$ load(\\\"$mawhome/lineintegral/lineintegral2.mac\\\")\$ ";
	$output = `$mawtimeout $maxima2 --batch-string="$command"`;
	$keywords = ["fx", "fy", "fz", "x", "y", "z", "dx", "dy", "dz", "tmin", "tmax", "integrand", "primitive", "result", "ok", "integrand-math", "Fa", "Fb"];
}

$output = str_replace("\n", " ", $output);
$results = [];
foreach ($keywords as $key => $value) {
	preg_match("/###START $value (.*) $value *END###/s", $output, $matches);
	$result = str_replace("\$\$", "", $matches[1]);
	$results[$value] = $result;
}

if ((!(strpos($results["primitive"], "int") === false)) || (!(strpos($output, "integrate: variable must not be a number;") === false))) {
	maw_html_head();
	echo sprintf("<h2  class='red'>%s</h2>", __("Sorry, an error occurred when processing your input"));
	echo("<h3 class=\"red\">" . __("Maxima failed to find the primitive function.") . "</h3>");
	echo("<pre><span>" . str_replace("END###", "END###\n", show_TeX($output)));
	save_log_err($datcasip . " integral unknown", "lineintegral");
	die("</span></pre></html>");
}

if ($results["ok"] == "") {
	maw_errmsgB();
	echo("<pre><span>" . highlight_errors(str_replace("END###", "END###\n", show_TeX($output))));
	save_log_err($datcasip . " ???", "lineintegral");
	die("</span></pre></html>");
}

if ($kind == 1) {
	$TeX_function = sprintf("%s: $ F=%s$", __("Function"), $results["fx"]);
} else {
	if ($dimension == 2) {
		$TeX_function = sprintf("%s: $ \\vec F=\\left(%s,%s\\right)$", __("Function"), $results["fx"], $results["fy"]);
	} else {
		$TeX_function = sprintf("%s: $ \\vec F=\\left(%s,%s,%s\\right)$", __("Function"), $results["fx"], $results["fy"], $results["fz"]);
	}
}

if ($dimension == 3) {
	$TeX_curve = sprintf("%s $ C$: $ \\vec r(t)=\\Bigl(%s, %s, %s\\Bigr),\\qquad  t \\in[%s,%s]$", __("Curve"), $results["x"], $results["y"], $results["z"], $results["tmin"], $results["tmax"]);
} else {
	$TeX_curve = sprintf("%s $ C$: $ \\vec r(t)=\\Bigl(%s, %s\\Bigr),\\qquad t \\in[%s,%s]$", __("Curve"), $results["x"], $results["y"], $results["tmin"], $results["tmax"]);
}

if ($dimension == 3) {
	$TeX_curve_der = sprintf("%s: $ \\frac{d\\vec r}{dt}=\\Bigl(%s,%s,%s\\Bigr) $", __("Curve derivatives"), $results["dx"], $results["dy"], $results["dz"]);
} else {
	$TeX_curve_der = sprintf("%s: $ \\frac{d\\vec r}{dt}=\\Bigl(%s,%s\\Bigr) $", __("Curve derivatives"), $results["dx"], $results["dy"]);
}

if ($kind == 1) {
	$TeX_length = sprintf("%s: $ %s=%s $ ", __("Length of linear element"), "\\left|\\frac{d\\vec r}{dt}\\right|", $results["ds"]);
	$TeX_int = "\\int_CF\\;\\mathrm{d}s";
} else {
	$TeX_length = "";
	$TeX_int = "\\int_C\\vec F\\;\\mathrm{d}\\vec r";
}


$TeX_transformation = sprintf("%s: \\\\ $ %s=\\int_{%s}^{%s}%s \\;\\mathrm{d}t $ ", __("Transformation to Riemann integral"), $TeX_int, $results["tmin"], $results["tmax"], $results["integrand"]);

$TeX_primitive_function = sprintf("%s: \\\\ $ \\int %s\\;\\mathrm{d}t=%s $ ", __("Primitive function"), $results["integrand"], $results["primitive"]);

$TeX_eval = sprintf("%s: \\\\ $ \\int_{%s}^{%s}%s\\;\\mathrm{d}t=\\left[%s\\right]_{%s}^{%s}=%s-\\left[%s\\right]=%s$", __("Evaluation of definite integral"), $results["tmin"], $results["tmax"], $results["integrand"], $results["primitive"], $results["tmin"], $results["tmax"], $results["Fb"], $results["Fa"], $results["result"]);

$TeX_final = sprintf("%s: \\\\ $ %s=%s$", __("Final answer"), $TeX_int, $results["result"]);

$maw_tempdir = "/tmp/MAW_lineintegral" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

$x_ = formconv_gnuplot($x);
$y_ = formconv_gnuplot($y);
$z_ = formconv_gnuplot($z);
$tmin_ = formconv_gnuplot($tmin);
$tmax_ = formconv_gnuplot($tmax);
$terminal = "postscript eps size 5in,5in";

if ($dimension == 3) {
	system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_parametric_3D.bash \"$x_\" \"$y_\" \"$z_\" \"$tmin_\" \"$tmax_\"  \"$terminal\" \"curve.eps\"; $epstopdf curve.eps");
} else {
	system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_parametric_2D.bash \"$x_\" \"$y_\" \"$tmin_\" \"$tmax_\"  \"$terminal\" \"curve.eps\"; $epstopdf curve.eps");
}

define("SOUB", $maw_tempdir . "/lineint.tex");
$soubor = fopen(SOUB, "w");

$TeXfile = '
\usepackage{graphicx}
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}
\rightskip 0 pt plus 1 fill

\MAWhead{%s}

\leavevmode %s 

\bigskip

\leavevmode \hbox{%s}\qquad \hbox{%s}

\bigskip

%s

\bigskip

%s

\bigskip

%s

\bigskip

%s

\bigskip

%s

\bigskip

\includegraphics{curve.pdf}
\end{document}
';

if ($_REQUEST["output"] == "html"):
	if ($mawISAjax == 0) {
		maw_html_head();
	}
	$TeX_transformation = str_replace("\\\\", "", $TeX_transformation);
	$TeX_primitive_function = str_replace("\\\\", "", $TeX_primitive_function);
	$TeX_eval = str_replace("\\\\", "", $TeX_eval);
	$TeX_final = str_replace("\\\\", "", $TeX_final);
	$xR = rawurlencode($x);
	$yR = rawurlencode($y);
	$zR = rawurlencode($z);
	$tminR = rawurlencode($tmin);
	$tmaxR = rawurlencode($tmax);
	$curveimg = "$mawphphome/gnuplot/curve.php?x=$xR&y=$yR&z=$zR&tmin=$tminR&tmax=$tmaxR&svg=1";
	$curveimg = "<img alt=\"" . __("Processing image") . "\"src=\"$curveimg\"/>";
	$TeXfile = "<h2>" . __("Line integral") . "</h2><div class=logickyBlok><div class=inlinediv><p>" . $TeX_function . "</p><p>" . $TeX_curve . "</p></div></div><div class=inlinediv><div class=logickyBlok><p>" . $TeX_curve_der . "</p><p>" . $TeX_length . "</p><p>" . $TeX_transformation . "</p></div></div> <div class=inlinediv><div class=logickyBlok><p>" . $TeX_primitive_function . "</p><p>&nbsp;&nbsp;(<a target=\"_blank\" href=\"$mawhtmlhome/index.php?lang=$lang&form=integral&variable=t&function=" . rawurlencode($results["integrand-math"]) . "\">" . __("Help to find the primitive function") . "</a>)</p><p>" . $TeX_eval . "</p></div></div><div class=logickyBlok><p>" . $TeX_final . "</p></div>";
	echo $TeXfile;
	echo "<div class=logickyBlok><div class=centerimg><p>$curveimg</p></div></div>";
	if ($mawISAjax == 0) {
		echo('</body></html>');
	}
	save_log($datcasip, "lineintegral");
	system("rm -r " . $maw_tempdir);
	die();
endif;

$TeXfile = $TeXheader . sprintf($TeXfile, __("Line integral"), $TeX_function, $TeX_curve, $TeX_curve_der, $TeX_length, $TeX_transformation, $TeX_primitive_function, $TeX_eval, $TeX_final);
fwrite($soubor, $TeXfile);
fclose($soubor);

define("NAZEV_SOUBORUB", $maw_tempdir . "/output");
$souborb = fopen(NAZEV_SOUBORUB, "w");
fwrite($souborb, "**** Maxima output ****");
fwrite($souborb, $output);
fclose($souborb);

system("cd $maw_tempdir; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex lineint.tex>>output; $catchmawerrors");

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline = exec("cd " . $maw_tempdir . "; cat errors");

if ($lastline != "") {
	maw_errmsgB("<pre>");
	system("cd " . $maw_tempdir . "; cat output|$mawcat");
	echo $TeXfile;
	system("rm -r " . $maw_tempdir);
	save_log_err($datcasip, "lineintegral");
	die("</pre></body></html>");
} else {
	/* here we send PDF file into browser */
	send_PDF_file_to_browser("$maw_tempdir/lineint.pdf");
	save_log($datcasip, "lineintegral");
	system("rm -r " . $maw_tempdir);
}

?>