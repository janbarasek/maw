<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2012 Robert Marik

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

$scriptname = "gnuplot_region";

require("../common/maw.php");

$f = formconv_gnuplot(rawurldecode($_REQUEST["f"]));
$g = formconv_gnuplot(rawurldecode($_REQUEST["g"]));
$a = formconv_gnuplot(rawurldecode($_REQUEST["a"]));
$b = formconv_gnuplot(rawurldecode($_REQUEST["b"]));
$xmin = formconv_gnuplot("(" . rawurldecode($_REQUEST["xmin"]) . ")");
$xmax = formconv_gnuplot("(" . rawurldecode($_REQUEST["xmax"]) . ")");
$ymin = formconv_gnuplot("(" . rawurldecode($_REQUEST["ymin"]) . ")");
$ymax = formconv_gnuplot("(" . rawurldecode($_REQUEST["ymax"]) . ")");
$vars = rawurldecode($_REQUEST["vars"]);
$size = rawurldecode($_REQUEST["size"]);

if ($vars == "dx dy") {
	$swap = 1;
} else {
	$swap = "";
}
if ($vars == "r dr dphi") {
	$polar = 1;
} else {
	$polar = "";
}
if ($vars == "r dphi dr") {
	$polar = 1;
	$swap = 1;
}

check_for_security($f . $g . $xmin . $xmax . $ymin . $ymax . $a . $b);

/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/

$maw_tempdir = "/tmp/MAW_gnuplot_region" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

$file = $maw_tempdir . "/a.svg";
if ($polar == "") {
	system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_region.bash \"$f\" \"$g\" \"$a\" \"$b\" \"$xmin\" \"$xmax\" \"$ymin\" \"$ymax\" \"$swap\"");
} else {
	system("cd $maw_tempdir; $bash $mawhome/gnuplot/gnuplot_region_polar.bash \"$f\" \"$g\" \"$a\" \"$b\" \"$xmin\" \"$xmax\" \"$ymin\" \"$ymax\" \"$swap\"");
}

//header("Content-Type: image/png");
//header("Content-Disposition: attachment; filename=".basename($file).";" );
//header("Content-Transfer-Encoding: binary");
header("Content-Type: image/svg+xml");
header("Content-Disposition: attachment; filename=" . basename($file) . ";");
readfile($file);

$datcasip = "funkce: $f, $g, $a, $b";

system("rm -r " . $maw_tempdir);
save_log($datcasip, "gnuplot_region");

?>



