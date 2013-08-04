<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="imghead">
		<?php echo JText::_('COM_JEM_SEARCH').' '; ?>
		<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onChange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('COM_JEM_GO'); ?></button>
		<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_JEM_RESET'); ?></button>
	</div>

	<div class="imglist">
		<?php
		for ($i = 0; $i < count($this->images); $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('image');
		endfor;
		?>
	</div>

	<div class="clear"></div>

	<div class="pnav"><?php echo $this->pagination->getListFooter(); ?></div>

	<input type="hidden" name="option" value="com_jem" />
	<input type="hidden" name="view" value="imagehandler" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
</form>