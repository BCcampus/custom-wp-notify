<?php
/**
 * Class CwpTest
 *
 * @package Custom_Wp_Notify
 */

/**
 * Sample test case.
 */
class CwpTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	/**
	 * @param $name
	 *
	 * @return \ReflectionMethod
	 * @throws \ReflectionException
	 */
	protected static function getMethod($name) {
		$class = new ReflectionClass('BCcampus\CwpShortcode');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	/**
	 *
	 */
	function test_maybeUrl() {
		$no_protocol = 'url.net';
		$random      = 'random string';

		$test_maybe_url  = self::getMethod( 'maybeUrl' );
		$obj             = new \BCcampus\CwpShortcode();
		$result_protocol = $test_maybe_url->invokeArgs( $obj, [ $no_protocol ] );
		$result_random   = $test_maybe_url->invokeArgs( $obj, [ $random ] );

		$this->assertFalse( $result_random );
		$this->assertStringMatchesFormat( '//url.net', $result_protocol );

	}
}
