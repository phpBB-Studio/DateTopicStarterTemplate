<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\migrations;

class install_mcp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . 'modules
			WHERE module_class = "mcp"
				AND module_langname = "MCP_DTST_CAT"';
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'mcp',
				0,
				'MCP_DTST_CAT'
			)),
			array('module.add', array(
				'mcp',
				'MCP_DTST_CAT',
				array(
					'module_basename'	=> '\phpbbstudio\dtst\mcp\main_module',
					'modes'				=> array('front', 'recent', 'adjust'),
				),
			)),
		);
	}
}
