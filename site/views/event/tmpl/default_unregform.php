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
<?php
//the user is allready registered. Let's check if he can unregister from the event


if ($this->print == 0) {

if ($this->row->unregistra == 0) :

	//no he is not allowed to unregister
	echo JText::_( 'COM_JEM_ALLREADY_REGISTERED' );

else:

	//he is allowed to unregister -> display form
	?>
	<form id="JEM" action="<?php echo JRoute::_('index.php'); ?>" method="post">
		<p>
			<?php if ($this->isregistered == 2): ?>
				<?php echo JText::_( 'COM_JEM_WAITINGLIST_UNREGISTER_BOX' ).': '; ?>
			<?php else: ?>
				<?php echo JText::_( 'COM_JEM_UNREGISTER_BOX' ).': '; ?>
			<?php endif;?>
			<input type="checkbox" name="reg_check" onclick="check(this, document.getElementById('jem_send_attend'))" />
		</p>
		<p></p>
		<div class="center">
			<input class="btn" type="submit" id="jem_send_attend" name="jem_send_attend" value="<?php echo JText::_( 'COM_JEM_UNREGISTER' ); ?>" disabled="disabled" />
		</div>

		<p></p>
			<input type="hidden" name="rdid" value="<?php echo $this->row->did; ?>" />
			<?php echo JHtml::_( 'form.token' ); ?>
			<input type="hidden" name="task" value="delreguser" />
	</form>
	<?php
endif;
}