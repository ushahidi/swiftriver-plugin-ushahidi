<?php defined('SYSPATH') or die('No direct script access');
/**
 * Tests the Model_Deployment ORM library for the ushahidi plugin
 *
 * @package    Swiftriver
 * @category   Tests
 * @author     Ushahidi Team
 * @copyright  (c) 2008-2012 Ushahidi Inc <http://www.ushahidi.com>
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Model_DeploymentTest extends Unittest_TestCase {
	
	/**
	 * Provides data for test_add_deployment
	 *
	 * @return array
	 */
	public function provider_add_deployment()
	{
		return array(
			array(
				// $user_id
				1,
				
				//  $data
				array(
					"deployment_url" => "http://ushahidi.dev",
					"deployment_name" => "Local Ushahidi Dev",
					"client_id" => "xyz-Abcd123",
					"client_secret" => "fc099uid-P19azwe74d"
				)
			)
		);
	}
	
	/**
	 * If the user and provided data passes the validation checks,
	 * the deployment is added to the database otherwise an exception
	 * is thrown
	 *
	 * @test
	 * @dataProvider provider_add_deployment
	 * @covers Model_Deployment::add_deployment
	 */
	public function test_add_deployment($user_id, $data)
	{
		$result = Model_Deployment::add_deployment($user_id, $data);
		
		// Assert that the return value is an instance of Model_Deployment
		$this->assertInstanceOf('Model_Deployment_User', $result);
		
		// Verify that we can retrive a deployment record given its url
		$this->assertInstanceOf('Model_Deployment', Model_Deployment::get_deployment_by_url($data['deployment_url']));		
	}	
	
}