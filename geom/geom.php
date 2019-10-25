<?php

/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
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

$scriptname="geom";

require ("../common/maw.php");

$fcef=$_REQUEST["funkcef"];
$fceg=$_REQUEST["funkceg"];
$akce=$_REQUEST["akce"];
$meza=$_REQUEST["meza"];
$mezb=$_REQUEST["mezb"];
$colors=$_REQUEST["colors"];
$hidden=$_REQUEST["hidden"];
$xmin=check_decimal($_REQUEST["xmin"],"xmin");
$xmax=check_decimal($_REQUEST["xmax"],"xmax");
$ymin=check_decimal($_REQUEST["ymin"],"ymin");
$ymax=check_decimal($_REQUEST["ymax"],"ymax");

$requestedoutput="html";
if ($_REQUEST["output"]=="pdf") {$requestedoutput="pdf";}

if ($fceg=="") {$fceg="0";}
if ($fcef=="") {$fcef="0";}
if ($akce!=1) {$parameters=' ';}

check_for_security("$fcef, $fceg, $meza, $mezb, $xmin, $xmax, $ymin, $ymax");


$fcef=input_to_maxima($fcef,__("upper function")); 
$fceg=input_to_maxima($fceg,__("lower function")); 

$linkfcef=$fcef;
$linkfceg=$fceg;

check_for_y($fcef);
check_for_y($fceg);

$variables='x';

$datcasip="funkce: $fcef,$fceg, meze:$meza..$mezb,  x:$xmin..$xmax,   y:$ymin..$ymax";
$problem="nekorektni zadani";

if ($akce ==1)
  {// looking for curve intersections
  maw_html_head();
  echo(sprintf(__("We will look for the intercepts of  y=%s and y=%s, symbolically and numerically."),$fcef,$fceg)."(".__("The solution is at the line starting with %o2").")<BR><pre><h2>".__("Symbolic computation")."</h2>");
  system("$mawtimeout $maxima --batch-string=\"display2d:false\$ solve($fcef=$fceg,[x]);\"");
  
  echo "<h2>".__("Numerical computation")."</h2>";
  
  system ("$mawtimeout $maxima --batch-string=\"display2d:false\$ float(solve($fcef=$fceg,[x]));\"");
  
  echo "</pre>"; 
  //echo __("<h2>Remarks</h2><ul> <li>You can use the clipboard to copy the output into the form.</li> <li>Use back button to go back to the form.</li><li>Remember to change the action for computation now. Otherwise you get this page again.</li></ul>");
  
  $parameters="a=$meza&b=$mezb&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=".rawurlencode("$fcef")."&g=".rawurlencode("$fceg");
  echo ("<h2>".__("Picture")."</h2>"."<center><img class=centerimg alt=\"Loading ...\" src=\"$mawphphome/gnuplot/gnuplot_region.php?$parameters\"></center>");
  die("</body></html>");
}


$variables=' ';
$meza=input_to_maxima($meza,__("lower bound for integration"));
$mezb=input_to_maxima($mezb,__("upper bound for integration"));
$xmin=input_to_maxima($xmin,"xmin");
$xmax=input_to_maxima($xmax,"xmax");
$ymin=input_to_maxima($ymin,"ymin");
$ymax=input_to_maxima($ymax,"ymax");

// test if the interval for integration is a subset of integral for picture
$kontrola=`$mawtimeout $maxima --batch-string="print (\"asdf\",is($meza>=$xmin),is($mezb<=$xmax),\"asdf\",\"lll\",is($mezb-($meza)>0));"`;

check_for_errors($kontrola,$datcasip,"geom");

if (!(preg_match("~asdf *true ~",$kontrola)))
  {
    if (preg_match('~^-?[0-9]+\.*[0-9]*$~',$meza)) 
      {
	$xmin=$meza;
      }
    else
      {
	maw_html_head();
	echo sprintf(__("<h2>Error</h2>The interval for integration, %s, is not subset of the interval for drawing picture %s."),"[$meza,$mezb]","[$xmin,$xmax]")." ".__(" Change <i>xmin</i>.");
	$datcasip=$datcasip." bad limits";
	save_log_err($datcasip,"geom");
	die();
      }
  }
if (!(preg_match("~true *asdf~",$kontrola)))
  {
    if (preg_match('~^-?[0-9]+\.*[0-9]*$~',$mezb)) 
      {
	$xmax=$mezb;
      }
    else
      {
	maw_html_head();
	echo sprintf(__("<h2>Error</h2>The interval for integration, %s, is not subset of the interval for drawing picture %s."),"[$meza,$mezb]","[$xmin,$xmax]");
	echo " ".__("Change <i>xmax</i>.");
	$datcasip=$datcasip." bad limits";
	save_log_err($datcasip,"geom");
	die();
      }
  }

if (preg_match("~lll *false ~",$kontrola))
  {
    maw_html_head();
    $fixedlink=str_replace(mezaa,mezb,str_replace(mezb,meza,str_replace(meza,mezaa,$maw_URI)));
    $fixedlink=preg_replace('/.*\?/','',$fixedlink);
    $fixedlink=preg_replace('/&referer=.*?&/','&',$fixedlink);
    $link="$mawhtmlhome/index.php?form=geom&auto=1&$fixedlink";
    //echo ("<h2>aaaa</h2>".$link);
    //$link=str_replace(mezaa,mezb,str_replace(mezb,meza,str_replace(meza,mezaa,$maw_URI)));
    //http://um.mendelu.cz/dev-maw/geom/geom.php?lang=cs&ip=62.245.112.168&referer=http%3A%2F%2Fum.mendelu.cz%2Fdev-maw-html%2Findex.php%3Flang%3Dcs%26amp%3Bform%3Dintegral&funkceg=1&funkcef=4&meza=0&mezb=1&akce=0&xmin=-1&xmax=3&ymin=-1&ymax=3
    printf (__("<h2>Error</h2>The upper limit (%s) for the set (and for integration) is bigger then the lower limit (%s). Interchange limits in the form or clicking this %slink%s."),"<i>$mezb</i>","<i>$meza</i>","<a href=\"$link\">","</a>");
    $datcasip=$datcasip." interchanged limits";
    save_log_err($datcasip,"geom");
    die();
    $linkfceg=$fcef;    
    $linkfcef=$fceg;    
  }


// 1-st test: both functions are continuous and well ordered on [a,b]
$kontrola=`$mawtimeout $maxima --batch-string="load(\"$mawhome/geom/tests.mac\"); testreal($fcef,$meza,$mezb);testreal($fceg,$meza,$mezb);test1($fcef,$fceg,$meza,$mezb);"`;
if (preg_match("~an error.~",$kontrola))
  {
    maw_html_head();
    $fcions="<hr><center>$ y={}".formconv($fcef)."$ <br><br>$ y={}".formconv($fceg)."$ </center><hr>";
    if ((stristr($kontrola,"lower bound bigger than upper bound")) && (!(stristr($kontrola,"the function has not real value at"))))
      {
	$kontrolaB=`$mawtimeout $maxima --batch-string="load(\"$mawhome/geom/tests.mac\"); test1($fceg,$fcef,$meza,$mezb);"`;
	if ((preg_match("~an error.~",$kontrolaB)))
	  {
	    if (stristr($kontrolaB,"lower bound bigger than upper bound"))
	      {
		$fcions=$fcions."<b class='red'>".__("The functions have probably one or more intersection inside the interval. Check the picture below. You can find the intersection by choosing the corresponding option in the form on the previous page.")."</b><hr>";
		$problem="prusecky uvnitr";
	      }
	    else
	      {
		$fcions=$fcions."<b class='red' >".__("One of the functions has probably point of discontinuity on the interval under consideration. Look on the picture below and check your input.")."</b><hr>";
		$problem="nespojitost";
	      }
	  }
	else
	  {
	    $linkname="<a href=\"$mawphphome/..".str_replace(funkceff,funkceg,str_replace(funkceg,funkcef,str_replace(funkcef, funkceff,$maw_URI)))."\">".__("link")."</a>";	
	    $fcions=$fcions."<b class='red'>".sprintf(__("Probably exchanged f and g functions. Interchange these functions either in the previous form or by clicking the following %s."),$linkname)."</b><hr>";
	    $problem="prehozene funkce";
	  }
      }
    else
      {
	$fcions=$fcions."<b class='red'>".__("One of the functions has probably point of discontinuity on the interval under consideration. Look on the picture below and check your input.")."</b><hr>";
      }

    echo "<h2 class='red'>".__("Error")."</h2>";
    echo $fcions.sprintf(__("<ul><li>Error when checking continuity of functions and when checking that lower bound is smaller than upper bound.<li> Are you sure that both %s and %s are defined and continuous on the interval %s and %s is bigger than %s on this interval?<li>Check your functions on the following picture.</ul>"), $fcef, $fceg, "[$meza,$mezb]" , $fcef , $fceg);
    $parameters="a=$meza&b=$mezb&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=".rawurlencode("$fcef")."&g=".rawurlencode("$fceg");
    check_for_errors($kontrola,$datcasip." ".$problem,"geom","<img  class=centerimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\">");
    
    die();
  }

check_for_errors($kontrola,$datcasip." ".$problem,"geom");

// volume of revolution - test that the region does not intersect x axis

if (($akce=="2")||($akce=="3"))
  {
    $kontrola=`$mawtimeout $maxima --batch-string="load(\"$mawhome/geom/tests.mac\"); test2($fcef,$fceg,$meza,$mezb);"`;
    if (preg_match("~an error.~",$kontrola))
      {
	maw_html_head();
	$fcions="<hr><center>$ y=".formconv($fcef)."$ <br><br>$ y=".formconv($fceg)."$ </center><hr>";
	if (stristr($kontrola,"the region between curves intercests"))
	  {
	    $fcions=$fcions."<b class='red'>".__("The x axis goes through the set from the formulation of the problem. The problem is not well formulated.")."</b><hr>";
	    $problem="osa x protina mnozinu";
	  }

	echo "<h2 class='red'>".__("Error")."</h2>".$fcions;
	echo sprintf(__("<ul><li>Error when checking continuity of functions and when checking that lower bound is smaller than upper bound.<li> Are you sure that both %s and %s are defined and continuous on the interval %s and %s is bigger than %s on this interval?<li>Check your functions on the following picture.</ul>"),$fcef,$fceg,"[$meza,$mezb]",$fcef,$fceg);
	save_log_err($datcasip." volume ".$problem,"geom");
        $parameters="a=$meza&b=$mezb&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=".rawurlencode("$fcef")."&g=".rawurlencode("$fceg");
	echo ("<img  class=centerimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\">");
	echo("<pre>$kontrola</pre></body></html>");
	die();
      }
    // check if both functions are positive or negative
    $kontrola2=`$mawtimeout $maxima --batch-string="load(\"$mawhome/geom/tests.mac\"); test1($fceg,0,$meza,$mezb);"`;         
    if (preg_match("~an error.~",$kontrola2))
      {
	$tempf=$fcef;
	$fcef=$fceg;
	$fceg=$tempf;
      }
  }

if ($akce ==3)
  { // 3d picture - solid of revolution
    $viewa=$_REQUEST["viewa"];
    $viewb=$_REQUEST["viewb"];
    
    if ($viewa=="") {$viewa="60";}
    if ($viewb=="") {$viewb="30";}
    maw_html_head();
    
    echo '<h2>';
    echo __("Solid of revolution");
    echo '&nbsp;&nbsp;&nbsp;$f(x)='.formconv($linkfcef)."\$, \$g(x)=".formconv($linkfceg)."\$";
    echo '</h2>';
    
    function changeviewgnuplot ($a,$b,$c)
    {
      global $viewa,$viewb,$linkfcef,$linkfceg,$meza,$mezb,$xmin,$xmax,$ymin,$ymax,$colors,$hidden,$lang;
      $href="<a href=\"geom.php?akce=3&viewa=".chviewa($viewa+$a)."&viewb=".chviewb($viewb+$b)."&funkcef=".rawurlencode("$linkfcef")."&funkceg=".rawurlencode("$linkfceg")."&xmin=".rawurlencode("$xmin")."&xmax=".rawurlencode("$xmax")."&ymin=".rawurlencode("$ymin")."&ymax=".rawurlencode("$ymax")."&mezb=".rawurlencode("$mezb")."&meza=".rawurlencode("$meza")."&colors=$colors&hidden=$hidden&lang=$lang\">".$c."</a>";
      return ($href);
    }
    
    function chviewb($b)
    {
      if ($b<0) {return ("350");}
      if ($b>360) {return ("10");}
      return($b);
    }
    
    function chviewa($a)
    {
      if ($a<0) {return ("170");}
      if ($a>180) {return ("10");}
      return($a);
    }
    
    
    //echo '<br>',changeviewgnuplot(10,0,__("up")),'<br>';
    //echo changeviewgnuplot(0,10,__("left")),'<br>';
    //echo changeviewgnuplot(0,-10,__("right")),'<br>';
    //echo changeviewgnuplot(-10,0,__("down")),'<br>';
    
    //$href="geom.php?akce=2&funkcef=".rawurlencode("$linkfcef")."&funkceg=".rawurlencode("$linkfceg")."&xmin=".rawurlencode("$xmin")."&xmax=".rawurlencode("$xmax")."&ymin=".rawurlencode("$ymin")."&ymax=".rawurlencode("$ymax")."&meza=".rawurlencode("$meza")."&mezb=".rawurlencode("$mezb")."&colors=$colors&hidden=$hidden&lang=$lang";
    //echo ("<a href=\"$href\">".__("Volume computation")."</a><br>");
    
    
    $strsolid="<img class=centerimg alt=\"Loading ...\" src=\"../../maw/geom/solid.php?viewa=".rawurlencode($viewa)."&viewb=".rawurlencode($viewb)."&fcef=".rawurlencode("$fcef")."&fceg=".rawurlencode("$fceg")."&meza=".rawurlencode("$meza")."&mezb=".rawurlencode("$mezb")."&xmin=".rawurlencode("$xmin")."&xmax=".rawurlencode("$xmax")."&ymin=".rawurlencode("$ymin")."&ymax=".rawurlencode("$ymax")."&colors=$colors&hidden=$hidden\">";
    
    echo $strsolid;
    
    //echo '<br>',changeviewgnuplot(10,0,__("up")),'<br>';
    //echo changeviewgnuplot(0,10,__("left")),'<br>';
    //echo changeviewgnuplot(0,-10,__("right")),'<br>';
    //echo changeviewgnuplot(-10,0,__("down")),'<br>';
    
    //$href="geom.php?akce=2&funkcef=".rawurlencode("$linkfcef")."&funkceg=".rawurlencode("$linkfceg")."&xmin=".rawurlencode("$xmin")."&xmax=".rawurlencode("$xmax")."&ymin=".rawurlencode("$ymin")."&ymax=".rawurlencode("$ymax")."&meza=".rawurlencode("$meza")."&mezb=".rawurlencode("$mezb")."&colors=$colors&hidden=$hidden&lang=$lang";
    //echo ("<a href=\"$href\">".__("Volume computation")."</a><br>");
    die("</body></html>");
  }


/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir="/tmp/MAW_geom".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);

define ("NAZEV_SOUBORU", $maw_tempdir."/vstup");
$soubor=fopen(NAZEV_SOUBORU, "w");
fwrite($soubor,"$fcef\n$fceg\n$meza\n$mezb\n$xmin\n$xmax\n$ymin\n$ymax\n$lang\n$akce\n$mawphphome\n".__("Error in the function or in the bound for integration."));
fclose($soubor); 


$TeXfile='
\usepackage[metapost]{mfpic}
\opengraphsfile{obrazek}
\clipmfpic\mfpicunit=1cm

\begin{document}

\def\information{'.__("Click the integral to send the function to a tool which helps to evaluate the indefinite integral.").'}

\def\intfailed{\par \medskip \leavevmode{\fboxsep=5pt \fbox{'.__("Maxima failed to find the primitive function.").'}}}

\def\AreaA#1#2#3{'.__("We find area under the curve #1 on the interval from #2 to #3.").'}
\def\AreaB#1#2#3#4{'.__("We find area between curves #1 and #2 on the interval from #3 to #4.").'}
\def\VolumeA#1#2#3{'.__("We find volume of solid of revolution formed by revolving area under the curve #1 on the interval from #2 to #3 around $ x$ axis.").'}
\def\VolumeB#1#2#3#4{'.__("We find volume of solid of revolution formed by revolving area between curves #1 and #2 on the interval from #3 to #4 around $ x$ axis..").'}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{'.__("Geometrical application of definite integral").'}

\input data.tex

\closegraphsfile

\test
\end{document}
';

$soubor=fopen($maw_tempdir."/geom.tex", "w");
fwrite($soubor,$TeXheader.$TeXfile);
fclose($soubor); 

system ("cd $maw_tempdir; perl -s $mawhome/geom/geom.pl -maxima=$maxima -maxima2=$maxima2 -mawhome=$mawhome"); 


if ( $requestedoutput=="pdf" );
{
   system ("cd $maw_tempdir; echo \"***** LaTeX *****\" >> output; $pdflatex geom.tex>>output; echo \"***** MetaPost *****\" >> output; $mawtimeout $mpost obrazek.mp>>output; echo \"***** LaTeX *****\" >> output");
   if (function_exists("geom_before_latex")) {geom_before_latex();}
   system ("cd $maw_tempdir; $pdflatex geom.tex>>output; $catchmawerrors; grep Chyba output>>errors; grep 'Emergency stop.' output>>errors; cp * /tmp/");
}

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline=exec("cd ".$maw_tempdir."; cat errors"); 

$arithmetic_overflow=exec("cd ".$maw_tempdir."; grep 'Arithmetic overflow.' output"); 
$dimension_too_large=exec("cd ".$maw_tempdir."; grep 'Dimension too large' output"); 

$datcasip="funkce: $fcef,$fceg, meze:$meza..$mezb,  x:$xmin..$xmax,   y:$ymin..$ymax, ";
if ($akce=="2") 
  {$datcasip=$datcasip."objem";} 
 else 
  {$datcasip=$datcasip."obsah";}

if ($lastline!="") {
  maw_errmsgB("");
  if ($arithmetic_overflow!="") 
    {
      $datcasip=$datcasip." Artihmetic overflow";

      echo "<b class='red'>".__("Arithmetic overflow. Consider smaller bounds for the picture on the <i>x</i> axis or bigger on the <i>y</i> axis.")."</b> <br>"; 
    }
  elseif ($dimension_too_large!="") 
    {
      $datcasip=$datcasip." Dimension too large";
      echo "<b class='red'>".__("Error: Dimension too large.")."<br>".__("Consider reasonable bounds for axes, please.")."</b><br>";
    }
  echo "<b class='red'>".__("Check also that both functions are continuous on the interval used to evaluate the integral and to draw the picture.")."</b><pre>"; 
  system("cd ".$maw_tempdir.";grep -v '^$' output > output.tmp ; grep 'positive' output|tail >> output.txt; uniq -u output.tmp>> output.txt; cat output.txt|$mawcat");

  system ("rm -r ".$maw_tempdir);
  save_log_err($datcasip,"geom");
  die("</pre></body></html>");} 
else
{
  if ($requestedoutput=="html")
  {
    if ($mawISAjax==0) {maw_html_head();}
    require ("htmloutput.php");
    if ($mawISAjax==0) {echo('</body></html>');}
  }
  else
  {
  /* here we send PDF file into browser */
  send_PDF_file_to_browser("$maw_tempdir/geom.pdf");
  } 
/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

  system ("rm -r ".$maw_tempdir);
  save_log($datcasip,"geom");
}


?>



