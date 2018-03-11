<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfundingfiles'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

            <?php echo $this->form->renderField('title'); ?>
            <?php echo $this->form->renderField('description'); ?>
            <?php echo $this->form->renderField('section'); ?>
            <?php echo $this->form->renderField('user_id'); ?>
            <?php echo $this->form->renderField('filename'); ?>
            <?php echo $this->form->renderField('project_id'); ?>
            <?php echo $this->form->renderField('id'); ?>

            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>