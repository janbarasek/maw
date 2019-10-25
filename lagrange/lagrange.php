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


$scriptname = "lagrange";
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

if (($retezec != "") || (preg_match("~[0-9]-~", $data)) || (substr_count($data, ",") != (substr_count($data, ";") + 1)) || (substr_count($data, ";") <= 0) || (substr_count($data, ";") > 5)) {
	$data_H = highlight_semicolons($data);
	maw_html_head();
	echo("<h2 class='red'>" . __("Incorrect input") . "</h2>" . sprintf(__("<ul><li>Use numbers only, no functions are allowed.</li><li>Use commas and semicolons to separate point coordinates and points in the data file.</li><li>Use at least two and no more than six points. If you really need to interpolate for bigger or more general data file, use the full power of the <a href=\"http://maxima.sourceforge.net\">Maxima</a> software and the command lagrange on your local computer.</li></ul><br>Your input was: %s"), "<span class='red'> $data_H</span>"));
	if (substr_count($data, ";") > 5) {
		echo("<br><b>" . __("You are not allowed to enter more than six points.") . "</b><br>");
	}
	if (substr_count($data, ",") > (substr_count($data, ";") + 1)) {
		echo("<br><b>" . __("Missing semicolon or more commas than expected.") . "</b><br>");
	}
	if (substr_count($data, ",") < (substr_count($data, ";") + 1)) {
		echo("<br><b>" . __("Missing comma or more semicolons than expected.") . "</b><br>");
	}
	$datcasip = "$data";
	save_log_err($data, "lagrange");
	die("</body></html>");
}


/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir = "/tmp/MAW_lagrange" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);


/* We open temporary files and write informatins                        */
/* ---------------------------------------------------------------------*/

define("NAZEV_SOUBORU", $maw_tempdir . "/data");
$soubor = fopen(NAZEV_SOUBORU, "w");


/* Commands for maxima session - derivatives and zeros of derivatives   */
/* ---------------------------------------------------------------------*/

fwrite($soubor, $data . "\n" . $lang . "\n");
fclose($soubor);

$TeXfile = '
\everymath{\displaystyle}
\usepackage{graphicx}
\parindent 0 pt
\fboxsep 3pt
\relpenalty =10000         
\binoppenalty =10000
\exhyphenpenalty =1000
\def\OpakujZnak #1#2{\mathchardef #2=\mathcode`#1
  \activedef #1{#2\nobreak\discretionary{}{\hbox{\qquad$#2$}}{}}
  \uccode`\~=0 \mathcode`#1="8000 }
\def\activedef #1{\uccode`\~=`#1 \uppercase{\def~}}
\begin{document}

\OpakujZnak ={\eqORI} 
\OpakujZnak +{\plusORI}
\OpakujZnak -{\minusORI}

\pagestyle{empty}
\MAWhead{' . __("Interpolation with Lagrange polynomial") . '}

\bigskip

\input data.tex

' . __("Given points in the plane, we look for the polynomial of interpolation.") . '

\zadani

\bigskip
\hrule
\bigskip

' . sprintf(__("We have %s points, the degree of the polynomial is not greater than %s."), "\\pocetbodu{}", "\stupen") . '

\bigskip
' . __("We write the form of the Lagrange polynomial from the $ y$ coordinates.") . '

\begin{equation*}
\L
\end{equation*}


\bigskip
' . __("Using $ x$ coordiantes, we write and simplify the small Lagrange polynomials.") . '
\pompol

\bigskip

' . __("We use small Lagrange polynomials in the interpolation formula and sum up like powers of $ x$ (the final result is in the frame).") . '


$\vysledek$

\begin{center}
  \includegraphics[width=0.8\hsize, height=0.3\vsize]{graf.pdf}
\end{center}
\end{document}
';

$soubor = fopen("$maw_tempdir/lagrange.tex", "w");
fwrite($soubor, $TeXheader . $TeXfile);
fclose($soubor);

system("cd $maw_tempdir; LANG=$locale_file.UTF-8 perl -s $mawhome/lagrange/lagrange.pl -requestedoutput=$requestedoutput -formconv_bin=$formconv_bin -mawhome=$mawhome -maxima=$maxima; $epstopdf graf.eps; echo \"<br><br><h3>LaTeX</h3>\">>output; $mawtimeout $pdflatex lagrange.tex>>output; cp * /tmp/");
system("cd $maw_tempdir; $catchmawerrors");

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
	require($maw_tempdir . "/data.php");
	printf("<h3>%s</h3>", __("Interpolation with Lagrange polynomial"));

	printf("<div class=inlinediv><div class=logickyBlok><p>%s</p>", __("Given points in the plane, we look for the polynomial of interpolation."));
	echo $zadani;
	echo("</div></div>");

	echo("<div class=inlinediv><div class=logickyBlok>");
	printf(__("We have %s points, the degree of the polynomial is not greater than %s."), "$pocetbodu", "$stupen");

	printf("<p>%s</p>", __("We write the form of the Lagrange polynomial from the $ y$ coordinates."));

	echo("\$\$ $L \$\$");
	echo("</div></div>");

	echo("<div class=inlinediv><div class=logickyBlok>");
	printf("<p>%s</p>", __("Using $ x$ coordiantes, we write and simplify the small Lagrange polynomials."));
	echo $pompol;
	echo("</div></div>");

	echo("<div class=inlinediv><div class=logickyBlok>");
	printf("<p>%s</p>", __("We use small Lagrange polynomials in the interpolation formula and sum up like powers of $ x$ (the final result is in the frame)."));
	echo("\$\$ $vysledek \$\$");
	echo("</div></div>");

	echo("<div class=inlinediv><div class=logickyBlok><h3>" . __("Graph") . "</h3>");
	echo("<img src=\"$mawphphome/lagrange/lagrange.php?output=svg&body=" . rawurlencode($datasaved) . "\">");
	echo("</div></div>");


	save_log($data, "lagrange");
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
	save_log_err($data, "lagrange");
	die("</pre></body></html>");
} else {
	/* here we send PDF file into browser */

	$uspech = 1;
	send_PDF_file_to_browser("$maw_tempdir/lagrange.pdf");
}

system("rm -r " . $maw_tempdir);
save_log($data, "lagrange");

?>



