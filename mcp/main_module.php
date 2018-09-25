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
 * Date Topic Event Calendar MCP module.
 */
class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	function main($id, $mode)
	{
		global $phpbb_container;

		$lang		= $phpbb_container->get('language');
		$controller	= $phpbb_container->get('phpbbstudio.dtst.mcp.controller');

		$controller->set_page_url($this->u_action);

		$this->tpl_name = 'mcp_dtst_body';
		$this->page_title = $lang->lang('MCP_DTST_' . utf8_strtoupper($mode));

		switch ($mode)
		{
			case 'recent':
				$controller->recent();
			break;

			case 'adjust':
				$controller->adjust();
			break;

			case 'front':
			default:
				$controller->front();
			break;
		}
	}
}
