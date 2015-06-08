<?php
 
if(defined('WOOPS_ENABLED') && WOOPS_ENABLED){
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
