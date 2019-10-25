<?php
require("../common/maw.php");
$maw_tempdir=$_REQUEST["dir"];
$maw_filtr=$_REQUEST["filtr"];
maw_html_head();
echo ("Lines: ");
system("/usr/bin/wc -l ../common/log/$maw_tempdir.log");
echo ("<br>"."Last requests:"."<br>");
echo ("<hr>");
if ($maw_tempdir=="integral")
{
  if ($maw_filtr=="") 
    {
      system ("tail -n 200 $maw_filtr ../common/log/$maw_tempdir.log|tac");
    }
  else
    {
      system ("grep $maw_filtr ../common/log/$maw_tempdir.log|tac");
    }
}
elseif ($maw_tempdir=="access")
{
system ("tail -n 500 ../common/log/$maw_tempdir.log|tac");  
}
elseif ($maw_tempdir=="ps")
{
system ("cat ../common/log/$maw_tempdir.log");  
}
else
{
system ("tail -n 100 ../common/log/$maw_tempdir.log|tac");  
}
echo("<body>");
?>
