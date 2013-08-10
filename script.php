<?php
defined('_JEXEC') or die;

$db = JFactory::getDBO();
jimport('joomla.filesystem.folder');


/**
 * Script file of JEM component
*/
class com_jemInstallerScript
{
	/**
	 * Method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$error = array(
				'summary' => 0,
				'folders' => 0
		);
		?>
<table class="adminlist">
	<tr>
		<td valign="top"><img src="../media/com_jem/images/jemlogo.png"
			height="100" width="250" alt="jem Logo" align="left">
		</td>
		<td valign="top" width="100%">
			<h1>JEM</h1>
			<p class="small">
				by <a href="http://www.joomlaeventmanager.net" target="_blank">joomlaeventmanager.net</a><br />
				Released under the terms and conditions of the <a
					href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU
					General Public License</a>.
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h2>Installation Status:</h2>
			<h3>Check Folders:</h3> <?php
			$imageDir = "/images/jem";

			$createDirs = array(
					$imageDir,
					$imageDir.'/categories',
					$imageDir.'/categories/small',
					$imageDir.'/events',
					$imageDir.'/events/small',
					$imageDir.'/venues',
					$imageDir.'/venues/small'
			);

			// Check for existance of /images/jem directory
			if ($direxists = JFolder::exists(JPATH_SITE.$createDirs[0])) {
			echo "<p><span style='color:green;'>Success:</span> Directory <i>$createDirs[0]</i> already exists. Skipping creation.</p>";
		} else {
			echo "<p><span style='color:orange;'>Info:</span> Directory <i>$createDirs[0]</i> does NOT exist.</p>";
			echo "<p>Trying to create folder structure:</p>";

			echo "<ul>";
			// Folder creation
			foreach($createDirs as $directory) {
				if ($makedir = JFolder::create(JPATH_SITE.$directory)) {
					echo "<li><span style='color:green;'>Success:</span> Directory <i>$directory</i> created.</li>";
				} else {
					echo "<li><span style='color:red;'>Error:</font> Directory <i>$directory</i> NOT created.</li>";
					$error['folders']++;
				}
			}
			echo "</ul>";
		}

		if($error['folders']) {
		?>
			<p>
				Please check the existance of the listed directories.<br /> If they
				do not exist, create them and ensure JEM has write access to these
				directories.<br /> If you don't so, you prevent JEM from functioning
				correctly. (You can't upload images).
			</p> <?php
		}

		echo "<h3>Settings</h3>";

		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__jem_settings";
		$db->setQuery($query);
		$db->loadResult();

		if($db->loadResult()) {
			echo "<p><span style='color:green;'>Success:</span> Found existing (default) settings.</p>";
		}

		echo "<h3>Summary</h3>";

		foreach ($error as $k => $v) {
			if($k != 'summary') {
				$error['summary'] += $v;
			}
		}

		if($error['summary']) {
		?>
			<p style='color: red;'>
				<b>JEM was NOT installed successfully!</b>
			</p> <?php
		} else {
		?>
			<p style='color: green;'>
				<b>JEM was installed successfully!</b> Have Fun.
			</p> <?php
		}
		?>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		?>
		<h2>Uninstall Status:</h2>
		<?php
		echo '<p>' . JText::_('COM_JEM_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		?>
		<h2>Update Status:</h2>
		<?php
		echo '<p>' . JText::sprintf('COM_JEM_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		$jversion = new JVersion();

		// Minimum Joomla version as per Manifest file
		$requiredJoomlaVersion = $parent->get('manifest')->attributes()->version;

		// abort if the current Joomla release is older than required version
		if(version_compare($jversion->getShortVersion(), $requiredJoomlaVersion, 'lt')) {
			Jerror::raiseWarning(100, JText::sprintf('COM_JEM_PREFLIGHT_WRONG_JOOMLA_VERSION', $requiredJoomlaVersion));
			return false;
		}

		// abort if the release being installed is not newer than the currently installed version
		if ($type == 'update') {
			// Installed component version
			$oldRelease = $this->getParam('version');

			// Installing component version as per Manifest file
			$newRelease = $parent->get('manifest')->version;

			if (version_compare($newRelease, $oldRelease, 'lt')) {
				Jerror::raiseWarning(100, JText::sprintf('COM_JEM_PREFLIGHT_INCORRECT_VERSION_SEQUENCE', $oldRelease, $newRelease));
				return false;
			}

			// Initialize schema table if necessary
			$this->initializeSchema($oldRelease);
		}

		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_JEM_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_JEM_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * Get a parameter from the manifest file (actually, from the manifest cache).
	 *
	 * @param $name  The name of the parameter
	 *
	 * @return The parameter
	 */
	private function getParam($name) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE type = "component" AND element = "com_jem"');
		$manifest = json_decode($db->loadResult(), true);
		return $manifest[$name];
	}

	/**
	 * Sets parameter values in the component's row of the extension table
	 *
	 * @param $param_array  An array holding the params to store
	 */
	private function setParams($param_array) {
		if (count($param_array) > 0) {
			// read the existing component value(s)
			$db = JFactory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE type = "component" AND element = "com_jem"');
			$params = json_decode($db->loadResult(), true);

			// add the new variable(s) to the existing one(s)
			foreach ($param_array as $name => $value) {
				$params[(string) $name] = (string) $value;
			}

			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' .
					$db->quote($paramsString) .
					' WHERE type = "component" AND element = "com_jem"');
			$db->query();
		}
	}

	private function initializeSchema($versionId) {
		$db = JFactory::getDbo();

		// Get extension ID of JEM
		$query = "SELECT extension_id FROM #__extensions WHERE type='component' and element='com_jem'";
		$db->setQuery($query);
		$extensionId = $db->loadResult();

		if(!$extensionId) {
			// This is a fresh installation, return
			return;
		}

		// Check if an entry already exists in schemas table
		$query = $db->getQuery(true);
		$query->select('version_id')->from('#__schemas')->where('extension_id = '.$extensionId);
		$db->setQuery($query);

		if($db->loadResult()) {
			// Entry exists, return
			return;
		}

		// Insert extension ID and old release version number into schemas table
		$query = $db->getQuery(true);
		$query->insert('#__schemas')
			->columns($db->quoteName(array('extension_id', 'version_id')))
			->values(implode(',', array($extensionId, $versionId)));

		$db->setQuery($query);
		$db->query();
	}
}
