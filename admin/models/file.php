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

// Register Observers
JLoader::register('CrowdfundingFilesObserverFile', CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR .'/tables/observers/file.php');
JObserverMapper::addObserverClassToClass('CrowdfundingFilesObserverFile', 'CrowdfundingFilesTableFile', array('typeAlias' => 'com_crowdfundingfiles.file'));

class CrowdfundingFilesModelFile extends JModelAdmin
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
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.file.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data The data of item
     *
     * @return    int      Item ID
     */
    public function save($data)
    {
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, 'id');
        $title       = Joomla\Utilities\ArrayHelper::getValue($data, 'title');
        $description = Joomla\Utilities\ArrayHelper::getValue($data, 'description');
        $section     = Joomla\Utilities\ArrayHelper::getValue($data, 'section');
        $userId      = Joomla\Utilities\ArrayHelper::getValue($data, 'user_id');

        if (!$description) {
            $description = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('title', $title);
        $row->set('description', $description);
        $row->set('section', $section);
        $row->set('user_id', $userId);

        $row->store(true);

        return $row->get('id');
    }
}
