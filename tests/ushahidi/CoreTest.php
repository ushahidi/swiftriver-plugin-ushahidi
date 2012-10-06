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
				'http://stable.ushahidi.com',
				'http://www.google.com'
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
	public function test_is_compatible_deployment($url, $bad_url)
	{
		$is_compatible = Ushahidi_Core::is_compatible_deployment($url);
		
		$this->assertTrue($is_compatible);
		
		$not_compatible = Ushahidi_Core::is_compatible_deployment($bad_url);
		$this->assertFalse($not_compatible);
	}

	/**
	 * Provides data for test_get_request_url
	 *
	 * @return array
	 */
	public function provider_get_request_url()
	{
		return array(
			array(
				'http://stable.ushahidi.com/',
				'api?task=categories',
				'http://stable.ushahidi.com/api?task=categories'
			)
		);
	}

	/**
	 * Tests concatenation of the deployment url and a segment to
	 * produce a full request uri
	 *
	 * @covers Ushahidi_Core::get_request_url
	 * @dataProvider provider_get_request_url
	 */
	public function test_get_request_url($url, $endpoint, $expected)
	{
		// Build out the URL
		$request_url = Ushahidi_Core::get_request_url($url, $endpoint);

		// Return url string should match the on in $expected
		$this->assertEquals($request_url, $expected);
	}
}