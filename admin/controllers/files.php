<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding files controller class.
 *
 * @package        CrowdfundingFiles
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingFilesControllerFiles extends Prism\Controller\Admin
{
    public function getModel($name = 'File', $prefix = 'CrowdfundingFilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
