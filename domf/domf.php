<?php
$scriptname = "domf";

require("../common/maw.php");

$functions = 'abs|asin|acos|atan|acot|sin|cos|tan|cot|log|exp|sqrt';
$parameters = ' ';


$fcef = $_REQUEST["funkcef"];
$akce = $_REQUEST["akce"];
$xs = $_REQUEST["xs"];
$ys = $_REQUEST["ys"];
$stacbody = $_REQUEST["stacbody"];
$df = $_REQUEST["df"];
$pocetprom = $_REQUEST["onevar"];
$xmin = $_REQUEST["xmin"];
$xmax = $_REQUEST["xmax"];

check_for_security($fcef . ", " . $stacbody . ", " . $xs . ", " . $ys);

if ($pocetprom == "1") {
	$variables = 'x';
}

$fcef = input_to_maxima($fcef);
$loginfo = "funkce: z=$fcef, definicni obor";

if ($akce == 5) {
	$maw_tempdir = "/tmp/MAW_domf" . getmypid() . "xx" . RandomName(6);
	system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);

	if ($_REQUEST["format"] == "png") {

		$vystup = `$mawtimeout $maxima2 --batch-string="display2d:false;load(\"$mawhome/domf/df.mac\"); osetrimocniny($fcef);"`;

		check_for_errors($vystup, $loginfo, "domf");

		$vystup = str_replace("\n", "", $vystup);

		if (preg_match("~uprfce(.*)konec~", $vystup)) {
			$pattern = '/uprfce(.*?)konec/';
			preg_match($pattern, $vystup, $upravenafunkce);
			$fcef = $upravenafunkce[1];
		}


		$funkcegnuplot = `echo "$fcef" | $formconv_bin -r -O gnuplot`;
		$funkcegnuplot = chop($funkcegnuplot);

		define("NAZEV_SOUBORU", $maw_tempdir . "/vstup");
		$soubor = fopen(NAZEV_SOUBORU, "w");

		if ($pocetprom == "1") {
			fwrite($soubor, "unset key\nunset ztics\nunset ytics\n set terminal png size 1000,70 transparent\n set output \"obrazek.png\"\n set isosample 500,50\n set xrange [$xmin:$xmax] \n set yrange [-1:1]\n set view 0,0\n splot $funkcegnuplot, 'a' with dots\n");
			system("cd $maw_tempdir; echo \"$xmin 0 0\">a; gnuplot vstup; convert -shave 160x5 obrazek.png obrazek.png");
		} else {
			fwrite($soubor, "unset key\nunset ztics\n set terminal png size 700,700 transparent\n set output \"obrazek.png\"\n set isosample 900\n set xrange [-$df:$df] \n set yrange [-$df:$df]\n set view 0,0\n splot $funkcegnuplot, 'a' with dots\n");
			system("cd $maw_tempdir; echo \"$xmin $ymin 0\">a; gnuplot vstup; convert -shave 120x120 obrazek.png obrazek.png");
		}
		fclose($soubor);

		$file = $maw_tempdir . "/obrazek.png";

		header("Content-Type: image/png");
		header("Content-Disposition: attachment; filename=" . basename($file) . ";");
		header("Content-Transfer-Encoding: binary");

		readfile($file);
		system("rm -r " . $maw_tempdir);
		save_log($loginfo, "domf");

		die();
	} else {
		maw_html_head();
		if ($pocetprom == "1") {
			$tempmsg = sprintf(__("Domain of %s"), "\$f(x)=" . formconv($fcef) . "\$");
		} else {
			$tempmsg = sprintf(__("Domain of %s"), "\$f(x,y)=" . formconv($fcef) . "\$");
		}
		echo "<h3>$tempmsg</h3>";

		echo($maw_processing_msg);
		ob_flush();
		flush();
		if (function_exists("maw_after_flush")) {
			echo(maw_after_flush());
		}

		function hide_message()
		{
			return ('<script>document.getElementById("processing").style.display = "none";</script>');
		}

		$vystup = `$mawtimeout -t 15 $maxima2 --batch-string="display2d:false;load(\"$mawhome/domf/df.mac\"); definicni_obor($fcef);"`;

		check_for_errors($vystup, $loginfo, "domf");

		if (!(stristr($vystup, "o3)"))) {
			echo __("Sorry, we are unable to process your function. The problem has been saved and we will investigate.");
			save_log_err($fcef, "unparsed");
			die();
		}

		$vystup = str_replace("\n", "", $vystup);

		if (preg_match("~zacatek(.*)konec~", $vystup)) {
			$pattern = '/zacatek(.*?)konec/';
			preg_match_all($pattern, $vystup, $podminky);


			$pocet = count($podminky[1]);
			echo '<b>', __("Conditions"), '</b>', __("(Not including sets with zero measure, such as points, curves and boundaries in the picture.)"), '<br><br><table>';
			echo '<tr><th><b>' . __("Condition") . '</b></th><th><b>' . __("Domain where the inequality is valid") . '</b> (' . __("in red") . ')</th></tr>';
			for ($ii = 0; $ii < $pocet; $ii++) {
				$mezikrok = explode("funkce", $podminky[1][$pocet - $ii - 1]);
				echo "<tr><td><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$" . formconv_replacements($mezikrok[0]) . "\$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></td><td>";
				if ($mezikrok[1] != " 0 ") {
					if ($pocetprom == "1") {
						echo '<img alt="' . __("Processing image") . ' ..." src="' . $mawphphome . '/domf/domf.php?onevar=1&amp;akce=5&amp;funkcef=', rawurlencode("$mezikrok[1]"), '&amp;xmin=', $xmin, '&amp;xmax=', $xmax, '&amp;format=png" width=700px><br>&nbsp;&nbsp;', __("Points on boundary"), ': &nbsp;&nbsp;$' . formconv_replacements($mezikrok[2]) . '$</td>';
					} else {
						echo '<img alt="' . __("Processing image") . ' ..." src="' . $mawphphome . '/domf/domf.php?akce=5&amp;funkcef=', rawurlencode("$mezikrok[1]"), '&amp;df=', $df, '&amp;format=png" height=250px>';
					}
				} else {
					echo '&nbsp;&nbsp;&nbsp;', __("skipping the picture ... (as explained hereinabove)");
					if ($pocetprom == "1") {
						echo '<br>&nbsp;&nbsp;', __("Points on the boundary"), ': &nbsp;&nbsp;$' . formconv_replacements($mezikrok[2]) . '$</td>';
					}
				}
				echo '</td></tr>';
			}
			if ($pocetprom == "1") {
				echo("\n");
				echo '<tr><td><b>' . __("Answer") . ':</b> <br>' . __("the intersection is red region") . '</td><td>
<img alt="' . __("Processing image") . ' ..." src="' . $mawphphome . '/domf/domf.php?onevar=1&amp;akce=5&amp;funkcef=', rawurlencode("$fcef"), '&amp;xmin=', $xmin, '&amp;xmax=', $xmax, '&amp;format=png" width=700px></td></tr>';
				echo("\n");
			}
			echo '</table><br><br>';


			if ($pocetprom != "1") {
				echo '<b>', __("Domain (red region)"), '</b> <br>';
				echo '<img alt="' . __("Processing image") . ' ..." src="' . $mawphphome . '/domf/domf.php?akce=5&amp;funkcef=', rawurlencode("$fcef"), '&amp;df=', $df, '&amp;format=png">';
			}
			if ($pocetprom == "1") {
				echo __("The boundary points for the subintervals on the picture are either points of discontinuity, or the points in which one of the partial inequalities becomes equality. In this version of MAW we are not able to find out, if these points belong to the domain or not. This problem is left to the user. Also ignore complex numbers, if present.");
			}
		} else {
			echo __("No restriction on the domain of the function found. The function seems to be defined everywhere.");
		}

		system("rm -r " . $maw_tempdir);
		save_log($loginfo, "domf");
		die(hide_message() . "</body></html>");
	}
}


?>



