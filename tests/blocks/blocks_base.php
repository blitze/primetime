<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\sitemaker\tests\blocks;

abstract class blocks_base extends \phpbb_database_test_case
{
	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array('blitze/sitemaker');
	}

	/**
	 * Configure the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		define('FORUMS_PREVIEW_FIRST_POST', 1);
		define('FORUMS_PREVIEW_LAST_POST', 2);

		define('FORUMS_ORDER_FIRST_POST', 0);
		define('FORUMS_ORDER_LAST_POST', 1);
		define('FORUMS_ORDER_LAST_READ', 2);

		$tpl_data = array();
		$this->ptemplate = $this->getMockBuilder('\blitze\sitemaker\services\template')
			->disableOriginalConstructor()
			->getMock();

		// make sure we've set template file
		$this->ptemplate->expects($this->any())
			->method('assign_vars')
			->will($this->returnCallback(function($data) use (&$tpl_data) {
				$tpl_data = array_merge($tpl_data, $data);
			}));

		// make sure we've set template file
		$this->ptemplate->expects($this->any())
			->method('assign_block_vars')
			->will($this->returnCallback(function($key, $data) use (&$tpl_data) {
				$tpl_data[$key][] = $data;
			}));

		$this->ptemplate->expects($this->any())
			->method('render_view')
			->will($this->returnCallback(function() use (&$tpl_data) {
				return $tpl_data;
			}));
	}
}
