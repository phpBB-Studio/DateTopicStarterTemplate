<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\acp;

/**
 * Date Topic Event Calendar ACP module info.
 */
class dtst_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbstudio\dtst\acp\dtst_module',
			'title'		=> 'ACP_DTST_TITLE',
			'modes'		=> [
				'locations'	=> array(
					'title'	=> 'ACP_DTST_LOCATIONS',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
				'settings'	=> array(
					'title'	=> 'ACP_DTST_SETTINGS',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
				'privmsg'	=> array(
					'title'	=> 'ACP_DTST_PRIVMSG',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
				'lpr_settings'	=> array(
					'title'	=> 'ACP_DTST_LPR_SETTINGS',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
				'lpr_reputation'	=> array(
					'title'	=> 'ACP_DTST_LPR_REPUTATION',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
				'lpr_ranks'	=> array(
					'title'	=> 'ACP_DTST_LPR_RANKS',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> array('ACP_DTST_TITLE')
				),
			],
		];
	}
}
