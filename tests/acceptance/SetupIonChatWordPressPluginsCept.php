<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Install Ion Chat');
global $testSiteURLs;
$testSiteURLs = $I->getSiteUrls();

$I->reconfigureThisVariable(["url" => ('http://' . $testSiteURLs[0])]);
run($I);
$I->reconfigureThisVariable(["url" => ('http://' . $testSiteURLs[1])]);
run($I);

$I->reconfigureThisVariable(["url" => ('http://' . $testSiteURLs[0])]);
setup_chat($I);
$I->reconfigureThisVariable(["url" => ('http://' . $testSiteURLs[1])]);
setup_chat($I);

function setup_chat($I){
    $I->loginAsAdmin();
    $I->amOnPage("/wp-admin/edit.php?post_type=bpbm-chat");
    $I->click("Create new Chat Room");
    $I->fillField("post_title", "Main Chat");
    $I->click("Publish");
}

function run($I){
try {
    $I->loginAsAdmin();
} catch (Exception $e) {return true;}
    $I->see("WordPress");
try {
    //$I->click("Dismiss");
    } catch (Exception $e) {return true;}
try {
    $I->click(".woocommerce-message-close");
} catch (Exception $e) {return true;}



$I->click("Activate");
$I->click('#cb-select-all-1');
$I->click("#bp-admin-component-submit");
$I->click('Opt in to make "Better Messages" better!');
$I->click("Skip");
$I->amOnPage("/wp-admin/edit.php?post_type=bpbm-chat");
$I->click(".page-title-action");



}