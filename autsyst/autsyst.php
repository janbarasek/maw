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


$scriptname="autsyst";
require ("../common/maw.php");

$fcef=$_REQUEST["funkcef"];
$fceg=$_REQUEST["funkceg"];
$akce=$_REQUEST["akce"];
$xs=$_REQUEST["xs"];
$ys=$_REQUEST["ys"];

$requestedoutput="html";
if ($_REQUEST["output"]=="pdf") {$requestedoutput="pdf";}

check_for_security($fcef.", ".$fceg.", ".$xs.", ".$ys);

if ($akce !=1)
{
   $parameters=' ';
}

$fcef=input_to_maxima($fcef,__("function")." x'=..."); 
$fceg=input_to_maxima($fceg,__("function")."y'=..."); 

$loginfo="system: x'=$fcef, y'=$fceg, stac bod: x=$xs,y=$ys";


if ($akce ==1)
{
  function najdi_SB($retezec)
  {
    $retezec=str_replace("\n","",$retezec);
    preg_match("/keyprint .* keyprint/",$retezec,$matches);
    $vystup=$matches[0];
    $vystup=str_replace("keyprint", "", $vystup);
    $vystup=str_replace("{", "", $vystup);
    $vystup=str_replace("}", "", $vystup);
    $vystup=str_replace(" [", "[", $vystup);
    $vystup=str_replace("] ", "]", $vystup);
    return ($vystup);
  }

  function print_as($expr)
  {
    global $lang, $mawhtmlhome,$texrender,$fcef,$fceg,$mawphphome;
    $expr=str_replace("[","",$expr);
    $expr=str_replace("]","",$expr);
    $expr=str_replace("$","",$expr);
    $expr=str_replace("\n","",$expr);
    list($xs,$ys)=explode(",",$expr);
    $str="<li> <a target=\"_blank\" title=\"".__("Computation opens in new panel or window.")."\" href=\"$mawphphome/autsyst/autsyst.php?funkcef=".rawurlencode($fcef)."&funkceg=".rawurlencode($fceg)."&xs=".rawurlencode($xs)."&ys=".rawurlencode($ys)."&lang=".$lang."\">"."$ \\left[".formconv($xs).",".formconv($ys)."\\right] $</a><br>";
    return ($str);    
  }
  
  maw_html_head();	

  echo "<h3>".__("Autonomous system").':&nbsp;&nbsp;&nbsp;';
  
  echo ("\\(\\begin{cases}x'=".formconv($fcef)."\\\\y'=".formconv($fceg)."\\end{cases}\\)").'</h3><br>';

  echo __("We look for stationary points of the autonomous system.")."<BR><br>";

  $command="yes \"pos;\" | $mawtimeout $maxima --batch-string=\"f(x,y):=$fcef; g(x,y):=$fceg; load(\\\"$mawhome/autsyst/stationary_as.mac\\\")$\"";
  $command_out=`$command`;
  check_for_errors($command_out,$loginfo,"autsyst");

  $points=najdi_SB($command_out);
  $points=str_replace("],[","];[",$points);
    echo '<h2>',__("Stationary points"),'</h2>';
  if (str_replace(" ","",$points)=="") 
    {
      echo "<span class='red'>".__("No isolated stationary point has been found. Either there are no stationary points or the problem is too difficult for Maxima.")."</span>";
      save_log_err($loginfo." nenasli jsme stacionarni bod","autsyst");
      die("<hr><pre><small><b>Transcript of Maxima session:</b><br>".$command_out."</body></html>");
    }
  $allpoints=explode(";",$points);
  foreach ($allpoints as $i => $value)
    {
      echo("<br>".print_as($value));
    }
  echo ("<br>&nbsp;&nbsp;&nbsp;<small>(".__("click the point to get Jacobi matrix and clasify the stationary point").")</small>");
  if (stristr($points,"%"))
    {
      echo ("<br>".__("<b>Problems:</b> Infinitely many solutions, or some solutions cannot be written using elementary functions."));
    }  
  echo("<hr><pre><small><b>Transcript of Maxima session:</b><br>");
  echo $command_out;
  die("</body></html>");
}



/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/


$maw_tempdir="/tmp/MAW_aut".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);



define ("NAZEV_SOUBORU", $maw_tempdir."/vstup");
$soubor=fopen(NAZEV_SOUBORU, "w");
fwrite($soubor,"$fcef\n$fceg\n$xs\n$ys\n$lang\n");
fclose($soubor); 

define ("NAZEV_SOUBORU2", $maw_tempdir."/autsyst.tex");
$soubor=fopen(NAZEV_SOUBORU2, "w");
$TeXfile='
\fboxsep 0 pt
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{'.__("Autonomous system in plane").'}

\input data.tex

\textbf{'.__("Autonomous system").':}
\begin{align*}
 x\'={}&\ftex\\\\ 
 y\'={}&\gtex\\\\ 
\end{align*}

\textbf{'.__("Stationary point").':} $[x,y]=\left[\stbod\right]$

\bigskip
\hrule

\bigskip

\makeatletter
\def\matrix#1{\null\,\vcenter{\normalbaselines\m@th
    \ialign{\hfil$##$\hfil&&\quad\hfil$##$\hfil\crcr
       \mathstrut\crcr\noalign{\kern-\baselineskip}
       #1\crcr\mathstrut\crcr\noalign{\kern-\baselineskip}}}\,}
 \def\pmatrix#1{\left(\matrix{#1}\right)}
\makeatother

 \textbf{'.__("Jacobi matrix").': }
 $J(x,y)=
 \jakobihomatice
 $

 \bigskip
 \textbf{'.__("Jacobi matrix at stationary point").':  }
 $J\left(\stbod\right)=
 \jakobihomatices
 $
 
 \bigskip
 \textbf{'.__("Characteristic polynomial").': }

 a) '.sprintf(__("From the determinant of %s"),"\$J-\\lambda I\$").':

\null\qquad $\Bigl|J-\lambda I\Bigr|=
 \left |\matrix{\dfxs-\lambda&\dfys\cr \dgxs &\dgys-\lambda\cr}\right|=
 \left(\dfxs-\lambda\right)\left(\dgys-\lambda\right)-(\dfys)(\dgxs)=
 \charpoly$

 b) '.sprintf(__("From trace and determinant of %s"),"\$J\$").':

\null\qquad $|J|=\determinant$

\null\qquad $\hbox{Tr } J=\trace$

\null\qquad $\lambda^2-\text{Tr}(J)\lambda+|J|=\charpoly$

 \bigskip
 \textbf{'.__("Eigenvalues").': } \vlastnicisla

 \textbf{'.__("Eigenvalues (numerically)").': } \vlastnicislanum

 \bigskip
 \textbf{'.sprintf(__("Stationary point is %s."),"\\decision").'}

\end{document}
';
fwrite($soubor,$TeXheader.$TeXfile);
fclose($soubor); 

system ("cd $maw_tempdir; LANG=$locale_file.UTF-8 perl -s $mawhome/autsyst/autsyst.pl -mawhome=$mawhome -maxima=$maxima; $pdflatex autsyst.tex>>output; $catchmawerrors; cp * ..");


/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline=exec("cd ".$maw_tempdir."; cat errors"); 

if ($lastline!="") {
  maw_errmsgB("<pre>"); 
  echo put_tex_to_html("\\usepackage{color}\\color{red}\\begin{cases}x'=".formconv($fcef)."\\\\y'=".formconv($fceg)."\\end{cases}").'<br>';
  system("cd $maw_tempdir; cat output|$mawcat");
  system ("rm -r ".$maw_tempdir);
  save_log_err($loginfo, "autsyst");
  die("</pre></body></html>");
} 
else
{
  if ($requestedoutput=="html")
  {
    if ($mawISAjax==0) {maw_html_head();}
    require ("$maw_tempdir/data.php");
    require ("htmloutput.php");
    if ($mawISAjax==0) {echo('</body></html>');}
  }
  else
  {
  /* here we send PDF file into browser */
  send_PDF_file_to_browser("$maw_tempdir/autsyst.pdf");
  }
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system ("rm -r ".$maw_tempdir);
save_log($loginfo, "autsyst");

?>



