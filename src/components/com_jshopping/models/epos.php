<?php
/*
* @info     Платёжный модуль bgpb для JoomShopping
* @package  bgpb
* @author   esas.by
* @license  GNU/GPL
*/
require_once(dirname(dirname(__FILE__)) . '/payments/pm_epos/init.php');

use esas\cmsgate\joomshopping\CmsgateModel;
defined('_JEXEC') or die();

class JshoppingModelEpos extends CmsgateModel
{


}