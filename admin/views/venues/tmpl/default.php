<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;


$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_jem.category');
$saveOrder	= $listOrder=='ordering';

$params		= (isset($this->state->params)) ? $this->state->params : new JObject();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

?>

<script>
window.addEvent('domready', function() {
	var h = <?php echo $params->get('highlight','0'); ?>;

	switch(h)
	{
	case 0:
		break;
	case 1:
		highlightvenues();
		break;
	}
});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jem&view=venues'); ?>" method="post" name="adminForm" id="adminForm">


<!--  SIDEBAR -->

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>



<!-- FILTER -->

<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JEM_SEARCH');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_( 'COM_JEM_SEARCH' );?>" value="<?php echo $this->escape($this->state->get('filter_search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_JEM_SEARCH'); ?>" />
			</div>

			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>

			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>


			<div class="btn-group pull-right">
				<?php echo $this->lists['filter']; ?>
				<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions',array('all' => 0, 'archived' => 0, 'trash' => 0)), 'value', 'text', $this->state->get('filter_state'), true);?>
				</select>
			</div>
</div>

<div class="clearfix"> </div>


<!-- TABLE -->

<table class="table table-striped" id="articleList">
	<thead>
		<tr>
			<th width="1%" class="center"><?php echo JText::_( 'COM_JEM_NUM' ); ?></th>
			<th width="1%" class="center"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
			<th class="title"><?php echo JHtml::_('grid.sort', 'COM_JEM_VENUE', 'a.venue', $listDirn, $listOrder ); ?></th>
			<th width="20%"><?php echo JHtml::_('grid.sort', 'COM_JEM_ALIAS', 'a.alias', $listDirn, $listOrder ); ?></th>
			<th><?php echo JText::_( 'COM_JEM_WEBSITE' ); ?></th>
			<th><?php echo JHtml::_('grid.sort', 'COM_JEM_CITY', 'a.city', $listDirn, $listOrder ); ?></th>
			<th><?php echo JHtml::_('grid.sort', 'COM_JEM_STATE', 'a.state', $listDirn, $listOrder ); ?></th>
			<th width="1%"><?php echo JHtml::_('grid.sort', 'COM_JEM_COUNTRY', 'a.country', $listDirn, $listOrder ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JText::_( 'JSTATUS' ); ?></th>
			<th><?php echo JText::_( 'COM_JEM_CREATION' ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JText::_( 'COM_JEM_EVENTS' ); ?></th>
			<th width="8%" colspan="2"><?php echo JHtml::_('grid.sort', 'COM_JEM_REORDER', 'a.ordering', $listDirn, $listOrder ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JHtml::_('grid.sort', 'COM_JEM_ID', 'a.id', $listDirn, $listOrder ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="20">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody id="seach_in_here">
		<?php
		foreach ($this->items as $i => $row) :
		$ordering	= ($listOrder == 'ordering');
	/*	$row->cat_link = JRoute::_('index.php?option=com_categories&extension=com_jem&task=edit&type=other&cid[]='. $row->catid);*/
		$canCreate	= $user->authorise('core.create');
		$canEdit	= $user->authorise('core.edit');
		$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $row->checked_out == $userId || $row->checked_out == 0;
		$canChange	= $user->authorise('core.edit.state') && $canCheckin;




			$link 		= 'index.php?option=com_jem&amp;task=venue.edit&amp;id='. $row->id;
			$published 	= JHtml::_('jgrid.published', $row->published, $i, 'venues.', $canChange, 'cb', $row->publish_up, $row->publish_down);
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
			<td class="center"><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
			<td align="left" class="venue">
				<?php if ($row->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'venues.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_jem&task=venue.edit&id='.(int) $row->id); ?>">
						<?php echo $this->escape($row->venue); ?></a>
				<?php else : ?>
						<?php echo $this->escape($row->venue); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php
				if (JString::strlen($row->alias) > 25) {
					echo JString::substr($this->escape($row->alias), 0 , 25).'...';
				} else {
					echo $this->escape($row->alias);
				}
				?>
			</td>
			<td align="left">
				<?php
				if ($row->url) {
				?>
					<a href="<?php echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
						<?php
						if (JString::strlen($row->url) > 25) {
							echo JString::substr( htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
						} else {
							echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8');
						}
						?>
					</a>
				<?php
				} else {
					echo  '-';
				}
				?>
			</td>
			<td align="left" class="city"><?php echo $row->city ? htmlspecialchars($row->city, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
			<td align="left" class="state"><?php echo $row->state ? htmlspecialchars($row->state, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
			<td class="center" class="country"><?php echo $row->country ? htmlspecialchars($row->country, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
			<td class="center"><?php echo $published; ?></td>
			<td>
				<?php echo JText::_( 'COM_JEM_AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
				<?php echo JText::_( 'COM_JEM_EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
				<?php
				$delivertime 	= JHtml::Date( $row->created, JText::_( 'DATE_FORMAT_LC2' ) );
				$edittime 		= JHtml::Date( $row->modified, JText::_( 'DATE_FORMAT_LC2' ) );
				$ip				= $row->author_ip == 'COM_JEM_DISABLED' ? JText::_( 'COM_JEM_DISABLED' ) : $row->author_ip;
				$image 			= JHtml::image('media/com_jem/images/icon-16-info.png', JText::_('COM_JEM_NOTES') );
				$overlib 		= JText::_( 'COM_JEM_CREATED_AT' ).': '.$delivertime.'<br />';
				$overlib		.= JText::_( 'COM_JEM_WITH_IP' ).': '.$ip.'<br />';
				if ($row->modified != '0000-00-00 00:00:00') {
					$overlib 	.= JText::_( 'COM_JEM_EDITED_AT' ).': '.$edittime.'<br />';
					$overlib 	.= JText::_( 'COM_JEM_EDITED_FROM' ).': '.$row->editor.'<br />';
				}
				?>
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_JEM_VENUE_STATS'); ?>::<?php echo $overlib; ?>">
					<?php echo $image; ?>
				</span>
			</td>
			<td class="center"><?php echo $row->assignedevents; ?></td>
			<td align="right">
				<?php
				/* @todo fix ordering  */
				echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering );
				?>
			</td>
			<td align="left">
				<?php
				echo $this->pagination->orderDownIcon( $i,$this->pagination->total, true, 'orderdown', 'Move Down', $ordering );
				?>
			</td>
			<td class="center"><?php echo $row->id; ?></td>
		</tr>
		<?php endforeach; ?>

	</tbody>

</table>

<p class="copyright">
	<?php echo JEMAdmin::footer( ); ?>
</p>
<div>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>