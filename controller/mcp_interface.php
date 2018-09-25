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
 * Date Topic Event Calendar MCP Interface.
 */
interface mcp_interface
{
	/**
	 * Overview of worst performing users based on reputation.
	 *
	 * @return void
	 * @access public
	 */
	public function front();

	/**
	 * Display recent reputation actions.
	 *
	 * @return void
	 * @access public
	 */
	public function recent();

	/**
	 * Adjust a user's reputation
	 *
	 * @return void
	 * @access public
	 */
	public function adjust();

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return void
	 * @access public
	 */
	public function set_page_url($u_action);
}
