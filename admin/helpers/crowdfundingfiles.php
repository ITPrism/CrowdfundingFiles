<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is CrowdfundingFiles helper class
 */
class CrowdfundingFilesHelper
{
    protected static $extension = 'com_crowdfundingfiles';

    /**
     * Configure the Linkbar.
     *
     * @param    string  $vName  The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName === 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_FILES'),
            'index.php?option=' . self::$extension . '&view=files',
            $vName === 'files'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_search=' . rawurlencode('Crowdfunding Files'),
            $vName === 'plugins'
        );
    }

    /**
     * Generate a path to the folder, where the files are stored.
     *
     * @param string $rootPath   A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     * @param int    $userId     User Id.
     *
     * @throws \UnexpectedValueException
     * @return string
     */
    public static function getMediaFolder($rootPath = '', $userId = 0)
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $folder = $params->get('media_directory', 'media/crowdfundingfiles');
        if (strpos($folder, '/') === 0) {
            $folder = substr($folder, 1);
        }

        $folder = $rootPath .'/'. $folder;

        if ((int)$userId > 0) {
            $folder .= '/'. 'user' . (int)$userId;
        }

        return JPath::clean($folder, '/');
    }

    /**
     * Generate a URI path to the folder, where the files are stored.
     *
     * @param string $root
     * @param int $userId User Id.
     *
     * @return string
     */
    public static function getMediaFolderUri($root = '', $userId = 0)
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $uri = $params->get('media_directory', 'media/crowdfundingfiles');
        if (strpos($uri, '/') === 0) {
            $uri = substr($uri, 1);
        }

        $uri = $root. $uri;

        if ((int)$userId > 0) {
            $uri .= '/user' . (int)$userId;
        }

        return $uri;
    }
}
