<?php
/*
 * This file is part of Panopto-PHP-SSO-CAS.
 *
 * Panopto-PHP-Client is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Panopto-PHP-SSO-CAS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Panopto-PHP-SSO-CAS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright: Andrew Martin, Newcastle University
 *
 */

    error_reporting(E_ALL);
    date_default_timezone_set("Europe/London");
    //This page needs to be protected by the auth solution...
    // In other words, when panopto passes the user here you want them to be prompted to
    // fill in their authenticated details to get to this page which passes the authenticated user
    // back to panopto. If the user is already logged in previously then this page will work
    // it's magic without prompting the user... as they are already logged in and therefore already
    // have a CAS session :)

    require_once("Logger.php");

    $logger = new Logger("panopto.log");
    $logger->log("*************".date("H:i:s d/m/Y")."***************");

    $action = $_REQUEST["action"];
    $authcode = $_REQUEST["authCode"];
    $serverName = $_REQUEST["serverName"];
    $expiration = $_REQUEST["expiration"];
    $callbackURL = $_REQUEST["callbackURL"]; // Where to go back to

    $logger->log($action."<br/>");
    $logger->log($authcode."<br/>");
    $logger->log($serverName."<br/>");
    $logger->log(date("H:i:s d/m/Y",$expiration)."<br/>");
    $logger->log($callbackURL."<br/>");

    $requestAuthPayload = "serverName=" . $serverName . "&expiration=" . $expiration;
    $panoptoCAS2ApiKey = "12345678910"; // <- Your CAS API KEY HERE!
    $requestAuthCode = strtoupper(sha1($requestAuthPayload."|".$panoptoCAS2ApiKey));
    $logger->log($requestAuthCode."<br/>");
    //If valid request
    if($requestAuthCode==$authcode)
    {
        //User should be taken down from the authenticated reqest variable e.g. REMOTE_USER
        //If user doesn't exist it gets created!
        $user = "TEST"; // <- test user name

        //Provision folders and groups for user

        $logger->log("Our user will be: ".$user."<br/>");
        $callbackURL = explode("?ReturnUrl=",$callbackURL);
        $callbackURLRet = $callbackURL;
        $callbackURL = urldecode($callbackURL[0]);

        $instance = "CAS2"; // <- name of the auth provider in panopto
        $userKey = $instance."\\".$user;
        $logger->log("Callback to: ".$callbackURL."<br/>");
        $responseParams = "serverName=".$serverName."&externalUserKey=".$userKey."&expiration=".$expiration;
        $responseAuthCode = strtoupper(sha1($responseParams. "|".$panoptoCAS2ApiKey));
        $logger->var_dump($callbackURLRet);
        $url = $callbackURLRet[0]."?ReturnUrl=".$callbackURLRet[1]."&".$responseParams."&authCode=".$responseAuthCode;

        $logger->log($url);
        header("Location: ".$url);
    }
    else
    {
        print_header();
        echo "Invalid auth code.";
        print_footer();
    }
?>