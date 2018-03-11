<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding Files controller class.
 *
 * @package        CrowdfundingFiles
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingFilesControllerFiles extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return   CrowdfundingFilesModelFiles   The model.
     * @since    1.5
     */
    public function getModel($name = 'Files', $prefix = 'CrowdfundingFilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function upload()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        $projectId = $this->input->post->getUint('project_id');

        // Get component parameters
        $params = $app->getParams('com_crowdfundingfiles');
        /** @var  $params Joomla\Registry\Registry */

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingFilesModelFiles */

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
        if (!$projectId or !$validator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::sprintf('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT_FILE_TOO_LARGE', $params->get('max_size')))
                ->failure();

            echo $response;
            $app->close();
        }

        $file = $this->input->files->get('file');
        if (!$file) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the folder where the images will be stored
        $mediaUri = CrowdfundingFilesHelper::getMediaFolderUri('', $userId);

        // Get the folder where the images will be stored
        $destination = CrowdfundingFilesHelper::getMediaFolder(JPATH_ROOT, $userId);
        if (!JFolder::exists($destination)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }
        
        try {
            // Upload a file.
            $options = array(
                'legal_extensions'  => $params->get('legal_extensions', 'PDF, RTF, DOC, PPS, PPT, XLS'),
                'legal_types'       => $params->get('legal_types', 'application/pdf, text/rtf, application/powerpoint, application/mspowerpoint, application/msword, application/excel, application/vnd.ms-powerpoint'),
                'max_size'          => (int)$params->get('max_size', 5),
                'destination'       => $destination
            );
            $filedata = $model->uploadFile($file, $options);

            // Store information about a file in database.
            $options = array(
                'project_id'  => $projectId,
                'user_id'     => $userId,
                'media_uri'   => $mediaUri
            );
            $file = $model->storeFile($filedata, $options);

        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfundingfiles');

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDINGFILES_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDINGFILES_FILES_UPLOADED'))
            ->setData($file)
            ->success();

        echo $response;
        $app->close();
    }

    public function remove()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Create response object
        $response = new Prism\Response\Json();

        $userId = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get file ID.
        $fileId = $this->input->post->getUint('file_id');

        // Validate owner of the file.
        $ownerValidator = new Crowdfundingfiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
        if (!$ownerValidator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_INVALID_FILE'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the folder where the files are stored.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder('', $userId);

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingFilesModelFiles */

        try {
            $model->removeFile($fileId, $mediaFolder);
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfundingfiles');
            throw new Exception($e->getMessage());
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDINGFILES_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDINGFILES_FILE_DELETED'))
            ->setData(array('file_id' => $fileId))
            ->success();

        echo $response;
        $app->close();
    }

    public function edit()
    {
        // Check for request forgeries.
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Create response object
        $response = new Prism\Response\Json();

        $userId = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get file ID.
        $fileId = $this->input->get->getUint('file_id');

        // Validate owner of the file.
        $ownerValidator = new Crowdfundingfiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
        if (!$ownerValidator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_INVALID_FILE'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingFilesModelFiles */

        $filedata = array();

        try {
            $filedata = $model->loadFiledata($fileId, $userId);
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfundingfiles');
            throw new Exception($e->getMessage());
        }

        $response
            ->setData($filedata)
            ->success();

        echo $response;
        $app->close();
    }

    public function update()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Create response object
        $response = new Prism\Response\Json();

        $userId = (int)JFactory::getUser()->get('id');
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get file ID.
        $fileId = $this->input->post->getUint('file_id');

        // Validate owner of the file.
        $ownerValidator = new Crowdfundingfiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
        if (!$ownerValidator->isValid()) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_INVALID_FILE'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingFilesModelFiles */

        $filedata = array(
            'title'       =>  $this->input->post->getString('title'),
            'description' =>  $this->input->post->getString('description'),
            'section'     =>  $this->input->post->getString('section')
        );

        try {
            $model->updateFiledata($fileId, $userId, $filedata);
        } catch (RuntimeException $e) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_crowdfundingfiles');
            throw new Exception($e->getMessage());
        }

        $response->success();

        echo $response;
        $app->close();
    }
}
