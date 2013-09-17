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
<div id="jem" class="jem_jem">
	<div class="icons">
		<?php
			echo JEMOutput::printbutton( $this->print_link, $this->params );
			echo JEMOutput::submitbutton( $this->dellink, $this->params );
			echo JEMOutput::archivebutton( $this->params, $this->task );

		?>
	</div>

	<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
		<h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>

	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
		</div>
	<?php endif; ?>

	<!--table-->
	<h2><?php echo JText::_( 'COM_JEM_SEARCHAREA' ); ?></h2>
	<form action="<?php echo $this->action; ?>" method="post" name="adminForm" id="adminForm">
		<?php echo $this->loadTemplate('events_table'); ?>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

		<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
		<input type="hidden" name="view" value="eventslist" />
	</form>

	<!--footer-->

	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<p></p>
	</div>

	<?php echo JEMOutput::icalbutton('', 'eventslist'); ?>

	<div class="copyright">
		<?php echo JEMOutput::footer( ); ?>
	</div>
</div>