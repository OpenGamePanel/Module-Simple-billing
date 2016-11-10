Hi guys!

EXIM as smarthost is only needed for hosts with dynamically assigned IP because it avoids to be blocked by some mail servers like hotmail.

Upload all files.

Login with ssh and change to the user 'www-data'(in ubuntu) or 'apache'(in centos) or any other user that manages the apache server with, for example:

sudo su - www-data

Now edit the cron tab for this user

crontab -e

and copy this line at the end of the file and save it (WARNING:modify it with the correct path):

*/1 * * * * php /var/www/html/ogp/modules/billing/cron-shop.php

Now this script searches for expired game homes every minute, and wil stop and remove them if they are expired.
If you would like to do this at midnight every day instead of every minute you should use

0 0 * * * php /var/www/html/ogp/modules/billing/cron-shop.php

TIP: Searching in google, for example, "cron every month" you will find the correct code to search expired homes and remove them every month.

