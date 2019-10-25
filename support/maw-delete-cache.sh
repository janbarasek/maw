# This may help the admin to clean selectively the MAW cache.
# Use 
#       bash maw-delete-cache.sh | grep php | xargs rm 
# as root to remove cache computations
# which have not been used (not mentioned in md5_used file.
# You may want to change the paths.

cd /var/www/maw_cache/integral/
for f in *.php
do 
  temp=${f%.*}
  echo "... processing $temp"
  if grep -q $temp /var/www/maw/common/log/md5_used.log
  then
    echo "... has been used"
  else
    echo "... NOT used, consider delete"
    echo "/var/www/maw_cache/integral/$f"
  fi
done

