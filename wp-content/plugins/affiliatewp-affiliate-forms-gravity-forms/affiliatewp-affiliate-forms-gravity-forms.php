<?php
/**
 * Plugin Name: AffiliateWP - Affiliate Forms For Gravity Forms
 * Plugin URI: https://affiliatewp.com/add-ons/pro/affiliate-forms-for-gravity-forms/
 * Description: Create an affiliate registration form using Gravity Forms
 * Author: AffiliateWP, LLC
 * Author URI: https://affiliatewp.com
 * Version: 1.0.17
 * Text Domain: affiliatewp-afgf
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AffiliateWP_Affiliate_Forms_For_Gravity_Forms' ) ) {

	/**
	 * Affiliate Forms for Gravity Forms loader.
	 *
	 * @since 1.0
	 */
	final class AffiliateWP_Affiliate_Forms_For_Gravity_Forms {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of AffiliateWP_Affiliate_Forms_For_Gravity_Forms exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @access private
		 * @since  1.0
		 * @var    AffiliateWP_Affiliate_Forms_For_Gravity_Forms
		 * @static
		 */
		private static $instance;


		/**
		 * The plugin's version number.
		 *
		 * @access private
		 * @since  1.0
		 * @var    string
		 * @static
		 */
		private static $version = '1.0.17';

		/**
		 * The affiliate registration handler instance variable.
		 *
		 * @access public
		 * @since  1.0
		 * @var    Affiliate_WP_Gravity_forms_Register
		 */
		public $register;

		/**
		 * Main AffiliateWP_Affiliate_Forms_For_Gravity_Forms Instance.
		 *
		 * Insures that only one instance of AffiliateWP_Affiliate_Forms_For_Gravity_Forms exists
		 * in memory at any one time. Also prevents needing to define globals all over the place.
		 *
		 * @access public
		 * @since  1.0
		 * @static
		 *
		 * @return AffiliateWP_Affiliate_Forms_For_Gravity_Forms The one true instance.
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Affiliate_Forms_For_Gravity_Forms ) ) {

				self::$instance = new AffiliateWP_Affiliate_Forms_For_Gravity_Forms;
				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->init();
				self::$instance->hooks();

				self::$instance->register = new Affiliate_WP_Gravity_forms_Register;

			}

			return self::$instance;
		}

		/**
		 * Throws an error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-afgf' ), '1.0' );
		}

		/**
		 * Disables unserializing of the class.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-afgf' ), '1.0' );
		}

		/**
		 * Sets up the instance.
		 *
		 * @access private
		 * @since  1.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Resets the class instance.
		 *
		 * @access public
		 * @since  1.0
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Sets up the plugin constants.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'AFFWP_AFGF_VERSION' ) ) {
				define( 'AFFWP_AFGF_VERSION', self::$version );
			}

			// Plugin Folder Path
			if ( ! defined( 'AFFWP_AFGF_PLUGIN_DIR' ) ) {
				define( 'AFFWP_AFGF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'AFFWP_AFGF_PLUGIN_URL' ) ) {
				define( 'AFFWP_AFGF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'AFFWP_AFGF_PLUGIN_FILE' ) ) {
				define( 'AFFWP_AFGF_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'affiliatewp_afgf_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale',  get_locale(), 'affiliatewp-afgf' );
			$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliatewp-afgf', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/affiliatewp-afgf/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/affiliatewp-afgf/ folder
				load_textdomain( 'affiliatewp-afgf', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/affiliatewp-afgf/languages/ folder
				load_textdomain( 'affiliatewp-afgf', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'affiliatewp-afgf', false, $lang_dir );
			}
		}

		/**
		 * Includes the necessary plugin files.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function includes() {

			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/functions.php';
			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/class-register.php';
			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/fields.php';
			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/email-tags.php';

			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/admin.php';
			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/shortcodes.php';
			require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/class-upgrader.php';

			// must be an affiliate registration form set up
			if ( affwp_afgf_get_registration_form_id() ) {
				require_once AFFWP_AFGF_PLUGIN_DIR . 'includes/emails.php';
			}


		}

		/**
		 * Initializes the plugin.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function init() {

			if ( is_admin() ) {
				self::$instance->updater();
			}

		}

		/**
		 * Sets up the default actions and filters.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function hooks() {

			// Plugin meta.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

			// Add the affiliate.
			add_action( 'gform_entry_created', array( $this, 'add_affiliate' ), 10, 2 );
		}

		/**
		 * Adds an affiliate account.
		 *
		 * @access public
		 * @since 1.0
		 *
		 * @param array $entry Entry data.
		 * @param array $form  Form data.
		 */
		public function add_affiliate( $entry, $form ) {

			$form_id = affwp_afgf_get_registration_form_id();

			// Bail if not the registration form.
			if ( ! $form_id || $form['id'] !== $form_id ) {
				return;
			}

			// Register the user.
			$this->register->register_user( $entry, $form );
		}

		/**
		 * Modifies the plugin meta links in plugins list table.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param array  $links The current links array.
		 * @param string $file  A specific plugin table entry.
		 * @return array The modified links array.
		 */
		public function plugin_meta( $links, $file ) {
		    if ( $file == plugin_basename( __FILE__ ) ) {
		        $plugins_link = array(
		            '<a title="' . __( 'Get more add-ons for AffiliateWP', 'affiliatewp-afgf' ) . '" href="http://affiliatewp.com/addons/" target="_blank">' . __( 'Get add-ons', 'affiliatewp-afgf' ) . '</a>'
		        );

		        $links = array_merge( $links, $plugins_link );
		    }

		    return $links;
		}

		/**
		 * Loads the custom plugin updater.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @see AffWP_AddOn_Updater
		 *
		 * @return void
		 */
		public function updater() {

			if ( class_exists( 'AffWP_AddOn_Updater' ) ) {
				$updater = new AffWP_AddOn_Updater( 25083, __FILE__, self::$version );
			}
		}
	}

	/**
	 * The main function responsible for returning the one true AffiliateWP_Affiliate_Forms_For_Gravity_Forms
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example:
	 *
	 *     $affiliatewp_afgf = affiliatewp_afgf();
	 *
	 * @since 1.0
	 *
	 * @return AffiliateWP_Affiliate_Forms_For_Gravity_Forms The one true class instance.
	 */
	function affiliatewp_afgf() {

		// If AffiliateWP or Gravity Forms aren't active, fire off the activation scripts.
	    if ( ! class_exists( 'Affiliate_WP' ) || ! class_exists( 'GFForms' ) ) {

			if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
	            require_once 'includes/class-activation.php';
	        }

	        if ( ! class_exists( 'AffiliateWP_Affiliate_Forms_For_Gravity_Forms_Activation' ) ) {
	            require_once 'includes/class-activation-gravity-forms.php';
	        }

	        // AffiliateWP activation.
			if ( ! class_exists( 'Affiliate_WP' ) ) {
				$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation = $activation->run();
			}

			if ( ! class_exists( 'GFForms' ) ) {
				$activation = new AffiliateWP_Affiliate_Forms_For_Gravity_Forms_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation = $activation->run();
			}

	    } else {
	        return AffiliateWP_Affiliate_Forms_For_Gravity_Forms::instance();
	    }

	}
	add_action( 'plugins_loaded', 'affiliatewp_afgf', 100 );

}
