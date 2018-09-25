<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\mcp;

/**
 * Date Topic Event Calendar MCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbbstudio\dtst\mcp\main_module',
			'title'		=> 'MCP_DTST_CAT',
			'modes'		=> array(
				'front'			=> array(
					'title'	=> 'MCP_DTST_FRONT',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_m_dtst_mod',
					'cat'	=> array('MCP_DTST_CAT')
				),
				'recent'		=> array(
					'title'	=> 'MCP_DTST_RECENT',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_m_dtst_mod',
					'cat'	=> array('MCP_DTST_CAT')
				),
				'adjust'		=> array(
					'title'	=> 'MCP_DTST_ADJUST',
					'auth'	=> 'ext_phpbbstudio/dtst && acl_m_dtst_mod',
					'cat'	=> array('MCP_DTST_CAT')
				),
			),
		);
	}
}
