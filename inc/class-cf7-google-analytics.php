<?php
/**
 * Contact Form 7 Google Analytics
 *
 * @package WordPress
 * @subpackage CF7_Google_Analytics
 */

/**
 * Contact Form 7 Google Analytics
 *
 * @package WordPress
 * @subpackage CF7_Google_Analytics
 */
class CF7_Google_Analytics {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.8.0';

	/**
	 * Available actions.
	 *
	 * @var string
	 */
	protected $actions = array(
		'invalid'     => array(
			'name'        => 'Invalid',
			'description' => 'An Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input.',
		),
		'spam'        => array(
			'name'        => 'Spam',
			'description' => 'An Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected.',
		),
		'mail_sent'   => array(
			'name'        => 'Mail Sent',
			'description' => 'An Ajax form submission has completed successfully, and mail has been sent.',
		),
		'mail_failed' => array(
			'name'        => 'Mail Failed',
			'description' => 'An Ajax form submission has completed successfully, but it has failed in sending mail.',
		),
		'sent'        => array(
			'name'        => 'Sent',
			'description' => '(Legacy behavior) An Ajax form submission has completed successfully, regardless of other incidents.',
		),
	);

	/**
	 * Send action options.
	 *
	 * @since 1.8.0
	 *
	 * @var array
	 */
	protected $send_actions;

	/**
	 * Get options.
	 *
	 * @since 1.8.0
	 *
	 * @param string $action Action to check.
	 *
	 * @return array
	 */
	public function get_send_actions( $action ) {
		if ( ! isset( $this->send_actions ) ) {
			$this->send_actions = get_option( 'cf7_ga_send_actions' );
		}

		if ( 'all' === $action ) {
			return $this->send_actions;
		}

		if ( is_array( $this->send_actions ) && array_key_exists( $action, $this->send_actions ) ) {
			return $this->send_actions[ $action ];
		}

		return false;
	}

	/**
	 * Get this plugin’s directory URL
	 *
	 * @return string Plugin directory URL
	 */
	private function get_plugin_dir_url() {
		return plugin_dir_url( CF7GA_PLUGIN_FILE );
	}

	/**
	 * Load everything
	 */
	public function __construct() {

		/** Check version and load the correct file */
		$wpcf7 = get_option( 'wpcf7' );
		if ( $wpcf7['version'] <= '4.7' ) {
			add_filter( 'wpcf7_ajax_json_echo', array( $this, 'add_old_tracking' ), 10, 2 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/** Register backend assets */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_assets' ) );

		/** Add settings page */
		add_action( 'admin_init', array( $this, 'settings_api' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/** Add notice about v1.7.0 changes */
		if ( is_admin() && get_option( 'cf7-ga-170-notice-dismissed' ) === false ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_170' ) );
			add_action(
				'admin_enqueue_scripts',
				function() {
					wp_enqueue_script( 'admin-cf7-ga' );
				}
			);
			add_action( 'wp_ajax_cf7_ga_dismiss_notice_170', array( $this, 'cf7_ga_dismiss_notice_170' ) );
		}

		/** Add notice about v1.8.0 changes */
		if ( is_admin() && get_option( 'cf7-ga-180-notice-dismissed' ) === false ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_180' ) );
			add_action(
				'admin_enqueue_scripts',
				function() {
					wp_enqueue_script( 'admin-cf7-ga' );
				}
			);
			add_action( 'wp_ajax_cf7_ga_dismiss_notice_180', array( $this, 'cf7_ga_dismiss_notice_180' ) );
		}
	}

	/**
	 * Send Google Analytics tracking events when form is successfully submitted and mail sent.
	 *
	 * @param  array $items  Return from CF7.
	 * @param  array $result WPCF7 data about status, message, etc.
	 * @return array modified array to return to the browser
	 */
	public function add_old_tracking( $items, $result ) {
		$form = WPCF7_ContactForm::get_current();

		if ( 'mail_sent' === $result['status'] ) {
			if ( ! isset( $items['onSentOk'] ) ) {
				$items['onSentOk'] = array();
			}

			$items['onSentOk'][] = sprintf(
				'
				if ( typeof gtag !== "undefined" ) {
					gtag( "event", "contact_form_7", {"event_category": "Contact Form 7", "event_action": "Sent", "event_label": "%1$s"} );
				}
				if ( typeof dataLayer !== "undefined" ) {
					dataLayer.push({ "event": "Contact Form 7", "event_action": "Sent", "event_label": formLabel });
				}
				if ( typeof ga !== "undefined" ) {
					ga( "send", "event", "Contact Form", "Sent", "%1$s" );
				}
				if ( typeof _gaq !== "undefined" ) {
					_gaq.push([ "_trackEvent", "Contact Form", "Sent", "%1$s" ]);
				}
				if ( typeof __gaTracker !== "undefined" ) {
					__gaTracker( "send", "event", "Contact Form", "Sent", "%1$s" );
				}
				',
				esc_js( $form->title() )
			);
		}

		return $items;
	}

	/**
	 * Enqueue script for DOM events.
	 */
	public function enqueue_assets() {
		// Get all forms.
		$form_args   = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
		);
		$forms_query = get_posts( $form_args );
		$forms       = array();

		foreach ( $forms_query as $form ) {
			$forms[ 'ID_' . $form->ID ] = $form->post_title;
		}

		// Get options.
		$send_actions = get_option( 'cf7_ga_send_actions' );

		wp_enqueue_script( 'wpcf7-ga-events', $this->get_plugin_dir_url() . 'js/cf7-google-analytics.min.js', array( 'contact-form-7' ), $this->version, true );
		wp_add_inline_script( 'wpcf7-ga-events', 'var cf7GASendActions = ' . wp_json_encode( $this->get_send_actions( 'all' ) ) . ', cf7FormIDs = ' . wp_json_encode( $forms ), 'before' );
	}

	/**
	 * Enqueue backend assets
	 */
	public function enqueue_backend_assets() {
		wp_register_script( 'admin-cf7-ga', $this->get_plugin_dir_url() . 'js/admin.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Add backend settings.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function settings_api() {

		// Register setting.
		register_setting( 'cf7_ga', 'cf7_ga_send_actions' );

		// Add the section.
		add_settings_section(
			'cf7_ga',
			__( 'Contact Form 7 to Google Analytics', 'cf7_ga' ),
			array( $this, 'settings_header' ),
			'cf7_ga'
		);

		// Add the individual settings.
		foreach ( $this->actions as $key => $value ) {
			add_settings_field(
				'cf7_ga_' . $key,
				$value['name'],
				array( $this, 'render_setting_' . $key ),
				'cf7_ga',
				'cf7_ga'
			);
		}

	}

	/**
	 * Register options page.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page( 'Contact Form 7 to Google Analytics Options', 'CF7 to GA', 'manage_options', 'cf7_ga', array( $this, 'options_page' ) );
	}

	/**
	 * Display options page.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function options_page() {

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		settings_errors( 'cf7_ga_messages' );
		?>
		<div class="wrap">
		<form action="options.php" method="post">
		<?php
		settings_fields( 'cf7_ga' );
		do_settings_sections( 'cf7_ga' );
		submit_button();
		?>
		</form>
		</div>
		<?php
	}

	/**
	 * Display settings page header content.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function settings_header() {
		?>
		<p>Choose which actions you would like to send to Google Analytics:</p>
		<?php
	}

	/**
	 * Render setting field for invalid.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function render_setting_invalid() {
		$action = 'invalid';
		?>
		<label><input type="checkbox" name="cf7_ga_send_actions[<?php echo esc_attr( $action ); ?>]" value="true" <?php checked( 'true', $this->get_send_actions( $action ) ); ?>/><?php echo esc_html( $this->actions[ $action ]['description'] ); ?></label>
		<?php
	}

	/**
	 * Render setting field for spam.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function render_setting_spam() {
		$action = 'spam';
		?>
		<label><input type="checkbox" name="cf7_ga_send_actions[<?php echo esc_attr( $action ); ?>]" value="true" <?php checked( 'true', $this->get_send_actions( $action ) ); ?>/><?php echo esc_html( $this->actions[ $action ]['description'] ); ?></label>
		<?php
	}

	/**
	 * Render setting field for mail_sent.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function render_setting_mail_sent() {
		$action = 'mail_sent';
		?>
		<label><input type="checkbox" name="cf7_ga_send_actions[<?php echo esc_attr( $action ); ?>]" value="true" <?php checked( 'true', $this->get_send_actions( $action ) ); ?>/><?php echo esc_html( $this->actions[ $action ]['description'] ); ?></label>
		<?php
	}

	/**
	 * Render setting field for mail_failed.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function render_setting_mail_failed() {
		$action = 'mail_failed';
		?>
		<label><input type="checkbox" name="cf7_ga_send_actions[<?php echo esc_attr( $action ); ?>]" value="true" <?php checked( 'true', $this->get_send_actions( $action ) ); ?>/><?php echo esc_html( $this->actions[ $action ]['description'] ); ?></label>
		<?php
	}

	/**
	 * Render setting field for sent.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function render_setting_sent() {
		$action = 'sent';
		?>
		<label><input type="checkbox" name="cf7_ga_send_actions[<?php echo esc_attr( $action ); ?>]" value="true" <?php checked( 'true', $this->get_send_actions( $action ) ); ?>/><?php echo esc_html( $this->actions[ $action ]['description'] ); ?></label>
		<?php
	}

	/**
	 * Add admin notice about new tracking behavior.
	 *
	 * @since 1.7.0
	 */
	public function admin_notices_170() {
		?>
		<div class="notice notice-info cf7-ga-notice is-dismissible" data-version="170">
			<h2>Contact Form 7 to Google Analytics Update</h2>
			<p>The tracking behavior has <strong>added more events</strong> since version 1.7.0. It now sends data to Google Analytics about <strong>all</strong> form submission attempts. Here is a list of the events you will begin to see since the upgrade:</p>
			<ul>
				<li><strong>Invalid</strong>: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input.
				<li><strong>Spam</strong>: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected.
				<li><strong>Mail Sent</strong>: Fires when an Ajax form submission has completed successfully, and mail has been sent.
				<li><strong>Mail Failed</strong>: Fires when an Ajax form submission has completed successfully, but it has failed in sending mail.
				<li><strong>Sent</strong>: Fires when an Ajax form submission has completed successfully, regardless of other incidents. (This is the old plugin behavior.)
			</ul>

			<p>Note: you will begin seeing <strong>multiple events</strong> in Google Analytics for each form submission: “Sent” plus one of the other four, depending on what happened on submission.</p>
		</div>
		<?php
	}

	/**
	 * Update option for CF7 GA 170 notes.
	 *
	 * @since 1.7.0
	 */
	public function cf7_ga_dismiss_notice_170() {
		update_option( 'cf7-ga-170-notice-dismissed', 1, false );
	}

	/**
	 * Add admin notice about options for disabling events.
	 *
	 * @since 1.8.0
	 */
	public function admin_notices_180() {
		?>
		<div class="notice notice-info cf7-ga-notice is-dismissible" data-version="180">
			<h2>Contact Form 7 to Google Analytics Update</h2>
			<p>As requested by many users, you can now choose which events will be sent or ignored. Visit the <a href="<?php echo esc_url( get_admin_url( null, 'options-general.php?page=cf7_ga' ) ); ?>">settings page</a> to choose which events to send.</p>
		</div>
		<?php
	}

	/**
	 * Update option for CF7 GA 180 notes.
	 *
	 * @since 1.8.0
	 */
	public function cf7_ga_dismiss_notice_180() {
		update_option( 'cf7-ga-180-notice-dismissed', 1, false );
	}

}
