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

$scriptname = "prubeh";

require("../common/maw.php");

$fce = $_REQUEST["funkce"];
$xmin = $_REQUEST["xmin"];
$xmax = $_REQUEST["xmax"];
$ymin = $_REQUEST["ymin"];
$ymax = $_REQUEST["ymax"];
if (!($xmin == "")) {
	$xmin = check_decimal($xmin, "xmin");
}
if (!($xmax == "")) {
	$xmax = check_decimal($xmax, "xmax");
}
if (!($ymin == "")) {
	$ymin = check_decimal($ymin, "ymin");
}
if (!($ymax == "")) {
	$ymax = check_decimal($ymax, "ymax");
}


$requestedoutput = $_REQUEST["output"];

check_for_security("$fce, $xmin, $xmax, $ymin, $ymax");

check_for_y($fce);
$variables = 'x';


$funkce = input_to_maxima($fce);
check_for_y($fce);


/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir = "/tmp/MAW_maxima" . getmypid() . "xx" . RandomName(6);
system("mkdir $maw_tempdir; chmod oug+rwx $maw_tempdir; touch $maw_tempdir/uspech");

$prvnipruchod = `$mawtimeout $maxima --batch-string="display2d:false$ [load(linearalgebra), f(x):=$funkce, g:xthru(f(x)),u:ratsimp(num(g)),polynomialp(num(g),[x]),v:denom(g),polynomialp(denom(g),[x]),kraceni,if is(num(ratsimp(f(x)))=0) or ratsimp(diff(expand(num(g))/expand(num(ratsimp(f(x)))),x))=0 then 0 else gcd(expand(num(xthru(f(x)))),expand(denom(xthru(f(x))))),zkraceno,ratsimp(f(x)),testnapolynom,polynomialp(g,[x])];"`;

check_for_errors($prvnipruchod, "funkce: $fce,  x:$xmin..$xmax,   y:$ymin..$ymax, typ:$typ, bad input", "prubeh");

ereg("\(%o2\).*", $prvnipruchod, $vystup);

$testpol = ereg_replace("\n", "", $vystup[0]);
$testpol = ereg_replace(" ", "", $testpol);

if ((ereg("true.*true", $testpol)) and (ereg("testnapolynom,false", $testpol))) {
	$typ = "racionalni lomena funkce";
	if ((!ereg("kraceni,0", $testpol)) and (ereg("\"/\"", $testpol))) {
		maw_html_head();
		$kraceni = ereg_replace(".*kraceni,", "", $testpol);
		$kraceni = ereg_replace("zkraceno.*", "", $kraceni);
		$kraceni = ereg_replace(",", "", $kraceni);
		$pokraceni = ereg_replace(".*zkraceno,", "", $testpol);
		$pokraceni = ereg_replace("].*", "", $pokraceni);
		$pokraceni = ereg_replace(",.*testnapolynom.*", "", $pokraceni);

		save_log("funkce: " . $fce . "  NENI ZKRACENO - neprobehl vypocet", "prubeh");
		die(sprintf(__("<h2>Simplify your function first</h2>The numerator and denominator can be divided by the common factor %s. Simplify the fraction first and then write the function into the field again.<br><br> Remark: Simplifying we get y=%s."), $kraceni, $pokraceni));
	}

	$TeXfile = '\usepackage{graphicx}
\fboxsep=3pt

\relpenalty =10000         
\binoppenalty =10000
\exhyphenpenalty =1000
\def\OpakujZnak #1#2{\mathchardef #2=\mathcode`#1
  \activedef #1{#2\nobreak\discretionary{}{\hbox{\qquad$#2$}}{}}
  \uccode`\~=0 \mathcode`#1="8000 }
\def\activedef #1{\uccode`\~=`#1 \uppercase{\def~}}

\begin{document}
\def\parity{\par ' . _("The function is neither odd nor even.") . '\par}
\def\odd{\par ' . _("The function is odd.") . '\par}
\def\even{\par ' . _("The function is even.") . '\par}

\OpakujZnak ={\eqORI} 
\OpakujZnak +{\plusORI}
%\OpakujZnak -{\minusORI}

\pagestyle{empty}
\everymath{\displaystyle}
\input data.tex

\MAWhead{' . __("Investigating function using Maxima CAS") . '}


' . __("We investigate the function") . ' \fbox{\fbox{$y=\funkce\ifx\fa\fb\else=\fb\fi$}}

' . __("The computation is usually divided into two pages. The first page contains computations and the second page the graph.") . '


\bigskip
\hrule width \hsize
\bigskip

' . __("Condition for points of discontinuity") . ': $\rcenesp=0 $

\def\nic{}
\ifx\nesp\nic
' . __("Maxima failed to find points of discontinuity. They probably do not exist.") . '
\else
' . __("Points of discontinuity") . ': $\nesp$
\fi

' . __("Condition for \$x\$-intercepts") . ': $\rcenul$

\ifx\nuly\nic
' . __("Maxima failed to find \$x\$-intercepts. They probably do not exist.") . '
\else
' . __("\$x\$-intercepts") . ': $\nuly$\fi

\parity
\bigskip
\hrule width \hsize
\bigskip

\ifx\vypocetder\nic\else
' . __("Evaluation of \$y'\$") . ':\quad $\vypocetder=\derivacesouc$

\fi
' . __("The first derivative") . ': \fbox{$y\'=\derivacesouc$}

\bigskip
' . __("Condition for stationary points") . ': $\rcestac=0$

\ifx\stac\nic
' . __("Maxima failed to find stationary points. They probably do not exist.") . '
\else
' . __("Stationary points") . ': $\stac$
\fi

\bigskip
\hrule width \hsize
\bigskip
\ifx\vypocetderB\nic\else
' . __("Evaluation of \$y''\$") . ':\quad $\vypocetderB=\derivacesoucB$

\fi
' . __("The second derivative") . ': \fbox{$y\'\'=\derivacesoucB$}

\bigskip
' . __("Condition for critical points") . ': $\rcekrit=0$

\ifx\krit\nic
' . __("Maxima failed to find critical points. They probably do not exist.") . '
\else
' . __("Critical points") . ': $\krit$
\fi

\bigskip
\hrule width \hsize
\bigskip

\ifx\asymptota\nic
' . __("There is no asymptote at \$\pm\infty\$.") . '
\else
' . __("The asymptote at both \$\pm\infty\$ is the line \$\asymptota\$.") . '
\fi

\bigskip
\hrule width \hsize
\bigskip

\vbox{
' . __("Graph") . '

\includegraphics[width=\hsize]{graf.pdf}

}
\end{document}';


	$soubor = fopen("$maw_tempdir/function.tex", "w");
	fwrite($soubor, $TeXheader . $TeXfile);
	fclose($soubor);

	$prikaz = "cd $maw_tempdir; touch data.tex; echo '<h4>*** Maxima ****</h4>'>>output; LANG=$locale_file.UTF-8  perl -s $mawhome/prubeh/rlf.pl -maxima=$maxima -formconv_bin=$formconv_bin -f='$funkce' -lang='$lang' -xmin='$xmin' -xmax='$xmax' -ymin='$ymin' -ymax='$ymax' -mawhome=$mawhome; echo '<h4>*** GNUPLOT ****</h4>'>>output; $mawtimeout gnuplot < obrazek >> output;  sed 's/\/LT1 { PL \[4 dl 2 dl\] 0 1 0 DL } def/\/LT1 { PL \[\] 0 0 1 DL } def/' graf.eps > out.eps; mv out.eps graf.eps; $epstopdf graf.eps; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex function.tex>>output; $catchmawerrors; cp * ..";

	system($prikaz);

	$htmlFile = sprintf("<h2>%s</h2>", __("Investigating function using Maxima CAS"));

	require("$maw_tempdir/data.php");

	$tempHTML = $funkceHTML;
	if ($fbHTML != $faHTML) {
		$tempHTML = $tempHTML . " = " . $fbHTML;
	}
	$htmlFile = $htmlFile . sprintf("<p>%s $ \\displaystyle %s $ </p>", __("We investigate the function"), "y= $tempHTML ");

	$htmlFile = $htmlFile . sprintf(" <div class='inlinediv'><div class='logickyBlok'>");
	$temp = _("The function is neither odd nor even.");
	if ($parityHTML == "odd") {
		$temp = _("The function is odd.");
	}
	if ($parityHTML == "even") {
		$temp = _("The function is even.");
	}

	$htmlFile = $htmlFile . sprintf("<p> %s </p>", $temp);

	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s=0 $ </p>", __("Condition for points of discontinuity"), $rcenespHTML);
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("Points of discontinuity"), $nespHTML);
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("\$x\$-intercepts"), $nulyHTML);
	$htmlFile = $htmlFile . "</div></div>";

	$htmlFile = $htmlFile . sprintf(" <div class='inlinediv'><div class='logickyBlok'>");
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle y' = %s = %s $ </p>", __("Evaluation of \$y'\$"), $vypocetderHTML, $derivacesoucHTML);
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle y' = %s $ </p>", __("The first derivative"), $derivacesoucHTML);
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s =0 $ </p>", __("Condition for stationary points"), $rcestacHTML);

	if ($stacHTML == ' ') {
		$temp = __("Maxima failed to find stationary points. They probably do not exist.");
	} else {
		$temp = sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("Stationary points"), $stacHTML);
	}
	$htmlFile = $htmlFile . $temp;
	$htmlFile = $htmlFile . "</div></div>";

	$htmlFile = $htmlFile . sprintf(" <div class='inlinediv'><div class='logickyBlok'>");

	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle y'' = %s $ </p>", __("The second derivative"), $derivacesoucBHTML);
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s =0 $ </p>", __("Condition for critical points"), $rcekritHTML);

	if ($kritHTML == ' ') {
		$temp = __("Maxima failed to find critical points. They probably do not exist.");
	} else {
		$temp = sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("Critical points"), $kritHTML);
	}
	$htmlFile = $htmlFile . $temp;
	$htmlFile = $htmlFile . "</div></div>";


	if ($asymptotaHTML == '') {
		$temp = __("There is no asymptote at \$\pm\infty\$.");
	} else {
		$temp = sprintf(__("The asymptote at both \$ \pm\infty \$ is the line \$\\displaystyle  %s \$."), $asymptotaHTML);
	}
	$htmlFile = $htmlFile . "<p>" . $temp . "</p>";

	$htmlFile = $htmlFile . sprintf("<img class=centerimg alt='Processing image ...' src=\"%s/prubeh/zpracuj.php?funkce=%s&xmin=%s&xmax=%s&ymin=%s&ymax=%s&output=png\">", $mawphphome, rawurlencode($fce), rawurlencode($xmin), rawurlencode($xmax), rawurlencode($ymin), rawurlencode($ymax));


} else {
	if (ereg("true,1,true", $testpol)) {
		$typ = "polynom";
	} else {
		$typ = "ani polynom ani racionalni lomena funkce";
	}

	/* We open temporary files and write informatins                        */
	/* ---------------------------------------------------------------------*/
	define("NAZEV_SOUBORU", $maw_tempdir . "/prikazy");
	$soubor = fopen(NAZEV_SOUBORU, "w");

	$trigsimp = "";
	if (ereg("sin|cos|tan|cot|asin|acos|atan", $funkce)) {
		$trigsimp = "trigsimp";
	}

	if (ereg("asin|acos", $funkce)) {
		$radcan = "";
	} else {
		$radcan = "radcan";
	}

	/* Commands for maxima session - derivatives and zeros of derivatives   */
	/* ---------------------------------------------------------------------*/
	/* removed  assume_pos: true;assume_pos_pred: lambda ([x], display (x), true);
	 */
	fwrite($soubor, "block(load(stringproc),load(\"$mawhome/common/maw_solve.mac\"),testreal(cislo):=block(print(\"aaa\",cislo),if (lhs(cislo)#x) or not freeof(x,rhs(cislo)) then 'x=zahodit else(vysl:errcatch(test:ev(imagpart(rectform(rhs(cislo))),numer), if (test#0) then 'x=zahodit else    block(if (slength(string(tex(rhs(cislo),false)))>400) then 'x=\"\\\\dots\"     else 'x=rhs(cislo))),    if (vysl=[]) then [] else vysl[1])),pfeformat:true,simp:false,logexpand:false,f:" . $funkce . ");\n");
	fwrite($soubor, 'block(simp:true,tex(f,"' . $maw_tempdir . '/output.tex"));' . "\n");
	fwrite($soubor, 'tex(factor(ratsimp(' . $trigsimp . '(' . $radcan . '(diff(f,x))))),"' . $maw_tempdir . '/output.tex");' . "\n");
	fwrite($soubor, '(secondder:diff(f,x,2),if numberp(secondder) then tex(secondder,"' . $maw_tempdir . '/output.tex") else tex(factor(ratsimp(' . $trigsimp . '(' . $radcan . '(secondder)))),"' . $maw_tempdir . '/output.tex"));' . "\n");
	fwrite($soubor, 'if numberp(factor(diff(f,x))) then tex([],"' . $maw_tempdir . '/output.tex' . '") else tex(map(testreal,maw_solve_in_domain(factor(ratsimp(' . $trigsimp . '(radcan(diff(f,x))))),f,x)),"' . $maw_tempdir . '/output.tex");' . "\n");
	fwrite($soubor, 'block( if numberp(factor(diff(f,x,2))) then tex([],"' . $maw_tempdir . '/output.tex' . '") else tex(map(testreal,maw_solve_in_domain(factor(ratsimp(' . $trigsimp . '(radcan(diff(f,x,2))))),f,x)),"' . $maw_tempdir . '/output.tex"));' . "\n");
	fwrite($soubor, 'if numberp(f) then tex([],"' . $maw_tempdir . '/output.tex' . '") else tex(map(testreal,maw_solve_in_domain(f,f,x)),"' . $maw_tempdir . '/output.tex");' . "\n");


	fclose($soubor);


	$TeXfile = '
\usepackage{graphicx}
\fboxsep=3pt
\begin{document}

\def\dots{\hbox{\footnotesize(' . __("formula too long") . ')}}

\pagestyle{empty}
\everymath{\displaystyle}
\input outputf.tex
\input funkce.tex

\MAWhead{' . __("Investigating function using Maxima CAS") . '}


' . __("Function") . ': \fbox{\fbox{$y=\funkce$}}

\bigskip
' . __("Zeros of function") . ': $\nuly$

\bigskip
\hrule width \hsize
\bigskip

' . __("First derivative") . ': \fbox{$y\'=\derivace$}

\bigskip

' . __("Zeros of 1st derivative") . ': $\stac$

\bigskip
\hrule width \hsize
\bigskip

' . __("Second derivative") . ': \fbox{$y\'\'=\druha$}

\bigskip

' . __("Zeros of 2nd derivative") . ': $\krit$

\bigskip
\input lostsol.tex
\bigskip
\hrule width \hsize
\bigskip

\vbox{
  ' . __("Graph") . '

\includegraphics[width=\hsize]{graf.pdf}

}
\end{document}
';

	$soubor = fopen("$maw_tempdir/function.tex", "w");
	fwrite($soubor, $TeXheader . $TeXfile);
	fclose($soubor);

	$prikaz = "cp filter_image.pl $maw_tempdir; cp filtr.pl $maw_tempdir; cd $maw_tempdir; echo '<h4>*** GNUPLOT ****</h4>'>output; $mawtimeout gnuplot < obrazek >> output; perl filter_image.pl > a.eps; mv a.eps graf.eps; $epstopdf graf.eps; echo '<h4>*** MAXIMA ****</h4>'>>output;$mawtimeout  $maxima -b prikazy >> output; perl filtr.pl; LANG=$locale_file.UTF-8 perl -s $mawhome/prubeh/lostsol.pl -mawhome=$mawhome> lostsol.tex; echo \"$funkce_tex\" > funkce.tex; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex function.tex>>output; $catchmawerrors; cp * ..";

	/* Command for gnuplot                                                  */
	/* ---------------------------------------------------------------------*/
	define("NAZEV_SOUBORU_OBR", $maw_tempdir . "/obrazek");
	$souborobr = fopen(NAZEV_SOUBORU_OBR, "w");

	fwrite($souborobr, "mysqrt(x)=(x>=0)? sqrt(x):0/0 \n");
	fwrite($souborobr, "mylog(x)=(x>0)? log(x):0/0 \n");
	fwrite($souborobr, "set zeroaxis lt -1 \n");
	fwrite($souborobr, "set xtics axis nomirror \n");
	fwrite($souborobr, "set ytics axis nomirror \n");
	fwrite($souborobr, "set samples 1000 \n");
	fwrite($souborobr, "set noborder \n");
	fwrite($souborobr, "set term postscript eps color \n");
	fwrite($souborobr, "unset key \n");
	fwrite($souborobr, 'set output "graf.eps"' . "\n");
	fwrite($souborobr, "set xrange [" . $xmin . ":" . $xmax . "]\n");
	fwrite($souborobr, "set yrange [" . $ymin . ":" . $ymax . "]\n");
	fwrite($souborobr, "set style function lines\n");
	$funkcegnuplot = `$mawtimeout echo "$funkce" | $formconv_bin -r -O gnuplot`;
	$funkcegnuplot = chop($funkcegnuplot);
	$funkcegnuplot = str_replace("sqrt", "mysqrt", $funkcegnuplot);
	$funkcegnuplot = str_replace("log", "mylog", $funkcegnuplot);
	fwrite($souborobr, "plot " . $funkcegnuplot . " linewidth 10\n");
	fclose($souborobr);

	system($prikaz);

	$htmlFile = sprintf("<h2>%s</h2>", __("Investigating function using Maxima CAS"));
	require("$maw_tempdir/data.php");

	$htmlFile = $htmlFile . sprintf("<p>%s $ \\displaystyle %s $ </p>", __("We investigate the function"), "y= $funkceHTML ");


	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("\$x\$-intercepts"), $nulyHTML);

	$htmlFile = $htmlFile . sprintf(" <div class='logickyBlok'>");
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle y' = %s $ </p>", __("The first derivative"), $derivaceHTML);
	if ($stacHTML == '') {
		$temp = __("Maxima failed to find stationary points. They probably do not exist.");
	} else {
		$temp = sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("Stationary points"), $stacHTML);
	}
	$htmlFile = $htmlFile . $temp;
	$htmlFile = $htmlFile . "</div>";

	$htmlFile = $htmlFile . sprintf(" <div class='logickyBlok'>");
	$htmlFile = $htmlFile . sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle y'' = %s $ </p>", __("The second derivative"), $druhaHTML);

	if ($kritHTML == '') {
		$temp = __("Maxima failed to find critical points. They probably do not exist.");
	} else {
		$temp = sprintf("<p>%s :&nbsp;&nbsp;&nbsp; $\\displaystyle %s $ </p>", __("Critical points"), $kritHTML);
	}
	$htmlFile = $htmlFile . $temp;
	$htmlFile = $htmlFile . "</div>";

	$lostsolsHTML = `grep "Some solutions will be lost." $maw_tempdir/output`;
	if ($lostsolsHTML != "") {
		$lostsolsHTML = __("Maxima is using arc-trig functions to get a solution. Some solutions will be lost.");
	}
	$htmlFile = $htmlFile . $lostsolsHTML;

	$htmlFile = $htmlFile . sprintf("<br><img class=centerimg alt='Processing image ...' src=\"%s/prubeh/zpracuj.php?funkce=%s&xmin=%s&xmax=%s&ymin=%s&ymax=%s&output=png&rendom=%s\">", $mawphphome, rawurlencode($fce), rawurlencode($xmin), rawurlencode($xmax), rawurlencode($ymin), rawurlencode($ymax), $maw_tempdir);

}


if ($requestedoutput == "png") {
	system("cd $maw_tempdir; convert  -density 150 graf.eps graf.png");
	$file = $maw_tempdir . "/graf.png";

	header("Content-Type: image/png");
	header("Content-Disposition: attachment; filename=" . basename($file) . ";");
	header("Content-Transfer-Encoding: binary");
	readfile($file);
	system("rm -r " . $maw_tempdir);
	die();
}


/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/


$lastline = exec("cd $maw_tempdir; cat errors");

$uspech = 0;
if ($lastline != "") {
	maw_errmsgB("<pre>");
	system("cd $maw_tempdir; cat output.tex; cat outputf.tex; cat funkce.tex;cat output");
	echo("</pre></body></html>");
} else {
	/* here we send PDF file into browser */
	$uspech = 1;


	if ($requestedoutput == "html"):
		{
			if ($mawISAjax == 0) {
				maw_html_head();
			}
			$htmlFile = str_replace($formconv_repl_input, $formconv_repl_output, $htmlFile);
			echo $htmlFile;


			if ($mawISAjax == 0) {
				echo('</body></html>');
			}
		} else:
		send_PDF_file_to_browser("$maw_tempdir/function.pdf");
	endif;
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/


$navratovykod = exec("cd $maw_tempdir; cat uspech");
system("rm -r " . $maw_tempdir);


$datcasip = "funkce: $fce,  x:$xmin..$xmax,   y:$ymin..$ymax, typ:$typ, $navratovykod, ";

if ($uspech == 1) {
	save_log($datcasip, "prubeh");
} else {
	save_log_err($datcasip, "prubeh");
}

?>



