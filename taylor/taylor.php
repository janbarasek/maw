<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2010 Robert Marik, Miroslava Tihlarikova
Copyright 2011-2012 Robert Marik, Miroslava Tihlarikova

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

$scriptname="taylor";

require ("../common/maw.php");
$funkce=$_REQUEST["funkce"];
$rad=$_REQUEST["rad"];
$bod=$_REQUEST["bod"];

check_for_security("$funkce, $rad, $bod");
check_for_y($funkce);
$variables='x';

$funkce=input_to_maxima($funkce);

$variables=' ';
$bod=input_to_maxima($bod);

if (!(preg_match('~^[1-9]$~',$rad)))
  {
    maw_html_head();
    echo ("<span class='red'><b>".__("<h2>Error on input</h2>The degree of Taylor polynomial must be an integer between 1 and 9.")."</b></span>");
    die();
  }

/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/


$maw_tempdir="/tmp/MAW_taylor".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);


exec ("$mawtimeout $maxima --batch-string=\"[ev($funkce,x=$bod),float(ev($funkce,x=$bod,numer))];\">$maw_tempdir/output");
$kontrolaA=exec("cd $maw_tempdir; grep error output"); 
$kontrolaB=exec("cd $maw_tempdir; grep %i[^1] output"); 
$kontrola=$kontrolaA.$kontrolaB;

if ( $kontrola!="" )
  {
 $datcasip="<span style='color: rgb(255, 0, 0);'> $funkce, $rad, $bod ERROR</span>\n";
 save_log($datcasip,"taylor");
 system ("rm -r ".$maw_tempdir);
 maw_html_head();
 echo ("<h2 class='red'>".__("Incorrect problem")."</h2> ".sprintf(__("The function %s is not continuous at %s and we cannot find this Taylor polynomial."),$funkce,$bod));
 die("</body></html>");
  }

$params="-maxima=$maxima -function='$funkce' -order='$rad' -point='$bod' -mawhome=$mawhome ";

$TeXfile='\begin{document}
\def\retezecA#1#2#3{\hrule\medskip\textbf{'.__("Problem").': } 
'.__("Find $ #1 $-degree Taylor polynomial for function $ f(x)=#2$ in $ x_0=#3$.").'\par\medskip\hrule\bigskip}
\def\retezecB{\textbf{1.} '.__("Function and value of the function").': }
\def\retezecC#1{\medskip\textbf{2.} '.__("Derivatives, derivatives at  $#1$ and coefficients of Taylor polynomial").':}
\def\retezecD{'.__("Taylor polynomial").': }


\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{'.__("Taylor polynomial").'}

'.__("$ n$-degree  Taylor polynomial for the function  $ f(x)$ around  $ x=x_0$").':
\begin{align*}
  T_n(x)&=f(x_0)+f\'(x_0)(x-x_0)+\frac{f\'\'(x_0)}{2!}(x-x_0)^2+\frac{f\'\'\'(x_0)}{3!}(x-x_0)^3+\cdots\\\\&+\frac{f^{(i)}(x_0)}{i!}(x-x_0)^i+\cdots \frac{f^{(n)}(x_0)}{n!}(x-x_0)^n
\end{align*}

\fboxsep 4pt
\fboxrule 0 pt
\input data.tex
\test

\end{document}
';

$soubor=fopen("$maw_tempdir/taylor.tex", "w");
fwrite($soubor,$TeXheader.$TeXfile);
fclose($soubor);

if ($_REQUEST["output"]=="html")
{$htmloutput=1;} else {$htmloutput=0;}

system ("cd $maw_tempdir; LANG=$locale_file.UTF-8 perl -s $mawhome/taylor/taylor.pl $params; echo '<h4>*** Maxima output ****</h4>'>>output; cat data.tex>>output; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex taylor.tex>>output; cp * .. ; $catchmawerrors");

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline=exec("cd ".$maw_tempdir."; cat errors"); 


$uspech=0;
if ($lastline!="") {
  maw_errmsgB("<pre>");
  system("cd ".$maw_tempdir."; cat output|$mawcat"); 
  system ("rm -r ".$maw_tempdir);
  save_log_err("$funkce, $rad, $bod","taylor");
  die("</pre></body></html>");} 
 else
   {
     $uspech=1;
     if ($htmloutput==1)
     {
        if ($mawISAjax==0) {maw_html_head();}
        printf("<h2>%s</h2>",__("Taylor polynomial"));
        echo "<div class=logickyBlok>";
        echo __("$ n$-degree  Taylor polynomial for the function  $ f(x)$ around  $ x=x_0$");
        echo '\[\begin{align*}
            T_n(x)&=f(x_0)+f\'(x_0)(x-x_0)+\frac{f\'\'(x_0)}{2!}(x-x_0)^2+\frac{f\'\'\'(x_0)}{3!}(x-x_0)^3+\cdots\\\\&+\frac{f^{(i)}(x_0)}{i!}(x-x_0)^i+\cdots \frac{f^{(n)}(x_0)}{n!}(x-x_0)^n
            \end{align*}\]';
        echo '</div>';
        
        require("$maw_tempdir/data.php");
        echo "<div class=logickyBlok>";
                
        printf ("<h4>%s</h4>", __("Problem") );
        echo (str_replace(Array("#1","#2","#3"),Array($retezecA1,$retezecA2,$retezecA3),__("Find $ #1 $-degree Taylor polynomial for function $ f(x)=#2$ in $ x_0=#3$.")));
        echo '</div>';

        echo "<div class=inlinediv><div class=logickyBlok>";
        echo "<h4>".__("Function and value of the function")."</h4>";
        echo "$ $fx $, &nbsp;&nbsp;&nbsp;&nbsp; $ $fxat $";
        echo '</div></div> ';
        
        echo "<div class=inlinediv><div class=logickyBlok>";        
        echo "<h4>".str_replace("#1", $retezecC, __("Derivatives, derivatives at  $#1$ and coefficients of Taylor polynomial"))."</h4>";        
        echo '<style>
        table, th, td { border: 1px solid black; }
        table  { border-collapse:collapse; }
        th  {background-color:green; color:white; } 
        td {padding:10px;}
        </style><center>';
        echo $htmltable;  
        echo '</center></div></div> ';
        echo "<div class=inlinediv><div class=logickyBlok><h4>".__("Taylor polynomial")."</h4> \$ $resultHTML \$</div></div>";
        if ($mawISAjax==0) {echo('</body></html>');}
     }
     else
     {
       /* here we send PDF file into browser */
       send_PDF_file_to_browser("$maw_tempdir/taylor.pdf");
     }
   }

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system ("rm -r ".$maw_tempdir);

$datcasip="$funkce, $rad, $bod";
if ($uspech==1) 
  {save_log($datcasip."\n",taylor);}
?>



