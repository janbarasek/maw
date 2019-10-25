<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
Copyright 2008-2012 Robert Marik

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

require ("../common/maw.php");

$x=rawurldecode($_REQUEST["x"]);
$y=rawurldecode($_REQUEST["y"]);
$z=rawurldecode($_REQUEST["z"]);
$tmin=rawurldecode($_REQUEST["tmin"]);
$tmax=rawurldecode($_REQUEST["tmax"]);
$svg=$_REQUEST["svg"];

if ($svg=="1")
  {
    $maw_tempdir="/tmp/MAW_curve_preview".getmypid()."xx".RandomName(6);
    system("mkdir ".$maw_tempdir);
    $x=formconv_gnuplot($x);
    $y=formconv_gnuplot($y);
    $z=formconv_gnuplot($z);
    $tmin=formconv_gnuplot($tmin);
    $tmax=formconv_gnuplot($tmax);
    $z=str_replace(" ","",$z);
    if ( ($z=="") || ($z=="0") )
      {
    	$command="$bash $mawhome/gnuplot/gnuplot_parametric_2D.bash \"$x\" \"$y\" \"$tmin\" \"$tmax\" ";
      }
    else
      {
    	$command="$bash $mawhome/gnuplot/gnuplot_parametric_3D.bash \"$x\" \"$y\" \"$z\" \"$tmin\" \"$tmax\" ";
      }

    system ("cd $maw_tempdir; $command");
    $file=$maw_tempdir."/a.svg";
    
    header("Content-Type: image/svg+xml");
    header("Content-Disposition: attachment; filename=".basename($file).";" );
    //header("Content-Transfer-Encoding: binary");
    
    readfile($file);
    system ("rm -r ".$maw_tempdir);
    die();
}

echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html><head>\n <meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\">\n  <link rel=\"stylesheet\" type=\"text/css\" href=\"../common/styl.css\" >\n");
echo("<title>".__("Mathematical Assistant on Web")."</title>");

if (file_exists('../common/custom.css')) 
  {
    echo ("<link rel=\"stylesheet\" type=\"text/css\" href=\"../common/custom.css\" >");
  }

$mawhead_used=1;
echo  $maw_html_custom_head;
echo("</head>\n<body>\n");

echo sprintf("<h3>%s</h3>",__("Parametric curve"));

$variables="t"; $parameters=" ";
$x=input_to_maxima($x);
$y=input_to_maxima($y);
$z=str_replace(" ","",$z);
if ($z!="") {$z=input_to_maxima($z);}

$variables=" "; $parameters=" ";
$tmin=input_to_maxima($tmin);
$tmax=input_to_maxima($tmax);

$datcasip="krivka: $x, $y, $z; meze:$tmin..$tmax";

$curve_eqs=sprintf("x(t)=%s\\\\y(t)=%s",formconv($x),formconv($y));
if ($z!="") {$curve_eqs=$curve_eqs."\\\\z(t)=".formconv($z);}

//echo put_tex_to_html(sprintf("C\equiv \\begin{cases}%s\\end{cases}",$curve_eqs));
//echo "&nbsp;&nbsp;&nbsp;&nbsp; ".put_tex_to_html(sprintf("t\\in \\left(%s,%s\\right)",formconv($tmin),formconv($tmax)));
$x=rawurlencode($x);
$y=rawurlencode($y);
$z=rawurlencode($z);
$tmin=rawurlencode($tmin);
$tmax=rawurlencode($tmax);
echo ("<span class=\"red\"><img src=\"../../maw/gnuplot/curve.php?svg=1&x=$x&y=$y&z=$z&tmin=$tmin&tmax=$tmax\" alt=\"".__("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.")." ".__(" Submit the form to check, if the curve is well defined.")."\"></span>");

echo '<br><input class="zavrit" type="button" value="',__("Close").'" onclick="window.close()">';

save_log($datcasip,"curve");

?>

</body>