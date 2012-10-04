<?php defined('SYSPATH') or die('No direct script access');
/**
 * Tests the Ushahidi_Core lib for the ushahidi plugin
 *
 * @package    Swiftriver
 * @category   Tests
 * @author     Ushahidi Team
 * @copyright  (c) 2008-2012 Ushahidi Inc <http://www.ushahidi.com>
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Ushahidi_CoreTest extends Unittest_TestCase {
	
	/**
	 * Provides data for test_is_compatible_deployment
	 *
	 * @return array
	 */
	public function provider_is_compatible_deployment()
	{
		return array(
			array(
				'http://stable.ushahidi.com'
			)
		);
	}
	
	/**
	 * Tests the checking of a compatible Ushahidi deployment
	 *
	 * @test
	 * @covers Ushahidi_Core::is_compatible_deployment
	 * @dataProvider provider_is_compatible_deployment
	 */
	public function test_is_compatible_deployment($url)
	{
		$is_compatible = Ushahidi_Core::is_compatible_deployment($url);
		
		$this->assertTrue($is_compatible);
	}
}