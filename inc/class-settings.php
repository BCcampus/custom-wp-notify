<?php
/**
 * Project: custom-wp-notify
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Date: 2018-02-19
 * Licensed under GPLv3, or any later version
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace BCcampus;


class Settings {

	/**
	 * Add appropriate hooks
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'cwp_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'cwp_settings_init' ) );

	}

	function cwp_add_admin_menu() {

		add_options_page( 'custom-wp-notify', 'custom-wp-notify', 'manage_options', 'custom-wp-notify', array(
			$this,
			'cwp_options_page'
		) );

	}

	function cwp_settings_init() {

		register_setting( 'cwpOptions', 'cwp_settings' );

		add_settings_section(
			'cwp_pluginPage_section',
			__( '', 'wordpress' ),
			'',
			'cwpOptions'
		);

		add_settings_field(
			'cwp_enable',
			__( 'Enable Notifications', 'wordpress' ),
			array( $this, 'cwp_enable_render' ),
			'cwpOptions',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_frequency',
			__( 'Notification Frequency', 'wordpress' ),
			array( $this, 'cwp_frequency_render' ),
			'cwpOptions',
			'cwp_pluginPage_section'
		);

		add_settings_field(
			'cwp_template',
			__( 'Notification Template:', 'wordpress' ),
			array( $this, 'cwp_template_render' ),
			'cwpOptions',
			'cwp_pluginPage_section'
		);


	}

	function cwp_enable_render() {

		$options = get_option( 'cwp_settings' );
		?>
		<input type='checkbox' name='cwp_settings[cwp_enable]' <?php checked( $options['cwp_enable'], 1 ); ?> value='1'>
		<?php

	}

	function cwp_frequency_render() {

		$options = get_option( 'cwp_settings' );
		?>
		<select name='cwp_settings[cwp_frequency]'>
			<option value='1' <?php selected( $options['cwp_frequency'], 1 ); ?>>Daily</option>
			<option value='2' <?php selected( $options['cwp_frequency'], 2 ); ?>>Weekly</option>
			<option value='3' <?php selected( $options['cwp_frequency'], 3 ); ?>>Monthly</option>
		</select>

		<?php

	}


	function cwp_template_render() {

		$options = get_option( 'cwp_settings' );
		?>
		<textarea cols='60' rows='15'
		          name='cwp_settings[cwp_template]'><?php echo $options['cwp_template']; ?></textarea>
		<?php

	}

	function cwp_options_page() {

		?>
		<form action='options.php' method='post'>

			<h2>Custom WP Notify</h2>

			<?php
			settings_fields( 'cwpOptions' );
			do_settings_sections( 'cwpOptions' );
			submit_button();
			?>

		</form>
		<?php

	}

}

