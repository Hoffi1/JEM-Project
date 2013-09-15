<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

defined('_JEXEC') or die;


/**
 * View class for the Venues screen
 *
 * @package Joomla
 * @subpackage JEM
 *
 */

 class JEMViewVenues extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$user 		= JFactory::getUser();
		$document	= JFactory::getDocument();
		$url 		= JUri::root();

		// Initialise variables.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		$params = $this->state->get('params');

		// highlighter
		$highlighter = $params->get('highlight','0');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		JHtml::_('behavior.framework');


		//add css and submenu to document
		$document->addStyleSheet(JUri::root().'media/com_jem/css/backend.css');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
		$document->addCustomTag('<script type="text/javascript">jQuery.noConflict();</script>');

		$style = '
		thead {
			border-top: 1px solid grey;
		}
		';

		$document->addStyleDeclaration($style);


		if ($highlighter) {
			$document->addScript($url.'media/com_jem/js/highlighter.js');
			$style = '
			    .red a:link, .red a:visited, .red a:active {
			        color:red;}
			    ';
			$document->addStyleDeclaration($style);
		}

		//add style to description of the tooltip (hastip)
		//JHtml::_('behavior.tooltip');

		// add filter selection for the search
		$filters = array();
		$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_VENUE'));
		$filters[] = JHtml::_('select.option', '2', JText::_('COM_JEM_CITY'));
		$filters[] = JHtml::_('select.option', '3', JText::_('COM_JEM_STATE'));
		$filters[] = JHtml::_('select.option', '4', JText::_('COM_JEM_COUNTRY'));
		$filters[] = JHtml::_('select.option', '5', JText::_('JALL'));
		$lists['filter'] = JHtml::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $this->state->get('filter'));

		//assign data to template
		$this->lists = $lists;
		$this->user = $user;

		// add toolbar
		$this->addToolbar();

		parent::display($tpl);
		}


	/**
	 * Add Toolbar
	 */
	protected function addToolbar()
	{
		/* submenu */
		require_once JPATH_COMPONENT . '/helpers/helper.php';

		/*
		 * Adding title + icon
		 *
		 * the icon is mapped within backend.css
		 * The word 'venues' is referring to the venues icon
		 */
		JToolBarHelper::title(JText::_('COM_JEM_VENUES'), 'venues');

		/* retrieving the allowed actions for the user */
		$canDo = JEMHelperBackend::getActions(0);

		/* create */
		if (($canDo->get('core.create'))) {
			JToolBarHelper::addNew('venue.add');
		}

		/* edit */
		JToolBarHelper::spacer();
		if (($canDo->get('core.edit'))) {
			JToolBarHelper::editList('venue.edit');
		}

		/* state */
		if ($canDo->get('core.edit.state')) {
			if ($this->state->get('filter.state') != 2) {
				JToolBarHelper::publishList('venues.publish');
				JToolBarHelper::unpublishList('venues.unpublish');
			}

			/*
			if ($this->lists['state'] != -1) {
				JToolBarHelper::divider();
				if ($this->lists['state'] != 2) {
					JToolBarHelper::archiveList('venues.archive');
				} elseif ($this->lists['state'] == 2) {
					JToolBarHelper::unarchiveList('venues.unarchive');
				}
			}
			*/

		}

		/* delete-trash */
		if ($canDo->get('core.delete')) {
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('COM_JEM_CONFIRM_DELETE', 'venues.remove', 'JACTION_DELETE');
		}
		/*elseif ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('venues.trash');
			JToolBarHelper::divider();
		}
		*/

		/* copy */
		//JToolBarHelper::divider();
		//JToolBarHelper::spacer();
		//JToolBarHelper::custom('venues.copy', 'copy.png', 'copy_f2.png', 'COM_JEM_COPY');
		//JToolBarHelper::spacer();

		/* Reference to help-page located in the folder help.
		 * The variable 'true' is saying to look in the component directory
		 */
		JToolBarHelper::help('listvenues', true);
	}
}
?>