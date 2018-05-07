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

class install_permissions extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	/**
	 * Add and set the permissions to the Registered Users Group and Admins
	 * Inactive as per default for BOTS, NEWLY_REGISTERED_USERS and GUESTS.
	 */
	public function update_data()
	{
		return array(
			/* Add permissions NOT set */
			array('permission.add', array('u_allow_dtst')),
			array('permission.add', array('a_dtst_admin')),
			/* Now do set those permissions */
			array('permission.permission_set', array('REGISTERED', 'u_allow_dtst', 'group')),
			array('permission.permission_set', array('ADMINISTRATORS', 'a_dtst_admin', 'group')),
		);
	}
}
