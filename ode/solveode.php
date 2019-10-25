<?php
$scriptname = "solveode";
require("../common/maw.php");
require("../common/redirect.php");

list($fnc, $opts) = explode(";", $_SERVER['QUERY_STRING']);
$fnc = rawurldecode($fnc);

save_log($fnc . " | " . $_SERVER[HTTP_REFERER], "ode2ode");
if (stristr($fnc, "'")) {
	redirect($mawphphome . "/ode/ode.php?ode2=" . rawurlencode($fnc) . "&lang=" . $opts . "&akce=1");
} else {
	redirect($mawphphome . "/ode/ode.php?ode=" . rawurlencode($fnc) . "&lang=" . $opts);
}

?>
