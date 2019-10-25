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


$scriptname="trap";
require ("../common/maw.php");


$fce=$_REQUEST["funkce"];
$a=$_REQUEST["a"];
$b=$_REQUEST["b"];
$n=$_REQUEST["n"];

$requestedoutput="html";
if ($_REQUEST["output"]=="pdf") {$requestedoutput="pdf";}

check_for_security("$fce, $a, $b, $n");

check_for_y($fce);
$variables='x';
$parameters=' ';

$funkce=input_to_maxima($fce,__("function"));

$variables=' ';
$a=input_to_maxima($a,__("lower limit of integration"));
$b=input_to_maxima($b,__("upper limit of integration"));

if ((!(preg_match('~^[0-9]+$~',$n)))||(preg_match('~^0~',$n)))
  { 	maw_html_head();
        echo (__("<h2>Error on input</h2>The number of subintervals should be a positive integer."));
	echo "<br>".__("Check your input data").": $n";
	die();
  } 
else 
  {
    if (!(($n<21) and ($n>0)))
      {
	maw_html_head();
        echo (__("<h2>Error on input</h2>The number of subintervals should be an integer between 1 and 20."));
	echo "<br>".__("Check your input data").": $n";
	die();
      }
  }

$n=input_to_maxima($n,__("number of subintervals"));


/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir="/tmp/MAW_trapezoidal".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);




/* We open temporary files and write informatins                        */
/* ---------------------------------------------------------------------*/

define ("NAZEV_SOUBORU", $maw_tempdir."/zadani");
$soubor=fopen(NAZEV_SOUBORU, "w");


/* Commands for maxima session - derivatives and zeros of derivatives   */
/* ---------------------------------------------------------------------*/

fwrite($soubor,$funkce."\n");
fwrite($soubor,$a."\n");
fwrite($soubor,$b."\n");
fwrite($soubor,$n."\n");
fclose($soubor); 

/* Here we run all programms                                            */
/* ---------------------------------------------------------------------*/

$TeXfile='\nonstopmode
\usepackage{graphicx}
\usepackage[metapost]{mfpic}
\opengraphsfile{obrazek}
\clipmfpic
\mfpicunit=1cm
\fboxsep 0 pt
\begin{document}

\def\msgA#1#2#3#4#5#6#7{\textbf{'.__("Problem").':}
'.__("Function to be integrated").': $f(x)=#1$\par\bigskip
'.__("Lower limit").': $a =#3 \approx #2$\\\\
'.__("Upper limit").': $b =#5 \approx #4$\\\\
'.__("Number of subintervals").': $n =#6$
\par\medskip\hrule\medskip \textbf{'.__("Solution").':}
'.__("The length of one subinterval").': $h={{b-a\over n}}=#7$

}

\def\msgB{'.__("Solution from Newton--Leibniz formula").':}
\def\msgC{'.__("The result of advanced numerical method").':}
\def\msgD{'.__("Graph and approximation by trapezoids").':}

\def\intfailed{\par \medskip \leavevmode{\fboxsep=5pt \fbox{'.__("Maxima failed to find the primitive function.").'}}}


\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\begin{center}
  {\large '.__("Trapeziodal rule for definite integral").'}\\\\
  \url{http://user.mendelu.cz/marik/maw}
\end{center}


\input tabulka.tex

\closegraphsfile
\test
\end{document}
';

$soubor=fopen("$maw_tempdir/trapezoidal.tex", "w");
fwrite($soubor,$TeXheader.$TeXfile);
fclose($soubor);

system ("cd $maw_tempdir; touch output; echo \"<h3>perl and Maxima</h3>\">>output; perl -s $mawhome/trap/trapezoidal.pl  -requestedoutput=$requestedoutput -mawhome=$mawhome -maxima=$maxima > tabulka.tex; cp * /tmp/");

$datcasip="funkce: $fce, x=$a..$b, n=$n";

if ($requestedoutput=="html")
{
  if ($mawISAjax==0) {maw_html_head();}

  require($maw_tempdir."/data.php");

  if ($test!="OK")
  {save_log_err($datcasip,"trap");
   maw_errmsgB("<pre>");
   system("cd ".$maw_tempdir."; cat output|$mawcat");
   system ("rm -r ".$maw_tempdir);
   echo("</pre>");
   if ($mawISAjax==0) { echo ("</body></html>");}
   die();
  }
    
  printf ("<h3>%s</h3>",__("Trapeziodal rule for definite integral"));
  echo "\$\$ \int_{$msgA3}^{$msgA5}$msgA1 \\,\\mathrm{d}x\$\$";

  echo " <div class=inlinediv>";  
  echo "<div class=logickyBlok>";
  printf ("<p>%s: \$%s\$</p>",__("Function to be integrated"),$msgA1);
  printf ("<p>%s: \$ a=%s\\approx %s\$ </p>",__("Lower limit"),$msgA3,$msgA2);
  printf ("<p>%s: \$ b=%s\\approx %s\$ </p>",__("Upper limit"),$msgA5,$msgA4);
  printf ("<p>%s: \$ n=%s\$ </p>",__("Number of subintervals"),$msgA6);
  echo "</div>";
  echo "</div>";

  echo " <div class=inlinediv>";  
  echo "<div class=logickyBlok>";
  printf ("<h4>%s</h4>",__("Solution"));
  printf ("<p>%s: \$ h=\\frac{b-a}n=%s\$ </p>",__("The length of one subinterval"),$msgA7);
  echo $outputtable;
  echo "</div>";
  echo "</div>";

  echo " <div class=inlinediv>";  
  echo "<div class=logickyBlok>";
  printf ("<h4>%s</h4>",__("Final result"));
  echo $result;
  echo "</div>";
  echo "</div>";

  echo "<div class=logickyBlok>";
  printf ("<h4>%s</h4>",__("Comparison with other methods"));
  echo "<ul><li>";
  if ($intfailed=="1")
     { printf ("<p class=\"red\">%s<p>",__("Maxima failed to find the primitive function.")); }
  else 
     { 
       printf ("<p>%s:  %s  </p>",__("Solution from Newton--Leibniz formula"),$resultB); 
       printf ("(<a target=\"_blank\" href=\"$mawphphome/integral/integralx.php?$fce;lang=$lang\">%s</a>)",__("Help to find the primitive function"));
     }
  echo "</li><li>";
  printf ("<p>%s:  %s </p>",__("The result of advanced numerical method"),$resultC);
  echo "</li></ul></div>";

  if ($ymin>=0)
  {
  echo "<div class=logickyBlok>";
  printf ("<h4>%s</h4>",__("Graph and approximation by trapezoids"));
  printf ("<img class=\"centerimg\" src=\"$mawhomephp/maw/trap/trapezoidalgnu.php?n=$n&ymin=$ymin&ymax=$ymax&a=%s&b=%s&funkce=%s&allpoints=%s\">",rawurlencode($a),rawurlencode($b),rawurlencode($fce),rawurlencode($allpoints));
  echo ("</div>");
  }

  save_log($datcasip,"trap");
  system ("rm -r ".$maw_tempdir);
  if ($mawISAjax==0) {echo('</body></html>');}
  die();
}


system ("cd $maw_tempdir; echo \"<h3>LaTeX1</h3>\">>output; $pdflatex trapezoidal.tex >> output; echo \"<h3>Metapost</h3>\">>output; $mawtimeout $mpost obrazek.mp >> output; echo \"<h3>LaTeX2</h3>\">>output; $pdflatex trapezoidal.tex >> output; $catchmawerrors; grep Undefined output >> errors; cp * /tmp/");

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline=exec("cd ".$maw_tempdir."; cat errors"); 

$uspech=0;
if ($lastline!="") {
  save_log_err($datcasip,"trap");
  maw_errmsgB("<pre>");
  system("cd ".$maw_tempdir."; cat output|$mawcat");
  system ("rm -r ".$maw_tempdir);
  die("</pre></body></html>");
} 
else
{
  /* here we send PDF file into browser */
  send_PDF_file_to_browser($maw_tempdir."/trapezoidal.pdf");
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system ("rm -r ".$maw_tempdir);
save_log($datcasip,"trap");

?>



