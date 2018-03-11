<?php
/**
 * @package      Crowdfundingfiles
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Crowdfundingfiles\Helper;

use Joomla\Registry\Registry;
use Prism\Helper\HelperInterface;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare filedata.
 *
 * @package      Crowdfundingfiles
 * @subpackage   Helpers
 */
class PrepareFiledataHelper implements HelperInterface
{
    /**
     * Prepare the filedata of the items.
     *
     * @param array $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $item) {
                if ($item->filedata === null) {
                    $item->filedata = '{}';
                }

                if (is_string($item->filedata) and $item->filedata !== '') {
                    $filedata = new Registry;
                    $filedata->loadString($item->filedata);
                    $item->filedata = $filedata;
                }
            }
        }
    }
}
