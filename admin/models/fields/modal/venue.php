<?php
// No direct access
 defined('_JEXEC') or die('Restricted access');

 jimport('joomla.form.formfield');

 /**
  * Book form field class
  */
 class JFormFieldModal_Venue extends JFormField
 {
        /**
         * field type
         * @var string
         */
        protected $type = 'Modal_Venue';


 	/**
   * Method to get the field input markup
   */
  protected function getInput()
  {
          // Load modal behavior
          JHtml::_('behavior.modal', 'a.modal');

          // Build the script
          $script = array();
          $script[] = '    function jSelectVenue_'.$this->id.'(id, venue, object) {';
          $script[] = '        document.id("'.$this->id.'_id").value = id;';
          $script[] = '        document.id("'.$this->id.'_name").value = venue;';
          $script[] = '        SqueezeBox.close();';
          $script[] = '    }';

          // Add to document head
          JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

          // Setup variables for display
          $html = array();

          $link = 'index.php?option=com_jem&amp;view=venueelement&amp;tmpl=component&amp;function=jSelectVenue_'.$this->id;


          $db = JFactory::getDbo();
          $query = $db->getQuery(true);
          $query->select('venue');
          $query->from('#__jem_venues');
          $query->where('id='.(int)$this->value);
          $db->setQuery($query);


          $venue = $db->loadResult();

          if ($error = $db->getErrorMsg()) {
          	JError::raiseWarning(500, $error);
          }



          if (empty($venue)) {
                  $venue = JText::_('COM_JEM_SELECTVENUE');
          }
          $venue = htmlspecialchars($venue, ENT_QUOTES, 'UTF-8');

          // The current venue input field
          $html[] = '<span class="input-append">';
          $html[] = '  <input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$venue.'" disabled="disabled" size="35" />';
          $html[] = '    <a class="modal btn hasTooltip" title="'.JHtml::tooltipText('COM_JEM_SELECT').'" href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x:800, y:450}}">'.
          		JText::_('JSELECT').'</a>';
          $html[] = '</span>';


         // The active venue id field
          if (0 == (int)$this->value) {
                  $value = '';
          } else {
                  $value = (int)$this->value;
          }

          // class='required' for client side validation
          $class = '';
          if ($this->required) {
                  $class = ' class="required modal-value"';
          }

          $html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

         return implode("\n", $html);
  }




















 }
 ?>