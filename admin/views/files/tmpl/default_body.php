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
?>
<?php foreach ($this->items as $i => $item) {
    $mediaFolder = CrowdfundingFilesHelper::getMediaFolderUri(JUri::root(), $item->user_id);
    $mime        = $item->filedata->get('mime');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfundingfiles&view=file&layout=edit&id=' . $item->id); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
            <div class="small">
                <?php echo JText::_('COM_CROWDFUNDINGFILES_PROJECT'); ?> :
                <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects&filter_search=id:' . $item->project_id); ?>">
                    <?php echo $this->escape($item->project); ?>
                </a>
            </div>
        </td>
        <td>
            <?php echo $this->escape($item->filename); ?>
            <a class="btn btn-mini" href="<?php echo $mediaFolder . '/'. $item->filename; ?>" type="<?php echo $mime; ?>" download>
                <i class="icon-download"></i>
            </a>
        </td>
        <td>
            <?php echo $this->escape($item->section); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=users&filter_search=id:' . $item->user_id); ?>">
                <?php echo $this->escape($item->user); ?>
            </a>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id;?>
        </td>
    </tr>
<?php }?>
