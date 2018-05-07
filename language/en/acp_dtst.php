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
	$lang = [];
}

$lang = array_merge($lang, [
	'DTST_GENERAL'							=> 'General Settings',

	'ACP_DTST_LOCATION'						=> 'Preset location',

	'ACP_ADD_PRESET_LOCATION'				=> 'Add a new location',
	'ACP_ADD_PRESET_LOCATION_EXPLAIN'		=> 'No Emojis please, those will be automatically stripped.<br>Min. 1 max 86 chars allowed.',
	'ACP_REMOVE_PRESET_LOCATION'			=> 'Remove a location',
	'ACP_REMOVE_PRESET_LOCATION_EXPLAIN'	=> 'Chose a location to remove.',

	'ACP_LOCATIONS_MANAGEMENT'				=> 'Here you can add/remove custom locations, each form works per se.',
]);
