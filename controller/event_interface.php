<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\controller;

/**
 * Date Topic Event Calendar Event Interface.
 */
interface event_interface
{
	/**
	 * Handles actions (apply|reapply|withdraw|cancel) performed by a user for an event.
	 *
	 * @return void
	 * @access public
	 */
	public function handle();

	/**
	 * Handles the cancellation of an event by the host.
	 *
	 * @return mixed
	 * @access public
	 */
	public function cancel();

	/**
	 * Handles actions (accept|deny|remove) performed by a host for an event.
	 *
	 * @return mixed
	 * @access public
	 */
	public function manage();
}
