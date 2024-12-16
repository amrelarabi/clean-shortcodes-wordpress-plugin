<?php
/**
 * Function to test the shortcode cleaner for various scenarios.
 *
 * @return void
 */
function test_clean_shortcode_cases() {
	global $shortcode_tags;

	// Register dummy shortcodes for testing.
	$shortcode_tags['test'] = '__return_empty_string';
	$shortcode_tags['test2'] = '__return_empty_string';
	$shortcode_tags['test3'] = '__return_empty_string';
	$shortcode_tags['test4'] = '__return_empty_string';

	$test_cases = [
		// Basic Shortcode Cases
		[
			'name' => 'Single Self-Closing Shortcode',
			'input' => '[test]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Single Paired Shortcode',
			'input' => '[test]content[/test]',
			'shortcode' => 'test',
			'expected' => '',
		],

		// Multiple Shortcodes in a Single Post
		[
			'name' => 'Adjacent Shortcodes',
			'input' => '[test][test2][/test2]',
			'shortcode' => 'test',
			'expected' => '[test2][/test2]',
		],
		[
			'name' => 'Different Shortcodes',
			'input' => '[test]content[/test][test2]content[/test2]',
			'shortcode' => 'test',
			'expected' => '[test2]content[/test2]',
		],
		[
			'name' => 'Multiple Instances of the Same Shortcode',
			'input' => '[test]content1[/test][test]content2[/test]',
			'shortcode' => 'test',
			'expected' => '',
		],

		// Nested Shortcodes
		[
			'name' => 'Nested Shortcodes of the Same Type',
			'input' => '[test][test]content[/test][/test]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Nested Shortcodes of Different Types',
			'input' => '[test][test2]content[/test2][/test]',
			'shortcode' => 'test2',
			'expected' => '[test][/test]',
		],

		// Shortcodes with Attributes
		[
			'name' => 'Shortcode with a Single Attribute',
			'input' => '[test attr="value"]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Shortcode with Multiple Attributes',
			'input' => '[test attr1="value1" attr2="value2"]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Shortcode with Attributes and Content',
			'input' => '[test attr="value"]content[/test]',
			'shortcode' => 'test',
			'expected' => '',
		],

		// Edge Cases
		[
			'name' => 'Shortcode with Line Breaks',
			'input' => "[test]\ncontent\n[/test]",
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Shortcode Inside Paragraphs',
			'input' => '<p>[test]content[/test]</p>',
			'shortcode' => 'test',
			'expected' => '<p></p>',
		],
		[
			'name' => 'Partially Opened Shortcode',
			'input' => '[test]content',
			'shortcode' => 'test',
			'expected' => 'content',
		],
		[
			'name' => 'Shortcode with Unregistered Name',
			'input' => '[unregistered]content[/unregistered]',
			'shortcode' => 'unregistered',
			'expected' => '',
		],

		// Complex Scenarios
		[
			'name' => 'Shortcode with Content and Inline HTML',
			'input' => '[test]<b>content</b>[/test]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Multiple Nested Shortcodes',
			'input' => '[test][test2][test3]content[/test3][/test2][/test]',
			'shortcode' => 'test2',
			'expected' => '[test][test3]content[/test3][/test]',
		],
		[
			'name' => 'Mix of Registered and Unregistered Shortcodes',
			'input' => '[test]content[/test] [unknown]content[/unknown]',
			'shortcode' => 'unknown',
			'expected' => '[test]content[/test]',
		],
		[
			'name' => 'Shortcodes with Escaped Brackets',
			'input' => '\[test\]',
			'shortcode' => 'test',
			'expected' => '\[test\]',
		],
		[
			'name' => 'Shortcode with Inline Comments',
			'input' => '[test]<!-- Comment -->content[/test]',
			'shortcode' => 'test',
			'expected' => '',
		],
		[
			'name' => 'Shortcode with No Matching Closing Tag',
			'input' => '[test]content',
			'shortcode' => 'test',
			'expected' => 'content',
		],
	];

	foreach ( $test_cases as $case ) {
		$output = cus_clean_shortcode_content( $case['input'], $case['shortcode'] );
		$result = $output === $case['expected'] ? 'PASS' : 'FAIL';

		error_log( "{$case['name']} - {$result}" );
		if ( $result === 'FAIL' ) {
			error_log( "Expected: {$case['expected']}" );
			error_log( "Got: {$output}" );
		}
	}
}

/**
 * Simulated function to clean shortcodes from content.
 *
 * @param string $content   The post content.
 * @param string $shortcode The shortcode to clean.
 * @return string           The cleaned content.
 */
function cus_clean_shortcode_content( $content, $shortcode ) {
	$pattern = get_shortcode_regex( [ $shortcode ] );
	return preg_replace( "/$pattern/", '', $content );
}

// on page=shortcodes-analyzer&test=true
add_action( 'admin_init', function() {
	if ( isset( $_GET['test'] ) ) {
		test_clean_shortcode_cases();
		exit;
	}
} );
