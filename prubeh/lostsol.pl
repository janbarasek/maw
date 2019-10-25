use Locale::gettext;

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 


open(HLAVA,"output");
@pole=<HLAVA>;

$output=join("",@pole);
if ($output=~"Some solutions will be lost.")
{
    print gettext("Maxima is using arc-trig functions to get a solution. Some solutions will be lost.");
}
