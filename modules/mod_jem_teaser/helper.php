<?php
/**
 * @version 1.9.3
 * @package JEM
 * @subpackage JEM Teaser Module
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * JEM Moduleteaser helper
 *
 * @package Joomla
 * @subpackage JEM Teaser Module
 * @since 1.0
 */
class modJEMteaserHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	public static function getList(&$params)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$gid = JEMHelper::getGID($user);

		//all upcoming events//all upcoming events
		if ($params->get('type') == 1) {
			$where = " WHERE (TIMEDIFF(CONCAT(a.dates,' ',IFNULL(a.times,'00:00:00')),NOW()) > 1";
			$where .= " OR (a.enddates AND TIMEDIFF(CONCAT(a.enddates,' ',IFNULL(a.times,'00:00:00')),NOW())) > 1) ";
			$where .= ' AND a.published = 1';
			$order = " ORDER BY a.dates, a.times";
		}

		//archived events only
		elseif ($params->get('type') == 2) {
			$where = ' WHERE a.published = 2';
			$order = ' ORDER BY a.dates DESC, a.times DESC';
		}

		//currently running events only
		elseif ($params->get('type') == 3) {
			$where = ' WHERE a.published = 1';
			$where .= ' AND (a.dates = CURDATE()';
			$where .= ' OR (a.enddates >= CURDATE() AND a.dates <= CURDATE()))';
			$order = ' ORDER BY a.dates, a.times';
		}

		//clean parameter data
		$catid = trim($params->get('catid'));
		$venid = trim($params->get('venid'));
		$state = JString::strtolower(trim($params->get('stateloc')));

		//Build category selection query statement
		if ($catid) {
			$ids = explode(',', $catid);
			JArrayHelper::toInteger($ids);
			$categories = ' AND (c.id=' . implode(' OR c.id=', $ids) . ')';
		}

		//Build venue selection query statement
		if ($venid) {
			$ids = explode(',', $venid);
			JArrayHelper::toInteger($ids);
			$venues = ' AND (l.id=' . implode(' OR l.id=', $ids) . ')';
		}

		//Build state selection query statement
		if ($state) {
			$rawstate = explode(',', $state);

			foreach ($rawstate as $val) {
				if ($val) {
					$states[] = '"'.trim($db->escape($val)).'"';
				}
			}

			JArrayHelper::toString($states);
			$stat = ' AND (LOWER(l.state)='.implode(' OR LOWER(l.state)=',$states).')';
		}

		//perform select query
		$query = 'SELECT a.title, a.dates, a.enddates, a.times, a.endtimes, a.datdescription, a.datimage, l.venue, l.state, l.locimage, l.city, l.locdescription, c.catname,'
				.' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'
				.' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug,'
				.' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
				.' FROM #__jem_events AS a'
				.' INNER JOIN #__jem_cats_event_relations AS rel ON rel.itemid = a.id'
				.' INNER JOIN #__jem_categories AS c ON c.id = rel.catid'
				.' LEFT JOIN #__jem_venues AS l ON l.id = a.locid'
				. $where
				.' AND c.access <= '.$gid
				.' AND c.published = 1'
				.($catid ? $categories : '')
				.($venid ? $venues : '')
				.($state ? $stat : '')
				. $order
				.' LIMIT '.(int)$params->get('count', '2')
				;

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($params->get('use_modal', 0)) {
			JHtml::_('behavior.modal', 'a.flyermodal');
		}

		//Loop through the result rows and prepare data
		$i		= 0;
		$lists	= array();
		foreach ((array) $rows as $row)
		{
			//create thumbnails if needed and receive imagedata
			if ($row->datimage) {
				$dimage = JEMImage::flyercreator($row->datimage, 'event');
			} else {
				$dimage = null;
			}
			if ($row->locimage) {
				$limage = JEMImage::flyercreator($row->locimage, 'venue');
			} else {
				$limage = null;
			}

			//cut titel
			$length = strlen(htmlspecialchars($row->title));

			if ($length > $params->get('cuttitle', '25')) {
				$row->title = substr($row->title, 0, $params->get('cuttitle', '18'));
				$row->title = $row->title.'...';
			}

			$lists[$i] = new stdClass();
			$lists[$i]->title			= htmlspecialchars($row->title, ENT_COMPAT, 'UTF-8');
			$lists[$i]->venue			= htmlspecialchars($row->venue, ENT_COMPAT, 'UTF-8');
			$lists[$i]->catname			= htmlspecialchars($row->catname, ENT_COMPAT, 'UTF-8');
			$lists[$i]->state			= htmlspecialchars($row->state, ENT_COMPAT, 'UTF-8');
			$lists[$i]->city			= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$lists[$i]->eventlink		= $params->get('linkevent', 1) ? JRoute::_(JEMHelperRoute::getRoute($row->slug)) : '';
			$lists[$i]->venuelink		= $params->get('linkvenue', 1) ? JRoute::_(JEMHelperRoute::getRoute($row->venueslug, 'venue')) : '';
			$lists[$i]->categorylink	= $params->get('linkcategory', 1) ? JRoute::_(JEMHelperRoute::getRoute($row->categoryslug, 'category')) : '';
			$lists[$i]->date			= modJEMteaserHelper::_format_date($row, $params);

			$lists[$i]->day 			= modJEMteaserHelper::_format_day($row, $params);
			$lists[$i]->dayname			= modJEMteaserHelper::_format_dayname($row);
			$lists[$i]->daynum 			= modJEMteaserHelper::_format_daynum($row);
			$lists[$i]->month 			= modJEMteaserHelper::_format_month($row);
			$lists[$i]->year 			= modJEMteaserHelper::_format_year($row);

			$lists[$i]->time 			= $row->times ? modJEMteaserHelper::_format_time($row->dates, $row->times, $params) : '' ;

			if ($dimage == null) {
				$lists[$i]->eventimage		= JUri::base(true).'/media/system/images/blank.png';
				$lists[$i]->eventimageorig	= JUri::base(true).'/media/system/images/blank.png';
			} else {
				$lists[$i]->eventimage		= JUri::base(true).'/'.$dimage['thumb'];
				$lists[$i]->eventimageorig	= JUri::base(true).'/'.$dimage['original'];
			}

			if ($limage == null) {
				$lists[$i]->venueimage		= JUri::base(true).'/media/system/images/blank.png';
				$lists[$i]->venueimageorig	= JUri::base(true).'/media/system/images/blank.png';
			} else {
				$lists[$i]->venueimage		= JUri::base(true).'/'.$limage['thumb'];
				$lists[$i]->venueimageorig	= JUri::base(true).'/'.$limage['original'];
			}

			$length = $params->get( 'descriptionlength' );
			$length2 = 1;
			$etc = '...';
			$etc2 = "Keine Beschreibung";

			//strip html tags but leave <br /> tags
			//entferne html tags bis auf Zeilenumbrüche
			$description = strip_tags($row->datdescription, "<br>");

			//switch <br /> tags to space character
			//wandle zeilenumbrüche in leerzeichen um
			if ($params->get( 'br' ) == 0) {
			 $description = str_replace('<br />',' ',$description);
			}
			//
			if (strlen($description) > $length) {
				$length -= strlen($etc);
				$description = preg_replace('/\s+?(\S+)?$/', '', substr($description, 0, $length+1));
				$lists[$i]->eventdescription = substr($description, 0, $length).$etc;
			} else

			if (strlen($description) < $length2) {
			$length -= strlen($etc2);
			$description = preg_replace('/\s+?(\S+)?$/', '', substr($description, 0, $length+1));
			$lists[$i]->eventdescription = substr($description, 0, $length).$etc2;

			} else {
				$lists[$i]->eventdescription	= $description;
			}

			$i++;
		}
		return $lists;
	}

	/**
	 *format days
	 *
	 */
	protected static function _format_day($row, &$params)
	{
		//Get needed timestamps and format
		setlocale (LC_TIME, 'de_DE.UTF8');
		$yesterday_stamp	= mktime(0, 0, 0, date("m") , date("d")-1, date("Y"));
		$yesterday 			= strftime("%Y-%m-%d", $yesterday_stamp);
		$today_stamp		= mktime(0, 0, 0, date("m") , date("d"), date("Y"));
		$today 				= date('Y-m-d');
		$tomorrow_stamp 	= mktime(0, 0, 0, date("m") , date("d")+1, date("Y"));
		$tomorrow 			= strftime("%Y-%m-%d", $tomorrow_stamp);

		$dates_stamp		= strtotime($row->dates);
		$enddates_stamp		= $row->enddates ? strtotime($row->enddates) : null;

		//check if today or tomorrow or yesterday and no current running multiday event
		if($row->dates == $today && empty($enddates_stamp)) {
			$result = JText::_( 'TODAY' );
		} elseif($row->dates == $tomorrow) {
			$result = JText::_( 'TOMORROW' );
		} elseif($row->dates == $yesterday) {
			$result = JText::_( 'YESTERDAY' );
		} else {
			//if daymethod show day
			if($params->get('daymethod', 1) == 1) {

				//single day event
				$date = strftime('%A', strtotime( $row->dates ));
				$result = JText::sprintf('ON DATE', $date);

				//Upcoming multidayevent (From 16.10.2010 Until 18.10.2010)
				if($dates_stamp > $tomorrow_stamp && $enddates_stamp) {
				$startdate = strftime('%A', strtotime( $row->dates ));
				$result = JText::sprintf('FROM', $startdate);
				}

				//current multidayevent (Until 18.08.2008)
				if( $row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp ) {
				//format date
				$result = strftime('%A', strtotime( $row->enddates ));
				$result = JText::sprintf('UNTIL', $result);
				}
			} else { // show day difference
				//the event has an enddate and it's earlier than yesterday
				if ($row->enddates && $enddates_stamp < $yesterday_stamp) {
					$days = round( ($today_stamp - $enddates_stamp) / 86400 );
					$result = JText::sprintf( 'ENDED DAYS AGO', $days );

				//the event has an enddate and it's later than today but the startdate is today or earlier than today
				//means a currently running event with startdate = today
				} elseif($row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp) {
					$days = round( ($enddates_stamp - $today_stamp) / 86400 );
					$result = JText::sprintf( 'DAYS LEFT', $days );

				//the events date is earlier than yesterday
				} elseif($dates_stamp < $yesterday_stamp) {
					$days = round( ($today_stamp - $dates_stamp) / 86400 );
					$result = JText::sprintf( 'DAYS AGO', $days );

				//the events date is later than tomorrow
				} elseif($dates_stamp > $tomorrow_stamp) {
					$days = round( ($dates_stamp - $today_stamp) / 86400 );
					$result = JText::sprintf( 'DAYS AHEAD', $days );
				}
			}
		}
		return $result;
	}

	/**
	 * Method to format date information
	 *
	 * @access public
	 * @return string
	 */
	protected static function _format_date($row, &$params)
	{
		//Get needed timestamps and format
		$yesterday_stamp	= mktime(0, 0, 0, date("m") , date("d")-1, date("Y"));
		$yesterday 			= strftime("%Y-%m-%d", $yesterday_stamp);
		$today_stamp		= mktime(0, 0, 0, date("m") , date("d"), date("Y"));
		$today 				= date('Y-m-d');
		$tomorrow_stamp 	= mktime(0, 0, 0, date("m") , date("d")+1, date("Y"));
		$tomorrow 			= strftime("%Y-%m-%d", $tomorrow_stamp);

		$dates_stamp		= $row->dates ? strtotime($row->dates) : null;
		$enddates_stamp		= $row->enddates ? strtotime($row->enddates) : null;

		//if datemethod show day difference
		if($params->get('datemethod', 1) == 2) {
			//check if today or tomorrow
			if($row->dates == $today) {
				$result = JText::_('MOD_JEM_TEASER_TODAY');
			} elseif($row->dates == $tomorrow) {
				$result = JText::_('MOD_JEM_TEASER_TOMORROW');
			} elseif($row->dates == $yesterday) {
				$result = JText::_('MOD_JEM_TEASER_YESTERDAY');

			//This one isn't very different from the DAYS AGO output but it seems
			//adequate to use a different language string here.
			//
			//the event has an enddate and it's earlier than yesterday
			} elseif($row->enddates && $enddates_stamp < $yesterday_stamp) {
				$days = round(($today_stamp - $enddates_stamp) / 86400);
				$result = JText::sprintf('MOD_JEM_TEASER_ENDED_DAYS_AGO', $days);

			//the event has an enddate and it's later than today but the startdate is earlier than today
			//means a currently running event
			} elseif($row->dates && $row->enddates && $enddates_stamp > $today_stamp && $dates_stamp < $today_stamp) {
				$days = round(($today_stamp - $dates_stamp) / 86400);
				$result = JText::sprintf('MOD_JEM_TEASER_STARTED_DAYS_AGO', $days);

			//the events date is earlier than yesterday
			} elseif($row->dates && $dates_stamp < $yesterday_stamp) {
				$days = round(($today_stamp - $dates_stamp) / 86400);
				$result = JText::sprintf('MOD_JEM_TEASER_DAYS_AGO', $days);

			//the events date is later than tomorrow
			} elseif($row->dates && $dates_stamp > $tomorrow_stamp) {
				$days = round(($dates_stamp - $today_stamp) / 86400);
				$result = JText::sprintf('MOD_JEM_TEASER_DAYS_AHEAD', $days);
			}
		} else {
			//single day event
			$date = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->dates.' '.$row->times));
			$result = JText::sprintf('MOD_JEM_TEASER_ON_DATE', $date);

			//Upcoming multidayevent (From 16.10.2008 Until 18.08.2008)
			if($dates_stamp > $tomorrow_stamp && $enddates_stamp) {
				$startdate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->dates.' '.$row->times));
				$enddate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->enddates.' '.$row->endtimes));
				$result = JText::sprintf('MOD_JEM_TEASER_FROM_UNTIL', $startdate, $enddate);
			}

			//current multidayevent (Until 18.08.2008)
			if($row->enddates && $enddates_stamp > $today_stamp && $dates_stamp < $today_stamp) {
				//format date
				$result = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->enddates.' '.$row->endtimes));
				$result = JText::sprintf('MOD_JEM_TEASER_UNTIL', $result);
			}
		}

		return $result;
	}

	/**
	 * Method to format time information
	 *
	 * @access public
	 * @return string
	 */
	protected static function _format_time($date, $time, &$params)
	{
		$time = strftime($params->get('formattime', '%H:%M'), strtotime($date.' '.$time));
		return $time;
	}
	/*Calendar*/

	protected static function _format_dayname($row)
	{
		setlocale (LC_TIME, 'de_DE.UTF8');
		$date = strtotime($row->dates);
		$result = strftime("%A", $date);
		return $result;
	}
	protected static function _format_daynum($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%d", $date);
		return $result;
	}
	protected static function _format_year($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%Y", $date);
		return $result;
	}
	protected static function _format_month($row)
	{
		setlocale (LC_TIME, 'de_DE.UTF8');
		$date = strtotime($row->dates);
		$result = strftime("%B", $date);
		return $result;
	}
}