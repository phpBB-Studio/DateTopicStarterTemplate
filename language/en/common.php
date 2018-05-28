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
	'DTST_LEGEND_1'	=> '<strong><em>Topic Date Event Calendar creator</em></strong>',

	'DTST_LOCATION'					=> 'Event Location',
	'DTST_LOCATION_EXPLAIN'			=> 'You can chose a preset locations or use a custom one here below, or both.',
	'DTST_LOCATION_CUSTOM'			=> 'Custom',
	'DTST_LOCATION_PRESET'			=> 'Preset',
	'DTST_LOC_CUSTOM'				=> 'Location custom',
	'DTST_LOC_CUSTOM_HOLDER'		=> 'You may use/add a custom location.',
	'DTST_HOST'						=> 'Host',
	'DTST_HOST_EXPLAIN'				=> 'Who hosts this event, this can be you or anything else.',
	'DTST_DATE'						=> 'Event Date',
	'DTST_DATE_EXPLAIN'				=> 'You may select a date with the built-in date-picker.',
	'DTST_DATE_NONE'				=> 'N/A',
	'DTST_EVENT_CLOSED'				=> 'Event closed',
	'DTST_EVENT_TYPE'				=> 'Event Type',
	'DTST_AGE_MIN'					=> 'Min. age',
	'DTST_AGE_MAX'					=> 'Max. age',
	'DTST_AGE_RANGE'				=> 'Age range',
	'DTST_PARTICIPANTS'				=> 'Nr. of participants',
	'DTST_PARTICIPANTS_SOLO'		=> 'Participants',
	'DTST_PARTICIPANTS_ZERO'		=> '0 means unlimited',
	'DTST_PARTICIPANTS_ZEROZERO'	=> '0 to 0 means unlimited',
	'DTST_UNLIMITED'				=> 'Unlimited',
	'DTST_AGE_RANGE_NO'				=> 'N/A',
	'DTST_AGE_RANGE_ZERO'			=> '0 means N/A.',

	// Filters
	'DTST_FILTERS'					=> 'Filters',
	'DTST_AFTER'					=> 'After…',
	'DTST_BEFORE'					=> 'Before…',
	'DTST_MAXIMUM'					=> 'Maximum…',
	'DTST_MINIMUM'					=> 'Minimum…',

	// Opt-in/out participants
	'DTST_SLOTS'					=> 'Slots',

	'DTST_AGE_RANGE_UNL'			=> 'Not applicable',

	'DTST_ATTENDEES'				=> 'Attendees',
	'DTST_NO_ATTENDEES'				=> 'There are no attendees, yet.',
	'DTST_ATTENDEES_VIEW'			=> 'Toggle list.',
	'DTST_NO_WITHDRAWALS'			=> 'There are no withdrawals yet.',
	'DTST_ATTENDEES_LIST'			=> 'List',

	'DTST_USER_STATUS'				=> 'Your status',
	'DTST_STATUS_ATTENDING'			=> 'Attending',
	'DTST_STATUS_ATTENDING_NOT'		=> 'Not attending',
	'DTST_STATUS_WITHDRAWN'			=> 'Withdrawn',

	'DTST_OPT_ATTEND'				=> 'Attend',
	'DTST_OPT_REATTEND'				=> 'Reattend',
	'DTST_OPT_WITHDRAW'				=> 'Withdraw',

	'DTST_OPTED_IN'					=> 'You are now attending the event.',
	'DTST_OPTED_OUT'				=> 'You are no longer attending the event.',

	// Exceptions
	'DTST_NOT_AUTHORISED'			=> 'You are not authorized to Date Topic Event Calendar extension.',
	'DTST_TOPIC_NOT_FOUND'			=> 'The Topic you are after can not be found, sorry.',
));
