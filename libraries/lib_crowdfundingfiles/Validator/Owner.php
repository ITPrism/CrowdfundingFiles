<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfundingfiles\Validator;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality validation file owner.
 *
 * @package      CrowdfundingFiles
 * @subpackage   Validators
 */
class Owner implements ValidatorInterface
{
    protected $db;
    protected $fileId;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $fileId = 1;
     * $userId = 2;
     *
     * $file   = new Crowdfundingfiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     * @param int $fileId File ID.
     * @param int $userId User ID.
     */
    public function __construct($db, $fileId, $userId)
    {
        $this->db      = $db;
        $this->fileId  = $fileId;
        $this->userId  = $userId;
    }

    /**
     * Validate image owner.
     *
     * <code>
     * $fileId = 1;
     * $userId = 2;
     *
     * $file   = new Crowdfundingfiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
     * if (!$file->isValid()) {
     * ...
     * }
     * </code>
     *
     * @throws \RuntimeException
     */
    public function isValid()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__cffiles_files', 'a'))
            ->where('a.id = ' .(int)$this->fileId)
            ->where('a.user_id = ' . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        $result = $this->db->loadResult();

        return (bool)$result;
    }
}
