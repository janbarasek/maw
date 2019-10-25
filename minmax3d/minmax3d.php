<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2009 Robert Marik, Miroslava Tihlarikova
Copyright 2013 Robert Marik

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

$scriptname = "minmax3d";

require("../common/maw.php");

$fcef = $_REQUEST["funkcef"];
$akce = $_REQUEST["akce"];
$xs = $_REQUEST["xs"];
$ys = $_REQUEST["ys"];
$stacbody = $_REQUEST["stacbody"];

$requestedoutput = "html";
if ($_REQUEST["output"] == "pdf") {
	$requestedoutput = "pdf";
}

check_for_security($fcef . " " . $stacbody . " " . $xs . " " . $ys);

$datcasip = "funkce: z=$fcef";

if ($pocetprom == "1") {
	$variables = 'x';
}

$fcef = input_to_maxima($fcef);


function najdi_SB($retezec)
{
	$retezec = str_replace("\n", "", $retezec);
	preg_match("/keyprint .* keyprint/", $retezec, $matches);
	$vystup = $matches[0];
	$vystup = preg_replace('/ +/', "", $vystup);
	$vystup = str_replace("keyprint", "", $vystup);
	$vystup = str_replace("{", "", $vystup);
	$vystup = str_replace("}", "", $vystup);
	$vystup = str_replace(" [", "[", $vystup);
	$vystup = str_replace("] ", "]", $vystup);

	return ($vystup);
}


if (($akce == 1) || (($akce == 2) && (str_replace(" ", "", $stacbody) == "")) || (($akce == 0) && (str_replace(" ", "", $xs) == "")) || (($akce == 0) && (str_replace(" ", "", $ys) == ""))) {
	maw_html_head();
	if ($akce != 1) {
		echo(__("Missing stationary points in your input. I'll try to find the stationary points first."));
	}
	echo "<h1>" . sprintf(__("We look for stationary points of the function %s."), $fcef) . "</h2>";
	echo("<hr>" . put_tex_to_html("f(x,y)=" . formconv($fcef)) . "<hr>");
	$command = "$mawtimeout $maxima --batch-string=\"f(x,y):=" . $fcef . "\$ load(\\\"$mawhome/minmax3d/stationary.mac\\\")$\"";
	$command_out = `$command`;

	if (stristr($command_out, "not_two_vars")) {
		echo "<h2 class='red'>" . __("Error") . "</h2>" . sprintf(__("The function is not a function in two variables. You have to enter function in two variables x and y. If you want to investigate functions in one variable, you can continue %s here %s."), "<a href=\"$mawhtmlhome/index.php?form=prubeh&lang=$lang\">", "</a>");
		save_log_err($fcef . " neni funkce dvou promennych", "minmax3d");
		die();
	}

	check_for_errors($command_out, $fcef . " bad input", "minmax3d");
	$command_out_temp = $command_out;
	$points = str_replace("%pi", "pi", str_replace("%e", "e", najdi_SB($command_out)));
	echo '<h2>', __("Stationary points"), '</h2>', str_replace("],[", "];[", $points);
	if (str_replace(" ", "", $points) == "") {
		echo __("No stationary point has been found. Either there are no stationary points or the problem is too difficult for Maxima.");
		save_log_err($fcef . " nenasli jsme stacionarni bod", "minmax3d");
		die();
	}
	if (stristr($points, "%")) {
		echo("<br><span class='red'>" . __("<b>Problems:</b> Infinitely many solutions, or some solutions cannot be written using elementary functions.") . "</span>");
		die();
	}
	echo __("<h2>Remarks</h2><ul> <li>You can load the function and computed stationary points into the calculator for next calculations by clicking the following link ");

	$action = 0;
	if (substr_count($points, "],[") > 0) {
		$action = 2;
	}
	$points_to_form = str_replace("],[", "];[", $points);
	$point_to_form = explode(",", str_replace("]", "", str_replace("[", "", $points)));
	echo("<a href=\"$mawhtmlhome/index.php?form=minmax3d&function=" . rawurlencode($fcef) . "&pointx=" . rawurlencode($point_to_form[0]) . "&pointy=" . rawurlencode($point_to_form[1]) . "&points=" . rawurlencode($points_to_form) . "&akce=$action&lang=$lang\">" . __("put these point into the form") . "</a>.");
	echo("</li><li>" . __("If the derivatives are not polynomials and if these derivative are complicated, some stationary points may be missing.") . "</li></ul>");
	echo("<h2>" . __("The result of symbolic computation") . "</h2>");
	echo '<pre>', $command_out;
	save_log($datcasip . " vypocet stac. bodu", "minmax3d");
	die();
}

// the code for domain has moved into another directory

/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/


$maw_tempdir = "/tmp/MAW_minmax3d" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

if ($akce == 0) {
	$xs = input_to_maxima($xs);
	$ys = input_to_maxima($ys);
	$params = "-f='$fcef' -xs='$xs' -ys='$ys' -akce='$akce' -lang='$lang'";
} else {
	$params = "-f='$fcef' -xs='$stacbody' -ys='$stacbody' -akce='$akce' -lang='$lang'";
}

$soubor = fopen("$maw_tempdir/minmax3d.tex", "w");
$TeXfile = $TeXheader . '
\newif\ifjeden
\usepackage{fancybox}
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{' . __("Local extrema in two variables") . '}

\input data.tex




 \textbf{' . __("Function") . ':} $z=\ftex$ 
 \ifjeden

 \textbf{' . __("Stationary point") . ':} $[x,y]=\left[\stbod\right]$
 \else

 \textbf{' . __("Tested stationary points") . ':} \body
 \fi
\medskip
\hrule

\medskip

\makeatletter
\let\oldcr\cr
\def\cr{\oldcr\noalign{\kern 10pt}}
\def\matrix#1{\null\,\vcenter{\normalbaselines\m@th
    \ialign{\hfil$##$\hfil&&\quad\hfil$##$\hfil\crcr
       \mathstrut\crcr\noalign{\kern-\baselineskip}
       #1\crcr\mathstrut\crcr\noalign{\kern-\baselineskip}}}\,}
 \def\pmatrix#1{\left(\matrix{#1}\right)}
\makeatother

\fboxsep=6pt
  \textbf{' . __("The first derivatives") . ': }\fbox{$
  \begin{aligned}[t]
    \frac{\partial f(x,y)}{\partial x}&=\dfx\\\\
    \frac{\partial f(x,y)}{\partial y}&=\dfy
  \end{aligned}$}

\bigskip
  \textbf{' . __("The second derivatives") . ': }\fbox{$
  \begin{aligned}[t]
    \frac{\partial^2 f(x,y)}{(\partial x)^2}&=\dfxx\\\\
    \frac{\partial^2 f(x,y)}{\partial x\partial y}&=\dfxy\\\\
    \frac{\partial^2 f(x,y)}{(\partial y)^2}&=\dfyy\\\\
  \end{aligned}$}

\ifjeden
\bigskip
  \textbf{' . __("The second derivatives at stationary points") . ': }\fbox{$
  \begin{aligned}[t]
    \left.\frac{\partial^2 f(x,y)}{(\partial x)^2}\right|_{[x,y]=\left[\stbod\right]}&=\dfxxs\\\\
    \left.\frac{\partial^2 f(x,y)}{\partial x\partial y}\right|_{[x,y]=\left[\stbod\right]}&=\dfxys\\\\
    \left.\frac{\partial^2 f(x,y)}{(\partial y)^2}\right|_{[x,y]=\left[\stbod\right]}&=\dfyys\\\\
  \end{aligned}$}

  \medskip
  \textbf{' . __("Hessian") . ':  }{\def\pmatrix#1{\left|\matrix{#1}\right|}
  $H\left(\stbod\right)=
  \hessovamatices=\left[\dfxxs\right]\cdot\left[\dfyys\right]-\left[\dfxys\right]^2=\determinant
  $}
 
%\fboxrule 2pt
\medskip
\begin{center}
  \shadowbox{\begin{minipage}{0.9\linewidth}
  \conclusion
  \end{minipage}}
\end{center}

\else

\bigskip
\textbf{' . __("Hessian") . ':  }{\def\pmatrix#1{\left|\matrix{#1}\right|}
  $H\left(x,y\right)=
  \hess$}

\bigskip
\begin{center}
  \let\cr\oldcr
  \fboxrule 0pt
  \tabulka
\end{center}
\fi

\end{document}
';

fwrite($soubor, $TeXfile);
fclose($soubor);

system("cd $maw_tempdir; touch errorspl; LANG=$locale_file.UTF-8 perl -s $mawhome/minmax3d/minmax3d.pl -maxima=$maxima -mawhtmlhome=$mawhtmlhome -mawhome=$mawhome $params; echo \"***** LaTeX *****\" >> output; $pdflatex minmax3d.tex >> output; cp * /tmp/; $catchmawerrors");


/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline = exec("cd " . $maw_tempdir . "; cat errors");
$datcasip = "funkce: z=$fcef, stac bod: x=$xs,y=$ys; $stacbody, akce=$akce";

if ($lastline != "") {
	maw_errmsgB("<pre>");
	save_log_err($datcasip, "minmax3d");
	system("cd " . $maw_tempdir . "; cat output|$mawcat");
	system("rm -r " . $maw_tempdir);
	die("</pre></body></html>");
} else {
	if ($requestedoutput == "html") {
		if ($mawISAjax == 0) {
			maw_html_head();
		}
		require("$maw_tempdir/data.php");
		require("htmloutput.php");
		if ($mawISAjax == 0) {
			echo('</body></html>');
		}
	} else {
		/* here we send PDF file into browser */
		send_PDF_file_to_browser("$maw_tempdir/minmax3d.pdf");
	}
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system("rm -r " . $maw_tempdir);
save_log($datcasip, "minmax3d");

?>



