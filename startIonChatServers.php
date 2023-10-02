<?php

/*
Effects of this script:
two servers will be spun up
their IPs will be stored in the file servers.json
*/

$dev1instance = "i-054ee3b5cbcce9710";
$dev2instance = "i-00021f9572af5111c";

//why are there 2?
$dev1PHPStormID = "21605059-6780-4171-8532-2101fe40e69a";
$dev2PHPStormID = "5518cb1f-5645-4686-a67e-ed09681291ad";

$command = "aws ec2 start-instances --instance-ids $dev1instance --profile produser --region us-east-2";
echo ($command . PHP_EOL); shell_exec($command);

$command = "aws ec2 start-instances --instance-ids $dev2instance --profile produser --region us-east-2";
echo ($command . PHP_EOL); shell_exec($command);

sleep(120);

$command = "aws ec2 describe-instances --instance-ids $dev1instance --profile produser --region us-east-2";
echo ($command . PHP_EOL);$IP_RequestResponse = shell_exec($command);

$dev1IP = (((((json_decode($IP_RequestResponse))->Reservations)[0])->Instances)[0])->PublicIpAddress;
echo("Dev1 instance IP is $dev1IP" . PHP_EOL);

$command = "aws ec2 describe-instances --instance-ids $dev2instance --profile produser --region us-east-2";
echo ($command . PHP_EOL);

$IP_RequestResponse = shell_exec($command);
$dev2IP = (((((json_decode($IP_RequestResponse))->Reservations)[0])->Instances)[0])->PublicIpAddress;
echo("Dev2 instance IP is $dev2IP" . PHP_EOL);

$SSH_Commands = [
    //Mothership:
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . " /var/www/html/wp-content/plugins/WPbdd/startup.sh",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . " sudo chmod 777 -R /var/www",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . " wp config create --path=/var/www/html --dbname=wordpress --dbuser=wordpressuser --dbpass=password --force",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp core install --path=/var/www/html --url="http://' . $dev1IP . '" --title=Mothership --admin_name="Codeception" --admin_password="password" --admin_email="codeception@email.com" --skip-email',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp config set FS_METHOD direct --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . " wp rewrite structure '/%postname%/' --path=/var/www/html",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp option update uploads_use_yearmonth_folders 0 --path=/var/www/html',
  //  "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate email-tunnel/email-tunnel --path=/var/www/html',
  //  "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate email-tunnel/EmailTunnelMothership --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate classic-editor --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate wp-mail-catcher --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate user-switching --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate wp-crontrol --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate disable-administration-email-verification-prompt --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate woocommerce --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate disable-welcome-messages-and-tips --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate better-error-messages --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp user create Subscriberman subscriberman@email.com --role=subscriber --user_pass=password --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp config set WP_DEBUG true --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp post create --path=/var/www/html --post_author=1 --post_title=Chat --post_status=publish --post_type=page',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp user create Ion ion@email.com --role=subscriber --user_pass=password --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate buddypress --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate bp-better-messages --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . ' wp plugin activate ion-chat --path=/var/www/html',

    //Remote Node:
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . " /var/www/html/wp-content/plugins/WPbdd/startup.sh",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . " sudo chmod 777 -R /var/www",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . " wp config create --path=/var/www/html --dbname=wordpress --dbuser=wordpressuser --dbpass=password --force",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp core install --path=/var/www/html --url="http://' . $dev2IP . '" --title=RemoteNode --admin_name="Codeception" --admin_password="password" --admin_email="codeception@email.com" --skip-email',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp config set FS_METHOD direct --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . " wp rewrite structure '/%postname%/' --path=/var/www/html",
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp option update uploads_use_yearmonth_folders 0 --path=/var/www/html',
    //"ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate email-tunnel/email-tunnel --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate classic-editor --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate user-switching --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate wp-test-email --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate wp-crontrol --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate disable-administration-email-verification-prompt --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate disable-welcome-messages-and-tips --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp user create Subscriberman subscriberman@email.com --role=subscriber --user_pass=password --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp user create AltAdmin altadmin@email.com --role=administrator --user_pass=password --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp config set WP_DEBUG true --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate better-error-messages --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp post create --path=/var/www/html --post_author=1 --post_title=Chat --post_status=publish --post_type=page',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp user create Ion ion@email.com --role=subscriber --user_pass=password --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate buddypress --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate bp-better-messages --path=/var/www/html',
    "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev2IP . ' wp plugin activate ion-chat --path=/var/www/html',
    ];

//execute the above commands, one by one.:
foreach($SSH_Commands as $command){
    echo ($command . PHP_EOL);
    shell_exec($command);
}

//Store the IP addresses in the file servers.json
$servers = [$dev1IP, $dev2IP];
$fp = fopen('/var/www/html/wp-content/plugins/ion-chat/servers.json', 'w');
fwrite($fp, json_encode($servers));
fclose($fp);


echo("Copying servers.json to remotes:" . PHP_EOL);
$command = "scp -i /home/johndee/sportsman.pem servers.json ubuntu@$dev1IP:/var/www/html/wp-content/plugins/ion-chat/servers.json";
echo ($command . PHP_EOL);shell_exec($command);
$command = "scp -i /home/johndee/sportsman.pem servers.json ubuntu@$dev2IP:/var/www/html/wp-content/plugins/ion-chat/servers.json";
echo ($command . PHP_EOL);shell_exec($command);

//Setting up chat plugins:
$command = "cd /var/www/html/wp-content/plugins/ion-chat";
echo ($command . PHP_EOL); shell_exec($command);

$command = "bin/codecept run acceptance SetupIonChatWordPressPluginsCept.php -vvv --html";
echo ($command . PHP_EOL); shell_exec($command);




//Update the PHP storm files on the remotes, in case we want to push remote versions to git
updateXMLIPField(".idea/sshConfigs.xml", $dev1PHPStormID, $dev1IP);
updateXMLIPField(".idea/sshConfigs.xml", $dev2PHPStormID, $dev2IP);

////Creating WooCommerce product and order
//$command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $dev1IP . " php /var/www/html/wp-content/plugins/ion-chat/startupWooCommerce.php";
//echo ($command . PHP_EOL);shell_exec($command);

//$orderID = getOrderIDfromMothership($dev1IP);

/*
//Update Constants.class.php
$constantsFile = "/var/www/html/wp-content/plugins/ion-chat/src/EmailTunnel/Constants.class.php";
$blurb = file_get_contents($constantsFile);
$replaceWith = "http://$dev1IP";
$blurb = replaceTextInBetweenSingleQuotes($blurb, $replaceWith);
$blurb = changePropertyViaText($blurb, "CompleatedWooOrder", $orderID);
file_put_contents($constantsFile, $blurb);
$command = "scp -i /home/johndee/sportsman.pem $constantsFile ubuntu@$dev1IP:$constantsFile";
echo ($command . PHP_EOL);shell_exec($command);
$command = "scp -i /home/johndee/sportsman.pem $constantsFile ubuntu@$dev2IP:$constantsFile";
echo ($command . PHP_EOL);shell_exec($command);
*/


function replaceTextInBetweenSingleQuotes($blurb, $replaceWith) {
    return preg_replace("/'(.*?)'/", "'$replaceWith'", $blurb);
}


/*
Starting Local Selenium
cd /var/www/html/wp-content/plugins/WPbdd
nohup xvfb-run java -Dwebdriver.chrome.driver=/var/www/html/wp-content/plugins/WPbdd/chromedriver -jar selenium.jar &>/dev/null &
*/
function updateXMLIPField($XML_file, $identifier, $hostIPaddress) {
  // Load the XML file
  $xml = simplexml_load_file($XML_file);

  // Loop through each sshConfig element
  foreach ($xml->component->configs->sshConfig as $sshConfig) {
    // Check if the id attribute matches the identifier parameter
    if ((string)$sshConfig['id'] === $identifier) {
      // Update the host attribute with the new host IP address
      $sshConfig['host'] = $hostIPaddress;
    }
  }

  // Save the updated XML file
  $xml->asXML($XML_file);

  // Return the updated XML file as a string

    return file_get_contents($XML_file);
}


//ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@18.224.25.197 wp user subscriberman subscriberman@email.com --role=subscriber --user_pass=password    --path=/var/www/html