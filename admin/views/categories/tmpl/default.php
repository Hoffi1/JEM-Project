<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

?>

<form action="<?php echo JRoute::_('index.php?option=com_jem&view=categories'); ?>" method="post" name="adminForm" id="adminForm">


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
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_( 'COM_JEM_SEARCH' );?>" value="<?php echo $this->lists['search']; ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_JEM_SEARCH'); ?>" />
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
			  			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions',array('all' => 0, 'archived' => 0, 'trash' => 0)), 'value', 'text', $this->lists['state'], true);?>
			</select>
			</div>
	</div>

	<div class="clearfix"> </div>



	<!-- TABLE -->

	<table class="table table-striped" id="articleList">
	<thead>
		<tr>
			<th class="center" width="1%"><?php echo JText::_( 'COM_JEM_NUM' ); ?></th>
			<th class="center" width="1%"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'JCATEGORY', 'c.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="20%"><?php echo JHTML::_('grid.sort', 'COM_JEM_ALIAS', 'c.alias', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
      <th width="10px" class="center" nowrap="nowrap"><?php echo JText::_( 'COM_JEM_COLOR' ); ?></th>
			<th width="15%"><?php echo JHTML::_('grid.sort', 'COM_JEM_GROUP', 'gr.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JText::_( 'COM_JEM_EVENTS' ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JText::_( 'JSTATUS' ); ?></th>
			<th width="7%"><?php echo JHTML::_('grid.sort', 'COM_JEM_ACCESS', 'c.access', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="5%"><?php echo JHTML::_('grid.sort', 'COM_JEM_REORDER', 'c.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%"><?php echo JHTML::_('grid.order', $this->rows, 'filesave.png', 'saveordercat' ); ?></th>
			<th width="1%" class="center" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_JEM_ID', 'c.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="20">
				<?php
				echo $this->pagination->getListFooter();
				?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
			foreach ($this->rows as $i => $row) :
			$link 		= 'index.php?option=com_jem&amp;task=categories.edit&amp;cid[]='. $row->id;
			$grouplink 	= 'index.php?option=com_jem&amp;task=groups.edit&amp;cid[]='. $row->groupid;
			$published 	= JHTML::_('jgrid.published', $row->published, $i, 'categories.' );
			$access = $row->groupname;
   		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
			<td class="center"><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
			<td align="left">
				<?php
				if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
					echo $row->treename.' '.$this->escape($row->catname);
				} else {
				?>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_JEM_EDIT_CATEGORY' );?>::<?php echo $row->catname; ?>">
					<?php echo $row->treename.' ';?>
					<a href="<?php echo $link; ?>">
					<?php echo $this->escape($row->catname); ?>
					</a></span>
				<?php
				}
				?>
			</td>
			<td>
				<?php
				if (JString::strlen($row->alias) > 25) {
					echo JString::substr( htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
				} else {
					echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
				}
				?>
			</td>
      <td class="center">
        <div class="colorpreview" style="width: 20px; background: <?php echo ( $row->color == '' )?"transparent":$row->color; ?>;" title="<?php echo $row->color; ?>">
        &nbsp;
        </div>
      </td>
			<td class="center">
				<?php if ($row->catgroup) {	?>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_JEM_EDIT_GROUP' );?>::<?php echo $row->catgroup; ?>">
					<a href="<?php echo $grouplink; ?>">
						<?php echo htmlspecialchars($row->catgroup, ENT_QUOTES, 'UTF-8'); ?>
					</a></span>
				<?php
				} else {
					echo '-';
				}
				?>
			</td>
			<td class="center">
				<?php echo $row->assignedevents; ?>
			</td>
			<td class="center">
				<?php echo $published; ?>
			</td>
			<td align="center">
				<?php echo $access; ?>
			</td>
			<td class="order" colspan="2">
				<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $this->ordering ); ?></span>

				<span><?php echo $this->pagination->orderDownIcon( $i, $this->pagination->total, true, 'orderdown', 'Move Down', $this->ordering );?></span>

				<?php $disabled = $this->ordering ?  '' : '"disabled=disabled"'; ?>

				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area" style="text-align: center" />
			</td>
			<td class="center"><?php echo $row->id; ?></td>
		</tr>
		<?php
 endforeach;
		?>
	</tbody>

	</table>

	<p class="copyright">
		<?php echo JEMAdmin::footer( ); ?>
	</p>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="categories" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>