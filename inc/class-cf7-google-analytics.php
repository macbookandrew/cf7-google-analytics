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
	public $version = '1.8.5';

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
			$this->send_actions = get_option(
				'cf7_ga_send_actions',
				array(
					'invalid'     => 'true',
					'spam'        => 'true',
					'mail_sent'   => 'true',
					'mail_failed' => 'true',
					'sent'        => 'false',
				)
			);
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
			// TODO: drop support.
			add_filter( 'wpcf7_ajax_json_echo', array( $this, 'add_old_tracking' ), 10, 2 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/** Register backend assets */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_assets' ) );

		/** Add settings page */
		add_action( 'admin_init', array( $this, 'settings_api' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/** Cache form IDs and titles on form save */
		add_action( 'save_post_wpcf7_contact_form', array( $this, 'update_form_ids' ) );

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

		/** Add notice about v1.8.5 changes */
		if ( is_admin() && get_option( 'cf7-ga-185-notice-dismissed' ) === false ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_185' ) );
			add_action(
				'admin_enqueue_scripts',
				function() {
					wp_enqueue_script( 'admin-cf7-ga' );
				}
			);
			add_action( 'wp_ajax_cf7_ga_dismiss_notice_185', array( $this, 'cf7_ga_dismiss_notice_185' ) );
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
					dataLayer.push({ "event": "Contact Form 7", "event_action": "Sent", "event_label": %1$s });
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
		wp_enqueue_script( 'wpcf7-ga-events', $this->get_plugin_dir_url() . 'js/cf7-google-analytics.min.js', array( 'contact-form-7' ), $this->version, true );
		wp_add_inline_script( 'wpcf7-ga-events', 'var cf7GASendActions = ' . wp_json_encode( $this->get_send_actions( 'all' ) ) . ', cf7FormIDs = ' . $this->get_form_ids(), 'before' );
	}

	/**
	 * Enqueue backend assets
	 */
	public function enqueue_backend_assets() {
		wp_register_script( 'admin-cf7-ga', $this->get_plugin_dir_url() . 'js/admin.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Get or create form IDs transient.
	 *
	 * @since 1.8.2
	 *
	 * @return string JSON object with form IDs and titles.
	 */
	public function get_form_ids() {
		$form_ids = get_transient( 'cf7_ga_form_ids' );

		if ( empty( $form_ids ) ) {
			$form_ids = $this->update_form_ids();
		}

		return $form_ids;
	}

	/**
	 * Update form IDs transient.
	 *
	 * @since 1.8.2
	 *
	 * @return string JSON object with form IDs and titles.
	 */
	public function update_form_ids() {

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

		$form_ids = wp_json_encode( $forms );

		set_transient( 'cf7_ga_form_ids', $form_ids );

		return $form_ids;
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

	/**
	 * Add admin notice about options for disabling events.
	 *
	 * @since 1.8.5
	 */
	public function admin_notices_185() {
		?>
		<div class="notice notice-info cf7-ga-notice is-dismissible" data-version="185">
			<h2>Contact Form 7 to Google Analytics Important Change</h2>
			<p>If you are using any of these integrations listed below, the event label you will see in Google Analytics <strong>has been changed</strong> from “Contact Form” to “Contact Form 7” for consistency with all integrations.</p>
			<p>Affected integrations:</p>
			<ul style="list-style-type: disc; margin-left: 1.5em;">
				<li>Universal Google Analytics tracking code (analytics.js)</li>
				<li>Google Analytics Dashboard for WordpPress (GADWP)</li>
				<li>Classic Google Analytics</li>
				<li>Monster Insights</li>
				<li>Any other integration using these Javascript objects: <code>ga</code>, <code>_gaq</code>, <code>__gaTracker</code></li>
			</ul>
			<p>If you were using “Contact Form” as the event label in your goal, <strong>you must change your goals</strong> to reflect this change.</p>
		</div>
		<?php
	}

	/**
	 * Update option for CF7 GA 185 notes.
	 *
	 * @since 1.8.5
	 */
	public function cf7_ga_dismiss_notice_185() {
		update_option( 'cf7-ga-185-notice-dismissed', 1, false );
	}

}
