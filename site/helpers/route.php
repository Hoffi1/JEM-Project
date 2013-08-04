<?php
/**
 * @version 1.9.1
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
require_once(JPATH_SITE.'/components/com_jem/helpers/helper.php');

/**
 * JEM Component Route Helper
 * based on Joomla ContentHelperRoute
 *
 * @static
 * @package		JEM
 * 
 */
abstract class JEMHelperRoute
{
	protected static $lookup;
	const artificalId = 0;

	/**
	 * Determines an JEM Link
	 *
	 * @param int The id of an JEM item
	 * @param string The view
	 * @param string The category of the item
	 * 
	 *
	 * @return string determined Link
	 */
	public static function getRoute($id, $view = 'event', $category = null)
	{
		$needles = array(
			$view => array((int) $id)
		);

		if ($item = self::_findItem($needles)) {
			$link = 'index.php?Itemid='.$item;
		}
		else {
			// Create the link
			$link = 'index.php?option=com_jem&view='.$view.'&id='. $id;

			// Add category, if available
			if(!is_null($category)) {
				$link .= '&catid='.$category;
			}

			if ($item = self::_findItem($needles)) {
				$link .= '&Itemid='.$item;
			}
			elseif ($item = self::_findItem()) {
				$link .= '&Itemid='.$item;
			}
		}

		return $link;
	}

	public static function getCategoryRoute($id)
	{
		$needles = array(
			'category' => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_jem&view=category&id='. $id;

		// If no category view works try categories
		$needles['categories'] = array(self::artificalId);

		$category = new JEMCategories($id);
		if($category) {
			$needles['categories'] = array_reverse($category->getPath());
		}

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function getEventRoute($id, $catid = null)
	{
		$needles = array(
			'event' => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_jem&view=event&id='. $id;

		// Add category, if available
		if(!is_null($catid)) {
			// TODO
			//$needles['categories'] = $needles['category'];
			$link .= '&catid='.$catid;
		}

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function getVenueRoute($id)
	{
		$needles = array(
			'venue' => array((int) $id)
		);

		// Create the link
		$link = 'index.php?option=com_jem&view=venue&id='. $id;

		// If no venue view works try venues
		$needles['venues'] = array(self::artificalId);

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem()) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the active menuitem will be returned
	 *
	 * @param array The id and view
	 * 
	 *
	 * @return int Itemid
	 */
	protected static function _findItem($needles = null)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null) {
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_jem');
			$items = $menus->getItems('component_id', $component->id);

			if ($items) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];

						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}

						if (isset($item->query['id'])) {
							self::$lookup[$view][$item->query['id']] = $item->id;
						}
						// Some views have no ID, but we have to set one
						else {
							self::$lookup[$view][self::artificalId] = $item->id;
						}
					}
				}
			}
		}

		if ($needles) {
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view])) {
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int)$id])) {
							// TODO: Check on access. See commented code below
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}
		else {
			$active = $menus->getActive();
			if ($active) {
				return $active->id;
			}
		}

		return null;

// 		$user = JFactory::getUser();
// 		$gid = JEMHelper::getGID($user);

// 		//false if there exists no menu item at all
// 		if (!$items) {
// 			return false;
// 		} else {
// 			//Not needed currently but kept because of a possible hierarchic link structure in future
// 			foreach($needles as $needle => $id)
// 			{
// 				foreach($items as $item)
// 				{
// 					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id) && ($item->access <= $gid)) {
// 						return $item;
// 					}
// 				}

// 				/*
// 				//no menuitem exists -> return first possible match
// 				foreach($items as $item)
// 				{
// 					if ($item->published == 1 && $item->access <= $gid) {
// 						return $item;
// 					}
// 				}
// 				*/
// 			}
// 		}

// 		return false;
	}
}
?>