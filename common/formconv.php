<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
Copyright 2008-2012 Robert Marik

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

require("maw.php");

$variables = 'x|y|z|t';

$formconverror = "\\usepackage{color}\\color{red}\\bf???";

$vstup = rawurldecode($_REQUEST["expr"]);
$vars = $_REQUEST["vars"];
$region = $_REQUEST["region"];

$vstup = str_replace("tg", "tan", $vstup);
$vstup = str_replace("cotan", "cot", $vstup);
$vstup = str_replace("y\'", "y'", $vstup);

function convert_expr($vstup)
{
	global $formconverror, $formconv_bin;
	$vystup = `$mawtimeout echo "$vstup" | $formconv_bin`;
	$vystup = chop($vystup);
	$vystup = formconv_replacements($vystup);
	if ($vystup == "") {
		$vystup = $formconverror;
	}

	return (formconv_replacements($vystup));
}

echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html><head>\n <meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\">\n  <link rel=\"stylesheet\" type=\"text/css\" href=\"../common/styl.css\" >\n");
echo("<title>" . __("Mathematical Assistant on Web") . "</title>");

if (file_exists('../common/custom.css')) {
	echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"../common/custom.css\" >");
}

$mawhead_used = 1;
?>

<script type="text/javascript">

	var whichone;

	function writedata() {
		whichone = opener.thenumber

	}

	function updateit() {
		opener.document.forms['exampleform'].elements[whichone].value = "<?php echo($vystupb);?>";
		window.close()
	}
</script>


<?php
echo $maw_html_custom_head;
echo("</head>\n<body onload=\"writedata()\">\n");

$vystup = convert_expr($vstup);

if (($vars != "") && ($region == "1")) {
	$vars1 = rawurlencode($vars);
	$parameters = " ";
	$variables = " ";
	foreach (["a", "b", "xmin", "xmax", "ymin", "ymax"] as $value) {
		$$value = $_REQUEST[$value];
		$$value = input_to_maxima($$value);
		$$value = rawurlencode($$value);
	}

	echo "<h3>" . __("Region for integration") . "</h3>";
	if ($vars == "dy dx") {
		$variables = "x";
		foreach (["c", "d"] as $value) {
			$$value = $_REQUEST[$value];
			$$value = input_to_maxima($$value);
			$$value = rawurlencode($$value);
		}

		$parameters = "size=400x400&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
		echo("<span class=\"red\"><img src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"" . __("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.") . " " . __(" Submit the form to check, if the region for integration is well defined.") . "\"></span>");
		//echo ("<img src=\"$mawphphome/ineq2d/ineq2d.php?onevar=0&podmnez=&axislabels=on&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&akce=1&funkce=".rawurlencode("log(x-($a))+log(($b)-x)+log(($d)-y)+log(y-($c))")."\">");
	} elseif ($vars == "dx dy") {
		$variables = "y";
		foreach (["c", "d"] as $value) {
			$$value = $_REQUEST[$value];
			$$value = input_to_maxima($$value);
			$$value = rawurlencode($$value);
		}

		$parameters = "size=400x400&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
		echo("<span class=\"red\"><img src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"" . __("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.") . " " . __(" Submit the form to check, if the region for integration is well defined.") . "\"></span>");
		//echo ("<img src=\"$mawphphome/ineq2d/ineq2d.php?onevar=0&podmnez=&axislabels=on&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&akce=1&funkce=".rawurlencode("log(y-($a))+log(($b)-y)+log(($d)-x)+log(x-($c))")."\">");
	} elseif ($vars == "r dphi dr") {
		$variables = "r";
		foreach (["c", "d"] as $value) {
			$$value = $_REQUEST[$value];
			$$value = input_to_maxima($$value);
			$$value = rawurlencode($$value);
		}
		$parameters = "size=400x400&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
		echo("<span class=\"red\"><img src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"" . __("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.") . " " . __(" Submit the form to check, if the region for integration is well defined.") . "\"></span>");
		//echo ("<img src=\"$mawphphome/ineq2d/ineq2d.php?onevar=0&podmnez=&axislabels=on&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&akce=1&funkce=".rawurlencode("log(atan(y/x)-($a))+log(($b)-atan(y/x))+log(($d)^2-(x^2+y^2))+log(x^2+y^2-($c)^2)")."\">");
	} elseif ($vars == "r dr dphi") {
		$variables = "phi";
		foreach (["c", "d"] as $value) {
			$$value = $_REQUEST[$value];
			$$value = input_to_maxima($$value);
			$$value = rawurlencode($$value);
		}
		$parameters = "size=400x400&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
		echo("<span class=\"red\"><img src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"" . __("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.") . " " . __(" Submit the form to check, if the region for integration is well defined.") . "\"></span>");
		//echo ("<img src=\"$mawphphome/ineq2d/ineq2d.php?onevar=0&podmnez=&axislabels=on&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&akce=1&funkce=".rawurlencode("log(y-x*tan($a))+log(x*tan($b)-(y))+log(($d)^2-(x^2+y^2))+log(x^2+y^2-($c)^2)")."\">");
	} else {
		echo __("Under construction");
	}
} else {

	if ($vars == "") {
		if ($vystup != $formconverror) {
			echo __("Formula");
			echo ': <img align="bottom" src="', $texrender, $vystup, '" alt="math formula"/><br><br>';
			$vystupb = `$mawtimeout echo "$vstup" | $formconv_bin -O maxima -a`;
			$vystupb = highlight_parentheses(beautify_parentheses(str_replace("diff(y, x)", "y'", chop($vystupb))));
			echo __("In computer notation");
			echo ': <b>', $vystupb, '</b><br>';
		} else {
			echo '<span class="red bold">' . __("Formconv (program which parses your input) failed. Correct the formula on your input.") . '</span><br>';
			echo sprintf(__("You may try to pass your expression to %sWolframAlpha%s."), "<a href=\"http://www.wolframalpha.com/input/?i=" . rawurlencode($vstup) . "\">", "</a>") . "<hr>";
			echo(input_to_maxima($vstup));
		}
	} else {
		$a = $_REQUEST["a"];
		$b = $_REQUEST["b"];
		$c = $_REQUEST["c"];
		$d = $_REQUEST["d"];
		$vars = $_REQUEST["vars"];
		if ($vars == "dx dy") {
			$vars = "\\textrm{d}x\\, \\textrm{d}y";
		} elseif ($vars == "dy dx") {
			$vars = "\\textrm{d}y\\, \\textrm{d}x";
		} elseif ($vars == "r dr dphi") {
			$vars = "r\\;\\textrm{d}r\\, \\textrm{d}\\varphi";
		} elseif ($vars == "r dphi dr") {
			$vars = "r\\;\\textrm{d}\\varphi\\, \\textrm{d}r";
		}
		echo __("Formula");
		echo ': <img align="center" src="', $texrender, "\\int_{", convert_expr($a), "}^{", convert_expr($b), "}", "\\int_{", convert_expr($c), "}^{", convert_expr($d), "}\\left(", convert_expr($vstup), "\\right)\\;", $vars, '"/><br><br>';

	}

}
?>

<br>

<form name="checkit">
	<input type="hidden" id="data"/>
	<?php
	echo '<input type="button" value="', __("Close") . '" onclick="window.close()">';

	save_log($vstup, "formconv-preview");

	?>
</form>
</body>
</html>
