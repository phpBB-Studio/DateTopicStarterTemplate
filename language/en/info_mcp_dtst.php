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
	// Titles
	'MCP_DTST_CAT'				=> 'Events',
	'MCP_DTST_FRONT'			=> 'Overview',
	'MCP_DTST_RECENT'			=> 'Recent',
	'MCP_DTST_ADJUST'			=> 'Adjust',

	// Front
	'MCP_DTST_FRONT_LATEST_5'			=> 'Latest 5',
	'MCP_DTST_FRONT_CONDUCT_BAD'		=> 'Latest bad conducts',
	'MCP_DTST_FRONT_CONDUCT_BAD_NONE'	=> 'No bad conducts',
	'MCP_DTST_FRONT_THUMBS_DOWN'		=> 'Latest thumbs down',
	'MCP_DTST_FRONT_THUMBS_DOWN_NONE'	=> 'No thumbs down',
	'MCP_DTST_FRONT_NO_SHOW'			=> 'Latest no shows',
	'MCP_DTST_FRONT_NO_SHOW_NONE'		=> 'No no shows',
	'MCP_DTST_FRONT_MOD'				=> 'Latest moderator adjustments',
	'MCP_DTST_FRONT_MOD_NONE'			=> 'No moderator adjustments',
	'MCP_DTST_FRONT_MODERATED'			=> 'Moderated',

	// Recent
	'MCP_DTST_RECENT_SUBTITLE'	=> 'Overview of recent %s',
	'MCP_DTST_RECENT_EXPLAIN'	=> 'Here you will find a list of all recent %1$s actions. Use the %2$s to specify any criteria and the %3$s to find a user.',
	'MCP_DTST_RECENT_NONE'		=> 'No recent %s',

	// Adjust
	'MCP_DTST_ADJUST_SUBTITLE'	=> 'Adjust a user’s %s',
	'MCP_DTST_ADJUST_EXPLAIN'	=> 'Give or take %1$s from a user. Use the %2$s facility to look up and add a user automatically.',
	'MCP_DTST_ADJUST_SUCCESS'	=> 'The user’s %s has successfully been adjusted.',
	'MCP_DTST_ADJUST_NO_REP'	=> 'You have not specified a %s value.',
	'MCP_DTST_ADJUST_GIVE'		=> 'Give',
	'MCP_DTST_ADJUST_TAKE'		=> 'Take',

	// Logs
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
	'ACP_DTST_LOG_REP_MOD_GIVE'				=> '<strong>Gave %4$s to:</strong><br>» User: %1$s<br>» Amount: %2$s<br>» Reason: %3$s',
	'ACP_DTST_LOG_REP_MOD_TAKE'				=> '<strong>Took %4$s from:</strong><br>» User: %1$s<br>» Amount: %2$s<br>» Reason: %3$s',

	// Errors
	'MCP_DTST_ERR_REASON_EMOJIS_SUPPORT'	=> 'The “Reason” contains the following unsupported characters:<br>%s',
));
