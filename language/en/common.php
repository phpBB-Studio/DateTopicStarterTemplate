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
	'DTST_LOCATION'				=> 'Location',
	'DTST_LOCATION_EXPLAIN'		=> 'Preset locations.',
	'DTST_LOC_CUSTOM'			=> 'Location custom',
	'DTST_LOC_CUSTOM_HOLDER'	=> 'You may use/add a custom location.',
	'DTST_HOST'					=> 'Host',
	'DTST_DATE'					=> 'Event Date',
	'DTST_EVENT_TYPE'			=> 'Event Type',
	'DTST_AGE_MIN'				=> 'Min. age',
	'DTST_AGE_MAX'				=> 'Max age',
	'DTST_PARTECIPANTS'			=> 'Nr. of partecipants',
	'DTST_PARTECIPANTS_ZERO'	=> '0 means unlimited',
	'DTST_UNLIMITED'			=> 'Unlimited',
	'DTST_AGE_RANGE_NO'			=> 'N/A',
	'DTST_AGE_RANGE_ZERO'		=> '0 means N/A.',
));
