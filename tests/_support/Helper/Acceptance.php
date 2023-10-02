<?php
namespace Helper;

class Acceptance extends \Codeception\Module{

    private $hostname = "";

    /*
    Command line functions are executed either on localhost or on the remote dev servers.
    This function checks where the function is running, and if on local adds a prefix
    */
    public function doExecuteCommandLine($command, $target){
        $prefix = "";
        if($this->hostname == "kali"){
            $prefix = "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $target . " ";
        }
        return shell_exec($prefix . $command);
    }
    public function reconfigureThisVariable($array){
        $this->getModule('WPWebDriver')->_reconfigure($array);
        $this->getModule('WPWebDriver')->_restart();
    }

    public function _afterSuite(){
        //Cleanup:
        //shell_exec("wp post delete $(wp post list --format=ids) --force");
    }

    public function _beforeSuite($settings = []){
        //$hostname =
        $this->hostname = shell_exec("hostname");
    }

    public function pauseInTerminal(){
        echo "Press ENTER to continue: ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        echo "\n";
    }

    public function getSiteUrls(){
        return json_decode(file_get_contents('/var/www/html/wp-content/plugins/ion-chat/servers.json'), true);
    }

    public function clickSendConnectionEmailButton(\AcceptanceTester $I, $siteURL){
        $I->reconfigureThisVariable(["url" => ('http://' . $siteURL)]);
        $I->loginAsAdmin();
        $I->amOnPage('/wp-admin/tools.php?page=email-tunnel');
        $I->expect("the connection button is visible and active");
        $I->see("This site is not connected yet.");

        $I->amGoingTo('click the button');
        $I->expect("the remote to contact the mothership and a nonce to be created");
        $I->click("#request-connection-email-button");
        //sleep(1);
        //return;
    }

    public function resetEmailTunnel(\AcceptanceTester $I){
        //This script resets email-tunnel and ETM to a ready state
        global $testSiteURLs;
        $testSiteURLs = $I->getSiteUrls();
        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $testSiteURLs[0] . " php /var/www/html/wp-content/plugins/email-tunnel/doDeleteAlletmConnectionCPTs.php";
        echo(shell_exec($command));

        $command = "ssh -o StrictHostKeyChecking=no -i /home/johndee/sportsman.pem ubuntu@" . $testSiteURLs[1] . " php /var/www/html/wp-content/plugins/email-tunnel/doDeleteAlletmConnectionCPTs.php";
        echo(shell_exec($command));

        try {
            $I->reconfigureThisVariable(["url" => ('http://' . $testSiteURLs[0])]);
            $I->loginAsAdmin();
            $I->amOnPage("/wp-admin/admin.php?page=email-log");
            $I->see("Email Logs");
            $I->selectOption('#bulk-action-selector-top','Delete All Logs');
            $I->click("#doaction");
        } catch (Exception $e) {
        }//Nothing. We don't care about an error here. It just means 'nothing to delete'
    }
}
