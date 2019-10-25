<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
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


$scriptname="definite";
require ("../common/maw.php");

$fce=$_REQUEST["funkce"];
$a=$_REQUEST["a"];
$b=$_REQUEST["b"];


check_for_security("$fce, $a, $b");

check_for_y($fce);
$variables='x';
$parameters=' ';

$funkce=input_to_maxima($fce,__("function"));
$fce=$funkce;

$variables=' ';
$a=input_to_maxima($a,__("lower limit of integration"));
$b=input_to_maxima($b,__("upper limit of integration"));


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
fclose($soubor); 

if (ereg("sin|cos|tan|cot|asin|acos|atan",$funkce))
  {
     $opttrigsimp="opttrigsimp(f):=trigsimp(f)";
  }
else
  {
     $opttrigsimp="opttrigsimp(f):=f";
  }

$varint="variable:x\$ "; 

$logarcswitch=$_REQUEST["logarc"];

if ($logarcswitch=="on") 
  {
    $logarc="load(\\\"$mawhome/integral/simpinvtrigh.mac\\\")\$";
  }
 else
   {
    $logarc="";
   }


$command="display2d:false\$ f:$funkce\$ $opttrigsimp\$ $varint a:$a\$ b:$b\$ $logarc load(\\\"$mawhome/definite/definite.mac\\\")\$ ";

$output=`$mawtimeout $maxima --batch-string="$command"`;

function najdiretezec($klicoveslovo,$retezec,$key="###")
{
  $retezec=str_replace("\n","",$retezec);
  preg_match("/\#\#\# *".$klicoveslovo." (.*?)(\#\#\#)/",$retezec,$matches);
  $vystup=$matches[0];
  $vystup=str_replace("### ".$klicoveslovo, "", $vystup);
  $vystup=str_replace("###", "", $vystup);
  return ($vystup);
}

function remove_dollars($string) {return(str_replace("$$","",$string));}
function removepercent($string) {return(str_replace("%","",$string));}
$toutput=str_replace("\n","",$output);


if ($mawISAjax==0) {maw_html_head();}

$outputKeys=Array("dolnimez", "hornimez", "funkce", "primitiveFunction", "primitiveAtB", "primitiveAtA", "substitutingLimits", "definiteintegral", "length", "meanValue", "floatresult", "floatmean","meansgn");
foreach ($outputKeys as $value)
{
$$value=remove_dollars(najdiretezec($value,$toutput));
//printf("<br>$value: \$%s \$",$$value);
}

echo (str_replace("\\log","\\ln",sprintf ("<h3>%s</h4><div class=inlinediv><div class=logickyBlok>\\begin{align} \\int_{%s}^{%s}%s \\,\\mathrm{d}x&= \\left[%s\\right]_{%s}^{%s}\\\\ &=%s \\\\&=%s\\\\& \\approx %s \\end{align} <a target=\"_blank\" href=\"$mawphphome/integral/integralx.php?$fce;lang=$lang\">%s</a></div></div>",
__("Definite integral"),$dolnimez, $hornimez, $funkce, $primitiveFunction, $dolnimez, $hornimez, $substitutingLimits, $definiteintegral,$floatresult,__("Help to find the primitive function"))));

if ($meanValue!="") 
{
 printf (" <div class=inlinediv><div class=logickyBlok><h4>%s</h4>\\begin{align} \mu&=\\frac{1}{b-a}\\int_{a}^{b}f(x)\,\\mathrm{d}x\\\\&=\\frac{%s}{%s}\\\\&=%s\\\\&\\approx %s \\end{align}</div></div>",__("Mean value"),$definiteintegral, $length, $meanValue, $floatmean);
 if ($meansgn==" 1 ") 
 {
   printf (" <div class=inlinediv><div class=logickyBlok><h4>%s</h4><img src=\"$mawphphome/definite/definiteimg.php?&funkce=%s&a=%s&b=%s&mean=%s\"></div></div>",__("Graph of the function and mean value"),rawurlencode($fce),rawurlencode($a),rawurlencode($b),rawurlencode($floatmean));
 }
}


if ($mawISAjax==0) {echo('</body></html>');}

$datcasip="function: $fce, interval:[$a , $b]";
save_log($datcasip,"definite");
system ("rm -r ".$maw_tempdir);

?>



