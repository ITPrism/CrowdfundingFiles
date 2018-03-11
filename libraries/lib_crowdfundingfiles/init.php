<?php
/**
* @package      CrowdfundingFiles
* @subpackage   Library
* @author       Todor Iliev
* @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('JPATH_PLATFORM') or die;

if (!defined('CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR')) {
    define('CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_crowdfundingfiles');
}

if (!defined('CROWDFUNDINGFILES_PATH_COMPONENT_SITE')) {
    define('CROWDFUNDINGFILES_PATH_COMPONENT_SITE', JPATH_SITE . '/components/com_crowdfundingfiles');
}

if (!defined('CROWDFUNDINGFILES_PATH_LIBRARY')) {
    define('CROWDFUNDINGFILES_PATH_LIBRARY', JPATH_LIBRARIES . '/Crowdfundingfiles');
}

JLoader::registerNamespace('Crowdfundingfiles', JPATH_LIBRARIES);

// Register helpers
JLoader::register('CrowdfundingFilesHelper', CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR . '/helpers/crowdfundingfiles.php');

// Register HTML helpers
JHtml::addIncludePath(CROWDFUNDINGFILES_PATH_COMPONENT_SITE . '/helpers/html');
JLoader::register('JHtmlString', JPATH_LIBRARIES . '/joomla/html/html/string.php');

JLog::addLogger(
    array(
        'text_file' => 'com_crowdfundingfiles.errors.php'
    ),
    // Sets messages of specific log levels to be sent to the file
    JLog::CRITICAL + JLog::EMERGENCY + JLog::ALERT + JLog::ERROR + JLog::WARNING,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_crowdfundingfiles')
);
