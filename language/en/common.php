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
	'DTST_EVENT_TYPE_EXPLAIN'		=> 'Select a type of event to apply.',
	'DTST_AGE_MIN'					=> 'Age min.',
	'DTST_AGE_MAX'					=> 'Age max.',
	'DTST_AGE_RANGE'				=> 'Age range',
	'DTST_PARTICIPANTS'				=> 'Nr. of participants',
	'DTST_PARTICIPANTS_SOLO'		=> 'Participants',
	'DTST_PARTICIPANTS_ZERO'		=> '0 means unlimited',
	'DTST_UNLIMITED'				=> 'Unlimited',
	'DTST_UNLIMITED_OVERVIEW'		=> '---',
	'DTST_AGE_RANGE_NO'				=> 'N/A',
	'DTST_AGE_RANGE_ZERO'			=> '0 means N/A.',

	// Cancel event
	'DTST_EVENT_CANCEL'				=> 'Cancel event',
	'DTST_EVENT_CANCEL_CONFIRM'		=> 'Are you sure you wish to cancel the event?',
	'DTST_EVENT_CANCEL_SUCCESS'		=> 'You have successfully canceled the event.',

	// Filters
	'DTST_FILTERS'					=> 'Filters',
	'DTST_AFTER'					=> 'After…',
	'DTST_BEFORE'					=> 'Before…',
	'DTST_MAXIMUM'					=> 'Maximum…',
	'DTST_MINIMUM'					=> 'Minimum…',
	'DTST_PARTICIPANTS_ZEROZERO'	=> '0 to 0 means unlimited',

	// Opt-in/out participants
	'DTST_SLOTS'					=> 'Slots',

	'DTST_AGE_RANGE_UNL'			=> '--',

	'DTST_FULL_SCREEN'				=> 'Full screen',
	'DTST_REASON'					=> 'Reason',
	'DTST_REASON_TOO_LONG'			=> 'The reason you have entered is too long. It can be no longer than 255 characters.<br>You currently have <strong>%s</strong> characters.',
	'DTST_REASON_MISSING'			=> 'Please supply a reason.',
	'DTST_REASONS_POSTED'			=> 'The application reasons have been posted to this topic',

	'DTST_ATTENDEES'				=> 'Attendees',
	'DTST_ATTENDEES_LIST'			=> 'List',
	'DTST_ATTENDEES_VIEW'			=> 'Toggle list.',
	'DTST_ATTENDEES_TOO_MANY'		=> 'You have accepted too many applicants and exceeded the participants limit for this event.',
	'DTST_ATTENDEES_MANAGE'			=> 'Manage',
	'DTST_ATTENDEES_MANAGE_FULL'	=> 'Manage attendees',
	'DTST_ATTENDEES_MANAGE_SUCCESS'	=> 'You have successfully altered the attendees list of this event.',

	'DTST_NO_ACCEPTED'				=> 'There are no attendees.',
	'DTST_NO_APPLICATIONS'			=> 'There are no applications.',
	'DTST_NO_CANCELLATIONS'			=> 'There are no cancellations.',
	'DTST_NO_DENIALS'				=> 'There are no denials.',
	'DTST_NO_WITHDRAWALS'			=> 'There are no withdrawals.',

	'DTST_YOUR_STATUS'				=> 'Your status',
	'DTST_USER_STATUS_ACCEPTED'		=> 'Attending',
	'DTST_USER_STATUS_CANCELED'		=> 'Canceled',
	'DTST_USER_STATUS_DENIED'		=> 'Denied',
	'DTST_USER_STATUS_NOT'			=> 'Not attending',
	'DTST_USER_STATUS_PENDING'		=> 'Pending',
	'DTST_USER_STATUS_WITHDRAWN'	=> 'Withdrawn',

	'DTST_USER_STATUS_MSG_APPLIED'				=> 'You have successfully applied for this event.',
	'DTST_USER_STATUS_MSG_APPLIED_CONFIRM'		=> 'Are you sure you wish to apply for this event?',
	'DTST_USER_STATUS_MSG_CANCELED'				=> 'You have successfully canceled your application for this event.',
	'DTST_USER_STATUS_MSG_CANCELED_CONFIRM'		=> 'Are you sure you wish to cancel your application for this event?',
	'DTST_USER_STATUS_MSG_REAPPLIED'			=> 'You have successfully reapplied for this event.',
	'DTST_USER_STATUS_MSG_REAPPLIED_CONFIRM'	=> 'Are you sure you wish to reapply for this event?',
	'DTST_USER_STATUS_MSG_WITHDRAWN'			=> 'You have successfully withdrawn from this event.',
	'DTST_USER_STATUS_MSG_WITHDRAWN_CONFIRM'	=> 'Are you sure you wish to withdraw from this event?',

	'DTST_ACTION_ACCEPT'			=> 'Accept',
	'DTST_ACTION_DENY'				=> 'Deny',
	'DTST_ACTION_REMOVE'			=> 'Remove',

	'DTST_BUTTON_TEXT_ATTEND'		=> 'Attend',
	'DTST_BUTTON_TEXT_REAPPLY'		=> 'Reapply',
	'DTST_BUTTON_TEXT_WITHDRAW'		=> 'Withdraw',

	'DTST_REASON_QUOTE'				=> 'The reason',

	'DTST_TOPIC_PREFIX_CANCELED'	=> '[CANCELED] ',
	// Exceptions
	'DTST_NOT_AUTHORISED'			=> 'You are not authorized to Date Topic Event Calendar extension.',
	'DTST_TOPIC_NOT_FOUND'			=> 'The Topic you are after can not be found, sorry.',
));
