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

class install_acp_module extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DTST_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_DTST_TITLE',
				[
					'module_basename'	=> '\phpbbstudio\dtst\acp\dtst_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
