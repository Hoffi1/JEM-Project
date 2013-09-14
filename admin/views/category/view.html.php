<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;


/**
 * View class for the JEM category screen
 *
 * @package JEM
 *
 */
class JEMViewCategory extends JViewLegacy {

	public function display($tpl = null)
	{
		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$editor 	= JFactory::getEditor();
		$document	= JFactory::getDocument();
		$user 		= JFactory::getUser();
		$app 		= JFactory::getApplication();

		// Load the form validation behavior
		JHTML::_('behavior.formvalidation');

		//add css to document
		//$document->addStyleSheet(JURI::root().'media/com_jem/css/backend.css');
		$document->addScript(JURI::root().'media/com_jem/js/attachments.js');
		// for color picker
		$document->addStyleSheet(JURI::root().'media/com_jem/css/picker.css');
		$document->addScript(JURI::root().'media/com_jem/js/picker.js');

		//Get data from the model
		$model		= $this->getModel();
		$row	 	= $this->get('Data');
		$groups 	= $this->get('Groups');
		$categories = JEMCategories::getCategoriesTree(0);

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut($user->get('id'))) {
				JError::raiseWarning('SOME_ERROR_CODE', $row->catname.' '.JText::_('COM_JEM_EDITED_BY_ANOTHER_ADMIN'));
				$app->redirect('index.php?option=com_jem&view=categories');
			}
		}

		//clean data
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'catdescription');

		//build selectlists
		$Lists = array();
		$Lists['access'] 	= JHTML::_('access.assetgrouplist', 'access', $row->access);
		$Lists['parent_id'] = JEMCategories::buildcatselect($categories, 'parent_id', $row->parent_id, 1);

		//build image select js and load the view
		$js = "
		function elSelectImage(image, imagename) {
			document.getElementById('a_image').value = image;
			document.getElementById('a_imagename').value = imagename;
			document.getElementById('imagelib').src = '../images/jem/categories/' + image;
			window.parent.SqueezeBox.close();
		}";

		$link = 'index.php?option=com_jem&amp;view=imagehandler&amp;layout=uploadimage&amp;task=categoriesimg&amp;tmpl=component';
		$link2 = 'index.php?option=com_jem&amp;view=imagehandler&amp;task=selectcategoriesimg&amp;tmpl=component';
		$document->addScriptDeclaration($js);

		$imageselect = "\n<span class=\"input-append\">";
		$imageselect .= "\n<input class=\"input-medium\" style=\"background: #ffffff;\" type=\"text\" id=\"a_imagename\" value=\"$row->image\" disabled=\"disabled\" onchange=\"javascript:if (document.forms[0].a_imagename.value!='') {document.imagelib.src='../images/jem/categories/' + document.forms[0].a_imagename.value} else {document.imagelib.src='../images/blank.png'}\"; />";

		$imageselect .= "<a class=\"modal btn\" title=\"".JText::_('COM_JEM_UPLOAD')."\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('COM_JEM_UPLOAD')."</a>\n";
		$imageselect .= "<a class=\"modal btn\" title=\"".JText::_('COM_JEM_SELECTIMAGE')."\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('COM_JEM_SELECTIMAGE')."</a>\n";

		$imageselect .= "\n<button class=\"btn\" type=\"button\" onclick=\"elSelectImage('', '".JText::_('COM_JEM_SELECTIMAGE')."');\" value=\"".JText::_('COM_JEM_RESET')."\" /><i class=\"icon-remove\"></i></button>";
		$imageselect .= "\n<input type=\"hidden\" id=\"a_image\" name=\"image\" value=\"$row->image\" />";
		$imageselect .= '</span>';

		$this->imageselect 	= $imageselect;

		//build grouplist
		$grouplist		= array();
		$grouplist[] 	= JHTML::_('select.option', '0', JText::_('COM_JEM_NO_GROUP'));
		$grouplist 		= array_merge($grouplist, $groups);

		$Lists['groups']	= JHTML::_('select.genericlist', $grouplist, 'groupid', 'size="1" class="inputbox"', 'value', 'text', $row->groupid);

		//assign data to template
		$this->Lists 		= $Lists;
		$this->row 			= $row;
		$this->editor 		= $editor;
		$access2 = JEMHelper::getAccesslevelOptions();
		$this->access 		= $access2;

		// add toolbar
		$this->addToolbar();

		parent::display($tpl);
	}


	/**
	 * Add Toolbar
	 */
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', 1);

		//get vars
		$cid = JRequest::getVar('cid');

		//create the toolbar
		if ($cid) {
			JToolBarHelper::title(JText::_('COM_JEM_EDIT_CATEGORY'), 'categoriesedit');
		} else {
			JToolBarHelper::title(JText::_('COM_JEM_ADD_CATEGORY'), 'categoriesedit');
		}
		JToolBarHelper::apply('category.apply');
		JToolBarHelper::spacer();
		JToolBarHelper::save('category.save');
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('category.cancel');
		JToolBarHelper::spacer();
		JToolBarHelper::help('editcategories', true);
	}
}
?>