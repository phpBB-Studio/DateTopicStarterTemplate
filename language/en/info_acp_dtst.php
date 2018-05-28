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

$lang = array_merge($lang, array(
	// Cat
	'ACL_CAT_PHPBB_STUDIO'					=> 'phpBB Studio',
	// ACP
	'ACP_DTST_TITLE'						=> 'Date Topic Event Calendar',
	'ACP_DTST_LOCATIONS'					=> 'Locations',
	'ACP_DTST_LOCATIONS_SAVED'				=> 'Date Topic Event Calendar Locations saved.',
	'ACP_DTST_LOCATION_ADDED'				=> 'Date Topic Event Calendar location successfully added.',
	'ACP_DTST_LOCATION_REMOVED'				=> 'Date Topic Event Calendar location successfully removed.',
	'ACP_DTST_SETTINGS'						=> 'Settings',
	'ACP_DTST_SETTING_SAVED'				=> 'Date Topic Event Calendar Settings saved.',

	// ACP - Log
	'DTST_LOG_CONFIG_SAVED'					=> '<strong>Date Topic Event Calendar</strong> Locations configuration saved.',
	'DTST_LOG_LOCATION_ADDED'				=> '<strong>Date Topic Event Calendar location added.</strong><br>» %s',
	'DTST_LOG_LOCATION_REMOVED'				=> '<strong>Date Topic Event Calendar location removed.</strong><br>» %s',

	'DTST_LOG_SETTINGS_SAVED'				=> '<strong>Date Topic Event Calendar</strong> Settings configuration saved.',

	// ACP Forums
	'ACP_DTST_FORUMS_LEGEND'				=> 'Extended settings',
	'ACP_DTST_FORUMS_ENABLE'				=> 'Enable Date Topic Event Calendar extension',
	'ACP_DTST_FORUMS_ENABLE_EXPLAIN'		=> 'If set to <strong>Yes</strong> the functions provided will be in use here.<br>Setting back to <strong>No</strong> does preserve the existing Date Topic Event Calendar as well, so to be created/edited if you change your mind.<br><strong>Note:</strong> if set to <strong>No</strong> the option below will not be taken into consideration.',
	'ACP_DTST_F_FORCED_FIELDS'				=> 'Force all fields as mandatory',
	'ACP_DTST_F_FORCED_FIELDS_EXPLAIN'		=> 'If set to <strong>Yes</strong> the fields must be filled prior to submitting the topic, choosing <strong>No</strong> will leave that as per the user’s choice. Settings are not retro-active.',

	// ACP errors
	'ACP_DTST_ERR_LOCATION_EXISTS'			=> 'The location you are trying to ADD already exists in the DB.',
	'ACP_DTST_ERR_LONG_LOCATION'			=> 'The location you are trying to ADD is too long! Less than 86 chars allowed.',
	'ACP_DTST_ERR_SHORT_LOCATION'			=> 'The location you are trying to ADD is too short! More than 0 chars please.',
	'ACP_DTST_ERR_LOCATION_EMPTY'			=> 'The location you are trying to REMOVE is empty!.',
));
