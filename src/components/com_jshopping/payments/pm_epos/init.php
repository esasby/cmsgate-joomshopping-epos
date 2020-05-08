<?php
require_once(dirname(__FILE__) . '/vendor/esas/cmsgate-core/src/esas/cmsgate/CmsPlugin.php');

use esas\cmsgate\epos\RegistryEposJoomshopping;
use esas\cmsgate\CmsPlugin;


(new CmsPlugin(dirname(__FILE__) . '/vendor', dirname(__FILE__)))
    ->setRegistry(new RegistryEposJoomshopping())
    ->init();

