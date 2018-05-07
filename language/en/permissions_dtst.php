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
	'ACL_U_ALLOW_DTST'		=> '<strong>Date Topic Event Calendar</strong> - Can post calendar topics',
	'ACL_A_DTST_ADMIN'		=> '<strong>Date Topic Event Calendar</strong> - Can administer the extension',
));
