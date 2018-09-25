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
 * Date Topic Event Calendar Reputation Interface.
 */
interface reputation_interface
{
	/**
	 * Display reputation page per event.
	 *
	 * @return mixed
	 * @access public
	 */
	public function handle();

	/**
	 * Give reputation to a user.
	 *
	 * @return mixed
	 * @access public
	 */
	public function give();

	/**
	 * View reputation list for a specific user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  int		$page			The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access public
	 */
	public function view($user_id, $page);

	/**
	 * Delete a reputation for a user.
	 *
	 * @return mixed
	 * @access public
	 */
	public function delete();
}
