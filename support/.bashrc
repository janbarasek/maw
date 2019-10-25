# ~/.bashrc: executed by bash(1) for non-login shells.

export PS1='\h:\w\$ '
umask 022

# You may uncomment the following lines if you want `ls' to be colorized:
# export LS_OPTIONS='--color=auto'
# eval "`dircolors`"
# alias ls='ls $LS_OPTIONS'
# alias ll='ls $LS_OPTIONS -l'
# alias l='ls $LS_OPTIONS -lA'
#
# Some more alias to avoid making mistakes:
# alias rm='rm -i'
# alias cp='cp -i'
# alias mv='mv -i'
col='\e[1;34m'
nc='\e[0m'

clear
echo "********************************************************"
echo ""
echo -e "Your IP is after inet addr: ${col}"
ifconfig | head -n 2 | grep inet
echo -e "${nc}"
echo "Point www browser in your computer to the IP addr. above."
echo "Ctrl+Alt gives you access back to your computer."
echo ""
echo "********************************************************"
echo ""
echo -e "You can use the ${col}new Maxima 5.21 ${nc}(much slower but with bugfixes)"
echo "installed in /opt/maxima/bin directory."
echo "Make it avilable by uncommenting lines starting with \$maxima and \$maxima2"
echo "in the MAW config file. Run "
echo -e "${col}joe /var/www/maw/common/mawconfig.php${nc}"
echo "and find two lines near the end of the file which start with"
echo -e "${col}//\$maxima${nc} and ${col}//\$maxima2${nc}. Remove the charaters // and"
echo "save by crtl+k followed by x."
echo ""
