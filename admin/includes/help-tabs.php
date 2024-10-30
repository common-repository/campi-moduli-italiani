<?php
/**
 * The class used to render the help tabs.
 *
 * @link       https://wordpress.org/plugins/campi-moduli-italiani/
 * @since      1.0.0
 *
 * @package    campi-moduli-italiani
 * @subpackage campi-moduli-italiani/admin
 */

/**
 * The class used to render the help tabs.
 *
 * @link       https://wordpress.org/plugins/campi-moduli-italiani/
 * @since      1.0.0
 *
 * @package    campi-moduli-italiani
 * @subpackage campi-moduli-italiani/admin
 */
class GCMI_Help_Tabs {

	/**
	 * Object containing the screen in admin
	 *
	 * @var WP_Screen The Screen object
	 */
	private $screen;

	/**
	 * Class constructor
	 *
	 * Sets the screen to value passed
	 *
	 * @param WP_Screen $screen The current screen object.
	 * @return void
	 */
	public function __construct( WP_Screen $screen ) {
		$this->screen = $screen;
	}

	/**
	 * Sets help tab based on type
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $type A string related to the tab selected in the admin page.
	 * @return void
	 */
	public function set_help_tabs( $type ) {
		switch ( $type ) {
			case 'gcmi':
				$this->screen->add_help_tab(
					array(
						'id'      => 'gcmi_overview',
						'title'   => __( 'Overview', 'campi-moduli-italiani' ),
						'content' => $this->content( 'gcmi_overview' ),
					)
				);

				$this->screen->add_help_tab(
					array(
						'id'      => 'gcmi_update_tables',
						'title'   => __( 'Update tables', 'campi-moduli-italiani' ),
						'content' => $this->content( 'update_tables_overview' ),
					)
				);

				$this->sidebar();

				return;
		}
	}

	/**
	 * Sets help tab based on type
	 *
	 * @param string $name A string related to single help voice.
	 * @access private
	 * @return string
	 */
	private function content( $name ) {
		$content                  = array();
		$content['gcmi_overview'] = '<p>' . sprintf(
		/* translators: %1$s: Contact Form 7, plugin page link; %2$s: link to the page where ISTAT publishes used data; %3$s: link to the page where Agenzia delle entrate publishes used data */
			esc_html__( '"Campi Moduli Italiani" creates shortcodes and, if %1$s is activated, form-tags, useful into Italian forms. The first module written is used to select an Italian municipality. Optionally it can show details of selected municipality. The data used are retrivied from %2$s and from %3$s.', 'campi-moduli-italiani' ),
			'<a href="https://contactform7.com" target="_blank">Contact Form 7</a>',
			'<a href="https://www.istat.it/it/archivio/6789" target="_blank">https://www.istat.it/it/archivio/6789</a>',
			'<a href="https://www1.agenziaentrate.gov.it/servizi/codici/ricerca/VisualizzaTabella.php?ArcName=00T4" target="_blank">https://www1.agenziaentrate.gov.it/servizi/codici/ricerca/VisualizzaTabella.php?ArcName=00T4</a>'
		) . '</p>';

		$content['update_tables_overview'] = '<p>' . sprintf(
			/* translators: %1$s: link to ISTAT website; %2$s: link to the page where ISTAT publishes used data */
			esc_html__( 'On this screen, you can update tables by direct data download from %1$s and %2$s. For details about downloaded data, visit %3$s.', 'campi-moduli-italiani' ),
			'<a href="https://www.istat.it" target="_blank">https://www.istat.it</a>',
			'<a href="https://www.agenziaentrate.gov.it" target="_blank">https://www.agenziaentrate.gov.it</a>',
			'<a href="https://www.istat.it/it/archivio/6789" target="_blank">https://www.istat.it/it/archivio/6789</a>'
		) . '</p>';
		$content['update_tables_overview'] .= '<p>' . esc_html__( 'Check the update dates of your data and the update dates of the online files, pick tables to update, select the "Update tables" bulk action and click on "Apply".', 'campi-moduli-italiani' ) . '</p>';

		if ( ! empty( $content[ $name ] ) ) {
			return $content[ $name ];
		} else {
			return '';
		}
	}

	/**
	 * Sets the help sidebar
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function sidebar() {
		$content  = '<p><strong>' . __( 'For more information:', 'campi-moduli-italiani' ) . '</strong></p>';
		$content .= sprintf( '<p><a href="%s" target="_blank">', 'https://wordpress.org/plugins/campi-moduli-italiani/' ) . __( 'Plugin page', 'campi-moduli-italiani' ) . '</a></p>';
		$this->screen->set_help_sidebar( $content );
	}
}
