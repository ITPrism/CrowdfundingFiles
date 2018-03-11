<?php
/**
 * @package         CrowdfundingFiles
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('Crowdfunding.init');
jimport('Crowdfundingfiles.init');

/**
 * Crowdfunding Files Plugin
 *
 * @package        CrowdfundingFiles
 * @subpackage     Plugins
 */
class plgCrowdfundingFiles extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected $version;

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param stdClass  $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return null|string
     */
    public function onExtrasDisplay($context, $item, $params)
    {
        if (strcmp('com_crowdfunding.project.extras', $context) !== 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }
        
        if (!isset($item->user_id) or !$item->user_id) {
            return null;
        }

        // Create a media folder.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder(JPATH_ROOT);
        if (!JFolder::exists($mediaFolder)) {
            CrowdfundingHelper::createFolder($mediaFolder);
        }

        // Create a media folder for a user.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder(JPATH_ROOT, $item->user_id);
        if (!JFolder::exists($mediaFolder)) {
            CrowdfundingHelper::createFolder($mediaFolder);
        }

        $componentParams = JComponentHelper::getParams('com_crowdfundingfiles');
        /** @var  $componentParams Joomla\Registry\Registry */

        $maxFileSize = Prism\Utilities\FileHelper::getMaximumFileSize((int)$componentParams->get('max_size', 5), 'MB');
        $mediaUri    = CrowdfundingFilesHelper::getMediaFolderUri(JUri::root(), $item->user_id);

        $options = array(
            'project_id' => $item->id,
            'user_id' => $item->user_id
        );

        $files = new Crowdfundingfiles\File\Files(JFactory::getDbo());
        $files->load($options);

        // Load jQuery
        JHtml::_('jquery.framework');
        JHtml::_('Prism.ui.remodal');
        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.fileupload');
        JHtml::_('Prism.ui.serializeJson');
        JHtml::_('Prism.ui.joomlaHelper');

        // Include the translation of the confirmation question.
        JText::script('PLG_CROWDFUNDING_FILES_DELETE_QUESTION');

        $version = new Crowdfundingfiles\Version();
        $this->version = $version->getShortVersion();

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfunding', 'files'), '/');

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }
}
