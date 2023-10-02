<?php

//This script zips the production version

$version = readline('Version to create: ');


shell_exec("sudo rm -fr /var/www/html/wp-content/plugins/email-tunnel/email-tunnel");
shell_exec("sudo mkdir /var/www/html/wp-content/plugins/email-tunnel/email-tunnel");

copy("/var/www/html/wp-content/plugins/email-tunnel/email-tunnel.php", "/var/www/html/wp-content/plugins/email-tunnel/email-tunnel/email-tunnel.php");
//shell_exec("cp -r /var/www/html/wp-content/plugins/email-tunnel/src /var/www/html/wp-content/plugins/email-tunnel/email-tunnel/src");

shell_exec("sudo rsync -r --exclude src/ETM src email-tunnel");

shell_exec("sudo zip -r email-tunnel-$version.zip email-tunnel");
shell_exec("sudo rm email-tunnel.zip");
shell_exec("sudo cp email-tunnel-$version.zip email-tunnel.zip");