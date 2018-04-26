<?php
/**
 *
 * Date Topic Starter Template. An extension for the phpBB Forum Software package.
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
	'DTST_LOCATION_MISSING'			=> 'You must specify a “Location” when posting a new topic.',
	'DTST_HOST_MISSING'				=> 'You must specify a “Host” when posting a new topic.',
	'DTST_DATE_MISSING'				=> 'You must specify a “Date” when posting a new topic.',
));
