<div class=inlinediv>
<div class=logickyBlok>

<?php

    $vars1=rawurlencode($vars);
    $parameters=" "; $variables=" ";
    foreach (array("a", "b", "xmin", "xmax", "ymin", "ymax") as $value)
      {
	$$value=$_REQUEST[$value];
	$$value=input_to_maxima($$value);
	$$value=rawurlencode($$value);
      }

    echo "<center>".__("Region for integration")."</center>";
    if ($vars=="dy dx")
      {
	$variables="x";
	foreach (array("c", "d") as $value)
	  {
	    $$value=$_REQUEST[$value];
	    $$value=input_to_maxima($$value);
	    $$value=rawurlencode($$value);
	  }
	
	$parameters="size=500x500&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
	echo ("<span class=\"red\"><img width=400 class=centeringimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"".__("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.")." ".__(" Submit the form to check, if the region for integration is well defined.")."\"></span>");
      }
    elseif ($vars=="dx dy")
      {
	$variables="y";
	foreach (array("c", "d") as $value)
	  {
	    $$value=$_REQUEST[$value];
	    $$value=input_to_maxima($$value);
	    $$value=rawurlencode($$value);
	  }

	$parameters="size=500x500&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
	echo ("<span class=\"red\"><img  width=400 class=centeringimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"".__("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.")." ".__(" Submit the form to check, if the region for integration is well defined.")."\"></span>");
      }
    elseif ($vars=="r dphi dr")
      {
	$variables="r";
	foreach (array("c", "d") as $value)
	  {
	    $$value=$_REQUEST[$value];
	    $$value=input_to_maxima($$value);
	    $$value=rawurlencode($$value);
	  }
      	$parameters="size=500x500&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
	echo ("<span class=\"red\"><img  width=400 class=centeringimg class=centeringimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"".__("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.")." ".__(" Submit the form to check, if the region for integration is well defined.")."\"></span>");
      }
    elseif ($vars=="r dr dphi")
      {
	$variables="phi";
	foreach (array("c", "d") as $value)
	  {
	    $$value=$_REQUEST[$value];
	    $$value=input_to_maxima($$value);
	    $$value=rawurlencode($$value);
	  }
      	$parameters="size=500x500&a=$a&b=$b&xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&f=$c&g=$d&vars=$vars1";
	echo ("<span class=\"red\"><img  width=400 class=centeringimg src=\"../../maw/gnuplot/gnuplot_region.php?$parameters\" alt=\"".__("Processing the picture. If the picture does not appear within few seconds, you may have error in your math expressions.")." ".__(" Submit the form to check, if the region for integration is well defined.")."\"></span>");
      }
    else
      {
	echo __("Under construction"); 
      }
  


?>
</div></div>