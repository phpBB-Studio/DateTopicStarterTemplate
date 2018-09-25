<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

/**
* Some characters you may want to copy&paste: ’ » “ ” …
*/

$lang = array_merge($lang, array(
	// Cat
	'ACL_CAT_PHPBB_STUDIO'					=> 'phpBB Studio',
	// ACP
	'ACP_DTST_TITLE'						=> 'Date Topic Event Calendar',
	// ACP locations
	'ACP_DTST_LOCATIONS'					=> 'Locations',
	'ACP_DTST_LOCATIONS_SAVED'				=> 'Date Topic Event Calendar Locations saved.',
	'ACP_DTST_LOCATION_ADDED'				=> 'Date Topic Event Calendar location successfully added.',
	'ACP_DTST_LOCATION_REMOVED'				=> 'Date Topic Event Calendar location successfully removed.',
	'ACP_DTST_ALL_LOCATIONS_REMOVED'		=> 'Date Topic Event Calendar all locations successfully removed.',
	// ACP settings
	'ACP_DTST_SETTINGS'						=> 'Settings',
	'ACP_DTST_SETTING_SAVED'				=> 'Date Topic Event Calendar Settings saved.',
	// ACP PMs
	'ACP_DTST_PRIVMSG'						=> 'PMs settings',
	'ACP_DTST_PRIVMSG_SAVED'				=> 'Date Topic Event Calendar PMS Settings saved.',
	// Reputation Settings
	'ACP_DTST_LPR_SETTINGS'					=> 'Reputation Settings',
	'ACP_DTST_LPR_SETTING_SAVED'			=> 'Date Topic Event Calendar Reputation Settings Settings saved.',
	// Reputation Values
	'ACP_DTST_LPR_REPUTATION'				=> 'Reputation Values',
	'ACP_DTST_LPR_REPUTATION_VALUES_SAVED'	=> 'Date Topic Event Calendar Reputation Values Settings saved.',
	// Reputation Ranks
	'ACP_DTST_LPR_RANKS'					=> 'Reputation Ranks',
	'ACP_DTST_LPR_RANKS_SETTING_SAVED'		=> 'Date Topic Event Calendar Reputation Ranks Settings saved.',

	// ACP - Log
	'DTST_LOG_CONFIG_SAVED'					=> '<strong>Date Topic Event Calendar</strong> Locations configuration saved.',
	'DTST_LOG_LOCATION_ADDED'				=> '<strong>Date Topic Event Calendar location added.</strong><br>» %s',
	'DTST_LOG_LOCATION_REMOVED'				=> '<strong>Date Topic Event Calendar location removed.</strong><br>» %s',
	'DTST_LOG_ALL_LOCATIONS_REMOVED'		=> '<strong>Date Topic Event Calendar locations removed.</strong><br>» All',

	'DTST_LOG_SETTINGS_SAVED'				=> '<strong>Date Topic Event Calendar</strong> Settings configuration saved.',
	'DTST_LOG_PRIVMSG_SAVED'				=> '<strong>Date Topic Event Calendar</strong> PMs Settings configuration saved.',
	'DTST_LOG_REP_SETTINGS_SAVED'			=> '<strong>Date Topic Event Calendar</strong> Reputation Settings configuration saved.',
	'DTST_LOG_REP_VALUES_SAVED'				=> '<strong>Date Topic Event Calendar</strong> Reputation Values configuration saved.',
	'DTST_LOG_REP_RANKS_SAVED'				=> '<strong>Date Topic Event Calendar</strong> Reputation Ranks configuration saved.',

	'ACP_DTST_LOG_APPLIED'					=> '<strong>Applied for event:</strong><br />» %s',
	'ACP_DTST_LOG_CANCELED'					=> '<strong>Canceled application for event:</strong><br />» %s',
	'ACP_DTST_LOG_WITHDRAWN'				=> '<strong>Withdrew from event:</strong><br />» %s',

	'ACP_DTST_LOG_PM_APPLIED'				=> '<strong>Private message sent to host after applying for:</strong><br />» %s',
	'ACP_DTST_LOG_PM_CANCELED'				=> '<strong>Private message sent to host after canceling application for:</strong><br />» %s',
	'ACP_DTST_LOG_PM_WITHDRAWN'				=> '<strong>Private message sent to host after withdrawing from:</strong><br />» %s',

	'LOG_DTST_EVENT_CANCELED'				=> '<strong>Canceled event:</strong><br />» %s',

	'LOG_DTST_OPT_ACCEPTED'					=> '<strong>Application accepted:</strong><br />» %s',
	'LOG_DTST_OPT_DENIED'					=> '<strong>Application denied:</strong><br />» %s',

	'ACP_DTST_LOG_REPUTATION_UPDATED'		=> '<strong>User reputation updated for “%1$s”</strong><br />» from “%2$s” to “%3$s”',
	'ACP_DTST_LOG_REPUTATION_UPDATED_USER'	=> '<strong>User reputation updated</strong><br />» from “%1$s” to “%2$s”',

	'ACP_DTST_LOG_REPUTATION_DELETED'		=> '<strong>Deleted reputation from %s:</strong><br />» Event: %s<br />» Action: %s<br />» Reason: %s',

	'ACP_DTST_LOG_HOST_NO_REPLY'			=> '<strong>Host did not reply to application</strong><br>» Host: %1$s<br>» Event: %2$s<br>» Applicant: %3$s',
	'ACP_DTST_LOG_EVENT_ENDED'				=> '<strong>Event has ended:</strong><br>» %s',
	'ACP_DTST_LOG_REPUTATION_ENDED'			=> '<strong>Event’s reputation period has ended:</strong><br>» %s',

	// Reputation log
	'ACP_DTST_LOG_REP_CONDUCT_GOOD'			=> '<strong>Gave a “good conduct”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_CONDUCT_GOOD_DEL'		=> '<strong>Removed a “good conduct”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_CONDUCT_BAD'			=> '<strong>Gave a “bad conduct”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_CONDUCT_BAD_DEL'		=> '<strong>Removed a “bad conduct”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_THUMBS_UP'			=> '<strong>Gave a “thumbs up”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_THUMBS_UP_DEL'		=> '<strong>Removed a “thumbs up”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_THUMBS_DOWN'			=> '<strong>Gave a “thumbs down”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_THUMBS_DOWN_DEL'		=> '<strong>Removed a “thumbs down”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_NO_SHOW'				=> '<strong>Marked as a “no show”:</strong><br>» %s',
	'ACP_DTST_LOG_REP_MOD_GIVE'				=> '<strong>Gave reputation points to:</strong><br>» User: %1$s<br>» Amount: %2$s<br>» Reason: %3$s',
	'ACP_DTST_LOG_REP_MOD_TAKE'				=> '<strong>Took reputation points from:</strong><br>» User: %1$s<br>» Amount: %2$s<br>» Reason: %3$s',

	// ACP user profile administrate
	'ACP_DTST_USER_PROFILE_SETTINGS'		=> '“Date Topic Event Calendar” Extended settings',
	'ACP_DTST_USER_REP'						=> 'User reputation points',
	// Adjust
	'ACP_DTST_ADJUST'						=> 'Adjust a user’s reputation points',
	'ACP_DTST_ADJUST_EXPLAIN'				=> 'Give or take reputation from a user. Emojis will be stripped.',
	'ACP_DTST_ADJUST_NO_REP'				=> 'You have not specified a reputation value.',
	'ACP_DTST_ADJUST_AMOUNT'				=> 'Amount',
	'ACP_DTST_ADJUST_GIVE'					=> 'Give',
	'ACP_DTST_ADJUST_TAKE'					=> 'Take',
	'ACP_DTST_ADJUST_ACTION'				=> 'Select desired action',
	// Adjust errors
	'TOO_SHORT_DTST_REASON'					=> 'Please supply a reason when adjusting someone’s reputation.',
	'TOO_LONG_DTST_REASON'					=> 'The reason you supplied is too long.',
	'TOO_SMALL_DTST_REPUTATION'				=> 'The amount of reputation specified is too low.',
	'TOO_LARGE_DTST_REPUTATION'				=> 'The amount of reputation specified is too much.',

	// ACP Forums
	'ACP_DTST_FORUMS_LEGEND'				=> '“Date Topic Event Calendar” Extended settings',
	'ACP_DTST_FORUMS_ENABLE'				=> 'Enable Date Topic Event Calendar extension',
	'ACP_DTST_FORUMS_ENABLE_EXPLAIN'		=> 'If set to <strong>Yes</strong> the functions provided will be in use here.<br>Setting back to <strong>No</strong> does preserve the existing Date Topic Event Calendar as well, so to be created/edited if you change your mind.<br><strong>Note:</strong> if set to <strong>No</strong> the option below will not be taken into consideration.',
	'ACP_DTST_F_FORCED_FIELDS'				=> 'Force all fields as mandatory',
	'ACP_DTST_F_FORCED_FIELDS_EXPLAIN'		=> 'If set to <strong>Yes</strong> the fields must be filled prior to submitting the topic, choosing <strong>No</strong> will leave that as per the user’s choice. Settings are not retro-active.',

	// ACP errors
	'ACP_DTST_ERRORS'						=> 'Ooops! Something went wrong…',
	'ACP_DTST_ERR_LOCATION_EMOJIS_SUPPORT'	=> 'The location you are trying to ADD contains the following unsupported characters:<br>%s',
	'ACP_DTST_ERR_LOCATION_EXISTS'			=> 'The location you are trying to ADD already exists in the DB.',
	'ACP_DTST_ERR_LONG_LOCATION'			=> 'The location you are trying to ADD is too long! Less than 86 chars allowed.',
	'ACP_DTST_ERR_SHORT_LOCATION'			=> 'The location you are trying to ADD is too short! More than 0 chars please.',
	'ACP_DTST_ERR_LOCATION_EMPTY'			=> 'The location you are trying to REMOVE is empty!.',

	'ACP_DTST_ERR_PM_TITLE_EMOJIS_SUPPORT'		=> 'PM’s “Title” contains the following unsupported characters:<br>%s',
	'ACP_DTST_ERR_PM_MESSAGE_EMOJIS_SUPPORT'	=> 'PM’s “Message” contains the following unsupported characters:<br>%s',

	'ACP_DTST_ERR_REP_NAME_EMOJIS_SUPPORT'	=> 'The “Reputation name” contains the following unsupported characters:<br>%s',

	'ACP_DTST_ERR_RANK_TITLE_EMPTY'			=> 'Rank’s “Title” can’t be empty!',
	'ACP_DTST_ERR_RANK_TITLE_LONG'			=> 'Rank’s “Title” can’t be more than 15 chars!',
	'ACP_DTST_ERR_RANK_DESC_EMPTY'			=> 'Rank’s “Description” can’t be empty!',
	'ACP_DTST_ERR_RANK_DESC_LONG'			=> 'Rank’s “Description” can’t be more than 25 chars!',
	'ACP_DTST_ERR_TITLE_EMOJIS_SUPPORT'		=> 'Rank’s “Title” contains the following unsupported characters:<br>%s',
	'ACP_DTST_ERR_DESC_EMOJIS_SUPPORT'		=> 'Rank’s “Description” contains the following unsupported characters:<br>%s',
));
