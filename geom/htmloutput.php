<?

$problem = "";
$intfailed = "";
function AreaA($a, $b, $c)
{
	global $problem;
	$problem = str_replace(["#1", "#2", "#3"], [$a, $b, $c], __("We find area under the curve #1 on the interval from #2 to #3."));
}

function AreaB($a, $b, $c, $d)
{
	global $problem;
	$problem = str_replace(["#1", "#2", "#3", "#4"], [$a, $b, $c, $d], __("We find area between curves #1 and #2 on the interval from #3 to #4."));
}

function VolumeA($a, $b, $c)
{
	global $problem;
	$problem = str_replace(["#1", "#2", "#3"], [$a, $b, $c], __("We find volume of solid of revolution formed by revolving area under the curve #1 on the interval from #2 to #3 around $ x$ axis."));
}

function VolumeB($a, $b, $c, $d)
{
	global $problem;
	$problem = str_replace(["#1", "#2", "#3", "#4"], [$a, $b, $c, $d], __("We find volume of solid of revolution formed by revolving area between curves #1 and #2 on the interval from #3 to #4 around $ x$ axis.."));
}

require("$maw_tempdir/data.php");


printf("<div class=logickyBlok>%s</div>", $problem);


printf("<div class=inlinediv><div class=logickyBlok>%s</div>", $computation);

if ($intfailed != "") {
	printf("<div class=logickyBlok>%s</div>", __("Maxima failed to find the primitive function."));
}

?>
</div>
<div class=inlinediv>
	<div class=logickyBlok>
<?php
$parameters="a=$meza&b=$mezb&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=".rawurlencode("$fcef")."&g=".rawurlencode("$fceg");
echo ("".__("Picture").":"."<center><img alt=\"Loading ...\" src=\"$mawphphome/gnuplot/gnuplot_region.php?$parameters\"></center>");

?>
</div>
</div>