<?php
/**
 *
 * Date Topic Starter Template. An extension for the phpBB Forum Software package.
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
	// ACP Forums
	'ACP_DTST_FORUMS_LEGEND'				=>	'Extended settings',

	'ACP_DTST_FORUMS_ENABLE'				=>	'Enable Date Topic Starter Template extension',
	'ACP_DTST_FORUMS_ENABLE_EXPLAIN'		=>	'If set to <strong>Yes</strong> the functions provided will be in use here.<br>Setting back to <strong>No</strong> does preserve the existing Date Topic Starter Templates as well, so to be created/edited if you change your mind.<br><strong>Note:</strong> if set to <strong>No</strong> the two options below will not be taken into consideration.',

	'ACP_DTST_F_LOCATION'					=>	'Location default',
	'ACP_DTST_F_LOCATION_EXPLAIN'			=>	'Each forum can have a default Location preset, input here the desired Location to use as default and placeholder, can be changed later on a per topic basis.',

	'ACP_DTST_F_FORCED_FIELDS'				=>	'Force all fields as mandatory',
	'ACP_DTST_F_FORCED_FIELDS_EXPLAIN'		=>	'If set to <strong>Yes</strong> the fields must be filled prior to submitting the topic, choosing <strong>No</strong> will leave that as per the user’s choice. Settings are not retro-active.',
));
