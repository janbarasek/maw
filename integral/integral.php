<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2008 Robert Marik, Miroslava Tihlarikova
Copyright 2009-2012 Robert Marik

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

$scriptname="integral";

require ("../common/maw.php");

$onsubmit=" onSubmit=\"document.getElementById('form').style.display='none';document.getElementById('after-form').style.display='block';\" ";

function maw_html_headB()
{
  global $jsmath, $lang, $mawhead_used, $maw_html_custom_head, $maw_html_custom_body, $mawhtmlhome, $oriprom, $orifce;
if ($mawhead_used==1) { return ; }
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 <title>Mathematical Assistant on Web (integral) </title>
 <meta content="text/html; charset=UTF-8" http-equiv="content-type">
 <link rel="stylesheet" type="text/css" href="../common/styl.css" >';

if (file_exists('../common/custom.css')) 
{
  echo ("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../common/custom.css\" >");
}

echo'<script type="text/javascript" src="../../overlibmws/overlibmws.js"></script>';

echo $maw_html_custom_head;

echo '
</head>
   <body alink="#ee0000" link="#0000ee" vlink="#551a8b">
   <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div> '.$maw_html_custom_body.'
   <div class="support">';
if ("http://sourceforge.net/projects/mathassistant/forums/forum/796831"!=__("http://sourceforge.net/projects/mathassistant/forums/forum/796831"))
  { echo '
<a href="'.__("http://sourceforge.net/projects/mathassistant/forums/forum/796831").'">'.__("Support request").'</a><br>';
  }
echo '
<a href="http://sourceforge.net/tracker/?group_id=221048&amp;atid=1052162">'.__("Report bug").'</a><br>';
$backlink="";
if ($oriprom=="x") {$backlink=rawurlencode($orifce);}
echo "<a href=\"$mawhtmlhome/index.php?form=integral&lang=$lang&function=$backlink\">".__("Go home")."</a>";
echo "\n </div>";
if ($jsmath=="on")    {echo '<SCRIPT type="text/javascript" SRC="../../jsMath/jsMath.js"></SCRIPT>';}
echo '  <noscript> 
        <span class="red bold">'.__("You should turn JavaScript on to see popup informations.").'</span>  
        </noscript>
        ';
$mawhead_used=1;
}

function maw_integral_error()
{
   echo '<script type="text/javascript"> document.getElementById("gray_comment").style.display = "none"; </script> ';	
}

function maw_after_form() {
  //echo "<div id='after-form' style='display:none;'>Your input is being processed. Wait few seconds to see the output.</div>";
  echo "<div id='after-form' style='display:none;'>".sprintf(__("Your input is being processed. Wait few seconds to see the output. Click %shere%s to reopen the form which has been submited."),"<a href=\"#\" onclick=\"document.getElementById('after-form').style.display='none';document.getElementById('form').style.display='block';\">","</a>")."</div>";

}



$vystupmaximy="";
$all_hints2="";
//$parameters=" ";

$mimetex=$texrender;


$fce=$_REQUEST["funkce"];// function
$akce=$_REQUEST["akce"]; // action (method) used for the next step
$krok=$_REQUEST["krok"]; // the number of the steps in computation
$prom=$_REQUEST["prom"]; // variable used for integration
$secvar=$_REQUEST["secvar"]; // konstant (second variable in double integral)
$oriprom=$_REQUEST["oriprom"]; // variable used for integration
$oriproblem=$_REQUEST["oriproblem"]; // variable used for integration
$allprom=$_REQUEST["allprom"]; // all variables used until now
$u=$_REQUEST["u"]; // int. by parts
$v=$_REQUEST["v"]; // int. by parts
$substituce=$_REQUEST["substituce"]; // substitution
$novapromenna=$_REQUEST["novapromenna"]; // new variable after substitution
$kladna=$_REQUEST["kladna"]; // should we assume that new variable is positive?
$maw_tempdir=$_REQUEST["adresar"]; // temporary directory
$formconv=$_REQUEST["formconv"]; // use formconv to parse the formula?
$jsmath=$_REQUEST["jsmath"]; // use jsmath to diplay mathematics?
$vsechno=$_REQUEST["vsechno"]; // the result with integral replaced by I
$cislo="(".$_REQUEST["cislo"].")"; // number
$post=$_REQUEST["post"]; // post or get?
$postakce=$_REQUEST["postakce"]; // final modifications (after integration)
$pfeformatswitch=$_REQUEST["pfeformat"];
$logarcswitch=$_REQUEST["logarc"];
$novapromennahint=$_REQUEST["novapromennahint"];
$substitucehint=$_REQUEST["substitucehint"];
$backsubst=$_REQUEST["backsubst"];
$rozsirit=$_REQUEST["rozsirit"];
$rozsiritradcan=$_REQUEST["rozsiritradcan"];
$limit_a=$_REQUEST["limit_a"];
$limit_b=$_REQUEST["limit_b"];
$defint=$_REQUEST["defint"];
$tlacitko=$_REQUEST["tlacitko"];

if (ereg("^ *integrate\(",$fce)) 
{
  $dummy_fce=str_replace("integrate(","",$fce);
  list($fce,$dummy_variable)=split(',',$dummy_fce);
}

function savekey($string)
{
  global $maw_tempdir,$krok;
  return(" | <b>$krok</b> | <a href=\"../common/tail.php?dir=integral&amp;filtr=$maw_tempdir\">filtr</a>");
}

$maxima_total_runtime=0;
$maxima_time_last_call=0;
$maxima_total_calls=0;
$maxima_total_cached=0;
if (ereg("PDF",$tlacitko)) 
{
  $str="";
  for ($i=1;$i<=$krok-1;$i++)
    {
      $str=$str.file_get_contents("$maw_tempdir/".$i.".html");
    }
  $str=str_replace("<br>","\\par ",$str);
  $str=str_replace("<hr>","\\par\\medskip\\hrule \\medskip\n",$str);
  $str=str_replace("<img","IMG",$str);
  $str=str_replace("alt=\"math formula\">","GMI",$str);
  $str=preg_replace("/src=(.*?)\?/","",$str);
  $str=preg_replace("/\" *?style.*?GMI/","GMI",$str);
  $str=str_replace("IMG I=","$ I=",$str);
  $str=str_replace("IMG","$",$str);
  $str=str_replace("GMI","$",$str);
  $str=str_replace("<span class=\"math\">","$",$str);
  $str=str_replace("</span>","$",$str);
  $str=str_replace("<b>","\\textbf{",$str);
  $str=str_replace("</b>","}",$str);
  $str=preg_replace("/(Krok|Step)[0-9]*(.*)I/","$2 I",$str);
  $str=preg_replace("/(Krok|Step)[0-9]*/"," ",$str);
  if ($jsmath!="on") {$str=str_replace("\\fbox","\\boxed",$str);}
  $str=str_replace("&nbsp;","",$str);
//  echo '<pre>'.$str; die();

  $soubor=fopen("$maw_tempdir/integral.tex","w");
  $TeXfile=$TeXheader.'\newif\ifjeden
\usepackage{fancybox}
\fboxsep 0 pt

\def\init{\footnotesize\qquad}

\newcount\uprcount
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{\large '.__("Integral").'}

\rightskip 0 pt plus 1 fill\fboxsep=5pt'.$str.'
\end{document}';

  fwrite($soubor,$TeXfile);
  fclose($soubor);

  system ("cd $maw_tempdir; cat int.tex>>output; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex integral.tex>>output; $catchmawerrors");
  $lastline=exec("cd $maw_tempdir; cat errors"); 

  function make_filename_from_math_expression($expr)
  {
    $s=array("**","*","/","(",")");
    $r=array("^","×","÷","[","]");
    return(str_replace($s,$r,$expr)."-".RandomName(15));
  }
  if ($lastline!="") 
    {
      maw_errmsgB("<pre>");
      system("cd ".$maw_tempdir."; cat integral.tex; cat output");
      save_log_err("$krok steps ".savekey($maw_tempdir),"integral-PDF");
    }
  else
    {
      send_PDF_file_to_browser("$maw_tempdir/integral.pdf");
      save_log("$krok steps ".savekey($maw_tempdir),"integral-PDF");
    }
  system ("rm $maw_tempdir/integral.*; rm $maw_tempdir/errors; rm $maw_tempdir/output");
  die ("");
}

if (eregi("html",$tlacitko)) 
{
  $str="$oriproblem\n";
  $str=$str."\int ".chop(formconv($oriproblem))."\;\mathrm{d}$oriprom \n";
  for ($i=1;$i<=$krok-1;$i++)
    {
      $str=$str."\n".file_get_contents("$maw_tempdir/".$i.".html");
    }
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Type: application/html");
header("Content-Disposition: attachment; filename=soubor.html;");
header("Content-Transfer-Encoding: binary");
echo str_replace($texrender,"texrender?",$str);
 die();
}


if ($substitucehint!="") 
{
  $tt=split("BACK",$substitucehint);
  $substituce=$tt[0]; 
  $substituce_back=$tt[1];
  $substituce_back_orig=$substituce_back;
  $tt1=split("=",str_replace(" ","",$substituce)); 
  $tt2=split("=",str_replace(" ","",$substituce_back)); 
  if (($tt1[0]==$tt2[1]) && ($tt1[1]==$tt2[0])) {$substituce_back="";} 
  $novapromenna=$novapromennahint;
  $substitucehint="";
  $novapromennahint="";
}

// variables
if ($prom=="") {$prom="x";}
if ($allprom=="") {$allprom="xyzab".$prom;}

if (($akce=="s") or ($postakce=="zpetna_substituce"))
  {
    $variables=$prom.'|'.$novapromenna;
  }
  else
  {
    $variables=$prom;
  }
check_for_security($fce.$cislo.$u.$v.$novapromenna.$rozsirit);

$maximainit="load(functs), load(linearalgebra),";

if ($secvar=="x") 
  {
    $parameters='a|b|x';
    $maximainit=$maximainit."load(trgsmp), declare(x,constant),";
  } 
elseif ($secvar=="y") 
  {
    $parameters='a|b|y';
    $maximainit=$maximainit."declare(y,constant),";
  }

// take a number from the integral
if ($akce=="v")
  {
    $variables=' ';
    input_to_maxima($cislo);
  }

if ($pfeformatswitch=="on") 
  {
    $pfeformat="true";
  }
 else
   {
    $pfeformat="false";
   }

if (($logarcswitch=="on") && (preg_match('~asinh|acosh|atanh|acoth|asech|acsch~',$fce)))
//if ($logarcswitch=="on")
  {
    $logarc="load(\\\"$mawhome/integral/simpinvtrigh.mac\\\"),";
  }
 else
   {
    $logarc="";
   }

// method for the forms
//$metoda="get";
//if ($post=="on") {$metoda="post";}
$metoda="post";

$operace="";

if ($oriproblem=="") {$orifce=$fce;} else {$orifce=$oriproblem;}
$orifce=beautify_parentheses($orifce);

if ($vsechno=="") {$vsechno="I";}


if (($akce=="pp") and (($u=="") and ($v=="")))
{
  maw_html_head();
  maw_integral_error();
  save_log_err("Funkce: $fce, per partes bez zadani funkci".savekey($maw_tempdir),"integral");
  echo sprintf("\n<div class=\"h3_error\">%s</div>",__("Error: incorrect operation"));  
  echo sprintf(__("You have to specify %s or %s for integration by parts."),"<i>u</i>","<i>v'</i>");
  die("</html>");  
 }



$fce=str_replace("tg", "tan", $fce);   
$fce=str_replace("cotan", "cot", $fce);
$fce=str_replace("[", "(", $fce);
$fce=str_replace("]", ")", $fce);


$fbox="\\fbox";
$fboxb="\\fbox{";
$fboxe="}";
if ($jsmath=="on") {
$fboxb="\\fbox{\$\\displaystyle ";
$fboxe="\$}";
} 

$TeXstrings=Array();

function print_time_and_computations()
{
  global $vystupmaximy, $maxima_total_runtime, $maxima_total_runtime_hint, $maxima_total_calls, $maxima_total_cached, $all_hints2, $TeXstrings;
  echo ("<br>"."Computing time: ".maw_computing_time());
  echo ("<br>"."Maxima time: $maxima_total_runtime s in $maxima_total_calls Maxima calls ($maxima_total_cached computations from cache). ");
  
  echo '<hr><div id="unfold_link"><h4>','<a href="javascript:void(0)" onclick=\'document.getElementById("maxima_session_transcription").style.display = "block";document.getElementById("unfold_link").style.display = "none";\'>Maxima sessions (click to unfold)</a>','</h4></div><pre>';
  echo '<div id="maxima_session_transcription"><small>';
  if ((substr_count($vystupmaximy.$all_hints2,"\n")<500))
    {
      maxima_session_comment(" === looking for hints === ");
      echo $vystupmaximy;
      echo "\n<b>Time $maxima_total_runtime_hint</b>",$all_hints2,"</pre>";
    }
  else
    {
      echo 'The transcript of Maxima session is too long';
    }
  //print_r($TeXstrings);
  echo '</small></div><br><br><br><br>';
  echo '<script type="text/javascript"> document.getElementById("maxima_session_transcription").style.display = "none"; </script> ';	
}

function sanitize_maxima_output($inputstring)
{
  return ($inputstring);
}

function sanitize_maxima_output_before_write($inputstring)
{
  $inputstring=str_replace("\\","\\\\", $inputstring);
  $inputstring=str_replace("\$","\\\$", $inputstring);
  return ($inputstring);
}

function sanitize_maxima_input_before_write($inputstring)
{
  $inputstring=str_replace("\\","\\\\", $inputstring);
  $inputstring=str_replace("\$","\\\$", $inputstring);
  return ($inputstring);
}

function do_maxima($maxima_string)
{
  global $mawhome,$maxima_total_runtime,$maxima_total_calls,$maxima_total_cached,$maxima_start,$maxima_end,$maw_allow_cache,$maw_cache_directory,$maxima_time_last_call;
  $maxima_start=getmicrotime();
  $do_computation=true;
  $fingerprint=md5($maxima_string);
  if ( ($maw_allow_cache) && (file_exists($maw_cache_directory.$fingerprint.".php")) )
  {       
        require($maw_cache_directory.$fingerprint.".php");
	if ($maxima_result_input==$maxima_string)
	{
	   $do_computation=false;
           touch($maw_cache_directory.$fingerprint.".php");
	   $maxima_total_cached++;
           $handle = fopen("../common/log/md5_used.log","a");
           fwrite ($handle, $fingerprint."\n");
	   fclose($handle);
        }	   
	else
	{
           $handle = fopen("../common/log/errors_md5_used.log","a");
           fwrite ($handle, "------\n".$fingerprint."\n $maxima_result_input \n $maxima_string");
	   fclose($handle);
	   echo ("<div class=\"h3_error\">".__("MAW error: equal md5sum for different strings.")."</div>");
	   die(__("This error should be fixed. You may report this error to the authors.").hide_message());
	}
  }
  if ($do_computation)
  {
        $maxima_total_calls++;
        exec($maxima_string,$maxima_result_array,$retvalue);
	$maxima_result=join("\n",$maxima_result_array);
	$maxima_result=sanitize_maxima_output($maxima_result);
	if (($maw_allow_cache)&&($retvalue==0))
 	{
          $file_maxima=fopen($maw_cache_directory.$fingerprint.".php", "w");
          fwrite($file_maxima,"<?php \n");
          fwrite($file_maxima,"\$"."maxima_result_input=<<<STRRET\n".sanitize_maxima_input_before_write($maxima_string));
          fwrite($file_maxima,"\nSTRRET;\n\n");
          fwrite($file_maxima,"\$"."maxima_result=<<<STRRET\n".sanitize_maxima_output_before_write($maxima_result));
          fwrite($file_maxima,"\nSTRRET;\n?>");
          fclose($file_maxima);
	}
  }
  $maxima_end=getmicrotime();
  $maxima_time_last_call=($maxima_end)-($maxima_start);
  $maxima_total_runtime=$maxima_total_runtime+$maxima_time_last_call;
  return($maxima_result);
}

function maxima_command($prikaz,$opt="",$matchint="matchint")
{
  global $vystupmaximy,$operace,$novafce,$pfeformat,$mawhome,$logarcswitch,$prom,$oriprom,$maximainit, $TeXstrings,$maxima_total_runtime,$maxima_total_calls,$maxima_total_cached,$maxima, $mawtimeout, $maw_tempdir, $maxima_time_last_call;
  if (($logarcswitch=="on") && (preg_match('~asinh|acosh|atanh|acoth|asech|acsch|integrate~',$prikaz)))
    {
      $temp_logarc="load(\\\"$mawhome/integral/simpinvtrigh.mac\\\"),";
    }
  else
    {
      $temp_logarc="";
    }
  if ($novafce=="")
    {
      $maxima_string="$opt $mawtimeout $maxima --batch-string=\"block($maximainit maw_var:$prom,maw_var_ori:$oriprom, $temp_logarc load(\\\"$mawhome/integral/$matchint.mac\\\"),pfeformat:$pfeformat)$ $prikaz; tex(%,false);\"";
      $maxima_output_string=do_maxima($maxima_string);
    }
  else
    {
      $maxima_string="$opt $mawtimeout $maxima --batch-string=\"block($maximainit maw_var:$prom,maw_var_ori:$oriprom, $temp_logarc load(\\\"$mawhome/integral/$matchint.mac\\\"),pfeformat:$pfeformat , print(elapsed_run_time()), if not atom($novafce) then (if length(args($novafce))>1 then print(\\\"#\\\",op($novafce),\\\"#\\\") else (if op($novafce)=op(-maw_b) and not atom(args($novafce)[1]) then print(\\\"#\\\",op(args($novafce)[1]),\\\"#\\\"))),print(elapsed_run_time()))$ $prikaz;  (print(elapsed_run_time()),tex(%,false));\"";
      $maxima_output_string=do_maxima($maxima_string);
      if (ereg("# */* *#",$maxima_output_string))
	{
	  $operace="podil";
	}
      elseif (ereg("# *\+ *#",$maxima_output_string))
	{
	  $operace="soucet";
	}
    }
  $vystupmaximy=$vystupmaximy."\n<b>Time $maxima_time_last_call</b>".$maxima_output_string;
  $maxima_output_string=str_replace("\n"," ",$maxima_output_string);
  ereg("\(%o2\)(.*)\(%i3\)",$maxima_output_string,$vystup);
  if ($vystup[1]=="") 
    {
      maw_html_head();
      echo hide_message();
      save_log_err("No answer from Maxima: $prikaz".savekey($maw_tempdir),"integral");
      save_log("No answer from Maxima: $prikaz","integral_error");
      echo sprintf("\n<div class=\"h3_error\">%s</div>",__("Error"));  
      echo "<b>".__("Sorry. An explanation for this error is not known automatically. The problem has been logged and will be investigated.")."</b>";
      echo "<br><br>"."The following transcript of failed Maxima session may help you to find the problem. You are also welcome to report this problem to the authors."."<hr><br>";
      echo "<pre>$vystupmaximy</pre>";
      die("</html>");  
    }
  preg_match('/\(%o3\)(.*)/',$maxima_output_string,$vystupTeXarray);
  if (!(ereg("I",$vystupTeXarray[1])))
    {
      $vystupTeXarray[1]=stripslashes($vystupTeXarray[1]);
      $vystupTeXarray[1]=str_replace('$','',$vystupTeXarray[1]);
      $vystupTeXarray[1]=str_replace('"','',$vystupTeXarray[1]);
      $vystupTeXarray[1]=str_replace("\it #101I","\\mathrm{i}",$vystupTeXarray[1]);
      if ($vystupTeXarray[1]!="") {$TeXstrings[$vystup[1]]=formconv_replacements($vystupTeXarray[1]); }
    }
  return($vystup[1]);
}

function maxima_session_comment($str)
{
  global $vystupmaximy;
  $vystupmaximy=$vystupmaximy."\n<b><span class=\"red\">MAW Maxima session: $str</span></b>";
}

check_for_security($substituce);

if (($akce=="s") and (!(ereg("=",$substituce))) and ($substituce!="") )
{$substituce=$substituce." = ".$novapromenna;}

if ($akce=="s")
  {
    if ((strlen($novapromenna)!=1) or ($novapromenna==$prom) or ($substituce=="") or (ereg($novapromenna,$allprom)) or (!(ereg($novapromenna,$substituce))))
      {
	maw_html_head();
	maw_integral_error();
	save_log_err("Funkce: $fce, substituce: $substituce, nova promenna: $novapromenna".savekey($maw_tempdir),"integral");
	echo "\n<div class=\"h3_error\">".__("Error: incorrect substitution")."</div>";
	echo sprintf(__("You have to use new name for the variable and you have to enter which substitution you wish to use. The name of the variable consists of one letter only. This name cannot be %s."),join(", ",str_split($allprom)));
	die("</html>");
      }
    else
      {
	if (!(ereg($novapromenna,$allprom))) {$allprom=$allprom.$novapromenna;}
      }
  }


function RandomString($len){
    $randstr = '';
    srand((double)microtime()*1000000);
    for($i=0;$i<$len;$i++){
        $n = rand(48,120);
        while (($n >= 58 && $n <= 64) || ($n >= 91 && $n <= 96)){
            $n = rand(48,120);
        }
        $randstr .= chr($n);
    }
    return $randstr;
}

if ($krok=="") {$krok=1;}

if ($krok==1)
  {
    $maw_tempdir="/tmp/_MAW_integral".RandomString(15);;
    system ("mkdir $maw_tempdir; chmod oug+rwx $maw_tempdir");
    $oriprom=$prom;
    $variables=$prom;
    $tempfce=$fce;
    if ($formconv=="on") {$tempfce=math_to_maxima($fce); }
    if ($tempfce=="???") 
      {	
	maw_errmsgB('<span class="red bold">'.__("Formconv (program which parses your input) failed. Correct the formula on your input.").'</span><hr>');
	$temp=input_to_maxima($fce);
	maw_howto();
        die();
      }
    $fce=input_to_maxima($tempfce);
    if (preg_match("/abs/",$fce)) 
      {
          maw_html_head(); 
	  echo "\n<h3>".__("You probably want to integrate a function involving the absolute value.")."</h3> ".__("This is not supported in our integral assistant.");
	  die(hide_message());
      }
    $oriproblem=$fce;
    save_log(maxima_to_html_mimetex($oriproblem,"\\int ","\\, d $prom")." akce:".$akce." ".$substituce.savekey($maw_tempdir),"integral-original");
  }

if (($akce=="")&&($krok>1))
  {
    $akce="i";
    maw_html_headB(); 
    maw_integral_error();
    echo ("\n<div class=\"h3_error\">".__("You did not choose any action")."</div> ".__("Program Maxima finishes the answer automatically")." ".__("If you want to try various integration techniques, return to the previous page and choose the required method.")); 
  }

function math_to_tex($vstup)
{
  global $fce, $prom, $substituce, $novapromenna, $u, $v, $akce,$maw_tempdir,$TeXstrings, $mawtimeout, $formconv_bin;
  if (strlen($vstup)>1000) 
    {
      save_log_err("long formula (".(strlen($vstup))." chars), $fce, $prom, $substituce, $novapromenna, $u, $v, $akce".savekey($maw_tempdir), "integral");
      echo(__("<b>Too long formula, sorry.</b> <ul><li>Try to install maxima on your local computer or look at the formula on the page below.</li><li>The problem is probably too difficult, but you can try another method for integration.</li></ul>"));
      echo ("<pre>".preg_replace('/ +/',"\n",$vstup)."</pre>");
      die(hide_message());
    }
  if (isset($TeXstrings[$vstup])) {return($TeXstrings[$vstup]);}
  $vystup=`$mawtimeout echo "$vstup" | $formconv_bin -a`;
  $vystupb=`$mawtimeout echo "$vstup" | $formconv_bin`;
  if (strlen($vystupb)<strlen($vystup)) {$vystup=$vystupb;} 
  $vystup=chop($vystup);	
  $vystup=formconv_replacements($vystup);
  if ($vystup=="") {
    save_log_err("formconv error: $fce, $prom, $substituce, $novapromenna, $u, $v, $akce, vstup: $vstup".savekey($maw_tempdir), "integral");
    $vystup="???";
  }
  return($vystup);
}

function math_to_maxima($vstup)
{
  global $mawtimeout, $formconv_bin;
  $vystup=`$mawtimeout echo "$vstup" | $formconv_bin -a -O maxima`;
  $vystup=chop($vystup);	
  if ($vystup=="") {$vystup="???";}
  return($vystup);
}

function vypocitej($akcenotused,$fce,$promenna)
{
  // the first parameter is not used anymore, we use global variable instead
  global $maw_tempdir,$krok,$u,$v,$substituce,$novapromenna,$prom,$formconv,$jsmath,$fbox,$fboxb,$fboxe,$vsechno,$cislo,$allprom,$variables,$akce, $rozsirit,$rozsiritradcan,$oriprom, $oriproblem,$substituce_back, $mawtimeout, $formconv_bin;

  if ($akce=="soucet")
    {
      $scitance=chop(maxima_command("args($fce)"));
      $scitance=str_replace("[","",$scitance);
      $scitance=str_replace("]","",$scitance);
      $scitance=split(",",$scitance);
      $pocet=count($scitance);
      $pocet_on=0;
      for ($p=0; $p<$pocet; $p=$p+1)
	{
	  if ($_REQUEST["scitanec".$p]=="on")
	    {
	      $pocet_on=$pocet_on+1;
	    }
	}
      if ($pocet_on==$pocet) {$akce="i"; $ori_akce="soucet";}
    }

  if ($akce=="undo") {$krok=$krok-2;}
  elseif ($akce=="soucet")
    {
      maxima_session_comment("extracting terms from the sum");      
      $scitance=chop(maxima_command("args($fce)"));
      $scitance=str_replace("[","",$scitance);
      $scitance=str_replace("]","",$scitance);
      $scitance=split(",",$scitance);
      $pocet=count($scitance);
      for ($p=0; $p<$pocet; $p=$p+1)
	{
	  if ($_REQUEST["scitanec".$p]=="on")
	    {
	      $integrovana_cast=$integrovana_cast."+($scitance[$p])";
	    }
	}
      if ($integrovana_cast=="") {maw_integral_error();die("\n<div class=\"h3_error\">".__("Error")."</div>".__("You choosed to integrate some terms but no term has been marked for integration").hide_message());}

      $temp=str_replace("I","(forget(maw_var>0),integrate(radcan($integrovana_cast),$prom)+I)",$vsechno);
      maxima_session_comment("Integration some parts of the sum - evaluating the rest of the integral");
      $vypocet=maxima_command("(negsumdispflag:false,forget(maw_var>0),$fce-($integrovana_cast))");
      $komentar=__("Integrate terms in the sum").": ";
      $ttt=chop($vypocet);
      $ttt=str_replace(" ","",$ttt);
      //if ($ttt=="0") 
      //	{
      //	 $akce="i";
      //         $ori_akce="soucet";
      //         $hlaseni=sprintf("<span class=\"h3_error\">%s</span>%s",__("Error"),__("You choosed all terms for integration, which is not allowed. If you really want to integrate all the terms, use the choice \"ask the computer to finish the integration\" (in bold)."));
	  // die($hlaseni);
      //	}
      //else 
      maxima_session_comment("Integration some parts of the sum - evaluating the rest of the integral");
      {$vsechno=maxima_command("(negsumdispflag:false,expand($temp))","","matchint_short");}
    }
  elseif ($akce=="f") 
    {
      $vypocet=faktorizace($fce);
      $komentar=__("Factorization").": ";
    }
  elseif ($akce=="e") 
    {
      $vypocet=roznasobeni($fce);
      $komentar=__("Expansion").": ";
    }
  elseif ($akce=="fs") 
    {
      maxima_session_comment("fullratsimp");
      $vypocet=maxima_command("fullratsimp($fce)","","matchint_short");
      $komentar=__("Algebraic modification (fullratsimp function)").": ";
    }
  elseif ($akce=="logarc_") 
    {
      maxima_session_comment("logarc");
      $vypocet=maxima_command("logarc($fce)","","matchint_short");
      $komentar=__("Algebraic modification (logarc function)").": ";
    }
  elseif ($akce=="fsmap") 
    {
      maxima_session_comment("fullratsimp with map");
      $vypocet=maxima_command("map(fullratsimp,$fce)","","matchint_short");
      $komentar=__("Algebraic modification (fullratsimp function) for each term in a sum").": ";
    }
     elseif ($akce=="tr")
    {
      maxima_session_comment("trigreduce");
      $vypocet=maxima_command("trigreduce($fce)","","matchint_short");
      $komentar=__("Algebraic modification (trigreduce function)").": ";
    }
  elseif ($akce=="x") 
    {
      maxima_session_comment("xthru");
      $vypocet=maxima_command("xthru($fce)","","matchint_short");
      $komentar=__("Algebraic modification (xthru function)").": ";
    }
  elseif ($akce=="xmap") 
    {
      maxima_session_comment("xthru + map");
      $vypocet=maxima_command("map(xthru,$fce)","","matchint_short");
      $komentar=__("Algebraic modification (xthru function) for each term in a sum").": ";
    }
  elseif ($akce=="completesquare_asin") 
    {
      maxima_session_comment("complete square in arcsin");
      $vypocet=maxima_command("(completesquare_asin($fce))","","matchint_short");
      $komentar=__("Completing square").": ";
    }
  elseif ($akce=="completesquare_frac") 
    {
      maxima_session_comment("complete square in fraction");
      $vypocet=maxima_command("(completesquare_frac($fce))","","matchint_short");
      $komentar=__("Completing square").": ";
    }
  elseif ($akce=="split_fraction_for_integration") 
    {
      maxima_session_comment("split fraction into two");
      $vypocet=maxima_command("(split_fraction_for_integration($fce))","","matchint_short");
      $komentar=__("Clever expansion into two fractions").": ";
    }
  elseif ($akce=="ts") 
    {
      maxima_session_comment("trigsimp");
      $vypocet=maxima_command("trigsimp($fce)","","matchint_short");
      $komentar=__("Algebraic modification (trigsimp function)").": ";
    }
  elseif ($akce=="texp") 
    {
      maxima_session_comment("trigexpand");
      $vypocet=maxima_command("trigexpand($fce)","","matchint_short");
      $komentar=__("Algebraic modification (trigexpand function)").": ";
    }
  elseif ($akce=="seccsc") 
    {
      $vypocet=maxima_command("(tellsimp(sec(allexpr_2),1/cos(allexpr_2)),tellsimp(csc(allexpr_2),1/sin(allexpr_2)),$fce)");
      $komentar=__("Algebraic modification (removing sec and csc)").": ";
    }
  elseif ($akce=="rad") 
    {
      maxima_session_comment("radcan");
      $vypocet=maxima_command("radcan($fce)","","matchint_short");
      $komentar=__("Algebraic modification (radcan function)").": ";
    }
    elseif ($akce=="abs")
    {
      maxima_session_comment("removing absolute value");
      $tempvypocet=str_replace("abs","",$fce);
      $vypocet=maxima_command("$tempvypocet","","matchint_short");
      $komentar=__("We remove absolute values").": ";
    }
    elseif ($akce=="roots")
    {
      maxima_session_comment("rootscontract");
      $vypocet=maxima_command("(rootscontract($fce))","","matchint_short"); 
      $komentar=__("Contract product of roots (rootscontract)").": ";
    }
    elseif ($akce=="dp")
    {
      maxima_session_comment("division");
      $komentar=__("Division").": ";
    $vypocet=maxima_command("block(cit:num($fce),jm:denom($fce),divide(cit,jm)[1]+(divide(cit,jm)[2])/(jm))");
    }
  elseif ($akce=="p") 
    {
      $vypocet=zlomky($fce,$promenna);
      $komentar="<b>".__("Partial fractions").": </b>";
    }
  elseif ($akce=="s") 
    {
      if ($formconv=="on") 
	{
	  $substituce=math_to_maxima($substituce);
	}
      $temps=split("=",$substituce);
      $variables=$prom.'|'.$novapromenna;
      $tempsl=input_to_maxima($temps[0]);
      $tempsr=input_to_maxima($temps[1]);
      $substituce=$tempsl." = ".$tempsr;
      $vypocet=substituce($fce,$substituce,$novapromenna);
      $test=str_replace(" ","",$substituce);
      if (("tan(((1/2)*$prom))=$novapromenna"==$test)
	  ||("tan($prom/2)=$novapromenna"==$test)
	  ||("tan(($prom/2))=$novapromenna"==$test)) 
	{
	  $diferencialy="$prom=2\\mathop{\\text{arctan}}\\nolimits $novapromenna\\\\\\mathrm{d}$prom=\\frac{2}{1+{$novapromenna}^2}\\mathrm{d}$novapromenna";
	  if (ereg("sin",$fce))
		   {
		     $diferencialy=$diferencialy."\\\\ \\sin $prom=\\frac{2 {$novapromenna}}{{1+{$novapromenna}^2}}";
		   }
	  if (ereg("cos",$fce))
		   {
		     $diferencialy=$diferencialy."\\\\ \\cos $prom=\\frac{1- {$novapromenna}^2}{{1+{$novapromenna}^2}}";
		   }
	  if (ereg("tan",$fce))
		   {
		     $diferencialy=$diferencialy."\\\\ \\cos $prom=\\frac{2 {$novapromenna}^2}{{1-{$novapromenna}^2}}";
		   }
	}
      elseif
	(("tan(($prom))=$novapromenna"==$test)
	 ||("tan($prom)=$novapromenna"==$test)) 
	{
	  $diferencialy="$prom=\\mathop{\\text{arctan}}\\nolimits $novapromenna\\\\\\mathrm{d}{$prom}=\\frac{1}{1+{$novapromenna}^2}\\mathrm{d}{$novapromenna}";
	  if (ereg("sin",$fce))
		   {
		     $diferencialy=$diferencialy."\\\\ \\sin $prom=\\frac{{$novapromenna}}{\sqrt{1+{$novapromenna}^2}}";
		   }
	  if (ereg("cos",$fce))
		   {
		     $diferencialy=$diferencialy."\\\\ \\cos $prom=\\frac{1}{\sqrt{1+{$novapromenna}^2}}";
		   }
	}
      else
	{
	  maxima_session_comment("evaluating relation between differentials");
	  $diferencialy=maxima_command("shorter(diff($substituce))","","matchint_short");
	  $diferencialy=str_replace("del($prom)","A",$diferencialy);
	  $diferencialy=str_replace("del($novapromenna)","B",$diferencialy);
	  $ttt=`$mawtimeout echo "$diferencialy" | $formconv_bin -a -A A -A B -A $prom -A $novapromenna -A a -A b -A x -A y`;
	  $vystup=formconv_replacements($vystup);
	  $diferencialy=chop($ttt);	
	  if ($diferencialy=="") {$diferencialy="???";}
	  $diferencialy=str_replace("A","\\mathrm{d}$prom",$diferencialy);
	  $diferencialy=str_replace("B","\\mathrm{d}$novapromenna",$diferencialy);
	}

      $substvztah=math_to_tex($substituce);
      if ($substituce_back!=""){$substvztah_back=math_to_tex($substituce_back);}
     
      if ($jsmath=="on"){
	$komentar="<b>".__("Substitution")."</b>".tex_to_html("\\fbox{\$\\begin{array}{c}$substvztah\\\\$diferencialy\\\\$substvztah_back\\end{array}\$}")." ".__("yields").":";
      }
      else
      {
	$komentar="<b>".__("Substitution")."</b>".tex_to_html("\\fbox{\\begin{aligned}".str_replace("=","&=","$substvztah\\\\$diferencialy\\\\$substvztah_back")."\\end{aligned}}")." ".__("yields").":";
      }
      $prom=$novapromenna;
    }
  elseif ($akce=="v") 
    {
      $komentar=__("We remove constant multiple from the integral").": ";
      if ($formconv=="on") {$cislo=math_to_maxima($cislo);}
      $temp=str_replace("I","($cislo*I)",$vsechno);
      maxima_session_comment("removing constant multiple from integral - part 1 - updating the total");
      $vsechno=maxima_command("if ((numberp($cislo)) or (numberp(($cislo)/%pi)) or (numberp(log($cislo))) or (diff($cislo,$prom)=0)) and (ratsimp($cislo)#0) then (negsumdispflag:false,$temp)");
      maxima_session_comment("removing constant multiple from integral - part 2 - updating the integral");
      $vypocet=maxima_command("if ((numberp($cislo)) or (numberp(($cislo)/%pi)) or (numberp(log($cislo))) or (diff($cislo,$prom)=0)) and (ratsimp($cislo)#0) then ($fce)/($cislo)");
      if (ereg("false",$vsechno)) {maw_integral_error();die(__("<b>Error. The most general expression which you can remove from the integral is  a nonzero number written with digits or a multiple of constants pi and e.</b> Go back and revise your choice.").hide_message());}
    }
  elseif ($akce=="pp") 
    {
      $variables=$prom;
      if ($u!="")
	{
	  if ($formconv=="on") {$u=math_to_maxima($u);}
	  $u=input_to_maxima($u);
	  $vypocet=perpartesu($fce,$u,$promenna);
	  //$vypocetB=perpartesuB($fce,$u,$promenna);
	  $A=$u;
	  maxima_session_comment("integration by parts");
	  $B=maxima_command("integrate($u,$promenna)","","matchint_short");
	  if (ereg("integrate",$B)) 
	    {
	      maw_html_head();
	      maw_integral_error();
	      save_log_err("Funkce: $fce, per partes, nepodarilo se integrovat v' ".savekey($maw_tempdir),"integral");
	      echo sprintf("\n<div class=\"h3_error\"> %s </div> %s ",_("Error: Failed to evaluate integral of <i>v'</i>."),__("Try something another."));
	      die(hide_message()."</html>");  
	    }
	  maxima_session_comment("integration by parts");
	  $C=maxima_command("shorter(factor($fce)/($u))","","matchint_short");
	  maxima_session_comment("integration by parts");
	  $D=maxima_command("shorter(diff(shorter(($fce)/($u)),$promenna))","","matchint_short");
	}
      else
	{
          if ($formconv=="on") {$v=math_to_maxima($v);}
	  $v=input_to_maxima($v);
	  $vypocet=perpartesv($fce,$v,$promenna);
	  //$vypocetB=perpartesvB($fce,$v,$promenna);
	  maxima_session_comment("integration by parts");
	  $A=maxima_command("shorter(factor($fce)/($v))","","matchint_short");
	  maxima_session_comment("integration by parts");
	  $B=maxima_command("integrate(factor(($fce)/($v)),$promenna)","","matchint_short");
	  if (ereg("integrate",$B)) 
	    {
	      maw_html_head();
	      maw_integral_error();
	      save_log_err("Funkce: $fce, per partes, nepodarilo se integrovat v' ".savekey($maw_tempdir),"integral");
	      echo sprintf("\n<div class=\"h3_error\"> %s </div> %s ",_("Error: Failed to evaluate integral of <i>v'</i>."),__("Try something another."));
	      die(hide_message()."</html>");  
	    }
	  $C=$v;
	  maxima_session_comment("integration by parts");
	  $D=maxima_command("diff($v,$promenna)","","matchint_short");
	}
      $temp=$fboxb."\\begin{array}{ll}u=".math_to_tex($C)." & \ \ u'=".math_to_tex($D)."\\\\[6pt]v'=".math_to_tex($A)." & \ \ v=".math_to_tex($B)."\\end{array}".$fboxe;
      $komentar=sprintf(__("Integrating <b>by parts</b> with %s we get:"),tex_to_html($temp))." ";
      $temp=str_replace("I","(shorter(($C)*($B))-I)",$vsechno);
      maxima_session_comment("integration by parts - complete formula");
      $vsechno=maxima_command("(negsumdispflag:false,expandI($temp))","","matchint_short");
    }
  elseif ($akce=="sin2cos") 
    {
      maxima_session_comment($akce);
      $vypocet=maxima_command("(matchdeclare(maw_sin2,true),let (sin(maw_sin2)^2, 1 - cos(maw_sin2)^2),ratsimp(((letsimp((letsimp($fce))/sin(x))))*sin(x)))");
      $komentar=__("Even powers of sine function replaced by cosine").": ";
    }
  elseif ($akce=="cos2sin") 
    {
      maxima_session_comment($akce);
      $vypocet=maxima_command("(matchdeclare(maw_cos2,true),let (cos(maw_cos2)^2, 1 - sin(maw_cos2)^2),ratsimp(((letsimp((letsimp($fce))/cos(x))))*cos(x)))");
      $komentar=__("Even powers of cosine function replaced by sine").": ";
    }
  elseif ($akce=="multhru") 
    {
      if ($formconv=="on") 
	{
	  $temp=$rozsirit;
	  $rozsirit=math_to_maxima($temp);
	  if ($rozsirit=="???") {$rozsirit=$temp;}
	}
	    $optradcan="";
	    if ($rozsiritradcan=="on") {$optradcan="radcan";}
      $rozsirit=input_to_maxima($rozsirit);
      maxima_session_comment("multiply numerator and denominator");
      $vypocet=maxima_command("if not (zeroequiv($rozsirit,$prom)) then (simp:false,maw_f1:num($fce),maw_f2:denom($fce),ev(maw_f1:$optradcan(($rozsirit)*maw_f1),simp),ev(maw_f2:$optradcan(($rozsirit)*maw_f2),simp),print(maw_f1),maw_f1/maw_f2)");
      if (ereg("false",$vypocet)) {maw_integral_error();die(hide_message().__("<b>Error. You cannot multiply numerator and denominator by zero or a zero equivalent expression.</b> Go back and revise your choice."));}
      if ($rozsiritradcan=="on") 
	{
	  $komentar=sprintf(__("Multiplying both numerator and denominator by %s and simplification"),tex_to_html("\\small".math_to_tex($rozsirit)));
	}
      else 
	{
	  $komentar=__("Multiplying both numerator and denominator");
	}
      $komentar=$komentar.": ";
    }  
  if ($akce=="i") 
    {
      if ($ori_akce=="soucet")
	{
	  $vypocet=integrace($fce,$promenna,true);
	  $komentar=__("Integrating terms in a sum");
	}
      else
	{
      $vypocet=integrace($fce,$promenna);
      $komentar=__("Finishing computation by Maxima");
	}
      if (!($prom==$oriprom)) {$komentar=$komentar." ".__("(remember to return to the original variable)");}
      $komentar=$komentar.":";
    }

  if ($akce=="equation") 
    {
      $constantI=maxima_command("if zeroequiv($fce-($oriproblem),$prom) then 1 else (radcan(($fce)/($oriproblem)))");
      $vypocet=maxima_command("rhs(solve(I=".str_replace("I", "I*(".$constantI.")", $vsechno).",I)[1])");
      $vsechno2=maxima_command(str_replace("I", "I*(".$constantI.")", $vsechno));
      $komentar=__("Conversion into algebraic equation").str_replace("\\mathrm{i}","I",maxima_to_html($vsechno2,"I=",""))."<br>".__("Solution of this equation");
      $vsechno=$vypocet;
      if (!($prom==$oriprom)) {$komentar=$komentar." ".__("(remember to return to the original variable)");}
      $komentar=$komentar.":";
    }

  uloz($vypocet,$komentar);
  return($vypocet);
}

function uprav($postakce,$fce)
{
  global $prom, $novapromenna, $substituce,$formconv, $variables,$limit_a, $limit_b, $defint,$I,$oriproblem,$oriprom,$fboxb,$fboxe,$backsubst;
  if ($postakce=="zpetna_substituce")
    {
      $temp=$_REQUEST["s_final"];
      if ($temp=="hint") {$tt=split("=",$backsubst);$substituce=$tt[1];}
      $backsubst="NotAvailable";
      if ($formconv=="on") {$substituce=math_to_maxima($substituce);}
      if ($temp=="hint") {$variables=$oriprom;} else {$variables=$novapromenna;}
      $substituce=input_to_maxima($substituce);
      $komentar=__("Substitution")." ";
      $komentar=$komentar.tex_to_html($fboxb."\\begin{array}{c}".math_to_tex($prom."=".$substituce)."\\end{array}".$fboxe).": &nbsp;&nbsp;&nbsp;";
      maxima_session_comment("back substitution");
      $vypocet=maxima_command("subst(".$substituce.",".$prom.",".$fce.")");
      $prom=$novapromenna;
    }
  elseif ($postakce=="newton-leibniz")
    {
      $limit_a=input_to_maxima(math_to_maxima($limit_a));
      $limit_b=input_to_maxima(math_to_maxima($limit_b));
      maxima_session_comment("substituting lower limit");
      $Fa=maxima_command("ev($fce,$prom=$limit_a)");
      maxima_session_comment("substituting upper limit");
      $Fb=maxima_command("ev($fce,$prom=$limit_b)");
      maxima_session_comment("evaluating definite integral from Newton Leibinz formula");
      $vypocet=maxima_command("ev(".$fce.",$prom=$limit_b)-ev($fce,$prom=$limit_a)");
      $komentar=__("Substituting limits").": <br>".maxima_to_html($fce,"F(x)=","")."<br>".tex_to_html("F\\left(".math_to_tex($limit_b)."\\right)=".math_to_tex($Fb))."<br>".tex_to_html("F\\left(".math_to_tex($limit_a)."\\right)=".math_to_tex($Fa))."<br>";
      $I="\\int_{".math_to_tex($limit_a)."}^{".math_to_tex($limit_b)."} ".math_to_tex($oriproblem)."\\ \\textrm{d}$oriprom";
      $defint=rawurlencode($I);
    }
  elseif ($postakce=="float")
    {
      maxima_session_comment("numerical approximation");
      $vypocet=maxima_command("ev(float(".$fce."),numer)");
      $komentar=__("Approximation").": ";
    }
  else
    {
      maxima_session_comment("algebraic modification: $postakce");
      $vypocet=maxima_command($postakce."(".$fce.")");
      $komentar=__("Algebraic modification").": ";
    }
  uloz($vypocet,$komentar);
  return($vypocet);
}

function uloz($vypocet,$komentar)
{
  global $maw_tempdir,$krok,$akce,$postakce,$fce,$I,$defint;
  $handle = fopen($maw_tempdir."/".$krok.".html","w");
  fwrite ($handle, "<hr>\n".__("Step").($krok-1)."<br>");
  if (($akce=="i")||($akce=="equation")) 
    {
      $temp=maxima_to_html_vysledek($vypocet);
    }
  elseif ($akce=="dokonceni")
    { 
      if ($defint!="") {$I=rawurldecode($defint);} else {$I="I";}
      if ($postakce=="float") 
	{$temp=maxima_to_html($vypocet,$I." \\approx ","");}
      else
	{$temp=maxima_to_html($vypocet,$I." = ","");}
    }    
  else
    {
      $temp=maxima_to_html_vsechno($vypocet);
    }
  fwrite ($handle, $komentar);
  fwrite ($handle, $temp."<br>");
  fclose($handle);
}

function substituce($fce,$substituce,$novapromenna)
{
  global $kladna,$prom,$substituce_back_orig,$backsubst, $mawhome;
  $opt="";
  $test=str_replace(" ","",$substituce);
  if ((ereg("tan\(\(\(1/2\)\*$prom\)\)=$novapromenna",$test))||(ereg("tan\($prom/2\)=$novapromenna",$test))||(ereg("tan\(\($prom/2\)\)=$novapromenna",$test))) {$opt="trigexpand";}
  if ($kladna=="on")
    {
      maxima_session_comment("trying substitution, new variable is assumed to be positive");
      $answer=maxima_command("block(load(\\\"$mawhome/common/changevar2.mac\\\"),assume($novapromenna>0),logarc:false,$opt(rootscontract(diff(changevar2('integrate($fce,$prom),$substituce,$novapromenna,$prom),$novapromenna))))"," yes 'pos;' | ");
    }
  else
    {
      maxima_session_comment("trying substitution, new variable is NOT assumed to be positive");
      $answer=maxima_command("(load(\\\"$mawhome/common/changevar2.mac\\\"),logarc:false,$opt(rootscontract(diff(changevar2('integrate($fce,$prom),$substituce,$novapromenna,$prom),$novapromenna))))");
    }
  // if there is no hint for back substitution, we check if the user enters substitution in the form
  // new_variable = f(old_variable)
  $substituce=str_replace(" ","",$substituce);
  if ($substituce_back_orig=="") 
    {
      if (substr($substituce,0,2)=="$novapromenna=")
	{
	  $substituce_back_orig=$substituce;
	}
      elseif (substr($substituce,-2,2)=="=$novapromenna")
	{
	  $substituce_sides=preg_split('~=~',$substituce);
	  $substituce_back_orig="$substituce_sides[1]=$substituce_sides[0]";
	}
    }
  if ($substituce_back_orig=="") {$backsubst="NotAvailable";}
  else
    { 
      if ($backsubst=="") {$backsubst=$substituce_back_orig;}
      elseif ($backsubst!="NotAvailable")
	{
	  maxima_session_comment("join substitutions");
	  $backsubst=maxima_command("(simp:false,ev($substituce_back_orig,$backsubst))");
	}
    }
  if (str_replace("\n","",str_replace(" ","",$answer))=="0") {
    save_log_err("bad substitution: ".$fce." sub:$substituce $prom -> $novapromenna".savekey($maw_tempdir),"integral");
    die (hide_message().__("Sorry, the application cannot handle this substitution."));
  }
  // $prom=$novapromenna;
  return($answer);
}


function faktorizace($fce)
{ 
  maxima_session_comment("factorization");
  return(maxima_command("factor($fce)","","matchint_short"));
} 

function roznasobeni($fce)
{ 
  maxima_session_comment("expansion");
  return(maxima_command("expand($fce)","","matchint_short"));
} 

function perpartesu($fce,$u,$promenna)
{ 
  maxima_session_comment("integration by parts - evaluating the integrand");
  return(maxima_command("shorter(integrate($u,$promenna)*diff(($fce)/($u),$promenna))","","matchint_short"));
} 
/* function perpartesuB($fce,$u,$promenna) */
/* {  */
/*   return(maxima_command("integrate($u,$promenna)*(($fce)/($u))")); */
/* }  */

function perpartesv($fce,$v,$promenna)
{ 
  maxima_session_comment("integration by parts - evaluating the integrand");
  return(maxima_command("shorter(diff($v,$promenna)*integrate(($fce)/($v),$promenna))","","matchint_short"));
} 
/* function perpartesvB($fce,$v,$promenna) */
/* {  */
/*   return(maxima_command("$v*integrate(($fce)/($v),$promenna)")); */
/* }  */

function zlomky($fce,$prom)
{ 
  global $mawhome;
  return(maxima_command("(load(ntrig),my_partfrac($fce,$prom))","","matchint_short"));
} 

function integrace($fce,$prom,$usemap=false)
{ 
  global $maw_tempdir,$TeXstrings;
  if ($usemap) 
  {
    maxima_session_comment("integration using map wrapper");
    $result=maxima_command("(forget(maw_var>0),map(lambda([u],integrate(u,$prom)),$fce))");
  }
  else
  {
    maxima_session_comment("integration");
    $result=maxima_command("(forget(maw_var>0),answer:errcatch(integrate(($fce),$prom)),if answer=[] then 'integrate(($fce),$prom) else answer[1])");

  }
  if ((ereg("integrate",$result))||($result==""))
    {
      maw_html_headB();
      maw_integral_error();
      save_log_err("unevaluated: ".$fce.savekey($maw_tempdir),"integral");
      if ($result=="") {$result=" ";}
      if (isset($TeXstrings[$result])) {$result=tex_to_html($TeXstrings[$result]);}
      if ($prom=='x') {$fce_in_x=$fce;} else {$fce_in_x=maxima_command("ev($fce,$prom=x)");}
      die(sprintf(__("<b>Sorry, unable to evaluate the integral %s using Maxima.</b><br><br>You may try to help Maxima by performing some simplification (radcan?) or substitution.<br><br>You may get the answer using another Computer Algebra System, such as %s or %s which is reported by %s to be most powerful in integration."),tex_to_html("\\int ".formconv($fce)." \\;\\text{d}$prom"),"<a href=\"http://integrals.wolfram.com/index.jsp?expr=".rawurlencode($fce_in_x)."\">Mathematica</a>","<a href=\"http://axiom.axiom-developer.org/\">Axiom</a>","<a href=\"http://en.wikipedia.org/wiki/Risch_algorithm\">Wikipedia</a>").hide_message());
    }
  elseif (ereg("erf|li|eliptic|bessel",$result))
    {
      maw_html_headB();
      maw_integral_error();
      save_log_err("nonelementary: ".$fce.savekey($maw_tempdir),"integral");
      if (isset($TeXstrings[$result])) {$result=tex_to_html($TeXstrings[$result]);}
      die(sprintf(__("<b>Sorry, the answer does not exist in the class of <a href=\"http://en.wikipedia.org/wiki/Elementary_function\">elementary functions</a>.</b> From this reason the integral exceeds the level of primary target students. <br> Still curious? The answer is %s."),$result).hide_message());
    }
  elseif (ereg("atan2",$result))
    {
      maw_html_headB();
      maw_integral_error();
      save_log_err("atan2: ".$fce.savekey($maw_tempdir),"integral");
      if (isset($TeXstrings[$result])) {$result=tex_to_html($TeXstrings[$result]);}
      die(sprintf(__("<b>Sorry, the answer contains function %s which is not supported on output. <br> Still curious? The answer is %s."),"<a href=\"http://maxima.sourceforge.net/docs/manual/en/maxima_10.html#IDX379\">atan2</a>",$result).hide_message());
    }
  return($result);
} 


function maxima_to_html($vyraz,$pred,$za)
{
  global $mimetex,$jsmath;
  if ($jsmath=="on")
  {
  return(" <span class=\"math\">\\displaystyle{".$pred.chop(math_to_tex($vyraz)).$za."}</span> ");
  }
  else
  {
  return(" <img src=\"$mimetex".$pred.chop(math_to_tex($vyraz)).$za." \"style=\" padding: 8px; vertical-align: middle \" alt=\"math formula\"> ");
  }
}

function maxima_to_html_vsechno($vyraz)
{
  global $mimetex,$jsmath,$vsechno,$prom;
  $celyvysledek=str_replace("\\mathrm{i}", "\\int ".chop(math_to_tex($vyraz))."\\,\\mathrm{d}$prom", math_to_tex($vsechno));
  if ($jsmath=="on")
  {
  return(" <span class=\"math\">\\displaystyle{I=$celyvysledek}</span> ");
  }
  else
  {
  return(" <img src=\"$mimetex"."I=$celyvysledek\" style=\" padding: 8px; vertical-align: middle \" alt=\"math formula\"> ");
  }
}

function maxima_to_html_vysledek($vyraz)
{
  global $mimetex,$jsmath,$vsechno,$prom,$novafce;
   $temp=str_replace("I","($vyraz)",$vsechno);
   $vsechno=maxima_command("$temp");
   $novafce=$vsechno;
   $celyvysledek=chop(math_to_tex($vsechno));
   if ($jsmath=="on")
   {
   return(" <span class=\"math\">\\displaystyle{I=$celyvysledek}</span> ");
   }
   else
   {
   return(" <img src=\"$mimetex"."I=$celyvysledek\" style=\" padding: 8px; vertical-align: middle \" alt=\"math formula\"> ");
   }
}

function maxima_to_html_mimetex($vyraz,$pred,$za)
{
  global $mimetex,$jsmath;
  return(" <img src=\"$mimetex".$pred.chop(math_to_tex($vyraz)).$za."\" align=\"center\" alt=\"math formula\"> ");
}

function tex_to_html($vyraz)
{
  global $mimetex,$jsmath;
  if ($jsmath!="on")
  {
  return(" <img src=\"$mimetex".$vyraz."\" style=\"padding: 8px; vertical-align: middle \" alt=\"math formula\"> ");
  }
  else
  {
  return(" <span class=\"math\">\\displaystyle{".$vyraz."}</span> ");
  }
}


maw_html_headB();

echo '<h2>'.__('Integral methods assistant').'</h2>';

//echo ($maw_processing_msg);
//ob_flush();
//flush();
//if (function_exists("maw_after_flush")) {echo(maw_after_flush());}

function hide_message($show_gray_comment=0)
{
  $a='
<script  type="text/javascript">document.getElementById("processing").style.display = "none";';
  if ($show_gray_comment==0)
  { $a=$a.'document.getElementById("gray_comment").style.display = "none";';}
  return $a.'</script>
';
}

echo ("\n".'<div class="gray">');
echo ("\n".'<div id="gray_comment">');
if ($krok==1)
  {
    //echo '<b>'.__("If you can't see mathematical expressions, return to the previous page and change the method of rendering mathematics (the second checkbox).").'</b>';

    $link='<a href="'.$mawhtmlhome.'/index.php?form=integral&lang='.$lang;
    if ($oriprom=="x") {$link=$link."&function=".rawurlencode($orifce);}
    $link=$link.'">';
    echo sprintf(__("<ul><li> Choose the method for the next computation.  </li> <li> Try to find the methods which simplify the integral. If your attempt fails, you can return using Back button in you browser. You can also return directly to the %s initial form%s.</li><li>You can use computer to finish the integration. Educational value of such a this step is minimal, so use this possibility with care, please.</li><li>Questions marks |<b>?</b>| provide preview what you get when using this function. Simply move the cursor over the question mark and wait few seconds.</li><li>If the application gives incorrect or unexpected results, let us know please.</li></ul><hr>"),$link,"</a>");
    echo '<hr>';	

   }

if ((ae_detect_ie()) && ($krok==1) ){echo "\n".'<div class=\'ie_warning\';>',__("If the rendering of the formulas is bad, consider another browser than Internet Explorer. The application has been tested with <a href=\"http://www.mozilla.com/firefox/\">Firefox</a>."),'</div>';}


function preview_result($string)
{
  global $vystupmaximy,$texrender;
  $vystupmaximy2=str_replace("\n","",$vystupmaximy);
  $pattern='/TeX '.$string.'(.*?)XeT/';
  preg_match($pattern,$vystupmaximy2,$vystup);
  $result=(str_replace("$","",$vystup[1]));
  return(" [<b><a  onmouseover=\"return overlib('<img src=$texrender".rawurlencode(formconv_replacements($result)).">', WRAP, FGCLASS,'olfgmath');\" onmouseout=\"return nd();\">?</a></b>]&nbsp;&nbsp;");
}

function preview_result_subst($num)
{
  global $vystupmaximy,$texrender,$newvariable_hint;
  $vystupmaximy2=str_replace("\n","",$vystupmaximy);
  $pattern='/TeX substresults (.*?)XeT/';
  preg_match($pattern,$vystupmaximy2,$vystup);
  $result=(str_replace("\\\$","",$vystup[1]));
  $result=(str_replace("$$\\left[","",$result));
  $result=(str_replace("$$ \\left[","",$result));
  $result=(str_replace("\\right] $$","",$result));
  $result=(str_replace("\\$","",$result));
  $result=str_replace("\\mbox{{}`solve' is using arc-trig functions to get a solution.Some solutions will be lost.","{",$result);
  $result=str_replace("\\mbox{{}","{",$result);
  $result=(str_replace("$","",$result));
  $one_result=split(" , ",$result);
  return("[<b><a  onmouseover=\"return overlib('<img src=$texrender\\\\int{}".rawurlencode(formconv_replacements($one_result[$num]))."\\\\;\\\\text{d}".$newvariable_hint.">', WRAP, FGCLASS,'olfgmath');\" onmouseout=\"return nd();\">?</a></b>]&nbsp;&nbsp;&nbsp;");
}

echo ("\n".'</div>');
echo ('</div>'."\n");

if ($krok==1)
{
  $handle = fopen($maw_tempdir."/".$krok.".html","w");
  maxima_session_comment("initial call - pass function through Maxima");
  $novafce=maxima_command($fce,"","matchint_short");
  $ret=__("We integrate")." ".maxima_to_html($novafce,"I=\\int ","\\,\\textrm{d}".$prom)."<br>";
  fwrite ($handle, "\n".$ret."<br>\n");
  fclose($handle);
}
else
{
  if ($akce!="dokonceni")
    {
      $novafce=vypocitej($akce,$fce,$prom);
    }
  else
    {
      if ($postakce=="")
	{
           maw_html_headB();
	   maw_integral_error();
	   die (sprintf("\n<div class=\"h3_error\">%s</div>%s",__("Error"),__("Choose the action for the next computation")).hide_message());
	}
      elseif ($postakce!="substituce")
	{
	  $novafce=uprav($postakce,$vsechno);
	}
      else
	{
	  $novafce=uprav($postakce,$vsechno);
	}
    }
  if (($akce=="i")||($akce=="equation")) {$novafce=$vsechno;}
}

if (($akce=="dokonceni") && ($postakce=="konec"))
  {
    for ($i=1;$i<=$krok-1;$i++)
      {
	readfile("$maw_tempdir/".$i.".html");
      }
    if($jsmath=="on") 
      {
	echo("<SCRIPT type=\"text/javascript\" >jsMath.Process(document);</SCRIPT>");
      }
//       echo '<pre>',$vystupmaximy;
    die(hide_message());
  }

for ($i=1;$i<=$krok;$i++)
  {
    readfile("$maw_tempdir/".$i.".html");
  }


$krok=$krok+1;
echo $vypocet;

if (($akce=="dokonceni")&&($postakce=="float")) 
{
  echo '<div id="form"  style="display:block;"><form name="exampleform" method="',$metoda,'" action="integral.php"'.$onsubmit.'>';
  echo '<input name="adresar"  type="hidden" value="',$maw_tempdir,'">';
  echo '<input name="krok" type="hidden" value="',$krok,'">'; 
  echo '<input name="jsmath"  type="hidden" value="',$jsmath,'">';
  echo '<p style="text-align:right;">
  <input value="',__("Download html"),'" name="tlacitko" type="submit" class="tlacitko tlacitko_html" >
<input value="',__("Build PDF"),'" name="tlacitko" type="submit" class="tlacitko" >
  </p></form></div>';
  maw_after_form();

  if($jsmath=="on") 
      {
	echo("<SCRIPT type=\"text/javascript\">jsMath.Process(document);</SCRIPT>");
      }
//       echo '<pre>',$vystupmaximy;
    
  print_time_and_computations();
  die(hide_message()."\n</body></html>");
}

if (($akce=="i") ||($akce=="equation") || ($akce=="dokonceni")) 
  {
    maxima_session_comment("trying algebraic modifications - part 1");
    $testfunkci=maxima_command("(errcatch(try_functions($novafce)))","","matchint_short");
    maxima_session_comment("trying algebraic modifications - part 2");
    $testfunkci=$testfunkci.maxima_command("(errcatch(try_float($novafce)))","","matchint_short");

    echo '<br><br><br><div id="form" style="display:block;">
<form name="exampleform" ',$onsubmit,' 
method="',$metoda,'" action="integral.php">
<fieldset class="vnitrni">
<legend> ',__("Final simplifications"),'</legend>';
    echo '<i>',__("You may want to continue and simplify your result ..."),'</i><br>';
    echo '<input name="akce" value="dokonceni" type="hidden">
<input name="krok" type="hidden" value="',$krok,'">
<input name="funkce" type="hidden" value="',$novafce,'">
<input name="prom"  type="hidden" value="',$prom,'">
<input name="secvar"  type="hidden" value="',$secvar,'">
<input name="oriprom"  type="hidden" value="',$oriprom,'">
<input name="oriproblem"  type="hidden" value="',$oriproblem,'">
<input name="allprom"  type="hidden" value="',$allprom,'">
<input name="adresar"  type="hidden" value="',$maw_tempdir,'">
<input name="jsmath"  type="hidden" value="',$jsmath,'">
<input name="vsechno"  type="hidden" value="',$novafce,'">
<input name="lang"  type="hidden" value="',$lang,'">
<input name="post"  type="hidden" value="',$post,'">
<input name="pfeformat"  type="hidden" value="',$pfeformatswitch,'">
<input name="logarc"  type="hidden" value="',$logarcswitch,'">
<input name="defint"  type="hidden" value="',$defint,'">
<input name="backsubst"  type="hidden" value="',$backsubst,'">
'; 

    if ($formconv=="on") 
      {
	echo ' <input name="formconv" type="hidden" value="on">';
      }

    echo '<input name="postakce" value="radcan" type="radio" checked="checked">
',preview_result("radcan"),__("radcan"),'<br>';
    
    echo '<input name="postakce" value="expand" type="radio">
',preview_result("expand"),__("expand"),'<br>';
    
    echo '<input name="postakce" value="factor" type="radio">
',preview_result("factor"),__("factor"),'<br>';
    
    echo '<input name="postakce" value="fullratsimp" type="radio">
',preview_result("fullratsimp"),__("ratsimp"),'<br>';
    
    echo '<input name="postakce" value="mapfullratsimp" type="radio">
',preview_result("mapfullratsimp"),__("map + fullratsimp"),'<br>';
    
    echo '<input name="postakce" value="xthru" type="radio">
',preview_result("xthru"),__("xthru"),'<br>';
    
    echo '<input name="postakce" value="mapxthru" type="radio">
',preview_result("mapxthru"),__("map + xthru"),'<br>';
    
    if (ereg("cos|sin|tan|cot|sec|csc",$novafce))
      {
	echo '<input name="postakce" value="trigexpand" type="radio">
',preview_result("trigexp"),__("trigexpand"),'<br>';
	
	echo '<input name="postakce" value="trigsimp" type="radio">
',preview_result("trigsimp"),__("trigsimp"),'<br>';
	
	echo '<input name="postakce" value="trigreduce" type="radio">
',preview_result("trigreduce"),__("trigreduce"),'<br>';
      }
    
    if (ereg("asinh|acosh|atanh|acoth|asech|acsch",$novafce))
      {
	echo '<input name="postakce" value="logarc" type="radio">
',preview_result("logarc_"),__("logarc"),'<br>';	
      }
    
    if (ereg("log",$novafce))
      {
	echo '<input name="postakce" value="logcontract" type="radio">
',preview_result("logcontract"),__("logcontract"),'<br>';
      }
    
    if (ereg("sqrt",$novafce))
      {
	echo '<input name="postakce" value="rootscontract" type="radio">
',preview_result("rootscontract"),__("rootscontract"),'<br>';
      }
    
    if ($prom!=$oriprom)
      {
	echo '<input name="postakce" value="zpetna_substituce" type="radio" checked=\"checked\">
',__("substitution")." ";
	if (($backsubst!="")&&($backsubst!="NotAvailable")) 
	  {
	    echo "<br>&nbsp;&nbsp;<input name=\"s_final\" value=\"hint\" type=\"radio\" checked=\"checked\">",__("our hint").": ",maxima_to_html("$backsubst","",""),"<br>&nbsp;&nbsp;";
	  }
	echo "<input name=\"s_final\" value=\"user\" ";
	if (($backsubst!="")&&($backsubst!="NotAvailable"))
	  {
	    echo ("type=\"radio\" >".__("user expression").": ");
	  }
	else
	  {
	    echo ("checked=\"checked\" type=\"hidden\">");
	  }
	echo $prom,'=<input name="substituce" value=""> ', __("where the new variable is"),' <input name="novapromenna" value="',$oriprom,'" size="4" maxlength="1"><br>&nbsp;&nbsp;&nbsp;',__("Warning: your input is not checked against the substitution used during evaluation of the integral. If you use incorrect substitution, your result will be incorrect."),'<br>';
      }
    else
      { 
	if ($defint=="")
	  {  
	    echo '<input name="postakce" value="newton-leibniz" type="radio">',__("substitute limits")."<br>";
	    echo "&nbsp;&nbsp;&nbsp;".__("lower limit for")." $prom: ",'<input name="limit_a" value=""><br>';
	    echo "&nbsp;&nbsp;&nbsp;".__("upper limit for")." $prom: ",'<input name="limit_b" value=""><br>';
	  }
      }

    if ($defint!="")
      {
	echo '<input name="postakce" value="float" type="radio">
',preview_result("floatint"),__("numerical approximation"),'<br>';
      }

    echo '<hr><input name="postakce" value="konec" type="radio">',__("finished, show computations only").'<br>'; 
    
    echo '</fieldset><br>';
    echo '<input value="',__("Submit"),'" name="tlacitko" type="submit" class="tlacitko" id="myButton">
<script type="text/javascript"> document.getElementById("myButton").focus();scroll(0,0);</script>
<p style="text-align:right;"><input value="',__("Build PDF"),'" name="tlacitko" type="submit" class="tlacitko" >
<input value="',__("Download html"),'" name="tlacitko" type="submit" class="tlacitko tlacitko_html" ></p>
</form></div>';
    maw_after_form();

    
    if (($akce=="dokonceni"))
      {
	save_log(maxima_to_html_mimetex($orifce,"","")." akce:".$akce." ".$substituce.savekey($maw_tempdir),"integral");
      }
    else
      {
	save_log(maxima_to_html_mimetex($orifce,"\\int ","\\, d $prom")." dokoncit".savekey($maw_tempdir),"integral");
      }
    
    
    if($jsmath=="on") 
      {
	echo("<SCRIPT type=\"text/javascript\">jsMath.Process(document);</SCRIPT>");
      }
//       echo '<pre>',$vystupmaximy;

    print_time_and_computations();
    die(hide_message()."\n</body></html>");
  }

/* maxima_session_comment("??? ??? ???"); */
/* $nic=maxima_command($novafce);   */

$hintnum=0;

function hint_bb($number)
{global $hintnum;
  if ($number==$hintnum) {return ("<b>");} else {return("");}
}

function hint_be($number)
{global $hintnum;
  if ($number==$hintnum) {return ("</b>");} else {return("");}
}

function check_hint($number)
{global $hintnum;
  if ($number==$hintnum) {return ("checked=\"checked\"");} else {return("");}
}

function najdiretezec($klicoveslovo,$retezec)
{
  $retezec=str_replace("\n","",$retezec);
  preg_match("/\#\#\# *".$klicoveslovo." (.*?)(\(%o|\#\#\#)/",$retezec,$matches);
  $vystup=$matches[0];
  $vystup=str_replace("### ".$klicoveslovo, "", $vystup);
  $vystup=str_replace("(%o", "", $vystup);
  $vystup=str_replace("###", "", $vystup);
  $vystup=str_replace(" ", "", $vystup);
  return ($vystup);
}


function convert_numberformula_to_formula($number)
{ global $jsmath, $texred;
  $hints_formulas=Array("polynom",
			"\\int\\sin(x)",
			"\\int\\cos(x)",
			"\\int \\frac{1}{\\sin^2(x)}",
			"\\int \\frac{1}{\\cos^2(x)}",
			"\\int x^n",
			"\\int e^x",
			"\\int \\frac1{A^2+x^2}",
			"\\int \\frac{1}{\\sqrt{A^2-x^2}}",
			"\\int \\frac{1}{x}",
			"\\int \\frac1{A^2-x^2}",
			"\\int \\frac{1}{\\sqrt{x^2+B}}",
			"\\int\\sin^2x\\text{d}x=\\int\\frac{1-\\cos(2x)}{2}",
			"\\int\\cos^2x\\text{d}x=\\int\\frac{1+\\cos(2x)}{2}",
			"\\int a^x",
			"\\int\\sinh(x)",
			"\\int\\cosh(x)"			
			);
  if ($jsmath!="on"){$output="\\usepackage{color}\\color{"."$texred}\\small";}
  $output=$output.$hints_formulas[$number+0]."\\,\\text{d}x";
  return($output);
}

function hint_formula($maximaoutput)
{
  global $completesquare_frac,$completesquare_asin;
  $completesquare_asin=0;
  $completesquare_frac=0;
  $formula_number=str_replace("### testformula ","", najdiretezec("testformula",$maximaoutput));
  if ($formula_number==0) {return (__("integral of polynomial or constant"));}
  $formula_to_be_used=convert_numberformula_to_formula($formula_number);
  $linear_inside_part="";
  if (ereg("### formulawithlinear",$maximaoutput)) 
    {
      $linear_inside_part=" ".__("with linear inside part (you can use also substitution)");
    } 
  if ((($formula_number==7)||($formula_number==10)) && 
      (ereg("### completesquare",$maximaoutput)))
    {
      $linear_inside_part="&nbsp;&nbsp;&nbsp;&nbsp; ".__("(with completing square in denominator)");
      $completesquare_frac=1;	
    }
  if ((($formula_number==8)||($formula_number==11))  && 
      (ereg("### completesquare",$maximaoutput)))
    {
      $linear_inside_part="&nbsp;&nbsp;&nbsp;&nbsp;".__("(with completing square under square root)");
      $completesquare_asin=1;
    }
  if (($formula_number==13)||($formula_number==12))  
    {
      $linear_inside_part="&nbsp;&nbsp;&nbsp;&nbsp;".__("(the trigreduce function yields this step)");
    }
  $pf="";
  if ($formula_number==10) {$pf=__("partial fractions or")." ";}
  return($pf.__("formula").tex_to_html($formula_to_be_used,"","").$linear_inside_part);
}

$constmul_hint="";
$multhru_hint="1";
$u_hint="";
$v_hint="";
$substitution_hint="";
$backsubstitution_hint="";
$newvariable_hint="";

function get_new_variable()
{
  global $allprom,$prom;
  if (!(ereg("t",$allprom.$prom))) {return("t");}
  if (!(ereg("p",$allprom.$prom))) {return("p");}
  if (!(ereg("q",$allprom.$prom))) {return("q");}
  if (!(ereg("r",$allprom.$prom))) {return("r");}
  if (!(ereg("s",$allprom.$prom))) {return("s");}
  if (!(ereg("g",$allprom.$prom))) {return("g");}
  if (!(ereg("h",$allprom.$prom))) {return("h");}
  return("?");
}

function write_hints_for_substitution()
{
  global $substitution_hint, $newvariable_hint,$backsubstitution_hint;
  $temp_array=split(",",$substitution_hint);
  $temp_backarray=split(",",$backsubstitution_hint);
  if ($substitution_hint=="") {return("");}
  $output="\n<li><input name=\"novapromennahint\" value=\"$newvariable_hint\" type=\"hidden\"></li>";
  foreach ($temp_array as $i => $value)
    {
      $output=$output."\n<li><input name=\"substitucehint\" value=\"$value BACK $temp_backarray[$i]\" type=\"radio\"";
      if ($i==0) {$output=$output." checked=\"checked\"";}
      $output=$output.">".preview_result_subst($i).maxima_to_html($value,"","")."&nbsp;&nbsp;&nbsp;".__("and back substitution is")." ".$temp_backarray[$i]."</li>";
      //$output=$output.">".preview_result_subst($i).maxima_to_html($value,"","")."</li>";
    }
  $output=$output."\n<li><input name=\"substitucehint\" type=\"radio\" value=\"\"> ".__("custom").": ";
  return($output);
}

function addhint($stuff)
{
  return ("\n<li>".$stuff."</li>");
}

function clean_substitution_hint($substitution_hint)
{
  $substitution_hint=str_replace(" "," ",$substitution_hint);
  $substitution_hint=str_replace("[\"","",$substitution_hint);
  $substitution_hint=str_replace("[","",$substitution_hint);
  $substitution_hint=str_replace("\"]","",$substitution_hint);
  $substitution_hint=str_replace("]","",$substitution_hint);
  $substitution_hint=str_replace("\",\"",",",$substitution_hint);
  $substitution_hint=str_replace("\\","",$substitution_hint);
  $substitution_hint=str_replace("?","",$substitution_hint);
  $substitution_hint=str_replace("\"","",$substitution_hint);
  return($substitution_hint);
}

$maxima_total_runtime_hint=0;

function maxima_hint($funkce)
{ global $mawhome,$hintnum,$all_hints2,$prom,$jsmath,$constmul_hint,$u_hint,$v_hint,$substitution_hint,$backsubstitution_hint,$newvariable_hint, $multhru_hint,$operace,$oriprom,$vsechno,$oriproblem,$krok, $vsechno,$maximainit,$maxima_total_runtime,$maxima_total_calls,$maxima_total_runtime_hint,$maxima, $mawtimeout,$texred, $maxima_time_last_call;
  $hintoutput="";
  $know_what_to_do=0;  
  $input_maxima="yes 'pos;' | $mawtimeout $maxima --batch-string=\"($maximainit maw_var:$prom, maw_var_ori:$oriprom); load(\\\"$mawhome/integral/matchint.mac\\\");simp:false;logabs:false;test_fprime_over_f($funkce);simp:true;testpart(factor($funkce));testrlf($funkce);testrlfimproper($funkce);testmultiple($funkce);testformula($funkce);test_expand_into_formulas($funkce);testsubst($funkce);test_ostrogradski_method(rootscontract($funkce));testrlfxthru($funkce);load(ntrig);testpartfrac($funkce);\"";
  $vystupmax=do_maxima($input_maxima);
  $maxima_total_runtime_hint=$maxima_time_last_call;
  $all_hints2=$all_hints2.$vystupmax;
  $constmul_hint=str_replace("###constmul ","", najdiretezec("constmul",$vystupmax));
  $u_hint=str_replace("### u ","",najdiretezec("u",$vystupmax));
  $v_hint=str_replace("### v ","",najdiretezec("v",$vystupmax));
  $newvariable_hint=get_new_variable();
  $hintnum=0;
  if ($jsmath!="on")
    {
      $preamble="\\usepackage{color}\\color{"."$texred}\\small";
    }

  if (ereg("### twotimesbyparts",$vystupmax))
    {
      $texthint="\n".__("<ul><li>integrate two times by parts and keep the choice of u and v' suggested by this calculator</li><li>then remove constant multiple (if necessary) and you get some terms and the same integral as the original one</li><li>convert the problem into an algebraic equation where the integral is the unknown and solve this equation</li></ul>");
      if ($krok==2) 
	{ 
	  $tempbf="<b>".__("Read carefully the method and ignore other suggestions, such as substitution").": "; 
	  $tempbfe="</b>";
	}
      else
	{
	  $tempbf=__("Method").": "; 
	  $tempbfe="";
	}
      
      $hintoutput=$hintoutput.addhint($tempbf.$texthint.$tempbfe);
    }

  if (ereg("abs",$funkce)){
    $hintoutput=$hintoutput. addhint(__("absolute value causes problems in integration, but in most cases can be eliminated by considering suitable subinterval for integration."));
    $know_what_to_do=1;
  }
  
  /*  intergral 1/(13+12*sin(x))   */
  if ((ereg("### testformula (7|11)",$vystupmax))&&  (ereg("### xthru",$vystupmax)))
    {
      $hintoutput=$hintoutput.addhint(__("simplify the fractions using xthru or fullratsimp"));       
    }    

  if (ereg("### substhints",$vystupmax))
    { 
      if ($hintnum==0) {$hintnum=4;}
      $substitution_hint=str_replace("### substhints ","",najdiretezec("substhints",$vystupmax));
      $substitution_hint=str_replace("new_maw_var",$newvariable_hint,$substitution_hint);
      $substitution_hint=str_replace("maw_var",$prom,$substitution_hint);
      $backsubstitution_hint=str_replace("### backsubst ","",najdiretezec("backsubst",$vystupmax));
      $backsubstitution_hint=str_replace("new_maw_var",$newvariable_hint,$backsubstitution_hint);
      $backsubstitution_hint=str_replace("maw_var",$prom,$backsubstitution_hint);
      if ((ereg("### testsubstoddwrtsin",$vystupmax)) && (ereg("### testsubstoddwrtcos",$vystupmax)))
	{
	  $hintoutput=$hintoutput.addhint(__("substitution (the function is odd with respect to both sin and cos)"));
	}
      elseif (ereg("### testsubstoddwrtsin",$vystupmax))
	{
	  $hintoutput=$hintoutput.addhint(__("substitution (the function is odd with respect to sin)"));
	  $know_what_to_do=1;
	}
      elseif (ereg("### testsubstoddwrtcos",$vystupmax))
	{
	  $hintoutput=$hintoutput.addhint(__("substitution (the function is odd with respect to cos)"));
	  $know_what_to_do=1;
	}
      elseif (ereg("### testsubstevenwrtsincos",$vystupmax))
	{
	  $hintoutput=$hintoutput.addhint(__("substitution"));
	  $know_what_to_do=1;
	}
      elseif (ereg("### testsubsttrigsimphint",$vystupmax))
	{
	  $hintoutput=$hintoutput.addhint(__("substitution (use trigsimp function in the next step after goniometric substitution)"));
	}
      else
	{
	  $hintoutput=$hintoutput.addhint(__("substitution"));
	}
    }

  if (ereg("### testformula",$vystupmax)) 
    { 
      $hintnum=5; 
      $hintoutput=$hintoutput.addhint(hint_formula($vystupmax)); 
      $substitution_hint=str_replace("### substhints ","",najdiretezec("substhints",$vystupmax));
      $substitution_hint=str_replace("new_maw_var",$newvariable_hint,$substitution_hint);
      $substitution_hint=str_replace("maw_var",$prom,$substitution_hint);
      $substitution_hint=clean_substitution_hint($substitution_hint);
      $backsubstitution_hint=clean_substitution_hint($backsubstitution_hint);
      $know_what_to_do=1;
      return("\n<ul>\n".$hintoutput."\n</ul>\n");
    }

  if (ereg("### expands_into_formulas",$vystupmax))
    {
      $hintnum=9;
      $hintoutput=addhint(__("the function can be expanded into sum of formulas")).$hintoutput;
    }

  if (ereg("### divides_into_formulas",$vystupmax))
    {
      $hintnum=10;
      $hintoutput=addhint(__("the function can be divided into sum of formulas")).$hintoutput;
      $know_what_to_do=1;
    }
  
  if (ereg("### xthru",$vystupmax)) 
    { 
      $hintnum=6; 
      $hintoutput=$hintoutput.addhint(__("simplify fraction"));
    }
  
  if ((ereg("### logofdenom",$vystupmax)) /* && ($operace=="podil") */)
    { 
      $hintnum=5; 
      $hinttext=__("formula").tex_to_html($preamble."\\int\\frac {f'(x)}{f(x)}\\,\\text{d}x","","");
      $multhru_hint=str_replace("### multhruhint ","",najdiretezec("multhruhint",$vystupmax));
      if ($multhru_hint=="") {$multhru_hint=1;}
      if (($multhru_hint!="1")&&($operace=="podil"))
	{      
	  $hinttext=$hinttext." ".__("(you have to write the fraction in the form which matches the formula, consider multiplying both numerator and denominator by a convenient expression)");
	  $hintnum=8;
	}
      if (str_replace(" ","",$constmul_hint)!="1")
	{
	  $hinttext=$hinttext." ".__("(you can remove a constant multiple from the integral to match the formula exactly)") ;
	}
      $hintoutput=$hintoutput.addhint($hinttext); 
      $know_what_to_do=1;
    }

  if ((ereg("### rlftwofracs",$vystupmax)) && ($hintnum!=5))
    { 
      $hintnum=7; 
      $hintoutput=$hintoutput.addhint(sprintf(__("split in a clever way into two fractions of the form %s and use formulas for integration (ignore also the hint in the next step and finish the integration by Maxima)"),tex_to_html($preamble."\\int \alpha\\frac {f'(x)}{f(x)}+\\beta \\frac{1}{A^2+x^2}\\,\\text{d}x","",""))); 
      $know_what_to_do=1;
      return("\n<ul>\n".$hintoutput."\n</ul>\n");
    }

  if (ereg("### testbypart",$vystupmax)) 
    {
      $hintnum=1;
      $hintoutput=$hintoutput.addhint(__("integration by parts"));
      $know_what_to_do=1 ;
    }

  if (ereg("### testrlfimproper",$vystupmax)) 
    {
      $hintnum=2;
      $hintoutput=$hintoutput.addhint(__("divide polynomials or partial fractions"));
      $know_what_to_do=1;
    }

  if (ereg("### testpartfrac",$vystupmax)) 
    {
      if ($hintnum!=6)  {$hintnum=3;}
      $hintoutput=$hintoutput.addhint(__("expand to partial fractions"));
      $know_what_to_do=1;
    }

 
  if (ereg("### ostrogradski",$vystupmax)) 
    {
      $hintnum=5;
      $degree_of_pol=str_replace("### ostrpoldegree ","",najdiretezec("ostrpoldegree",$vystupmax));
      $ostrroot="\\sqrt{".formconv(str_replace("### ostrroot ","",najdiretezec("ostrroot",$vystupmax)))."}";
      if ($jsmath!="on")
	{
	  $form_of_primitive_function="\\usepackage{color}\\color{"."$texred}\\small";
	}
      $form_of_primitive_function=$form_of_primitive_function.(" I=P($prom)$ostrroot+\\int\\frac{k}{ $ostrroot}\\,\\text{d}$prom");
      $hintoutput=$hintoutput.addhint(sprintf(__("You can use method of undetermined coefficients: the primitive function has the form %s, where %s is %s and k is real number. The application of this method in steps is not covered by this application and from this reason you should leave the rest to Maxima and evaluate the integral by computer. It is also possible in some integrals of this type to remove the square root by a convenient substitution."),tex_to_html($form_of_primitive_function),"P($prom)",convert_degree_to_words($degree_of_pol)));
      $know_what_to_do=1;
    }

  if (ereg("[^a](tan|cot|sec|csc)",$funkce))
    {
      $hintoutput=$hintoutput.addhint(__("you can convert the function to sine and cosine functions with the <b>trigsimp</b> command")) ;
    }
  
  if (str_replace(" ","",$constmul_hint)!="1")
    {
      $hintoutput=$hintoutput.addhint(__("you can remove a constant multiple from the integral")) ;
    }

  if (($hintoutput=="") && (ereg("### testrlf",$vystupmax)))
    {
      $hintnum=5;
      $hintoutput=$hintoutput.addhint(__("rational function, but expansion into partial fractions failed (it could be necessary to simplify first (radcan, xthru, ...), or the function could be a <a href=\"http://en.wikipedia.org/wiki/Partial_fractions_in_integration#A_repeated_irreducible_2nd-degree_polynomial_in_the_denominator\">partial fraction with repeated irreducible 2nd-degree polynomial in the denominator</a>)")) ;
    }

  if (//(ereg("e|log",$funkce)) && (ereg("sin|cos",$funkce)) && 
      (($know_what_to_do==0)||(ereg("#### twotimesbyparts", $vystupmax))) && 
      ($prom==$oriprom) && ((ereg("true",maxima_command("freeof($prom,radcan(($funkce)/($oriproblem))) and not freeof(I,radcan($vsechno*radcan(($funkce)/($oriproblem))-I))","","matchint_short")))))
    {
      $ttt=str_replace("\n","",$vsechno);
      $ttt=str_replace(" ","",$ttt);
      if (($ttt!="I"))
	{
	  $ttt=maxima_command("is (equal($funkce,$oriproblem)) or zeroequiv($funkce-($oriproblem),$prom) or constantp(radcan(($funkce)/($oriproblem)))");
	  if (ereg("true",$ttt))
	    {        
	      $hintnum=11;
	      $hintoutput=$hintoutput.addhint(__("convert into equation for the integral")) ;
	    }
	}
    }

  if (ereg("#### trigfunctions_with_different_arguments",$vystupmax)) 
    { 
      if ($hintoutput=="")
	{
	  $hintoutput=$hintoutput.addhint(__("trigonometric functions have different inside parts, you can try to fix it by <b>trigexpand</b> or <b>trigreduce</b> command")) ;
	}
    }

  if ($hintoutput=="") {return("");}

  $substitution_hint=clean_substitution_hint($substitution_hint);
  $backsubstitution_hint=clean_substitution_hint($backsubstitution_hint);
  return("\n<ul>\n".$hintoutput."\n</ul>\n");
}

function convert_degree_to_words($n)
{
  $n=str_replace(" ","",$n);
  if ($n==0) {return(__("constant"));}
  elseif ($n==1) {return(__("linear polynomial"));}
  elseif ($n==2) {return(__("quadratic polynomial"));}
  elseif ($n==3) {return(__("cubic polynomial"));}
  else {return(sprintf(__("%s degree polynomial"),$n));}  
}


$hint=maxima_hint($novafce);

if ($hint!="")
  {
    echo '<span class="hint">',__("Our hint: (Some automatical hints based on few heuristic tests. Do not follow these hints blindly and use your mind instead.)"),$hint,'</span>';
  }


echo '<div id="form" style="display:block;"><form name="exampleform" ',$onsubmit,'
method="',$metoda,'" action="integral.php">
';

echo '
<fieldset  class="vnitrni"><legend class="podnadpis">';
echo (__("Next computation"));
echo'</legend>
<i>',__("(use radiobuttons to choose the next computation method)"),'</i><br><br>
';

$substitution_hint=clean_substitution_hint($substitution_hint);
$backsubstitution_hint=clean_substitution_hint($backsubstitution_hint);

maxima_session_comment("Results of suggested substitutions");
$blackhole=maxima_command("(assume_pos:true,load(\\\"$mawhome/common/changevar2.mac\\\"),try_substitutions(expr):=((trigexpand(rootscontract((savelogarc:logarc,logarc:false,A:diff(changevar2('integrate($novafce,$prom),expr,".$newvariable_hint.",$prom),".$newvariable_hint."),logarc:savelogarc,A))))),tem:map(try_substitutions,[".$substitution_hint."]),maw_print_one_try(tex(tem,false),substresults))", " yes 'pos;' | ","matchint_short");

maxima_session_comment("looking which algebraic modification changes the function");
$testfunkci=maxima_command("(outf:[],add(f):=(print (f),outf:append(outf,[f])),if (expand($novafce)#$novafce) then add(\"expand\"),if (factor($novafce)#$novafce) then add(\"factor\"),if (fullratsimp($novafce)#$novafce) then add(\"fullratsimp\"),if (ratsimp($novafce)#$novafce) then add(\"ratsimp\"),if (xthru($novafce)#$novafce) then add(\"xthru\"),if (radcan($novafce)#$novafce) then add(\"radcan\"),if (logarc($novafce)#$novafce) then add(\"logarc_\"),if (rootscontract($novafce)#$novafce) then add(\"rootscontract\"),errcatch(try_functions($novafce)),outf)","","matchint_short");

if (($operace=="podil")||($hintnum==2)||($hintnum==3))
  {
    echo hint_bb(10),hint_bb(2),'<input name="akce" value="dp" type="radio" ',check_hint(2),check_hint(10),'>
',preview_result("divide"),__("division"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_14.html#Item_003a-divide">divide</a>)',hint_be(2),hint_be(10),'<br>';
  }

if (($hintnum==2)||($hintnum==3)||ereg("### testpartfrac",$all_hints2))
  {
    echo hint_bb(3),'
<input name="akce" value="p" type="radio"',check_hint(3),'> 
',preview_result("partfrac"),__("partial fractions"),' (partfrac)',hint_be(3),'<br>';
  }

echo '<input name="akce" value="v" type="radio"> 
',__("move a number from the integral"),' <input name="cislo" value="',$constmul_hint,'" size="18"><br>';

if ($operace=="podil")
  {
    echo hint_bb(8),'<input name="akce" value="multhru" type="radio"',check_hint(8),'> ',__("multiply both numerator and denominator"),hint_be(8),' <input name="rozsirit" value="',$multhru_hint,'" size="20">';
    echo " (",__("and simplify"),'<input name="rozsiritradcan" type="checkbox">)<br>';
  }

echo '<br><hr>';

if (ereg("radcan",$testfunkci))
  {
    echo '
<input name="akce" value="rad" type="radio"> 
',preview_result("radcan"),__("simplify"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_9.html#Item_003a-radcan">radcan</a>)','
<br>';
  }

if (ereg("expand",$testfunkci))
  {
    echo hint_bb(9),'
<input name="akce" value="e" type="radio"',check_hint(9),'> 
',preview_result("expand"),__("expand"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_9.html#Item_003a-expand">expand</a>)',hint_be(9),'<br>';
  } 

if (ereg("factor",$testfunkci))
  {
    echo '
<input name="akce" value="f" type="radio"> 
',preview_result("factor"),__("factorize"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_14.html#Item_003a-factor">factor</a> ',__("(not very useful in most cases)"),')','<br>';
  }

if (ereg("logarc_",$testfunkci))
  {
    echo '
<input name="akce" value="logarc_" type="radio"> 
',preview_result("logarc_"),__("asinh(), acosh(), atanh(), .... -> log()"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_10.html#Item_003a-logarc">logarc</a>)','<br>';
  }

if (ereg("fullratsimp",$testfunkci))
  {
    echo '
<input name="akce" value="fs" type="radio"> 
',preview_result("fullratsimp"),__("simplify fraction"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_14.html#Item_003a-fullratsimp">fullratsimp</a>)','<br>
<input name="akce" value="fsmap" type="radio"> 
',preview_result("mapfullratsimp"),__("simplify fraction, each part separately"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_37.html#Item_003a-map">map</a>,<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_14.html#Item_003a-fullratsimp">fullratsimp</a>)','
<br>
';
  }

if (ereg("xthru",$testfunkci))
  {
    echo hint_bb(6),'<input name="akce" value="x" type="radio" ',check_hint(6),'> 
',preview_result("xthru"),__("simplify fraction"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_9.html#Item_003a-xthru">xthru</a>)',hint_be(6),'
<br><input name="akce" value="xmap" type="radio"> 
',preview_result("mapxthru"),__("simplify fraction, each part separately"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_37.html#Item_003a-map">map</a>,<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_9.html#Item_003a-xthru">xthru</a>)','
<br>
';
  }

if (ereg("sqrt",$novafce) && (ereg("rootscontract",$testfunkci)))
{
echo '<input name="akce" value="roots" type="radio">
',preview_result("rootscontract"),__("contract products of roots"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_20.html#Item_003a-rootscontract">rootscontract</a>)',' 
<br>';
}


if (ereg("[^a](cos|sin|tan|cot|sec|csc)",$novafce))
  {
    $testfunkci=maxima_command("(outf:[],add(f):=(print (f),outf:append(outf,[f])),if (trigsimp($novafce)#$novafce) then add(\"trigsimp\"),if (trigreduce($novafce)#$novafce) then add(\"trigreduce\"),if (trigexpand($novafce)#$novafce) then add(\"trigexp\"),matchdeclare(maw_sin2,true), let ([sin(maw_sin2)^2, 1 - cos(maw_sin2)^2],sin), if (ratsimp(((letsimp((letsimp($novafce,sin))/sin(x),sin)))*sin(x))#$novafce) then add(\"sin2\"),matchdeclare(maw_cos2,true),let ([cos(maw_cos2)^2, 1 - sin(maw_cos2)^2],cos), if (ratsimp(((letsimp((letsimp($novafce,cos))/cos(x),cos)))*cos(x))#$novafce) then add(\"cos2\") ,outf)");
    
    if (ereg("trigsimp",$testfunkci))
      {
	echo'<input name="akce" value="ts" type="radio"> 
',preview_result("trigsimp"),__("simplify trigonometric functions"),'  (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_10.html#Item_003a-trigsimp">trigsimp</a>)','<br>';
      }
    
    if (ereg("trigreduce",$testfunkci))
      {
	echo '<input name="akce" value="tr" type="radio"> 
',preview_result("trigreduce"),__("simplify trigonometric functions"),' (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_10.html#Item_003a-trigreduce">trigreduce</a>)','<br>';
      }
    
    if (ereg("trigexp",$testfunkci))
      {
	echo '<input name="akce" value="texp" type="radio"> 
',preview_result("trigexp"),__("simplify trigonometric functions"),'  (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_10.html#Item_003a-trigexpand">trigexpand</a>)','<br>';
      }
    
    $odddenom=" ".__("(and remove odd power from denominator, if possible)" );
    if (ereg("sin",$novafce) && ereg("sin2",$testfunkci))
      {
	echo '<input name="akce" value="sin2cos" type="radio"> 
',preview_result("sin2cos"),__("replace <b>even powers of sine</b> function by cosine function").$odddenom,'
<br>';
      }
    if (ereg("cos",$novafce) && ereg("cos2",$testfunkci))
      {
	echo '<input name="akce" value="cos2sin" type="radio"> 
',preview_result("cos2sin"),__("replace <b>even powers of cosine</b> function by sine function").$odddenom,'
<br>';
      }
  }

if (ereg("sec",$novafce) || ereg("csc",$novafce))
  {
    echo '<input name="akce" value="seccsc" type="radio"> 
',preview_result("seccsc"),__("replace function <b>sec</b> and <b>csc</b> by equivalent functions in terms <b>sin</b> and <b>cos</b>"),'<br>';
  }

if (ereg("abs",$novafce))
  {
    echo '<input name="akce" value="abs" type="radio" checked="checked"> 
<b>',__("remove absolute values"),'</b>
<br>';
  }


if ($completesquare_asin==1)
  {
    echo'    <input name="akce" value="completesquare_asin" type="radio" checked="checked"><b> 
',preview_result("sq2"),__("complete square under square root"),' 
</b>','<br>';
  }

if ($completesquare_frac==1)
  {
    echo'    <input name="akce" value="completesquare_frac" type="radio" checked="checked"><b> 
',preview_result("sq1"),__("complete square in denominator"),' 
</b>','<br>';
  }

if ($hintnum==7)
  {
    echo'    <input name="akce" value="split_fraction_for_integration" type="radio" checked="checked"><b> 
',preview_result("spl"),__("clever expansion into two fractions"),' 
</b>','<br>';
  }


if ($operace=="soucet")
  {
    echo '<b><input name="akce" value="soucet" type="radio" checked="checked"> 
',__("integrate terms in sum (mark terms to be integrated, you have to mark at least one term)"),'</b>';
    if ($prom=="x")
      {
	echo '<br>',__("(clicking the expression you get this part of integral in new window)");
      }
    function scitanec($cislo,$vyraz)
    {
      global $prom,$mawhome,$all_hints2,$maximainit,$mawphphome,$maxima_total_runtime,$maxima_total_calls,$maxima, $mawtimeout,$lang;
      $maxima_start=getmicrotime();
      $maxima_total_calls++;
      $tempcomp_input="$mawtimeout $maxima --batch-string=\"($maximainit maw_var:$prom) ;load(\\\"$mawhome/integral/matchint.mac\\\");testformula($vyraz);test_fprime_over_f($vyraz);\"";
      $tempcomp=do_maxima($tempcomp_input);
      $maxima_end=getmicrotime();
      $maxima_total_runtime=$maxima_total_runtime+$maxima_end-$maxima_start;
      $all_hints2=$all_hints2.$tempcomp;
      $tempcheck="";
      if ((ereg("### testformula",$tempcomp))||(ereg("### logofdenom",$tempcomp))) {$tempcheck=" checked=\"checked\" ";}
      echo ("<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"scitanec$cislo\" type=\"checkbox\" $tempcheck> ");
      if ($prom=="x")
	{
	  echo ("<a target=_blank style=\"text-decoration:none;\" href=\"$mawphphome/integral/integralx.php?".$vyraz.";lang=$lang \">");
	}
      echo maxima_to_html($vyraz,"\\displaystyle{\\int{","}\,\\mathrm{d}".$prom."}");
      if ($prom=="x")
	{
	  echo ("</a>");
	}
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"hint\";>";
      if ($tempcheck!="") 
	{
	  if (ereg("### logofdenom",$tempcomp)) 
	    {
	      echo (__("the numerator is derivative of denominator (or its constant multiple)"));
	    }
	  else
	    {
	      echo hint_formula($tempcomp);
	    }
	}
      echo "</span>";
  }

  maxima_session_comment("extracting arguments from sum, 2");
  $scitance=maxima_command("args($novafce)");
  $scitance=str_replace("[","",$scitance);
  $scitance=str_replace("]","",$scitance);
  $scitance=split(",",$scitance);
  $pocet=count($scitance);
  for ($p=0; $p<$pocet; $p=$p+1)
    {
      scitanec($p,$scitance[$p]);
    }
}

echo "\n<hr>\n";
echo hint_bb(4),'<input name="akce" value="s" type="radio"',check_hint(4),'> 
<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_19.html#Item_003a-changevar">',__("substitution"),'</a>',hint_be(4),'<ul  style="list-style-type: none;">',write_hints_for_substitution(),' <input name="substituce" value=""> ',__("(write an equation, e.g. x^2=t, this equation must involve new variable and no parameter)"),'
<div style="margin-left:30px;"> 
', __("with new variable"),' <input name="novapromenna" value="',$newvariable_hint,'" size="4" maxlength="1">
',__("and suppose that this variable is positive"),' <input name="kladna" type="checkbox" checked="checked"> 
',__("should be checked for substitutions of the form"), 
  tex_to_html("ax+b=t^2"),'</div></ul><br>
<input name="akce" value="pp" type="radio"',check_hint(1),'> 
',hint_bb(1),__("by parts (specify  <i>u</i> or <i>v'</i>, if you specify both, Maxima will use <i>v'</i> for the next computation)"),'
<div style="margin-left:30px;">
<table>
<tr><td><i>u =</i></td><td><input name="v" value="',$u_hint,'"></td></tr>
<tr><td><i>v\' =</i></td><td><input name="u" value="',$v_hint,'"></td></tr>
</table>
</div>',hint_be(1),'
<hr>
';


if ($hintnum==11)
  { 
    echo hint_bb(11),'<input name="akce" value="equation" type="radio"',check_hint(11),'> 
  <b>',__("convert into an equation for the unknown integral and solve with pure algebra"),'</b>',hint_be(11),'
<hr>
';
  }

echo hint_bb(5),'<input name="akce" value="i" type="radio"',check_hint(5),'> 
  <b>',__("use formula or ask the computer to finish the integration"),'</b> (<a href="http://maxima.sourceforge.net/docs/manual/en/maxima_19.html#Item_003a-integrate">integrate</a>)',hint_be(5),'
<br>
</fieldset>
<br>
<input value="',__("Submit"),'" name="tlacitko" type="submit" class="tlacitko"  id="myButton">
<script type="text/javascript"> document.getElementById("myButton").focus();scroll(0,0);</script>
<p style="text-align:right;"><input value="',__("Build PDF"),'" name="tlacitko" type="submit" class="tlacitko tlacitko_html">
<input value="',__("Download html"),'" name="tlacitko" type="submit" class="tlacitko tlacitko_html"></p>

<input name="krok" type="hidden" value="',$krok,'">
<input name="funkce" type="hidden" value="',$novafce,'">
<input name="prom"  type="hidden" value="',$prom,'">
<input name="secvar"  type="hidden" value="',$secvar,'">
<input name="oriprom"  type="hidden" value="',$oriprom,'">
<input name="oriproblem"  type="hidden" value="',$oriproblem,'">
<input name="allprom"  type="hidden" value="',$allprom,'">
<input name="adresar"  type="hidden" value="',$maw_tempdir,'"> 
<input name="jsmath"  type="hidden" value="',$jsmath,'">
<input name="vsechno"  type="hidden" value="',$vsechno,'">
<input name="lang"  type="hidden" value="',$lang,'">
<input name="post"  type="hidden" value="',$post,'">
<input name="pfeformat"  type="hidden" value="',$pfeformatswitch,'">
<input name="logarc"  type="hidden" value="',$logarcswitch,'">
<input name="backsubst"  type="hidden" value="',$backsubst,'">
';

if ($formconv=="on") 
  {
    echo ' <input name="formconv" type="hidden" value="on">';
  }
echo '
</form></div>';
maw_after_form();


if ($jsmat="on")
{
echo("<script type=\"text/javascript\">jsMath.Process(document);</script>");
}

$jsmath="";

if (($akce=="dokonceni") || ($akce=="i") || ($akce=="equation"))
  {
    save_log(maxima_to_html_mimetex($orifce,"","")." akce:".$akce." ".$substituce.savekey($maw_tempdir),"integral");
  }
else
  {
    save_log(maxima_to_html_mimetex($orifce,"\\int ","\\, d $prom")." akce:".$akce." ".$substituce.savekey($maw_tempdir),"integral");
  }


if ($hint=="") 
  {
    echo ("No automatical hint");
    if ((ereg("### testformula",$vystupmaximy.$all_hints2)) || (ereg("### ostrogradskii",$vystupmaximy.$all_hints2)))
      {
	echo (", but some hint has been printed");
      }
    else
      {
	echo (", no idea what to do");
	save_log(maxima_to_html_mimetex($novafce,"","").savekey($maw_tempdir),"integral-nohint");
      }
  }

print_time_and_computations();

die(hide_message(1)."\n</body></html>");

?>

