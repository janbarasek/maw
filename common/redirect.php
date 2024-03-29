<?php


function redirect($to, $code = 301)
{
	$location = null;
	$sn = $_SERVER['SCRIPT_NAME'];
	$cp = dirname($sn);
	if (substr($to, 0, 4) == 'http')
		$location = $to; // Absolute URL
	else {
		$schema = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
		$host = strlen($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		if (substr($to, 0, 1) == '/')
			$location = "$schema://$host$to";
		elseif (substr($to, 0, 1) == '.') // Relative Path
		{
			$location = "$schema://$host/";
			$pu = parse_url($to);
			$cd = dirname($_SERVER['SCRIPT_FILENAME']) . '/';
			$np = realpath($cd . $pu['path']);
			$np = str_replace($_SERVER['DOCUMENT_ROOT'], '', $np);
			$location .= $np;
			if ((isset($pu['query'])) && (strlen($pu['query']) > 0))
				$location .= '?' . $pu['query'];
		}
	}

	$hs = headers_sent();
	if ($hs == false) {
		if ($code == 301)
			header("301 Moved Permanently HTTP/1.1"); // Convert to GET
		elseif ($code == 302)
			header("302 Found HTTP/1.1"); // Conform re-POST
		elseif ($code == 303)
			header("303 See Other HTTP/1.1"); // dont cache, always use GET
		elseif ($code == 304)
			header("304 Not Modified HTTP/1.1"); // use cache
		elseif ($code == 305)
			header("305 Use Proxy HTTP/1.1");
		elseif ($code == 306)
			header("306 Not Used HTTP/1.1");
		elseif ($code == 307)
			header("307 Temorary Redirect HTTP/1.1");
		else trigger_error("Unhandled redirect() HTTP Code: $code", E_USER_ERROR);
		header("Location: $location");
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	} elseif (($hs == true) || ($code == 302) || ($code == 303)) {

		$cover_div_style = 'background-color: #ccc; height: 100%; left: 0px; position: absolute; top: 0px; width: 100%;';
		echo "<div style='$cover_div_style'>\n";
		$link_div_style = 'background-color: #fff; border: 2px solid #f00; left: 0px; margin: 5px; padding: 3px; ';
		$link_div_style .= 'position: absolute; text-align: center; top: 0px; width: 95%; z-index: 99;';
		echo "<div style='$link_div_style'>\n";
		echo "<p>Please See: <a href='$to'>" . htmlspecialchars($location) . "</a></p>\n";
		echo "</div>\n</div>\n";
	}
	exit(0);
}


?>