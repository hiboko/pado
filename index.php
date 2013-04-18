<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/library'),
    get_include_path(),
)));

include dirname(__FILE__) . "/library/PHPExcel.php";
include dirname(__FILE__) . "/library/PHPExcel/IOFactory.php";

/** Zend_Application */
require_once 'Zend/Application.php';

/** Common Class */
require_once dirname(__FILE__) . "/application/common/Common.php";
require_once dirname(__FILE__) . "/application/common/ComConst.php";
require_once dirname(__FILE__) . "/application/common/ParamCheck.php";

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();