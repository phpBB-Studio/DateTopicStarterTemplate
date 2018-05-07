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
 * Top Poster Of The Month ACP module info.
 */
class dtst_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbstudio\dtst\acp\dtst_module',
			'title'		=> 'ACP_DTST_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_DTST_SETTINGS',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_a_dtst_admin',
					'cat'	=> ['ACP_DTST_TITLE']
				],
			],
		];
	}
}
