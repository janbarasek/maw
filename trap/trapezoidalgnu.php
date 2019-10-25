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


$scriptname="trapezoidalgnu";
require ("../common/maw.php");


$fce=$_REQUEST["funkce"];
$allpoints=$_REQUEST["allpoints"];
$a=$_REQUEST["a"];
$b=$_REQUEST["b"];
$n=$_REQUEST["n"];
$ymin=$_REQUEST["ymin"];
$ymax=$_REQUEST["ymax"];

$maw_tempdir="/tmp/MAW_trapezoidal".getmypid()."xx".RandomName(6);
system ("mkdir ".$maw_tempdir."; chmod oug+rwx ".$maw_tempdir);

$temp=str_replace (";","\n",$allpoints);
$temp=str_replace (","," ",$temp);
system ("cd $maw_tempdir; echo \"$temp\" > data; ");

function math_to_GNUplot($vyraz)
{
  global $logbasegnuplot, $formconv_bin;
  $uprfunkceGNU=`echo "$vyraz" | $formconv_bin -r -O gnuplot`;
  $uprfunkceGNU=chop($uprfunkceGNU);	
  //$uprfunkceGNU=str_replace("log(", "mylog($logbasegnuplot,", $uprfunkceGNU);
  $uprfunkceGNU=str_replace("sqrt","mysqrt",$uprfunkceGNU);
  return($uprfunkceGNU);
}

$funkce=math_to_GNUplot($fce); 

define ("NAZEV_SOUBORU_OBR", $maw_tempdir."/vstup");
$souborobr=fopen(NAZEV_SOUBORU_OBR, "w");
  
fwrite($souborobr,"mylog(a,b)=(b>0)? log(b)/log(a):0/0\n");
fwrite($souborobr,"mysqrt(x)=(x>=0)? sqrt(x):0/0 \n");
$points=split(";",$allpoints);
array_shift ($points);
foreach ($points as $onepoint)
{
  $coords=split(",",$onepoint);
  fwrite ($souborobr, "set arrow from $coords[0],0 to $coords[0],$coords[1] nohead\n");
}
fwrite($souborobr,"set zeroaxis lt -1 \nunset key\n");
fwrite($souborobr,"set xtics axis nomirror \n");
fwrite($souborobr,"set ytics axis nomirror \n");
fwrite($souborobr,"set samples 1000 \n");
fwrite($souborobr,"set term svg font 'Verdana,9' rounded solid\n");
fwrite($souborobr,'set output "graf.svg"'."\n");
//fwrite($souborobr,"set term png transparent \n");
//fwrite($souborobr,'set output "graf.png"'."\n");
fwrite($souborobr,'unset key'."\n");
fwrite($souborobr,'set style fill pattern 4 bo'."\n");
fwrite($souborobr,"set xrange [".math_to_GNUplot($a).":".math_to_GNUplot($b)."]\n");
fwrite($souborobr,"set yrange [".$ymin.":".$ymax."]\n");
if ($dummy!="") {fwrite($souborobr,"set dummy ".$dummy."\n");}
if ($xlabel!="") {fwrite($souborobr,"set xlabel \" ".$xlabel."\"\n");}
if ($ylabel!="") {fwrite($souborobr,"set ylabel \" ".$ylabel."\"\n");}
fwrite($souborobr,"set style function lines\n");
//  $funkcegnuplot=`$mawtimeout echo "$funkce" | $formconv_bin -r -O gnuplot`;
//  $funkcegnuplot=chop($funkcegnuplot);	
fwrite($souborobr,"plot ".$funkce." with lines linewidth 3, \"data\" with filledcurves x1 rgb \"blue\"\n");
fclose($souborobr); 

system ("cd $maw_tempdir;  gnuplot vstup; cp * /tmp");

//$file=$maw_tempdir."/graf.png"; 
//header("Content-Type: image/png");
//header("Content-Disposition: attachment; filename=".basename($file).";" );
//header("Content-Transfer-Encoding: binary");
//readfile($file);
//die();

$file=$maw_tempdir."/graf.svg"; 
header('Content-Type: image/svg+xml');
header("Content-Disposition: attachment; filename=".basename($file).";" );
readfile($file);

system ("rm -r ".$maw_tempdir);



?>