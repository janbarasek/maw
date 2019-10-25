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

$texpath = "/usr/bin/";
$pdflatex = $texpath . "pdflatex";
$latex = $texpath . "latex";
$dvips = $texpath . "dvips";
$epstopdf = $texpath . "epstopdf";
$mpost = $texpath . "mpost";
$ps2pdf = "/usr/bin/ps2pdf";
$bash = "/bin/bash";


$texrender = "http://www.openmaths.org/cgi-bin/mathtex.cgi?";
$texred = "red";  // color in hints in integral assistant

$mawhome = "/var/www/maw";
$mawcat = $mawhome . "/common/mawcat";
$mawtimeout = $mawhome . "/support/timeout";

$mawphphome = "/maw";
$mawhtmlhome = "/maw-html";

$formconv_bin = "/usr/local/bin/formconv ";
$maxima = "maxima";

$maxima2 = "maxima";   // maxima called from domf  - maxima 5.13 is fast but 


$load_limit = 5;  // no computation if the server load exceeds this limit
$processes_limit = 70;  // no computation if the number of processes on server exceeds this limit

$detect_mendelu = false;

$maw_allow_cache = true;


$maw_cache_directory = "/tmp/MAWcache_";

$maw_mathjax = <<<EOD

 <script type="text/x-mathjax-config">
     MathJax.Hub.Register.StartupHook("TeX Jax Ready",function () {
          var TEX = MathJax.InputJax.TeX;
          var PREFILTER = TEX.prefilterMath;
          TEX.Augment({
            prefilterMath: function (math,displaymode,script) {
              math = "\\\\displaystyle{"+math+"}";
              return PREFILTER.call(TEX,math,displaymode,script);
            }
          });
        });
 </script>

<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
      inlineMath: [ ['$','$'], ["\\\\(","\\\\)"] ],
      displayMath: [ ['$$','$$'], ["\\\\[","\\\\]"] ],
      skipTags: ["script","noscript","style","textarea","code"],
      processEscapes: true
    },
    "HTML-CSS": { availableFonts: ["TeX"] }
  });
</script>

<script type="text/javascript"
   
src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>
EOD;

$TeX_language = '';

$maw_html_custom_head = "";
$maw_html_custom_body = "";

$TeX_rgbcolor = "0.612,0.902,0.953";

$lang = $_REQUEST["lang"];
if ($lang == "cz") {
	$lang = "cs";
}
if ($lang == "us") {
	$lang = "en";
}
if ($lang == "ua") {
	$lang = "uk";
}
$lang_array = ["cs", "en", "pl", "ca", "zh", "fr", "ru", "de", "it", "uk", "es"];
if (!(in_array($lang, $lang_array))) {
	$lang = "en";
}

$variables = 'x|y';
$parameters = 'a|b';
$constants = '%e|%pi';

$maw_URI = $_SERVER["REQUEST_URI"];
$maw_ip = $_REQUEST["ip"];

if (!(stristr($maw_URI, '?'))) {
	$maw_URI = $maw_URI . "?";
	$tempflag = false;
	foreach ($_POST as $key => $term) {
		if ($tempflag) {
			$maw_URI = $maw_URI . "&";
		}
		$tempflag = true;
		$maw_URI = $maw_URI . "$key=" . urlencode(htmlspecialchars($term));
	}
}

if ($lang == "cs") {
	$langl = "cs_CZ";
	$locale_file = "cs_CZ";
}
if ($lang == "en") {
	$langl = "en_US";
	$locale_file = "en_US";
}
if ($lang == "pl") {
	$langl = "pl_PL";
	$locale_file = "pl_PL";
}
if ($lang == "ca") {
	$langl = "ca_ES";
	$locale_file = "ca_ES";
}
if ($lang == "fr") {
	$langl = "fr_FR";
	$locale_file = "fr_FR";
}
if ($lang == "zh") {
	$langl = "zh_CN";
	$locale_file = "zh_CN";
}
if ($lang == "ru") {
	$langl = "ru_RU";
	$locale_file = "ru_RU";
}
if ($lang == "de") {
	$langl = "de_DE";
	$locale_file = "de_DE";
}
if ($lang == "it") {
	$langl = "it_IT";
	$locale_file = "it_IT";
}
if ($lang == "uk") {
	$langl = "uk_UA";
	$locale_file = "uk_UA";
}
if ($lang == "es") {
	$langl = "es_ES";
	$locale_file = "es_ES";
}
setlocale(LC_MESSAGES, $langl . ".UTF-8");
bindtextdomain("messages", "../locale");
textdomain("messages");
bind_textdomain_codeset("messages", "UTF-8");
function __($text)
{
	return gettext($text);
}

$maw_processing_msg = "\n<div id=\"processing\"><img src=\"../common/loading.gif\" align=\"middle\" alt=\"processing\"> " . __("Processing request") . "...</div>";

if (file_exists('../common/mawconfig.php')) {
	require('../common/mawconfig.php');
}


$catchmawerrors = "grep Incorrect output>errors; grep incorrect output>errors; grep error output >>errors; grep Error output >>errors; grep ERROR output>>errors";

function ae_detect_ie()
{
	if (isset($_SERVER['HTTP_USER_AGENT']) &&
		(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
		return true;
	else
		return false;
}


function mendelu_detect()
{
	global $detect_mendelu;
	if ($detect_mendelu == false) {
		return true;
	}
	$referer = $_SERVER['HTTP_REFERER'];
	if ((strpos($referer, 'mendelu') !== false) || (strpos($referer, 'localhost') !== false))
		return true;
	else {
		maw_html_head("MAWerror");
		echo("<h2>");
		echo(__("Accessing this script from a web page which is outside Mendel University is not allowed. You either came from such a page, or your browser blocks the HTTP_REFERER information.<br> Use official pages of <a href=\"http://user.mendelu.cz/marik/maw/index.php?lang=en\">Mathematical Assistant on Web</a> or setup your browser properly."));
		echo("</h2>");
		save_log("External link blocked: " . $referer, "external_blocked");
		die();

		return false;
	}
}


function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());

	return ((float) $usec + (float) $sec);
}


$mawstarttime = getmicrotime();

$mawhead_used = 0;
function maw_html_head($message = "")
{
	global $err_fieldname, $mawhead_used, $maw_html_custom_head, $maw_html_custom_body, $maw_mathjax;
	if ($mawhead_used == 1) {
		echo('<span style="display:none;"> ' . $message . ' </span>');

		return;
	}
	{
		echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html>\n<head>\n <meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\">\n <link rel=\"stylesheet\" type=\"text/css\" href=\"../common/styl.css\" >\n");
		echo("<title>" . __("Mathematical Assistant on Web") . "</title>");

		if (file_exists('../common/custom.css')) {
			echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"../common/custom.css\" >\n");
		}

		echo $maw_mathjax;

		echo $maw_html_custom_head;

		echo("\n</head>\n<body alink=\"#ee0000\" link=\"#0000ee\" vlink=\"#551a8b\">\n $maw_html_custom_body");
		echo('<span style="display:none;"> ' . $message . ' </span>');
		echo("<div class=\"support\">");
		if ("http://sourceforge.net/projects/mathassistant/forums/forum/796831" != __("http://sourceforge.net/projects/mathassistant/forums/forum/796831")) {
			echo '
<a href="' . __("http://sourceforge.net/projects/mathassistant/forums/forum/796831") . '">' . __("Support request") . '</a><br>';
		}
		echo("<a href=\"http://sourceforge.net/tracker/?group_id=221048&amp;atid=1052162\">" . __("Report bug") . "</a></div>");
	}
	$mawhead_used = 1;
	if ($err_fieldname != "") {
		print ("<h3 class='red'>");
		printf(__("Error when processing input from the field %s."), $err_fieldname);
		print ("</h3>");
	}
}

$errDc = "<br>" . __("You can use WYSIWYG editor <a href=\"http://user.mendelu.cz/tihlarik/dragmath/DragMath.html\" target=\"_blank\">Dragmath</a> to edit mathematical expressions and then move the expression into field in the form by using clipboard in your operating system. This editor is accessible at the end of some input fields, where we suppose nontrivial input. In these cases it is not necessary to use the clipboard and the transfer into field in our form is automatical.");
$errDc = "";

function maw_howto()
{
	global $variables, $parameters, $functions;
	$tempvar = str_replace("|", ", ", $variables);
	$temppar = str_replace("|", ", ", $parameters);
	$tempfun = str_replace("|", ", ", $functions);
	echo '<hr>';
	echo("\n<h3>" . __("Functions and characters available for mathematical notation") . sprintf("</h3>\n %s<ul><li>%s</li><li>", __("You can enter decimal number or mathematical formula which contains"), __("integers (no decimals)")));
	if ($tempvar != ' ') {
		if (strlen($tempvar) == 1) {
			echo sprintf("<span class='bold'>" . __('variable %s') . "<span>", $variables);
		} else {
			echo sprintf("<span class='bold'>" . __('variables %s') . "<span>", $tempvar);
		}
	} else {
		echo "<span class='bold'>" . __("no variables") . "</span>";
	}

	echo sprintf("</li>\n<li>%s</li>\n<li>%s</li>\n<li>%s</li>\n<li>%s</li>\n<li>%s %s</li>", __("constants %e and %pi"), __("addition, subtraction, multiplication and division, i.e. chars + - * /"), __("power ^ or **"), __("round parentheses"), __("functions"), $tempfun);
	if ($parameters != ' ')
		echo "\n<li>" . sprintf(__("parameters %s"), $temppar) . "</li>";
	echo("\n</ul>\n");
}

function maw_errmsgB($maw_string = "")
{
	maw_html_head("MAWerror");
	echo sprintf("<h2  class='red'>%s</h2>", __("Sorry, an error occurred when processing your input"));
	echo "<h3>" . __("Check your input data") . "</h3>";
	echo __("The following messages have been reported by external programs and they may help you to find the error.<br><br> Still in troubles? You can contact the authors. Email address is on the main page.") . "<br><br><BR>";
	echo("$maw_string");
}


function check_for_security($maw_string)
{
	global $lang, $errDc;
	/* We check for insecure commands, taken from maximaphp                 */
	/* ---------------------------------------------------------------------*/

	$insecureMaximaKeywords =
		[

			'limit', 'integrate', 'derivative', 'matrix', 'integral',
			'diff',
			'to_lisp',
			'to-maxima',
			'system',
			'eval_string',
			'compfile',
			'compile',
			'translate',
			'translate_file',
			'compile_file',
			'run_testsuite',
			'bug_report',
			'build_info',
			'demo',
			'appendfile',
			'batch',
			'batchload',
			'closefile',
			'filename_merge',
			'file_search',
			'file_type',
			'loadfile',
			'save',
			'stringout',
			'with_stdout',
			'writefile',
			'room',
			'status',
			'setup_autoload',
			'opena',
			'openr',
			'openw',
			'read_matrix',
			'read_lisp_array',
			'read_maxima_array',
			'read_hashed_array',
			'read_nested_list',
			'read_list',
			'write_data',
			'entermatrix',
			'openplot_curves',
			'xgraph_curves',
			'plot2d_ps',
			'psdraw_curve',
			'pscom',
			'dataplot',
			'histogram',
		];

	foreach ($insecureMaximaKeywords as $keyword) {
		if (stristr($maw_string, $keyword)) {
			maw_html_head("MAWerror");
			echo("<h3 class='red'>" . __("Error") . "</h3>");
			printf("<b>" . __("Check your input data") . "</b>: %s<br>", str_replace($keyword, "<span class='red letterspaced'>" . $keyword . "</span>", $maw_string) . "<br>");
			save_log($maw_string, "security");
			if ($keyword == "integrate") {
				echo("<b>" . __("Enter integrand only. Not the whole integral.") . "</b><br>");
			}
			echo(sprintf(__("Insecure or unsupported command or function %s. Do not use this command, please. If you insist on this command, use the full Maxima software."), "<b>$keyword</b>"));
			maw_howto();
			die($errDc . "</body></html>");
		}
	}

	return ("OK");
}

function maw_computing_time()
{
	global $mawstarttime;
	$mawsavetime = getmicrotime();

	return (round($mawsavetime - $mawstarttime, 3));
}

function maw_computing_time_string($str)
{
	return (" (computing time " . $str . " s) ");
}

function current_server_load()
{
	$load = explode(' ', `uptime`);

	return $load[count($load) - 3];
}

function current_processes_running()
{
	global $mawtimeout, $formconv_bin;
	$processes = `ps ax | grep $mawtimeout | grep -v /usr/bin/formconv | wc -l | tr -d " "`;

	return $processes;
}

function save_log($maw_string, $soubor)
{
	global $lang, $scriptname, $maw_URI, $maw_ip;
	if (stristr($maw_URI, '?')) {
		$temp = " <a href=\"../.." . $maw_URI . "&amp;internal=nosavelog\">link</a>";
	}
	if (((!(stristr($maw_URI, "nosavelog"))) && (!(stristr($maw_URI, "format=png")))) || (stristr($soubor, "too_fast_error"))) {
		if ($soubor == "errors") {
			$maw_string = $scriptname . " " . $maw_string;
		}
		$handle = fopen("../common/log/$soubor.log", "a");
		$maw_length = maw_computing_time();
		fwrite($handle, date("d.M.Y, H:i:s, ") . maw_computing_time_string($maw_length) . $temp . ": " . $maw_string . " jazyk:$lang<br>\n");
		fclose($handle);
		$handle = fopen("../common/log/access.log", "a");
		fwrite($handle, date("d.M.Y, H:i:s, ") . " IP:<a href=\"http://baremetal.com/cgi-bin/dnsip?target=$maw_ip\">$maw_ip</a> " . maw_computing_time_string($maw_length) . $temp . ": " . $maw_string . " jazyk:$lang<br>\n");
		fclose($handle);
		if ((($maw_length > 10) && (!(stristr($maw_URI, "integral.php")))) || ($maw_length > 15)) {
			$handle = fopen("../common/log/long.log", "a");
			fwrite($handle, date("d.M.Y, H:i:s, ") . maw_computing_time_string($maw_length) . " load: " . current_server_load() . " " . $temp . ": " . $maw_string . " jazyk:$lang<br>\n");
			fclose($handle);
		}
	}
}

function save_log_err($maw_string, $soubor)
{

	save_log("<span style='color: rgb(255, 0, 0);'>" . $maw_string . " ERROR</span>", $soubor);
	save_log("<span style='color: rgb(255, 0, 0);'>" . $maw_string . " ERROR</span>", "all-errors.log");


}


function check_char($znaky, $maw_string, $hlaska, $formconvswitch, $sprintf = 0)
{
	global $lang, $errDc;
	if (preg_match("~$znaky~", $maw_string, $check_char_match)) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo "<h3 class='red'>" . __("Error in mathematical notation") . "</h3>";
		printf("<b>" . __("Check your input data") . "</b>: %s<br><br>", str_replace($check_char_match[0], "<span class='red letterspaced'>" . $check_char_match[0] . "</span>", $maw_string));
		if ($sprintf == 1) {
			$hlaska = sprintf($hlaska, "\n<span class=\"red\"><span class=\"highlight\">" . $check_char_match[0] . "</span></span>");
		}
		echo($hlaska);
		if ($formconvswitch == 1) {
			hint_mimetex($maw_string);
		}
		maw_howto();
		die($errDc . "</body></html>");
	}
}

$functions = 'asinh|acosh|atanh|acoth|sinh|cosh|tanh|coth|abs|asin|acos|atan|acot|sin|cos|tan|cot|log|exp|sqrt|sec|csc';

$functionsh = 'asinh|acosh|atanh|acoth|sinh|cosh|tanh|coth|abs|log|exp|sqrt|sec|csc';

function highlight_parentheses($maw_string, $error = false)
{
	$j = strlen($maw_string);
	$parlevel = 0;
	$output = "";
	if ($error) {
		$err = "err";
	} else {
		$bg = "";
	}
	for ($k = 0; $k < $j; $k++) {
		$char = substr($maw_string, $k, 1);
		if ($char == "(") {
			$parlevel++;
			$temp = $parlevel % 4;
			$output = $output . "<span class='color$temp$err'>(";
		} elseif ($char == ")") {
			$parlevel--;
			$output = $output . ")</span>";
		} else {
			$output = $output . "$char";
		}
	}

	return "<span class='color0$err'>$output</span>";
}

function check_math_errors($maw_string)
{
	global $errDc;
	global $lang, $scriptname;
	global $functions, $functionsh, $variables, $constants, $parameters, $check_char_match;

	if (substr_count($maw_string, "(") != substr_count($maw_string, ")")) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo("<h3 class='red'>" . __("Error occurred when processing your formula.") . "</h3>" . __("Unmatched parenthesis has been found. Correct the problem please."));
		echo("<br><br><b>" . __("Check your input data") . "</b>: <b>" . highlight_parentheses($maw_string) . "</b>");
		echo("<br><br>" . sprintf(__("You used %s opening and  %s closing parentheses."), substr_count($maw_string, "("), substr_count($maw_string, ")")));
		echo("<br>");
		echo("<br><b>" . highlight_parentheses($maw_string, true) . "</b>");
		maw_howto();
		die("$errDc</body></html>");
	}

	$functionsU = strtoupper($functions);
	$functionshU = strtoupper($functionsh);

	$badMaximaKeywords
		= [
		'diff', 'int', 'lim', 'dx', 'dy', '\((\*|/)', '(\+|-|\*|/)\)',
		'(\))([0-9]+|' . $constants . '|' . $variables . ')',
		'(' . $constants . '|[0-9]+|' . $variables . '|\))(' . $constants . '|' . $variables . '|' . $functionsU . '|\()',
		'(' . $variables . '|' . $functionsU . ')([0-9]+|' . $constants . '|' . $variables . ')',
		'(' . $functionsU . ')[^(H]',
		'(' . $functionshU . ')[^(]', '\|',
		'\*\*\*',
	];

	$badMaximaFunctions = ['erf', 'pow'];

	foreach ($badMaximaFunctions as $keyword) {
		if (stristr($maw_string, $keyword)) {
			maw_html_head("MAWerror");
			echo("<h2  class='red'>" . sprintf(__("Unsupported function %s."), $keyword) . "</h2>");
			printf("<b>" . __("Check your input data") . "</b>: %s<br><br>", str_replace($keyword, "<span class='red  letterspaced'>" . $keyword . "</span>", $maw_string));
			echo __("Do not use this function. If you need to use this function in your computation, run the computation in Maxima on your local computer.");
			save_log($maw_string, "errors");
			maw_howto();
			die($errDc . "</body></html>");
			$check_math = $keyword;
		}
	}

	$check_math = "";   // this variable can be probably deleted

	if ($maw_string == "") {
		save_log("Prazdny retezec", "errors");
		maw_html_head("MAWerror");
		echo "<h3 class='red'>" . __("Error, empty string on input.") . "</h3>";
		maw_howto();
		die("$errDc</body></html>");
	}

	if (preg_match('~(\*|-|\+|/)$~', $maw_string, $problem)) {
		maw_html_head("MAWerror");
		echo("<h2 class='red'>" . __("Error occurred when processing your formula.") . "</h2>");
		echo(__("Incorrect input") . ": <span class='bold'>" . substr($maw_string, 0, -1) . "<span class='red'>$problem[0]</span></span><br>");
		echo "<span class='red bold'>" . sprintf(__("Binary operator %s found at the end of the string."), "<span class='bold red'>$problem[0]</span>") . "</span>";
		save_log("Binop at the end: $problem[0]", "errors");
		maw_howto();
		die("</body></html>");
	}

	if (preg_match('~^(\*|/)~', $maw_string, $problem)) {
		maw_html_head("MAWerror");
		echo("<h2 class='red'>" . __("Error occurred when processing your formula.") . "</h2>");
		echo(__("Incorrect input") . ": <span class='bold'><span class='red'>$problem[0]</span>" . substr($maw_string, 1) . "</span><br>");
		echo "<span class='red bold'>" . sprintf(__("Binary operator %s found at the beginning of the string."), "<span class='bold red'>$problem[0]</span>") . "</span>";
		save_log("Binop at the end: $problem[0]", "errors");
		maw_howto();
		die("</body></html>");
	}

	check_char("\.|,", $maw_string, sprintf("<ul><li>%s</li> <li>%s</li><li>%s</li><li>%s</li></ul>", __("Do not use comma."), __("Decimal number (dot separates decimal places) cannot be a part of another mathematical expression."), __("Do not use dot for multiplication."), __("Multiplication is denoted by star, write e.g. 2*x.")), 0);

	check_char("aa|bb", $maw_string, sprintf("<ul><li>%s</li> </ul>", __("Variables aa or bb are not allowed.")), 0);

	check_char("π", $maw_string, sprintf("<ul><li>%s</li></ul>", __("Char π is not allowed. Write %pi or pi instead.")), 0);

	check_char("∞|inf|n%ek", $maw_string, sprintf("<ul><li>%s</li></ul>", __("Char ∞ is not allowed. No part of MAW works with infinity. You are doing something curious. If you want to evaluate limits or improper integrals on computer, use some of computer algebra systems like Sage or Maxima (both are free to install and also available to try live on the Internet, without installing anything on your local computer).")), 0);

	check_char("²", $maw_string, sprintf("<ul><li>%s</li> </ul>", __("Use ^2 to insert second power. Not ².")), 0);

	check_char("½", $maw_string, sprintf("<ul><li>%s</li> </ul>", __("Use 1/2 to insert one half. Not ½.")), 0);

	check_char("[\+\-/][\+\-\*/]", $maw_string, "<ul><li>" . __("You cannot write group of two or more characters like %s.") . " " . __("Delete one of these characters or use parentheses properly.") . "</li></ul>", 0, 1);

	check_char("[\+\-/][\+\-/]", $maw_string, "<ul><li>" . __("You cannot write group of two or more characters like %s.") . " " . __("Delete one of these characters or use parentheses properly.") . "</li></ul>", 0, 1);

	check_char('\\\\', $maw_string, __("You used backslash or quote or double-quote or another insecure character in your input, which is not allowed. Do you try to enter the functions in TeX notation? Use notation of the Maxima program, please."), 0);

	check_char("(log10)|(log2)", $maw_string, "<br>" . __("Use only natural logarithm.") . "</b><br>" . __("Do not use decadic logarithm, please."), 0);

	check_char("($functions)\*\*", $maw_string, __("You used a name of a function followed by a power.<br>Do not use this notation. Write for example (sin(x))^2 instead of sin^2(x)."), 1);

	if ((preg_match("#($functions)([^(h]|\$)#", $maw_string, $problem_in_match)) || (preg_match("#($functionsh)([^(]|\$)#", $maw_string, $problem_in_match))) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		printf("<h3 class='red'>%s</h3>", __("Error, sorry. The name of the function is not followed by the parentheses."));
		printf("<b>" . __("Check your input data") . "</b>: <span class='text letterspaced'>%s</span><br>", str_replace($problem_in_match[0], "<span class='red letterspaced'>" . $problem_in_match[0] . "</span>", $maw_string) . "<br>");
		echo(__("<ul><li>The name of the function must be followed by its argument enclosed in parenthesis. Write e.g. sin(x) instead of sin x and sin(2*x) instead of sin 2*x</li><li>Remember that if you want to write power of a function, you cannot write something like sin^2(x), but (sin(x))^2.</li><li>For exponential function use exp(x) or %e^x or e^x.</li></ul>"));
		printf("<hr>" . _("Your input is (after deleting spaces and conversion ^ into **) %s and the disallowed substring catched by the input filter is %s."), "<span class='text letterspaced'>$maw_string</span>", "<span class='red letterspaced'>$problem_in_match[0]</span>" . "</body></html>");
		hint_mimetex($maw_string);
		maw_howto();
		save_log($maw_string, "errors");
		die($errDc . "</body></html>");
	}

	if ((preg_match("~[^0-9]\.~", $maw_string)) || (preg_match("~\.[^0-9]~", $maw_string))) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo("<h3 class='red'>" . __("Error occurred when processing your formula.") . "</h3>");
		printf("<b>" . __("Check your input data") . "</b>: <span class='letterspaced'>%s</span><br>", $maw_string);
		echo(__("Use dot only for decimal numbers, if necessary. Do not use dot for multiplication. The operator for multiplication is star, write for example 2*x for \"two eks\". If you wish to enter decimal number, this decimal number cannot be a part of mathematical formula.") . "<br>");
		maw_howto();
		die("$errDc</body></html>");
	}

	if (stristr($maw_string, ",")) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo("<h3 class='red'>" . __("Error occurred when processing your formula.") . "</h3>");
		printf("<b>" . __("Check your input data") . "</b>: <span class='letterspaced'>%</span><br>", $maw_string);
		echo(__("Do not use comma. To separate decimal places use the dot. If you wish to enter decimal number, this decimal number cannot be a part of mathematical formula.") . "<br>");
		maw_howto();
		die("$errDc</body></html>");
	}

	$maw_stringU = $maw_string;
	foreach (preg_split('/\|/', $functions) as $keyword) {
		$maw_stringU = str_replace($keyword, strtoupper($keyword), $maw_stringU);
	}
	foreach ($badMaximaKeywords as $keyword) {
		if (preg_match("~$keyword~", $maw_stringU, $problem)) {
			maw_html_head("MAWerror");
			printf(__("<h3>Error in mathematical notation</h3>Use lowercase letters only and notation from the program <a href=\"http://maxima.sourceforge.net\">Maxima</a>. <ul><li>Use star to denote multiplication, write for example 3*x^3-6*x+1</li><li>Use parenthesis to denote the scope of the operation, for example sin(2*x)+ln(x)</li><li>Do not use notation  sin^2(x), but (sin(x))^2.</li><li>Use abs(x) for absolute value, not |x|</li><li>Constants for the area of unit circle and the euler number are %%pi and %%e (yes, with percent sign, read the  <a href=\"http://maxima.sourceforge.net/docs/manual/en/maxima.html\">manual</a> to the Maxima program, a computer algebra system used for all computations).</li></ul><hr> Your input is (after omitting spaces and conversion ^ into **): %s and the problem is with the substring %s<br><br> Still in troubles? You can contact the authors. Email address is on the main page."), "<span class='text letterspaced'><b>" . strtolower(str_replace("$problem[0]", "<b class='red letterspaced'>$problem[0]</b>", $maw_string)) . "</b></span>", "<b><span class='letterspaced red'>" . strtolower($problem[0]) . "</span></b>");
			save_log($maw_string, "errors");
			hint_mimetex($maw_string);
			$maw_stringB = preg_replace("~exp~", "EXP", $maw_string);
			$maw_stringB = preg_replace("~([0-9]|x|y|%e|%pi|\))(%pi|%e|x|y|abs|tan|cot|asin|acos|atan|log|cos|EXP|sin|sec|\()~", "\\1*\\2", $maw_stringB);
			$maw_stringB = preg_replace("~(%pi|%e|x|y|\))([0-9]|\()~", "\\1*\\2", $maw_stringB);
			$maw_stringB = preg_replace("~EXP~", "exp", $maw_stringB);
			$maw_stringB = preg_replace("~\|(.*)\|~", "abs(\\1)", $maw_stringB);
			if ($maw_string != $maw_stringB) {
				printf(__("<h3>You may mean %s.</h3> If yes, use Copy and Paste to move this string into the input field.<br>To go back use the Back button in your browser."), "<span class='red'>" . $maw_stringB . "</span>");
			}
			maw_howto();
			die($errDc . "</body></html>");
			$check_math = $keyword;
		}
	}


	$maw_stringC = preg_replace("~($functions|$constants|$variables|$parameters|[0-9+-/\*e|]|pi| |\(|\))~", "", $maw_string);
	if ($maw_stringC != "") {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo("<h3 class='red'>" . __("Error, you typed unsupported chars or uppercase letters.") . "</h3>");
		printf("<b>" . __("Check your input data") . "</b>: <b><span class='red letterspaced'>%s</span></b><br><br>", preg_replace("~($functions|$constants|$variables|$parameters|[0-9+-/\*e|]|pi| |\(|\))~", "<span class='text'>\\1</span>", $maw_string));
		echo sprintf(__("After removing allowed strings we got redundant characters %s."), " <b><span class='red letterspaced';>$maw_stringC</span></b>");
		maw_howto();
		echo("</body></html>");
		die();
	}

	return ($check_math);
}  // end of check_math_errors


function send_PDF_file_to_browser($file)
{

	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

		$newfile = basename($file) . "_" . time() . "_" . RandomName(7) . ".pdf";
		system("cp $file /tmp/maw-dev-cache/$newfile ");
		header("Content-Type: application/json");
		echo json_encode(['data' => $newfile, 'file' => basename($file)]);
	} else {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Type: application/pdf");
		header("Content-Disposition: inline; filename=" . basename($file) . ";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($file));

		readfile($file);
	}

}


function input_to_maxima($inputtext, $fieldname = "")
{
	global $err_fieldname, $variables, $functions, $constants;
	$err_fieldname = $fieldname;

	if (preg_match('~^-?[0-9]+\.[0-9]+$~', $inputtext)) {
		return ($inputtext);
	}

	$inputtext = strtolower($inputtext);
	$inputtext = str_replace(" ", "", $inputtext);
	$inputtext = str_replace("ᶺ", "**", $inputtext);
	$inputtext = str_replace("^", "**", $inputtext);
	$inputtext = str_replace("ln", "log", $inputtext);
	$inputtext = str_replace("[", "(", $inputtext);
	$inputtext = str_replace("]", ")", $inputtext);
	$inputtext = str_replace("{", "(", $inputtext);
	$inputtext = str_replace("}", ")", $inputtext);
	$inputtext = str_replace("tg", "tan", $inputtext);
	$inputtext = str_replace("cotan", "cot", $inputtext);
	$inputtext = str_replace("arc", "a", $inputtext);
	$inputtext = str_replace("e", "%e", $inputtext);
	$inputtext = str_replace("s%ec", "sec", $inputtext);
	$inputtext = str_replace("%%e", "%e", $inputtext);
	$inputtext = str_replace("%exp", "exp", $inputtext);
	$inputtext = str_replace("pi", "%pi", $inputtext);
	$inputtext = str_replace("%%pi", "%pi", $inputtext);
	$inputtext = str_replace("−", "-", $inputtext);
	$inputtext = str_replace("√", "sqrt", $inputtext);
	$inputtext = str_replace("〖", "(", $inputtext);
	$inputtext = str_replace("〗", ")", $inputtext);

	$inputtext = preg_replace("~([0-9])($variables|$functions|$constants)~", "\\1*\\2", $inputtext);
	$inputtext = preg_replace("~($variables)($functions)~", "\\1*\\2", $inputtext);
	check_math_errors($inputtext);
	$err_fieldname = "";

	return ($inputtext);
}

function hint_mimetex($vzorec)
{
	global $lang, $errDc, $texrender, $formconv_bin;
	$vysledek = `echo "$vzorec"|formconv `;
	$vysledek = str_replace("\\,", " ", $vysledek);
	if ($vysledek != "") {
		echo("<hr><h3>" . __("We attempted to parse your input automatically") . "</h3>");
		printf(__("If you mean %s, you can try copy and paste the following string to the input field:"), sprintf("<img src=\"%s\\usepackage{color}\\large\\color{red} %s\" alt=\"\" border=0 align=bottom>", $texrender, $vysledek));
		$vysledek = beautify_parentheses(`echo "$vzorec"| $formconv_bin -O maxima`);
		echo(" <b>$vysledek</b><hr>");
	} else {
		maw_howto();
		die();
	}
}

function check_for_y($maw_string)
{
	if (stristr($maw_string, "y")) {
		save_log($maw_string, "errors");
		maw_html_head("MAWerror");
		echo '<h3 class="red">', __("Error in your formula"), '</h3><b><span class="red">', __("You probably use the <i>y</i> variable, which is not allowed here."), '</span></b>';
		maw_howto();
		die($errDc . "</body></html>");
	}
}

$formconv_repl_both = [
	"{{d}\over{d\,x}}\,y", "y' ",
	"\\left(y' \\right)", "y' ",
	"(y' )", "y' ",
	"\\log", "\\ln "];

if ($lang == "cs") {
	$formconv_repl_both = array_merge($formconv_repl_both,
		[
			"\\arctan", "\\mathop{\\mathrm{arctg}}\\nolimits ",
			"\\tan", "\\mathop{\\mathrm{tg}}\\nolimits ",
			"\\coth", "\\mathop{\\mathrm{cotgh}}\\nolimits ",
			"\\rm acoth", "\\mathop{\\mathrm{arccotgh}}\\nolimits ",
			"\\acot", "\\mathop{\\mathrm{acccotg}}\\nolimits ",
			"\\arccot", "\\mathop{\\mathrm{arctg}}\\nolimits ",
			"\\mathrm{arccot}", "\\mathop{\\mathrm{arccotg}}\\nolimits ",
			"\\rm arccot", "\\mathop{\\mathrm{arccotg}}\\nolimits ",
			"\\cot", "\\mathop{\\mathrm{cotg}}\\nolimits "]);
}

$formconv_repl_input = [];
$formconv_repl_output = [];

for ($i = 0; $i <= (count($formconv_repl_both) / 2 - 1); $i++) {
	array_push($formconv_repl_input, $formconv_repl_both[2 * $i]);
	array_push($formconv_repl_output, $formconv_repl_both[2 * $i + 1]);
}

function formconv_replacements($vstup)
{
	global $formconv_repl_input, $formconv_repl_output;

	return (str_replace($formconv_repl_input, $formconv_repl_output, $vstup));
}


function formconv($vstup)
{
	global $texrender, $formconv_bin, $mawtimeout;
	$formconverror = "\\usepackage{color}\\color{red}\\bf???";
	$vystup = `$mawtimeout echo "$vstup" | $formconv_bin `;
	$vystup = formconv_replacements($vystup);

	return ($vystup);
}

function beautify_parentheses($str)
{

	$str = chop($str);
	$str = "[[($str)]]";
	$trans = [];
	$lastopening = [];
	$length = strlen($str);
	$a = str_split($str);

	foreach ($a as $key => $value) {

		if ($value == "(") {

			array_push($lastopening, $key);
		} elseif ($value == ")") {
			$trans[$key] = array_pop($lastopening);

		}

	}

	foreach ($trans as $key => $value) {
		if (($trans[$key + 1]) && ($trans[$key + 1] + 1 == $value)) {

			$a[$trans[$key]] = "";
			$a[$key] = "";
		}
	}
	$a[0] = "";
	$a[1] = "";
	$a[2] = "";
	$a[$length - 1] = "";
	$a[$length - 2] = "";
	$a[$length - 3] = "";

	return (implode($a));
}


function formconv_gnuplot($vstup)
{
	global $formconv_bin;
	$vystup = `$mawtimeout echo "$vstup" | $formconv_bin -r -O gnuplot`;

	return (chop($vystup));
}


function put_tex_to_html($vstup)
{
	global $texrender;

	return ("<img src=\"" . $texrender . "" . $vstup . "\" align=\"middle\" alt=\"math formula\">");
}

function put_tex_to_html_MJ($vstup)
{
	return ("\$ " . $vstup . " \$");
}


function RandomName($len)
{
	$randstr = '';
	srand((double) microtime() * 1000000);
	for ($i = 0; $i < $len; $i++) {
		$n = rand(48, 120);
		while (($n >= 58 && $n <= 64) || ($n >= 91 && $n <= 96)) {
			$n = rand(48, 120);
		}
		$randstr .= chr($n);
	}

	return $randstr;
}

function check_decimal($vstup, $decfieldname = "")
{
	global $lang;
	$vstup = str_replace(" ", "", $vstup);
	if (!(preg_match('~^-?[0-9]+\.?[0-9]*$~', $vstup))) {
		maw_html_head("MAWerror");
		echo("<span class=\"red\">" . __("<h3>Incorrect input, use only (decimal) numbers to enter bounds for the picture.</h3> You are allowed to use only decimal numbers (character minus, digits and a dot as a separator of decimal places).") . "</span>");
		if (!($decfieldname == "")) {
			echo("<br>" . sprintf(__("Invalid input in the field %s."), "<b>" . $decfieldname . "</b>"));
		}
		die();
	}

	return ($vstup);
}

function show_TeX($str)
{
	global $texrender;
	$str = preg_replace("/\\\$\\\$((.|\n)*?)\\\$\\\$/", "<img src=\"" . $texrender . "\\1\" alt=\"\\1\" align=\"bottom\">", $str);

	return (formconv_replacements($str));
}


function check_last_call()
{


	return (0);
}

if ($TeX_language == '') {
	if ($lang == "zh") {
		$TeX_language = '\usepackage{CJKutf8}\AtBeginDocument{\begin{CJK*}{UTF8}{gbsn}}\AtEndDocument{\end{CJK*}}';
	} elseif (($lang == "ru") || ($lang == "uk")) {
		$TeX_language = '\usepackage[utf8]{inputenc}\usepackage[T2A]{fontenc}';
	} else {
		$TeX_language = '\usepackage[utf8]{inputenc}\usepackage{lmodern}\usepackage[T1]{fontenc}';
	}
}

$TeXhyperref = '\usepackage[urlbordercolor={' . str_replace(",", " ", $TeX_rgbcolor) . '}]{hyperref}';
$TeXheader = '
\documentclass[12pt]{article}
\def\tan{\mathop{\text{tg}}}
\usepackage[fleqn]{amsmath}
\usepackage[margin=1in, top=0.5in, bottom=0.5in]{geometry}
\usepackage{color}
\definecolor{mygreen}{rgb}{' . $TeX_rgbcolor . '}
' . $TeXhyperref . '
\let\rmdefault\sfdefault
\let\log\ln

\fboxsep 0 pt


\def\datum{\today\ {}
 \count0=\time \divide \count0 by 60 \the\count0:%
 \count1=\time\multiply \count0 by 60\advance\count1 by -\count0
 \ifnum\count1<10 0\fi
 \the\count1}

' . $TeX_language . '

\usepackage{eso-pic}
\AddToShipoutPicture{\hbox to \paperwidth{\color{mygreen}\vrule
width 1.5em height\paperheight\color{black}
\raise 5pt\hbox{ \tiny\datum}\hss \raise 5pt\hbox{\tiny Mathematical Assistant
on Web, http://user.mendelu.cz/marik/maw\quad}}}%

\rightskip 0 pt plus 1 fill

\everymath{\displaystyle}
\parindent 0 pt

\def\MAWhead#1{
\begin{center}
  {\large #1}\\\\
  \url{http://user.mendelu.cz/marik/maw}
\end{center}}
\def\mawserver{' . $mawphphome . '}
';

function highlight_errors($string)
{
	$string = preg_replace('/log.*(encountered|generated).*/', '<span class="red"><b>\0</b></span>', $string);
	$string = preg_replace('/.*(0 to a negative exponent).*/', '<span class="red"><b>\0</b></span>', $string);
	$string = preg_replace('/.*(Division by 0).*/', '<span class="red"><b>\0</b></span>', $string);

	return (preg_replace('/(([Ii]ncorrect|-- an error|[Ee]rror).*)/', '<span class="red"><b>!!!Here is the error. !!!</b><br>\1</span>', $string));
}

function check_for_errors($output, $string, $file, $msg = "")
{
	global $maw_tempdir;
	$cust_output = str_replace("0 errors, 0 warnings", "", $output);
	if ((preg_match('~[Ii]ncorrect|[Ee]rror~', $cust_output))) {
		if ($msg == "") {
			$msg = __("An error occurred when processing your input.<br>Check your formulas (perhaps using Preview button) and report the problem if you think that your input is correct and should be processed without any error.");
		}
		maw_html_head("MAWerror");
		maw_errmsgB("<b><big class=\"red\">" . $msg . "</big></b>");
		save_log_err($string, $file);
		system("rm -r " . $maw_tempdir);
		die(highlight_errors("<pre>" . $output . "</body></html>"));
	}

}

$maw_actual_load = current_server_load();
$maw_actual_processes = current_processes_running();
if (($maw_actual_load > $load_limit) || ($maw_actual_processes > $processes_limit)) {
	system("echo \"<hr><pre>\" >> /var/www/maw/common/log/ps.log");
	system("date >> /var/www/maw/common/log/ps.log");
	system("ps axo user,command >> /var/www/maw/common/log/ps.log");
	system("echo \"</pre>\" >> /var/www/maw/common/log/ps.log");
	save_log("overloaded: load: $maw_actual_load processes: $maw_actual_processes", "overloaded");
	maw_html_head("MAWerror");
	echo "<h2>" . __("Server overloaded") . "</h2>";
	echo __("Sorry, our server is overloaded, try it again after a reasonable time (10 sec).");
	echo "<br><br><b>";
	echo sprintf(__("Load: %s, Running processes: %s"), $maw_actual_load, $maw_actual_processes);
	echo "</b><br><br>";
	echo __("This error occurs if somebody (you or another user) clicks the submit button many times in a sort time. Don't do it, please. Click the Submit button only once");
	echo "<br><br>";
	echo __("This error occurs also if there are too many requests in a short time, for example if somebody uses this server for teaching in computer lab.");
	echo "<br><br>";
	echo __("To be independent on our server install MAW offline for yourselves on your local computer.");
	echo " ";
	echo __("This is pretty simple on Linux and on both Linux and Windows you can use precompiled virtual machine (link below the main title of each page).");
	die();
}

function highlight_semicolons($input)
{
	$input = str_replace(";", "</span><span class='bold'>&nbsp;&nbsp;;&nbsp;&nbsp;</span><span class=\"yellow_background\">", $input);
	$input = str_replace(",", "<span class='bold'> , </span>", $input);

	return ("<span class=\"yellow_background\">$input</span>");
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$mawISAjax = 1;
} else {
	$mawISAjax = 0;
}


?>
