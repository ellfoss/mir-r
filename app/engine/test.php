<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 10.10.2016
 * Time: 11:25
 */

include 'main.php';

//Sql::query("UPDATE `members` SET `checkClan` = NULL, `games` = NULL");

//Sql::query("DELETE FROM `wot_stat` WHERE `date` >= '2016-10-10'");
//Sql::query("DELETE FROM `wotb_stat` WHERE `date` >= '2016-10-10'");
//Sql::query("DELETE FROM `wowp_stat` WHERE `date` >= '2016-10-10'");
Sql::query("DELETE FROM `wows_stat` WHERE `date` >= '2016-10-10'");

//Sql::query("UPDATE `members` SET `wotUpdate` = NULL");
//Sql::query("UPDATE `members` SET `wotbUpdate` = NULL");
//Sql::query("UPDATE `members` SET `wowpUpdate` = NULL");
Sql::query("UPDATE `members` SET `wowsUpdate` = NULL");