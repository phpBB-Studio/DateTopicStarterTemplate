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
	/*
	 * Notification language info
	 * ---------------------------
	 * %1$s is the Username of the user managing the event
	 * Post subject is added automatically on the next line (by reference)
	 */
	'DTST_NOTIFICATION_CANCELED'		=> '<strong>Event canceled</strong> by %1$s:',
	'DTST_NOTIFICATION_ACCEPTED'		=> '<strong>Application accepted</strong> by %1$s:',
	'DTST_NOTIFICATION_DENIED'			=> '<strong>Application denied</strong> by %1$s:',
));
