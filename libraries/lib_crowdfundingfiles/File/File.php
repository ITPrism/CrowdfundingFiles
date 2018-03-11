<?php
/**
 * @package      CrowdfundingFiles\File
 * @subpackage   Removers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfundingfiles\File;

use Prism\Database;
use League\Flysystem\Filesystem;
use League\Flysystem\FileNotFoundException;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for removing a file from database.
 *
 * @package      CrowdfundingFiles\File
 * @subpackage   Removers
 */
class File extends Database\Table
{
    protected $id;
    protected $title;
    protected $description;
    protected $filename;
    protected $filedata = array();
    protected $section;
    protected $project_id;
    protected $user_id;

    protected $path;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Load file data from database.
     *
     * <code>
     * $fileId = 1;
     *
     * $file   = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.title, a.description, a.filename, a.filedata, a.section, a.project_id, a.user_id')
            ->from($this->db->quoteName('#__cffiles_files', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);

        $this->filedata = (array)json_decode($this->filedata);
    }

    /**
     * Store the data in database.
     *
     * <code>
     * $data = (
     *     "title"          => "File title...",
     *     "description"    => "File description...",
     *     "filename"       => "file.pdf",
     *     "filedata"       => array("mime" => "application/pdf", "filename" => "file.pdf", "filesize" => 25876, "filetype" => "pdf_document", "extension" => "pdf")
     *     "section"        => 'details', // details, conditions
     *     "project_id"     => 1,
     *     "user_id"        => 2
     * );
     *
     * $file   = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->bind($data);
     * $file->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function insertObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $filedata      = json_encode($this->filedata);

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName('#__cffiles_files'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('filedata') . '=' . $this->db->quote($filedata))
            ->set($this->db->quoteName('section') . '=' . $this->db->quote($this->section))
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $filedata      = json_encode($this->filedata);

        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName('#__cffiles_files'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('filedata') . '=' . $this->db->quote($filedata))
            ->set($this->db->quoteName('section') . '=' . $this->db->quote($this->section))
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id)
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Return file ID.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * if (!$file->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Return file title.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set file title.
     *
     * <code>
     * $fileId  = 1;
     * $title   = 'My File';
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * $file->setTitle($title);
     * </code>
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Return file description.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set file description.
     *
     * <code>
     * $fileId  = 1;
     * $description   = 'My file description...';
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * $file->setDescription($description);
     * </code>
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set section of the record.
     *
     * <code>
     * $fileId    = 1;
     * $section   = 'details';
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * $file->setSection($section);
     * </code>
     *
     * @param string $section
     *
     * @return self
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Return file name.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getFilename();
     * </code>
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Return the section of the record.
     * For 'details' the file will be displayed on details page.
     * For 'terms' the file will be displayed on payment wizard.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getSection();
     * </code>
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Return value from file data.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getData('filesize');
     * </code>
     *
     * @param string $key
     * @param mixed $default
     *
     * @return string
     */
    public function getData($key, $default = '')
    {
        return array_key_exists($key, $this->filedata) ? $this->filedata[$key] : $default;
    }

    /**
     * Return the project ID.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return (int)$this->project_id;
    }

    /**
     * Return the user ID.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Set relative path to files.
     *
     * <code>
     * $fileId  = 1;
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->setPath('/.../files');
     * </code>
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        
        return $this;
    }

    /**
     * Set filesystem object (adapter).
     *
     * <code>
     * $fileId  = 1;
     *
     * $localAdapter  = new League\Flysystem\Adapter\Local(JPATH_ROOT);
     * $filesystem    = new League\Flysystem\Filesystem($localAdapter);
     *
     * $file    = new Crowdfundingfiles\File\File(\JFactory::getDbo());
     * $file->load($fileId);
     *
     * echo $file->setFilesystem($filesystem);
     * </code>
     *
     * @param Filesystem $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Remove the file from database.
     * It will delete the file from the filesystem if you have provided filesystem object.
     *
     * <code>
     * $fileId = 1;
     * $filesFolder = "/.../folder";
     *
     * $localAdapter  = new League\Flysystem\Adapter\Local(JPATH_ROOT);
     * $filesystem    = new League\Flysystem\Filesystem($localAdapter);
     *
     * $file   = new Crowdfundingfiles\File\File(JFactory::getDbo());
     * $file->load($fileId);
     *
     * $file->setPath($mediaFolder);
     * $file->setFilesystem($filesystem);
     *
     * $file->remove();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws FileNotFoundException
     */
    public function remove()
    {
        // Delete the file.
        $this->delete();

        // Remove the record from database.
        if ($this->id > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName('#__cffiles_files'))
                ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Delete the file from filesystem.
     *
     * <code>
     * $fileId = 1;
     * $filesFolder = "/.../folder";
     *
     * $localAdapter  = new League\Flysystem\Adapter\Local(JPATH_ROOT);
     * $filesystem    = new League\Flysystem\Filesystem($localAdapter);
     *
     * $file   = new Crowdfundingfiles\File\File(JFactory::getDbo());
     * $file->load($fileId);
     *
     * $file->setPath($mediaFolder);
     * $file->setFilesystem($filesystem);
     *
     * $file->delete();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws FileNotFoundException
     */
    public function delete()
    {
        // Delete the file.
        if (($this->filename !== null and $this->filename !== '') and $this->filesystem !== null) {
            $file     = \JPath::clean($this->path .'/'. $this->filename, '/');

            if ($this->filesystem->has($file)) {
                $this->filesystem->delete($file);
            }
        }
    }
}
