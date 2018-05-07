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

class install_config_txt_locations extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return array(
			array('config_text.add', array('dtst_locations', json_encode(array(
							'',
							'Borås',
							'Eskilstuna',
							'Gävle',
							'Göteborg',
							'Halmstad',
							'Helsingborg',
							'Jönköping',
							'Karlstad',
							'Linköping',
							'Lund',
							'Malmö',
							'Norrköping',
							'Stockholm',
							'Sundsvall',
							'Södertälje',
							'Uppsala',
							'Umeå',
							'Västerås',
							'Växjö',
							'Örebro',
						)
					)
				),
			),
		);
	}

	public function revert_data()
	{
		return array(
			array('config_text.remove', array('dtst_locations')),
		);
	}
}
