<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;
use Joomla\Registry\Registry;

// no direct access
defined('_JEXEC') or die;

/**
 * Get a list of items
 */
class CrowdfundingFilesModelFiles extends JModelLegacy
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'File', $prefix = 'CrowdfundingFilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @param array $uploadedFileData
     * @param array $options
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     *
     * @return array
     */
    public function uploadFile(array $uploadedFileData, array $options)
    {
        $result          = array();
        $destination     = ArrayHelper::getValue($options, 'destination');
        $legalExtensions = ArrayHelper::getValue($options, 'legal_extensions');
        $legalFileTypes  = ArrayHelper::getValue($options, 'legal_types');

        $KB            = pow(1024, 2);
        $maxSize       = ArrayHelper::getValue($options, 'max_size', 0, 'int');
        $uploadMaxSize = $maxSize * $KB;

        if (!empty($uploadedFileData['name'])) {
            $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
            $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
            $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

            // Prepare file size validator
            $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
            $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

            // Prepare server validator.
            $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

            // Prepare image validator.
            $typeValidator = new Prism\File\Validator\Type($uploadedFile, $uploadedName);

            // Get allowed MIME types.
            $mimeTypes = explode(',', $legalFileTypes);
            $mimeTypes = array_map('trim', $mimeTypes);
            $typeValidator->setMimeTypes($mimeTypes);

            // Get allowed file extensions.
            $fileExtensions = explode(',', $legalExtensions);
            $fileExtensions = array_map('trim', $fileExtensions);
            $typeValidator->setLegalExtensions($fileExtensions);

            $file = new Prism\File\File($uploadedFile);
            $file
                ->addValidator($sizeValidator)
                ->addValidator($typeValidator)
                ->addValidator($serverValidator);

            // Validate the file
            if (!$file->isValid()) {
                throw new RuntimeException($file->getError());
            }

            // Upload the file.
            $filesystemOptions = new Registry;
            $filesystemOptions->set('filename_length', 6);

            $filesystemLocal = new Prism\Filesystem\Adapter\Local($destination);
            $sourceFile      = $filesystemLocal->upload($uploadedFileData, $filesystemOptions);

            if (!JFile::exists($sourceFile)) {
                throw new RuntimeException(JText::_('COM_CROWDFUNDINGFILES_ERROR_FILE_CANT_BE_UPLOADED'));
            }

            // Extract file data.
            $file = new Prism\File\File($sourceFile);
            $result = $file->extractFileData();
        }

        return $result;
    }

    /**
     * Store the files into database.
     *
     * @param array  $filedata
     * @param array  $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function storeFile($filedata, array $options)
    {
        $projectId = ArrayHelper::getValue($options, 'project_id', 0, 'int');
        $userId    = ArrayHelper::getValue($options, 'user_id', 0, 'int');
        $mediaUri  = ArrayHelper::getValue($options, 'media_uri');

        $result = array();

        if ((is_array($filedata) and count($filedata) > 0) and ($projectId > 0 and $userId > 0)) {
            $db = JFactory::getDbo();
            /** @var $db JDatabaseDriver */

            $filedataJson = json_encode($filedata);

            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName('#__cffiles_files'))
                ->set($db->quoteName('title') . '=' . $db->quote($filedata['filename']))
                ->set($db->quoteName('filename') . '=' . $db->quote($filedata['filename']))
                ->set($db->quoteName('filedata') . '=' . $db->quote($filedataJson))
                ->set($db->quoteName('project_id') . '=' . (int)$projectId)
                ->set($db->quoteName('user_id') . '=' . (int)$userId);

            $db->setQuery($query);
            $db->execute();

            $lastId = $db->insertid();

            // Add URI path to images
            $result = array(
                'id'       => $lastId,
                'filename' => $filedata['filename'],
                'mime'     => $filedata['mime'],
                'section'  => 'details',
                'file'     => $mediaUri . '/' . $filedata['filename']
            );
        }

        return $result;
    }

    /**
     * Delete a file.
     *
     * @param integer $fileId      File ID
     * @param string  $mediaFolder A path to files folder.
     *
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function removeFile($fileId, $mediaFolder)
    {
        // Remove the file.
        $file = new Crowdfundingfiles\File\File(JFactory::getDbo(), $fileId);
        $file->load($fileId);

        if ($file->getId()) {
            jimport('Prism.libs.Flysystem.init');
            $root         = JPath::clean(JPATH_ROOT, '/');
            $localAdapter = new League\Flysystem\Adapter\Local($root);
            $filesystem   = new League\Flysystem\Filesystem($localAdapter);

            $file->setPath($mediaFolder);
            $file->setFilesystem($filesystem);

            $file->remove();
        }
    }

    /**
     * Load file data.
     *
     * @param int $fileId
     * @param int $userId
     *
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return array
     */
    public function loadFiledata($fileId, $userId)
    {
        $filedata = array();

        // Remove the file.
        $file = new Crowdfundingfiles\File\File(JFactory::getDbo(), $fileId);
        $file->load(['id' => $fileId, 'user_id' => $userId]);

        if ($file->getId()) {
            $filedata = array(
                'title' => $file->getTitle(),
                'description' => $file->getDescription(),
                'section' => $file->getSection(),
            );
        }

        return $filedata;
    }

    /**
     * Load file data.
     *
     * @param int $fileId
     * @param int $userId
     *
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function updateFiledata($fileId, $userId, $filedata)
    {
        // Remove the file.
        $file = new Crowdfundingfiles\File\File(JFactory::getDbo(), $fileId);
        $file->load(['id' => $fileId, 'user_id' => $userId]);

        if ($file->getId()) {
            $file->setTitle($filedata['title']);
            $file->setDescription($filedata['description']);
            $file->setSection($filedata['section']);

            $file->store();
        }
    }
}
