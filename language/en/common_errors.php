<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'DTST_LOCATION_MISSING'			=> 'You must specify at least a “Location” when posting a new topic.',
	'DTST_HOST_MISSING'				=> 'You must specify a “Host” when posting a new topic.',
	'DTST_DATE_MISSING'				=> 'You must specify a “Date” when posting a new topic.',
	'DTST_EVENT_TYPE_MISSING'		=> 'You must specify an “Event type” when posting a new topic.',
	'DTST_LOC_CUSTOM_LONG'			=> 'The “Location custom” field can be max 100 chars!',
	'DTST_PARTICIPANTS_TOO_LOW'		=> 'The specified “Nr. of participants” (%1$s) is lower than the current amount of participants (%2$s).',
	'DTST_EVENT_ENDED_DATE'			=> 'You are not allowed to modify the “Date”. The event took place at %s.',
));
