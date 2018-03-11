<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Files
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfundingfiles\File;

use Prism\Database\Collection;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage files.
 *
 * @package      CrowdfundingFiles
 * @subpackage   Files
 */
class Files extends Collection
{
    /**
     * Load files data by ID from database.
     *
     * <code>
     * $options = array(
     *    "ids"         => array(1,2,3,4,5),
     *    "section"     => 'details',
     *    "project_id" => 1,
     *    "user_id"    => 2
     * );
     *
     * $files   = new Crowdfundingfiles\Files(JFactory::getDbo());
     * $files->load($options);
     *
     * foreach($files as $file) {
     *   echo $file["title"];
     *   echo $file["filename"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function load(array $options = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.title, a.description, a.filename, a.filedata, a.section, a.project_id, a.user_id')
            ->from($this->db->quoteName('#__cffiles_files', 'a'));

        $ids = array_key_exists('ids', $options) ? $options['ids'] : null;
        if (is_array($ids) and count($ids) > 0) {
            $ids = ArrayHelper::toInteger($ids);
            $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
        }

        $projectId = ArrayHelper::getValue($options, 'project_id', 0, 'int');
        if ($projectId > 0) {
            $query->where('a.project_id = ' . (int)$projectId);
        }

        $userId = ArrayHelper::getValue($options, 'user_id', 0, 'int');
        if ($userId > 0) {
            $query->where('a.user_id = ' . (int)$userId);
        }

        $section = ArrayHelper::getValue($options, 'section', '', 'word');
        if ($section !== '') {
            $query->where('a.section = ' . $this->db->quote($section));
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();

        foreach ($this->items as &$item) {
            $item['filedata'] = (array)json_decode($item['filedata']);
        }
        unset($item);
    }
}
