<?php
/**
 * @version 2.0.0
 * @package JEM
 * @copyright (C) 2013-2014 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * Holds the logic for attachments manipulation
 *
 * @package JEM
 */
class JButtonFrontend extends JButton {


	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Standard';



//Goes inside JButtonFrontend class definition.
public function fetchButton($type = 'Standard', $name = '', $text = '', $task = '', $list = true)
{
	$i18n_text = JText::_($text);
	$class = $this->fetchIconClass($name);
	$doTask = $this->_getCommand($text, $task, $list);

	$html = "<a href=\"javascript: void( $doTask);\" onclick=\"$doTask\" class=\"toolbar\">\n";
	$html .= "<span class=\"$class\">\n";
	$html .= "</span>\n";
	$html .= "$i18n_text\n";
	$html .= "</a>\n";

	return $html;
}

/**
 * Get the button CSS Id
 *
 * @param   string   $type      Unused string.
 * @param   string   $name      Name to be used as apart of the id
 * @param   string   $text      Button text
 * @param   string   $task      The task associated with the button
 * @param   boolean  $list      True to allow use of lists
 * @param   boolean  $hideMenu  True to hide the menu on click
 *
 * @return  string  Button CSS Id
 *
 *
 */
public function fetchId($type = 'Standard', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
{
	return $this->_parent->getName() . '-' . $name;
}

/**
 * Get the JavaScript command for the button
 *
 * @param   string   $name  The task name as seen by the user
 * @param   string   $task  The task used by the application
 * @param   boolean  $list  True is requires a list confirmation.
 *
 * @return  string   JavaScript command string
 *
 * 
 */
protected function _getCommand($name, $task, $list)
{
	JHtml::_('behavior.framework');
	$message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
	$message = addslashes($message);

	if ($list)
	{
		$cmd = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{ Joomla.submitbutton('$task')}";
	}
	else
	{
		$cmd = "Joomla.submitbutton('$task')";
	}

	return $cmd;
}

}
?>