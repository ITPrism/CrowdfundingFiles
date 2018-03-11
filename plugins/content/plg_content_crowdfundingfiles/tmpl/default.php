<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><span class="fa fa-files-o"></span> <?php echo JText::_('PLG_CONTENT_CROWDFUNDINGFILES_FILES');?></h4>
    </div>

    <div class="panel-body">
        <?php
        foreach ($files as $file) {
            $filesize  = array_key_exists('filesize', $file['filedata']) ? $file['filedata']['filesize'] : 0;
            $description = Joomla\String\StringHelper::trim(strip_tags($file['description']));

            $iconClass = '';
            $mimeType  = '';
            $mime      = array_key_exists('mime', $file['filedata']) ? $file['filedata']['mime'] : '';
            if ($mime !== '') {
                $filetype = new Prism\File\Type($mime);
                $iconClass = $filetype->getIcon();

                $mimeType = 'type="'.$mime.'"';
            }
        ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?php
                if ($iconClass !== '') {
                    echo '<span class="fa ' . $iconClass . '"></span>';
                }
                ?>
                <a href="<?php echo $mediaFolderUri .'/'. $file['filename']; ?>" <?php echo $mimeType; ?> download>
                    <?php echo htmlentities($file['title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php
                if ($filesize > 0) {
                    echo '(' . Prism\Utilities\MathHelper::convertFromBytes($filesize) .')';
                }?>

                <?php
                if ($description !== '') {
                    echo '<p>'.htmlspecialchars($description).'</p>';
                }?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>