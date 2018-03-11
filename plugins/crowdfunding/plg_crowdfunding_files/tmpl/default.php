<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the script that initializes the select element with banks.
$doc->addScript('plugins/crowdfunding/files/js/script.js?v=' . rawurlencode($this->version));
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <h4><span class="fa fa-file"></span> <?php echo JText::_('PLG_CROWDFUNDING_FILES_FILES');?></h4>
    </div>

    <div class="panel-body">
        <span class="btn btn-primary fileinput-button">
            <span class="fa fa-upload"></span>
            <span><?php echo JText::_('PLG_CROWDFUNDING_FILES_UPLOAD');?></span>
            <!-- The file input field used as target for the file upload widget -->
            <input id="js-cffiles-fileupload" type="file" name="file" data-url="<?php echo JRoute::_('index.php?option=com_crowdfundingfiles');?>" multiple />
        </span>
        <span class="fa fa-spinner fa-spin" id="js-cffiles-ajax-loader" style="display: none;" aria-hidden="true"></span>

        <?php if ($this->params->get('display_note', Prism\Constants::YES)) { ?>
        <div class="alert alert-info p-5 mt-5">
            <strong>
                <span class="fa fa-info-circle"></span>
                <?php echo JText::_('PLG_CROWDFUNDING_FILES_INFORMATION'); ?>
            </strong>
            <p><?php echo JText::sprintf('PLG_CROWDFUNDING_FILES_FILE_TYPES_NOTE', $this->params->get('files_type_info', 'PDF, RTF, DOC, PPS, PPT, XLS')); ?></p>
            <p><?php echo JText::sprintf('PLG_CROWDFUNDING_FILES_FILE_SIZE_NOTE', $maxFileSize); ?></p>
        </div>
        <?php } ?>

        <table class="table table-bordered mtb-25-0">
            <thead>
            <tr>
                <th class="col-md-5"><?php echo JText::_('PLG_CROWDFUNDING_FILES_TITLE');?></th>
                <th class="col-md-2 hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_FILENAME');?></th>
                <th class="col-md-1 hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_SECTION');?></th>
                <th class="col-md-4">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="js-cffiles-list">
                <?php foreach ($files as $file) {
                $fileType = array_key_exists('mime', $file['filedata']) ? $file['filedata']['mime'] : '';
                ?>
                <tr id="js-cffiles-file<?php echo $file['id']; ?>">
                    <td>
                        <?php
                        echo htmlentities($file['title'], ENT_QUOTES, 'UTF-8');
                        if ($file['description'] !== '') {
                            echo '<p>'.htmlentities($file['description'], ENT_QUOTES, 'UTF-8').'</p>';
                        }
                        ?>
                    </td>
                    <td class="hidden-xs"><?php echo htmlentities($file['filename'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="hidden-xs"><?php echo htmlentities($file['section'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a class="btn btn-default hidden-xs" href="<?php echo $mediaUri.'/'.$file['filename'];?>" type="<?php echo $fileType;?>" download>
                            <span class="fa fa-download"></span>
                            <span><?php echo JText::_('PLG_CROWDFUNDING_FILES_DOWNLOAD');?></span>
                        </a>

                        <button class="btn btn-default hidden-xs js-cffile-btn-edit" data-file-id="<?php echo (int)$file['id']; ?>">
                            <span class="fa fa-pencil"></span>
                            <span class="hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_EDIT');?></span>
                        </button>

                        <button class="btn btn-danger js-cffile-btn-remove" data-file-id="<?php echo (int)$file['id']; ?>">
                            <span class="fa fa-trash"></span>
                            <span class="hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_DELETE');?></span>
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>


<table style="display: none;">
    <thead>
    <tr>
        <th class="col-md-5"><?php echo JText::_('PLG_CROWDFUNDING_FILES_TITLE');?></th>
        <th class="col-md-2"><?php echo JText::_('PLG_CROWDFUNDING_FILES_FILENAME');?></th>
        <th class="col-md-1"><?php echo JText::_('PLG_CROWDFUNDING_FILES_SECTION');?></th>
        <th class="col-md-4">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
        <tr id="js-cffiles-element">
            <td>{TITLE}</td>
            <td class="hidden-xs">{FILENAME}</td>
            <td class="hidden-xs">{SECTION}</td>
            <td>
                <a class="btn btn-default hidden-xs js-cffile-btn-download" href="#" role="button" type="" download>
                    <span class="fa fa-download"></span>
                    <span class="hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_DOWNLOAD');?></span>
                </a>
                <button class="btn btn-default hidden-xs js-cffile-btn-edit">
                    <span class="fa fa-pencil"></span>
                    <span class="hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_EDIT');?></span>
                </button>
                <button class="btn btn-danger js-cffile-btn-remove">
                    <span class="fa fa-trash"></span>
                    <span class="hidden-xs"><?php echo JText::_('PLG_CROWDFUNDING_FILES_DELETE');?></span>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<div id="js-cffiles-modal">
    <form action="post" id="js-cffiles-form-edit" autocomplete="off">
        <div class="form-group">
            <label for="js-cffiles-edit-form-title"><?php echo JText::_('PLG_CROWDFUNDING_FILES_TITLE');?></label>
            <input type="text" name="title" class="form-control" id="js-cffiles-edit-form-title">
        </div>
        <div class="form-group">
            <label for="js-cffiles-edit-form-description"><?php echo JText::_('PLG_CROWDFUNDING_FILES_DESCRIPTION');?></label>
            <textarea name="description" class="form-control" id="js-cffiles-edit-form-description"></textarea>
        </div>

        <div class="form-group">
            <label for="js-cffiles-edit-form-section"><?php echo JText::_('PLG_CROWDFUNDING_FILES_SECTION');?></label>
            <select name="section" id="js-cffiles-edit-form-section">
                <option value="details"><?php echo JText::_('PLG_CROWDFUNDING_FILES_DETAILS');?></option>
                <option value="conditions"><?php echo JText::_('PLG_CROWDFUNDING_FILES_CONDITIONS');?></option>
            </select>

            <div class="alert alert-info mt-5">
                <p><?php echo JText::_('PLG_CROWDFUNDING_FILES_SECTION_DETAILS'); ?></p>
                <p><?php echo JText::_('PLG_CROWDFUNDING_FILES_SECTION_CONDITIONS'); ?></p>
            </div>
        </div>

        <input id="js-cffiles-form-edit-file-id" type="hidden" name="file_id" value="0" />
    </form>

    <div class="mt-10">
        <a href="javascript: void(0);" class="btn btn-primary" id="js-cffiles-edit-btn-submit">
            <span class="fa fa-check-circle"></span>
            <?php echo JText::_('COM_CROWDFUNDING_FILES_SUBMIT');?>
        </a>

        <a href="javascript: void(0);" class="btn btn-default" id="js-cffiles-edit-btn-cancel">
            <span class="fa fa-ban"></span>
            <?php echo JText::_('COM_CROWDFUNDING_FILES_CANCEL');?>
        </a>

        <span class="fa fa-spinner fa-spin" id="js-cffiles-modal-loader" style="display: none;" aria-hidden="true"></span>
    </div>

</div>