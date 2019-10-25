<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2009 Robert Marik, Miroslava Tihlarikova
Copyright 2010-2013 Robert Marik

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

$scriptname="ode";

require ("../common/maw.php");
 
$rovniceinput=$_REQUEST["ode"];
$akce=$_REQUEST["akce"];
if ($_REQUEST["output"]=="pdf") {$requestedoutput="pdf";} else {$requestedoutput="html";} 


if ($akce=="0") {$rovniceinput2="y'=".$rovniceinput2;}
else {$rovniceinput2=$_REQUEST["ode2"];}

if (($akce=="1") && (stristr($rovniceinput2,"\'\'")||stristr($rovniceinput2,"''"))) 
{
    maw_html_head();
    echo("<h3>".__("Incorrect input")."</h3>".__("There should be no higher derivative than first derivative."));
    die();
}

if ($rovniceinput2=="") {$rovniceinput2="y'=$rovniceinput";}

$datcasip="rovnice: $rovniceinput ; rovnice: $rovniceinput2 ; akce: $akce";

check_for_security($rovniceinput);

$rovniceinput=str_replace(" ", "", $rovniceinput);
$rovniceinput2=str_replace(" ", "", $rovniceinput2);

if (($akce=="0") && (preg_match("~y\'|=~",$rovniceinput)))
  {
    maw_errmsgB("<h3 class='red'>".__("Incorrect input")."</h3><span class='red'>".__("There should be no derivative  y' and no equality sign  = in your input. Both are written on the left from the input field (you have to enter the right hand side of the equation only).")."</span><br><br>".__("You have to solve the equation with respect to the derivative first and write the right hand side only. You may try also to write the full equation to the second field and switch the radiobutton on the left."));
    $akce=1;
    $rovniceinput2=$rovniceinput;    
    save_log_err($datcasip,"ode");
    die();
  }

$parameters=' ';

if ($akce=="1")
  {
    $badinput=0;
    check_for_security($rovniceinput2);
    $rovniceinput2=str_replace("y\'","y'",$rovniceinput2);
    $rovniceinput2=str_replace("y\\\\\\'","y'",$rovniceinput2);
//    if (!(ereg("=",$rovniceinput2))) {$rovniceinput2=$rovniceinput2."=0";}
//    $parameters='drvt';
    if (!(stristr($rovniceinput2,"y'")))
      {
        maw_html_head();
	die($rovniceinput2."<br><h2 class='red'>".__("Error: Missing derivative")." <i>y'</i>.</h2></body></html>");
      }
//    $rce=split("=",str_replace("y'","drvt",$rovniceinput2));
//    $left=input_to_maxima($rce[0]); 
//    $right=input_to_maxima($rce[1]); 
//    $command=str_replace("drvt","'diff(y,x)",$left)."=".str_replace("drvt","'diff(y,x)",$right);
    $outform=`$mawtimeout echo "$rovniceinput2" | $formconv_bin -O maxima`;
    $command=str_replace("diff","'diff",$outform);
    if ($outform=="") 
    {      
       save_log($rovniceinput2,"errors");
       maw_errmsgB();
       die("<h2 class='red'>".__("Error in your expression")."</h2>");
    }
    $rovniceinput2=str_replace("diff(y, x)","y'",$outform);
    $aa=exec("$mawtimeout -t 5 $maxima --batch-string=\"equation:$command;load(\\\"ode.mac\\\");\"",$bb);
    $retezec=str_replace("\n"," ",join("\n",str_replace("'diff(y,x)","y'",$bb)));
    $retezec=str_replace("\\\n"," ",$retezec);
    preg_match("/texbeg .* texend/",$retezec,$matches);
    $vystup=$matches[0];
    $vystup=str_replace("texbeg","",$vystup);
    $vystup=str_replace("texend","",$vystup);
    $vystup=str_replace("$","",$vystup);
    preg_match("/solbegin .* solend/",$retezec,$matchess);
    $matches=preg_split("~solend *solbegin~",$matchess[0]);
   
    if ((sizeof($matches)==1)&&(stristr($retezec,"ode2 solved")))
    {
      require ("../common/redirect.php");
      $expr=$matches[0];
      $expr=str_replace("solbegin","",$expr);
      $expr=str_replace("solend","",$expr);
      $expr=str_replace("$","",$expr);
      $expr=str_replace("\n","",$expr);
      $mathtexexpr=explode("soltex",$expr);
      $rovniceinput=$mathtexexpr[0];
//      redirect($mawphphome."/ode/ode.php?ode2=".rawurlencode($rovniceinput2)."&ode=".rawurlencode($mathtexexpr[0])."&akce=0&lang=".$lang);
    }
    else
{
    maw_html_head();
    echo("<h2>".sprintf(__("We try to solve the equation %s with respect to the derivative."),$rovniceinput2)."</h2>");
    echo (__("Equation").": <img src=\"".$texrender.formconv_replacements($vystup)."\" border=\"0\">");


    function printode($expr)
    {
      global $lang, $mawhtmlhome,$texrender,$rovniceinput2,$badinput,$mawphphome;
      $expr=str_replace("solbegin","",$expr);
      $expr=str_replace("solend","",$expr);
      $expr=str_replace("$","",$expr);
      $expr=str_replace("\n","",$expr);
      $mathtexexpr=explode("soltex",$expr);
      if ($mathtexexpr[1]=="")
      {
       $str="<br><h2 class='red'>".__("Error in solving. (Bad input?)")."</h2>";
       $badinput=1;
      }
      else
      {
       $str="<br> ".__("The equation solved with respect to the derivative").": <a href=\"$mawphphome/ode/solveode.php?".rawurlencode($mathtexexpr[0]).";$lang\"><img src=\"".$texrender."y'=".formconv_replacements($mathtexexpr[1])."\" border=\"0\" align=\"center\"></a>  <small>(".__("click the equation to get step by step solution").")</small>";
      } 
      return ($str);
    } 

    foreach ($matches as $i => $value)
      {
	echo("<br>".printode($value));
      }

    if ($badinput==1)
    {
      $parameters="";      
      maw_howto();
      echo "<pre>".highlight_errors(join("\n",$bb))."</pre>";      
      save_log_err($datcasip,"ode");
    }
    else
    {
      save_log($datcasip,"ode");
    }
    die();
  }
}


$rovnice=input_to_maxima($rovniceinput); 
if ($akce=="0") {$rovniceinput2="y'=".$rovnice;}
if (!(stristr($rovniceinput2,"="))) {$rovniceinput2=$rovniceinput2."=0";}


/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir="/tmp/MAW_ode".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);


$TeXcontents='
\fboxsep 0 pt
\usepackage{amsfonts}

\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\def\ODEproblem{%s}
\def\ODEsolution{%s}
\def\ODEfootnote{\footnotetext[1]{\ODEfootnoteA}\footnotetext[2]{\ODEfootnoteB}}
\def\ODEfootnoteA{%s}
\def\ODEfootnoteB{%s}
\def\ODEkonstres{%s}
\def\ODEkonstresHOM{%s}
\def\ODEnotfound{\\hbox{ %s }}
\def\ODEseparace{%s }
\def\ODEnekres{%s}
\def\ODEobres{%s}
\def\ODEpartres{%s}
\def\ODEvarconst{%s}
\def\ODErovnicejelinearni{%s}
\def\ODErovnicejehomogenni{%s}
\def\ODEassochomrce{%s}
\def\ODEhomogeneousequation{%s}
\def\ODEsingular_solution{%s}
\def\ODEsubstitution{%s }

\def\ODEunknownsolved#1{%s}
\def\ODEintfactormsg#1{ %s}

\MAWhead{'.__("Ordinary differential equation").'}

\newif\ifintegral\integraltrue
\newif\ifexplicit\explicittrue
\newif\ifconstantmsg\constantmsgtrue
\input data.tex

\test
\vbox{
\bigskip
\textbf{'.__("Remarks").'}
\begin{itemize}
\ifintegral\item '.__("By clicking the integral you load the integral into an interactive tool for integration.").'\fi
\ifexplicit\item '.__("We get the solution in its explicit form only for the linear equations. For the equations with separated variables we get only the implicit form.").'\fi
\ifconstantmsg\item '.__("Sometimes Maxima fails to find some constant solutions of the equation with separated variables.").'\fi
\end{itemize}
}
\end{document}
';

$subfrom=Array("!!ODEproblem", "!!ODEsolution", "!!ODEfootnoteA", "!!ODEfootnoteB", "!!ODEkonstresHOM", "!!ODEkonstres", "!!ODEnotfound", "!!ODEseparace", "!!ODEnekres", "!!ODEobres", "!!ODEpartres", "!!ODEvarconst", "!!ODErovnicejelinearni", "!!ODErovnicejehomogenni", "!!ODEassochomrce", "!!ODEhomogeneousequation", "!!ODEsingular_solution", "!!ODEsubstitution", "!!ODEunknownsolved", "!!ODEintfactormsg","!!Variacekonstant", "!!Integracnifaktor","!!Vypocetintegracnihofaktoru", "!!Resenipomociintegracnihofaktoru");

$subto=Array(__("Problem"),__("Solution"),__("The computation could be divided into more pages"),__("Not all brackets which are written by the computer are necessary. Many of them can be safely omitted."),__("Linear solution"),__("Constant solution"),"\\text{}\$".__("Not found.")."\${}",__("Separation of variables"),__("Nonconstant solution"),__("General solution"),__("Particular solution"),__("Variation of constant:"),__("Equation is linear"),__("Equation is homogeneous  -- we will use separation of variables and write the solution in explicit form."),__("Associated homogeneous equation"),__("Homogeneous equation"),__("Singular solution"),__("substitution"),__("The equation has been solved, but it is neither linear nor separable, homogeneous or exact and no intermediate steps are shown. The equation is #1."),__("The equation has integrating factor #1."),__("Method 1 (variation of constant)"), __("Method 2 (Integrating factor)"), __("Evaluating integrating factor"), __("Integration using integrating factor"));

$TeXcontents=$TeXheader.sprintf($TeXcontents,__("Problem"),__("Solution"),__("The computation could be divided into more pages"),__("Not all brackets which are written by the computer are necessary. Many of them can be safely omitted."),__("Constant solution"),__("Linear solution"),__("Not found."),__("Separation of variables"),__("Nonconstant solution"),__("General solution"),__("Particular solution"),__("Variation of constant: {\\color{blue}(the sum of blue expressions is zero)}"),__("Equation is linear"),__("Equation is homogeneous  -- we will use separation of variables and write the solution in explicit form."),__("Associated homogeneous equation"),__("Homogeneous equation"),__("Singular solution"),__("substitution"),__("The equation has been solved, but it is neither linear nor separable, homogeneous or exact and no intermediate steps are shown. The equation is #1."),__("The equation has integrating factor #1.")); 

define ("SOUBB", $maw_tempdir."/ode.tex");
$soubor=fopen(SOUBB, "w");
fwrite($soubor,$TeXcontents);
fclose($soubor);

//die("LANG=$locale_file.UTF-8 perl -s $mawhome/ode/ode.pl -mawhome=$mawhome -maxima=$maxima -ode='$rovnice' -lang='$lang' -fullode=\"$rovniceinput2\"");

if (function_exists("ode_before_latex")) {ode_before_latex();}

system ("cd $maw_tempdir; echo '<h4>*** Maxima output ****</h4>'>>output; LANG=$locale_file.UTF-8 perl -s $mawhome/ode/ode.pl -mawhome=$mawhome -maxima=$maxima -ode='$rovnice' -lang='$lang' -fullode=\"$rovniceinput2\" ;  cat data.tex>>output ; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex ode.tex>>output; cp * ..; $catchmawerrors");


// function show_TeX2($str)
// { 
//   global $texrender;
//   $str=preg_replace("/\\\$((.|\n)*?)\\\$/","<img src=\"".$texrender."\\1\">",$str);
//   return(formconv_replacements($str));
// }

// echo ("<pre>".show_TeX2(file_get_contents($maw_tempdir."/data.tex")));
// die();

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline=exec("cd $maw_tempdir; cat errors"); 

if ($lastline!="") {
  maw_errmsgB("<hr>".put_tex_to_html("y'=".formconv($rovniceinput))."<hr>");
  echo ("<pre>");
  system("cd ".$maw_tempdir."; cat output|$mawcat");
  save_log_err($datcasip,"ode");
  system ("rm -r ".$maw_tempdir);
  die("</pre></body></html>");
} 
else
{
  if ($requestedoutput=="html")
  {
    if ($mawISAjax==0) {maw_html_head();}
    $file = file_get_contents("$maw_tempdir/data.php");
    echo sprintf("<h2>%s</h2>", __("Ordinary differential equation"));
    echo (str_replace($subfrom,$subto,$file));
    if ($mawISAjax==0) {echo('</body></html>');}
  }
  else
  {
  /* here we send PDF file into browser */
  send_PDF_file_to_browser($maw_tempdir."/ode.pdf");
  } 
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system ("rm -r ".$maw_tempdir);
save_log($datcasip,"ode");

?>



