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
	/*
	 * Notification language info
	 * ---------------------------
	 * %1$s is the Username of the user managing the event
	 * Post subject is added automatically on the next line (by reference)
	 */
	'DTST_NOTIFICATION_CANCELED'		=> '<strong>Event canceled</strong> by %1$s:',
	'DTST_NOTIFICATION_ACCEPTED'		=> '<strong>Application accepted</strong> by %1$s:',
	'DTST_NOTIFICATION_DENIED'			=> '<strong>Application denied</strong> by %1$s:',

	/*
	 * %s is the string set in the ACP for "reputation"
	 * Event title (topic title) is added automatically on the next line (by reference)
	 */
	'DTST_NOTIFICATION_REPUTATION_CLOSED'	=> '<strong>The %s period closed for</strong>',
	'DTST_NOTIFICATION_REPUTATION_OPENED'	=> '<strong>The %s period opened for</strong>',
));
