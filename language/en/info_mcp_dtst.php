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
	'ACP_DTST_LOG_PM_APPLIED'				=> '<strong>Private message sent to host after applying for:</strong><br />» %s',
	'ACP_DTST_LOG_PM_CANCELED'				=> '<strong>Private message sent to host after canceling application for:</strong><br />» %s',
	'ACP_DTST_LOG_PM_WITHDRAWN'				=> '<strong>Private message sent to host after withdrawing from:</strong><br />» %s',

	'LOG_DTST_EVENT_CANCELED'				=> '<strong>Canceled event:</strong><br />» %s',

	'LOG_DTST_OPT_ACCEPTED'					=> '<strong>Application accepted:</strong><br />» %s',
	'LOG_DTST_OPT_DENIED'					=> '<strong>Application denied:</strong><br />» %s',

	'ACP_DTST_LOG_APPLIED'					=> '<strong>Applied for event:</strong><br />» %s',
	'ACP_DTST_LOG_CANCELED'					=> '<strong>Canceled application for event:</strong><br />» %s',
	'ACP_DTST_LOG_WITHDRAWN'				=> '<strong>Withdrew from event:</strong><br />» %s',
));
