<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2010 Robert Marik, Miroslava Tihlarikova
Copyright 2011-2013 Robert Marik

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

$scriptname = "mnc";
require("../common/maw.php");
$data = $_REQUEST["body"];
$datasaved = $data;

$requestedoutput = "html";
if ($_REQUEST["output"] == "pdf") {
	$requestedoutput = "pdf";
}
if ($_REQUEST["output"] == "svg") {
	$requestedoutput = "svg";
}

check_for_security($data);

$data = str_replace(" ", "", $data);
$data = preg_replace('/;$/', "", $data);
$retezec = preg_match("~[^-0-9;,.]~", $data);

if (($retezec != "") || (preg_match("~[0-9]-~", $data)) || (substr_count($data, ",") != (substr_count($data, ";") + 1))) {
	maw_html_head();
	echo("<h2 class='red'>" . __("Incorrect input") . "</h2>" . __("Use numbers only. Use commas and semicolons to separate point coordinates and points in the data file.") . "<br>" . __("Your bad input was") . ":<span class='red'> " . highlight_semicolons($data) . "</span>");
	if (substr_count($data, ",") > (substr_count($data, ";") + 1)) {
		echo("<br><b>" . __("Missing semicolon or more commas than expected.") . "</b><br>");
	}
	if (substr_count($data, ",") < (substr_count($data, ";") + 1)) {
		echo("<br><b>" . __("Missing comma or more semicolons than expected.") . "</b><br>");
	}

	$datcasip = "$data";
	save_log_err($datcasip, "mnc");
	die("</body></html>");
}

/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir = "/tmp/MAW_mnc" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

/* We open temporary files and write informatins                        */
/* ---------------------------------------------------------------------*/

define("NAZEV_SOUBORU", $maw_tempdir . "/data");
$soubor = fopen(NAZEV_SOUBORU, "w");


/* Commands for maxima session - derivatives and zeros of derivatives   */
/* ---------------------------------------------------------------------*/

define("SOUBB", $maw_tempdir . "/ctverce.tex");
$soubor = fopen(SOUBB, "w");
$TeXfile = $TeXheader . '
\usepackage{graphicx}
\begin{document}

\pagestyle{empty}

\MAWhead{' . __("Least squares method") . '}

\bigskip ' . __("Given data files, we look for optimal linear approximation in the form") . '
$y=ax+b$.

\medskip
\begin{minipage}{0.45\hsize}
  \begin{center} \textbf{' . __("Table") . ':} \medskip

    \begin{tabular}{|r||c|c||c|c|}
      \hline
      &$x$& $y$& $x^2$ & $xy$\\\\ \hline
      \input table.tex
      \hline
    \end{tabular}
  \end{center}
\end{minipage}
\vrule\,\vrule
\begin{minipage}{0.45\hsize}
  \begin{center}
    \textbf{' . __("System of equations") . ':}

  \end{center}
\input eq.tex
\end{minipage}

\bigskip
\textbf{' . __("Graph") . ':}

\let\oldstrut\strut
\def\strut{\footnotesize\oldstrut}
\input graf.tex

\end{document}
';

fwrite($soubor, $TeXfile);
fclose($soubor);

system("cd $maw_tempdir; LANG=$locale_file.UTF-8 perl -s $mawhome/mnc/mnc.pl -mawhome=$mawhome -requestedoutput=$requestedoutput -inputdata='$data'; cp * /tmp");

if ($requestedoutput == "pdf") {
	system("cd $maw_tempdir; $epstopdf graf.eps; cp * /tmp; $pdflatex ctverce.tex>>output");
	system("cd $maw_tempdir; $catchmawerrors");
}

if ($requestedoutput == "svg") {
	$file = $maw_tempdir . "/graf.svg";
	header('Content-Type: image/svg+xml');
	header("Content-Disposition: attachment; filename=" . basename($file) . ";");
	readfile($file);
	system("rm -r " . $maw_tempdir);
	die();
}

if ($requestedoutput == "html") {
	if ($mawISAjax == 0) {
		maw_html_head();
	}
	printf("<h3>%s</h3>", __("Least squares method"));
	printf("<p>%s</p>", __("Given data files, we look for optimal linear approximation in the form") . " \$y=ax+b\$.");
	echo("<div class=inlinediv><div class=logickyBlok><h4>" . __("Table") . "</h4>");

	echo "<table><tr><th>\$ n\\vphantom{x^2}\$</th><th> \$ x \\vphantom{x^2}\$ </th><th> \$ y\\vphantom{x^2}\$ </th><th> \$ x^2\$ </th><th> \$ xy\\vphantom{x^2}\$ </th></tr>";
	require($maw_tempdir . "/table.php");
	echo "</table></div></div> ";
	echo("<div class=inlinediv><div class=logickyBlok><h4>" . __("System of equations") . "</h4>");
	require($maw_tempdir . "/eq.php");
	echo("</div></div> ");

	echo("<div class=inlinediv><div class=logickyBlok><h3>" . __("Graph") . "</h3>");
	echo("<img src=\"$mawphphome/mnc/mnc.php?output=svg&body=" . rawurlencode($datasaved) . "\">");
	echo("</div></div>");

	save_log($data, "mnc");
	system("rm -r " . $maw_tempdir);
	if ($mawISAjax == 0) {
		echo('</body></html>');
	}
	die();
}


/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline = exec("cd " . $maw_tempdir . "; cat errors");

$uspech = 0;
if ($lastline != "") {
	maw_errmsgB("<pre>");
	system("cd " . $maw_tempdir . "; cat output");
	system("rm -r " . $maw_tempdir);
	save_log_err($data, "mnc");
	die("</pre></body></html>");
} else {
	/* here we send PDF file into browser */

	$uspech = 1;
	send_PDF_file_to_browser("$maw_tempdir/ctverce.pdf");
}

system("rm -r " . $maw_tempdir);
save_log($data, "mnc");

?>


