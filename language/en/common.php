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

/**
* Some characters you may want to copy&paste: ’ » “ ” …
*/

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
	'DTST_EVENT'					=> 'Event',
	'DTST_EVENTS'					=> 'Events',
	'DTST_EVENT_CLOSED'				=> 'Event closed',
	'DTST_EVENT_TYPE'				=> 'Event Type',
	'DTST_EVENT_TYPE_NONE'			=> 'No event types',
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

	'DTST_REASON_EMOJIS_SUPPORT'	=> 'The reason you have entered contains unsupported characters like the following:<br>%s',

	'DTST_EVENT_ENDED'				=> 'The event has ended and you are no longer able to manage the attendees.',

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

	'DTST_NO_REPLY'					=> 'No reply',

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

	'DTST_REPUTATION_NA'			=> '---',
	'DTST_REP_RANK_LIST'			=> 'User’s %s list',

	// Exceptions
	'DTST_NOT_AUTHORISED'			=> 'You are not authorized to Date Topic Event Calendar extension.',
	'DTST_TOPIC_NOT_FOUND'			=> 'The Topic you are after can not be found, sorry.',

	// Reputation
	'DTST_REP_EVENT'					=> 'Event %s',
	'DTST_REP_U_LIST'					=> 'User %s list',
	'DTST_REP_EVENT_NO_DATE'			=> 'The event has no date set, therefore a %s page will not be available for this event.',
	'DTST_REP_EVENT_NOT_ENDED'			=> 'The event has not taken place yet. Please come back at a later time.',
	'DTST_REP_END_DATE'					=> '%2$s closes after %1$s',
	'DTST_REP_END_DAYS'					=> '%s days remaining!',
	'DTST_REP_ENDED'					=> 'The %s period for this event has passed.',
	'DTST_REP_GIVE'						=> 'You can give %s',
	'DTST_REP_REMAIN'					=> '%s remaining!',

	'DTST_NO_REP'						=> 'The requested %s action does not exist.',

	'DTST_REP_CONDUCT_GOOD'				=> 'Good conduct',
	'DTST_REP_CONDUCT_GOOD_CONFIRM'		=> 'Are you sure you wish to give this attendee a good conduct?',
	'DTST_REP_CONDUCT_GOOD_SUCCESS'		=> 'You have successfully given this attendee a good conduct.',
	'DTST_REP_CONDUCT_GOOD_MAXED'		=> 'You have reached the maximum allowed of good conducts for this event.',
	'DTST_REP_CONDUCT_GOOD_NONE'		=> 'You have not given any attendees a good conduct for this event.',
	'DTST_REP_CONDUCT_GOOD_DEL_CONFIRM'	=> 'Are you sure you wish to remove the good conduct for this attendee?',
	'DTST_REP_CONDUCT_GOOD_DEL_SUCCESS'	=> 'You have successfully removed the good conduct for this attendee.',
	'DTST_REP_CONDUCT_BAD'				=> 'Bad conduct',
	'DTST_REP_CONDUCT_BAD_CONFIRM'		=> 'Are you sure you wish to give this attendee a bad conduct?',
	'DTST_REP_CONDUCT_BAD_SUCCESS'		=> 'You have successfully given this attendee a bad conduct.',
	'DTST_REP_CONDUCT_BAD_MAXED'		=> 'You have reached the maximum allowed of bad conducts for this event.',
	'DTST_REP_CONDUCT_BAD_NONE'			=> 'You have not given any attendees a bad conduct for this event.',
	'DTST_REP_CONDUCT_BAD_DEL_CONFIRM'	=> 'Are you sure you wish to remove the bad conduct for this attendee?',
	'DTST_REP_CONDUCT_BAD_DEL_SUCCESS'	=> 'You have successfully removed the bad conduct for this attendee.',
	'DTST_REP_THUMBS_UP'				=> 'Thumbs up',
	'DTST_REP_THUMBS_UP_CONFIRM'		=> 'Are you sure you wish to give this attendee a thumbs up?',
	'DTST_REP_THUMBS_UP_SUCCESS'		=> 'You have successfully given this attendee a thumbs up.',
	'DTST_REP_THUMBS_UP_MAXED'			=> 'You have reached the maximum allowed of thumbs up per event.',
	'DTST_REP_THUMBS_UP_NONE'			=> 'You have not given any attendees a thumbs up for this event.',
	'DTST_REP_THUMBS_UP_DEL_CONFIRM'	=> 'Are you sure you wish to remove the thumbs up for this attendee?',
	'DTST_REP_THUMBS_UP_DEL_SUCCESS'	=> 'You have successfully removed the thumbs up for this attendee.',
	'DTST_REP_THUMBS_DOWN'				=> 'Thumbs down',
	'DTST_REP_THUMBS_DOWN_CONFIRM'		=> 'Are you sure you wish to give this attendee a thumbs down?',
	'DTST_REP_THUMBS_DOWN_SUCCESS'		=> 'You have successfully given this attendee a thumbs down.',
	'DTST_REP_THUMBS_DOWN_MAXED'		=> 'You have reached the maximum allowed of thumbs down per event.',
	'DTST_REP_THUMBS_DOWN_NONE'			=> 'You have not given any attendees a thumbs down for this event.',
	'DTST_REP_THUMBS_DOWN_DEL_CONFIRM'	=> 'Are you sure you wish to remove the thumbs down for this attendee?',
	'DTST_REP_THUMBS_DOWN_DEL_SUCCESS'	=> 'You have successfully removed the thumbs down for this attendee.',
	'DTST_REP_NO_SHOW'					=> 'No show',
	'DTST_REP_NO_SHOW_CONFIRM'			=> 'Are you sure you wish to mark this participant as a no show?<br>This will delete all %1$s given and gained by this user!<br><strong>The deletion of %1$s can not be undone!</strong>',
	'DTST_REP_NO_SHOW_SUCCESS'			=> 'You have successfully marked this participant as a no show.',
	'DTST_REP_NO_SHOW_NONE'				=> 'No attendees have been marked as a <strong>“no show”</strong> by the host.',
	'DTST_REP_NO_SHOW_NOTICE'			=> 'You have been marked as a <strong>“no show”</strong> and are unable to give or gain %s for this event!',
	'DTST_REP_NOT_ATTENDING'			=> 'You can not thumb up/down for this event as you did not attend it!',
	'DTST_REP_NOT_SELF'					=> 'You can not thumb up/down yourself!',

	'DTST_REP_TOTAL'					=> '%1$s Total %2$s',
	'DTST_EVENT_TOTAL'					=> '%s Total events',
	'DTST_REP_NONE'						=> 'No %s',
	'DTST_EVENT_NONE'					=> 'No events',

	'DTST_REP_ACTION_MOD'				=> '%s adjusted',
	'DTST_REP_ACTION_HOSTED'			=> 'Hosted the event',
	'DTST_REP_ACTION_CANCELED'			=> 'Canceled the event',
	'DTST_REP_ACTION_ATTENDED'			=> 'Attended the event',
	'DTST_REP_ACTION_WITHDREW'			=> 'Withdrew from the event',
	'DTST_REP_ACTION_CONDUCT_GOOD'		=> 'Received a good conduct',
	'DTST_REP_ACTION_CONDUCT_BAD'		=> 'Received a bad conduct',
	'DTST_REP_ACTION_THUMBS_UP'			=> 'Received a thumbs up',
	'DTST_REP_ACTION_THUMBS_DOWN'		=> 'Received a thumbs down',
	'DTST_REP_ACTION_NO_SHOW'			=> 'Did not show up',
	'DTST_REP_ACTION_NO_REPLY'			=> 'Did not reply to an application',

	'DTST_REP_GIVEN_MOD'				=> 'Adjusted %s',
	'DTST_REP_GIVEN_HOSTED'				=> 'For hosting the event',
	'DTST_REP_GIVEN_CANCELED'			=> 'For canceling the event',
	'DTST_REP_GIVEN_ATTENDED'			=> 'For attending the event',
	'DTST_REP_GIVEN_WITHDREW'			=> 'For withdrawing from the event',
	'DTST_REP_GIVEN_CONDUCT_GOOD'		=> 'Gave a good conduct',
	'DTST_REP_GIVEN_CONDUCT_BAD'		=> 'Gave a bad conduct',
	'DTST_REP_GIVEN_THUMBS_UP'			=> 'Gave a thumbs up',
	'DTST_REP_GIVEN_THUMBS_DOWN'		=> 'Gave a thumbs down',
	'DTST_REP_GIVEN_NO_SHOW'			=> 'Marked as a no show',
	'DTST_REP_GIVEN_NO_REPLY'			=> 'For not replying to an application',

	'DTST_REP_STATS_HOSTED'				=> 'Hosted',
	'DTST_REP_STATS_ATTENDED'			=> 'Attended',
	'DTST_REP_STATS_NO_SHOWS'			=> 'No shows',
	'DTST_REP_STATS_GIVEN'				=> 'Given %s',
	'DTST_REP_STATS_RECEIVED'			=> 'Received %s',

	'DTST_REP_EVENT_BEST'				=> 'Best event',
	'DTST_REP_EVENT_WORST'				=> 'Worst event',
	'DTST_REP_EVENT_RECENT'				=> 'Recent event',

	'DTST_REP_DELETE'					=> 'Delete %s',
	'DTST_REP_DELETE_CONFIRM'			=> 'Are you sure you wish to delete this %s?',
	'DTST_REP_DELETE_SUCCESS'			=> 'You have successfully deleted the %s.',
	'DTST_REP_DELETE_FORBIDDEN'			=> 'You are not allowed to delete a user’s %s',

	'DTST_REP_TO'						=> 'To',
	'DTST_REP_CLASSIFIED'				=> '<em>Classified</em>',

	// Reputation's period -Post texts
	'DTST_REPUTATION_OPENED_TITLE'	=> 'The %s period has opened',
	'DTST_REPUTATION_OPENED_TEXT'	=> '[size=150]The %1$s period for this event has now opened![/size]
	
	If you have attended this event, now is the chance to give your opinion about it.
	You can give out %1$s by clicking on the link in the upper right corner.
	
	[b]This is an automated message.[/b]',

	'DTST_REPUTATION_CLOSED_TITLE'	=> 'The %s period has closed',
	'DTST_REPUTATION_CLOSED_TEXT'	=> '[size=150]The %1$s period for this event has now closed![/size]
	
	If you have attended this event, we thank you very much.
	Hope to see you all soon, again!
	
	[b]This is an automated message.[/b]',

	// Profile
	'DTST_USER_RAT_STARS'			=> 'Rating stars',
	'DTST_USER_RANK_BADGE'			=> 'Rank badge',
	'DTST_USER_RANK_DESC'			=> 'Rank description',
	'DTST_USER_PROGRESS'			=> 'Progress',
	'DTST_PROFILE_VIEW_RANGE'		=> 'range from 0 to ',
));
