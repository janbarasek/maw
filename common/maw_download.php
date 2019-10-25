<?php

$filename = $_REQUEST['filename'];
$file = $_REQUEST['file'];

$fullfile = "/tmp/maw-dev-cache/" . $filename;


//echo (basename($file));
//echo (filesize($fullfile));
//die();

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=" . $file . ";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($fullfile));

readfile($fullfile);


?>