<?php
$scriptname = "integral";
require("../common/maw.php");
require("../common/redirect.php");

list($fnc, $opts) = split(";", $_SERVER['QUERY_STRING']);
$fnc = rawurldecode($fnc);
if (ereg("lang=en", $opts)) {
	$lang = "en";
}

save_log($fnc . " | " . $_SERVER[HTTP_REFERER], "url2int");
redirect($mawphphome . "/integral/integral.php?formconv=on&prom=z&funkce=" . rawurlencode($fnc) . "&" . $opts);
?>
