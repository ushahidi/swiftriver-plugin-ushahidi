<?php defined('SYSPATH') or die('No direct script access');
/**
 * Tests the Ushahidi_Core lib that is shipped with the SwiftRiver plugin
 *
 * @package    Swiftriver
 * @category   Tests
 * @author     Ushahidi Team
 * @copyright  (c) 2008-2012 Ushahidi Inc <http://www.ushahidi.com>
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Model_Deployment_Push_LogTest extends Unittest_TestCase {
	
	/**
	 * Provides data for test_add_entry
	 * @return array
	 */
	public function provider_add_entry()
	{
		return array(
			array(
				array(
					"bucket_id" => 1,
					"droplet_id" => 200
				)
			)
		);
	}
	
	/**
	 * @test
	 * @dataProvider provider_add_entry
	 * @covers Model_Deployment_Push_Log::add_entry
	 */
	public function test_add_entry($data)
	{
		// Load the push settings for the bucket
		$push_settings = Model_Deployment_Push_Setting::get_settings($entry['bucket_id']);
		
		// Get the no. of drops that are currently pending
		$pending_count = $push_settings->pending_drop_count;

		// Run the add drop event
		Swiftriver_Event::run("swiftriver.bucket.droplet.add", $data);
		
		// Reload the settings
		$push_settings->reload();

		// Verify that the pending drop count has increased
		$this->assertEquals(($pending_count + 1), $push_settings->pending_drop_count);
	}
	
	/**
	 * Providers data for test_remove_entry
	 * @return array
	 */
	public function provider_remove_entry($data)
	{
		return array(
			array(
				array(
					"bucket_id" => 1,
					"droplet_id" => 150
				)
			)
		);
	}
	
	/**
	 * @test
	 * @dataProvider provider_remove_entry
	 * @covers Model_Deployment_Push_Log::remove_entry
	 */
	public function test_remove_entry($entry)
	{
		// Load the push settings before the 
		$push_settings = Model_Deployment_Push_Setting::get_settings($entry['bucket_id']);
		
		$pending_count = $push_settings->pending_drop_count;
		
		// Run the remove drop event
		Swiftriver_Event::run("swiftriver.bucket.droplet.remove", $data);
		
		// Verify that pending drop count has reduced
		$push_settings->reload();
		$this->asserEquals(($push_settings - 1), $push_settings->pending_drop_count);
	}
}