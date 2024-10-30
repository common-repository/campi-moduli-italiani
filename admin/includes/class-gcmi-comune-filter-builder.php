<?php
/**
 * The class used to render the comune's filter builder page.
 *
 * @link       https://wordpress.org/plugins/campi-moduli-italiani/
 * @since      2.2.0
 *
 * @package    campi-moduli-italiani
 * @subpackage campi-moduli-italiani/admin
 */

/**
 * The class used to render the filter builder page.
 *
 * @link       https://wordpress.org/plugins/campi-moduli-italiani/
 * @since      2.2.0
 *
 * @package    campi-moduli-italiani
 * @subpackage campi-moduli-italiani/admin
 */
class GCMI_Comune_Filter_Builder {
	/**
	 * Mostra la pagina del filter builder
	 */
	public static function show_comune_filter_builder_page(): void {
		$html  = '<div class="wrap" style="display:flex;align-items:baseline;">';
		$html .= '<span class="dashicons dashicons-filter"></span>';
		$html .= '<h1>' . esc_html__( 'A filter builder for municipalities selections.', 'campi-moduli-italiani' ) . '</h1></div>';

		$html .= '<div id="gcmi-fb-dialog" title="Basic dialog">';
		$html .= '</div>';

		$html .= '<div class="gcmi-fb-main-container">';
		$html .= '<div class="gcmi-fb-box-container">';
		$html .= '<div class="gcmi-fb-filters-container" id="gcmi-fb-filters-container">';
		$html .= self::print_filtri();
		$html .= '</div>';
		$html .= self::print_spinner();
		$html .= '<div class="gcmi-fb-tabs-container" id="gcmi-fb-tabs-container">';
		$html .= '<div id="gcmi-fb-tabs">';
		$html .= '<ul>';
		$html .= '<li><a href="#gcmi-fb-tabs-1">' . esc_html__( 'Existent/Ceased', 'campi-moduli-italiani' ) . '</a></li>';
		$html .= '<li><a href="#gcmi-fb-tabs-2">' . esc_html__( 'Select regions', 'campi-moduli-italiani' ) . '</a></li>';
		$html .= '<li><a href="#gcmi-fb-tabs-3">' . esc_html__( 'Select provinces', 'campi-moduli-italiani' ) . '</a></li>';
		$html .= '<li><a href="#gcmi-fb-tabs-4">' . esc_html__( 'Select municipalities', 'campi-moduli-italiani' ) . '</a></li>';
		$html .= '<li><a href="#gcmi-fb-tabs-5">' . esc_html__( 'Save filter', 'campi-moduli-italiani' ) . '</a></li>';
		$html .= '</ul>';

		$html .= '<div id="gcmi-fb-tabs-1">';
		$html .= '<input type="checkbox" class="gcmi-ui-toggle" id="gcmi-fb-include-ceased" name="gcmi-fb-include-ceased">';
		$html .= '<label for="gcmi-fb-include-ceased">' . esc_html__( 'Include ceased municipalities', 'campi-moduli-italiani' ) . '</label>';
		$html .= '</div>';

		$html .= '<div id="gcmi-fb-tabs-2">';
		$html .= '</div>';

		$html .= '<div id="gcmi-fb-tabs-3">';
		$html .= '</div>';

		$html .= '<div id="gcmi-fb-tabs-4">';
		$html .= '</div>';

		$html .= '<div id="gcmi-fb-tabs-5">';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="gcmi-fb-footer"><p><i>' .
			__( 'Italian forms fields', 'campi-moduli-italiani' ) . ' - ' .
			__( 'Italian municipalities\' filter builder ', 'campi-moduli-italiani' ) .
			'</i></p></div>';
		$html .= '</div>';
		$html .= '</div>';

		echo $html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Restituisce un oggetto JSON contenente l'html per l'aggiornamento delle tab
	 * Gestisce una richieste AJAX
	 *
	 * @since      2.2.0
	 *
	 * @package    campi-moduli-italiani
	 * @subpackage campi-moduli-italiani/admin
	 */
	public static function ajax_get_tabs_html(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );

		if ( array_key_exists( 'includi', $_POST ) ) {
			$includi = sanitize_text_field( wp_unslash( $_POST['includi'] ) );
			if ( 'true' === $includi || 'false' === $includi ) {
				$includi_cessati = $includi;
			} else {
				$includi_cessati = 'false';
			}
		} else {
			$includi_cessati = 'false';
		}

		$filtri_esistenti = self::get_list_filtri();

		if ( array_key_exists( 'filtername', $_POST ) && is_string( $_POST['filtername'] ) ) {
			$filter = self::sanitize_table_name( sanitize_key( wp_unslash( $_POST['filtername'] ) ) );
		} else {
			$filter = false;
		}
		if ( is_string( $filter ) && in_array( $filter, $filtri_esistenti ) ) {
			$filter_name = $filter;
		} else {
			$filter_name = false;
		}

		if ( false !== $filter_name ) {
			if ( 0 !== strpos( $filter_name, 'tmp_', 0 ) ) {
				if ( self::has_view_cessati( $filter_name ) ) {
					$includi_cessati = 'true';
				} else {
					$includi_cessati = 'false';
				}
			}
			$reg_selected = self::get_cod_regioni_in_view( $filter_name );
			$pro_selected = self::get_cod_province_in_view( $filter_name );
			$com_selected = self::get_cod_comuni_in_view( $filter_name );
		} else {
			$reg_selected = array();
			$pro_selected = array();
			$com_selected = array();
		}

		$list_regioni  = self::get_list_regioni( $includi_cessati, $reg_selected );
		$list_province = self::get_list_province( $includi_cessati, $pro_selected );
		$list_comuni   = self::get_list_comuni( $includi_cessati, $com_selected );

		$html_returned                   = array();
		$html_returned['regioni_html']   = self::print_regioni( $list_regioni );
		$html_returned['province_html']  = self::print_province( $list_province );
		$html_returned['comuni_html']    = self::print_comuni( $list_comuni );
		$html_returned['commit_buttons'] = self::print_commit_buttons( $filter_name ? $filter_name : '' );
		$html_returned['includi']        = $includi_cessati;

		wp_send_json( $html_returned );
	}

	/**
	 * Ottiene lista dei filtri.
	 *
	 * Ottiene la lista dei filtri per il tag comune, presenti nel database.
	 *
	 * @since 2.2.0
	 * @return array<string>
	 */
	private static function get_list_filtri() {
		return gcmi_get_list_filtri();
	}

	/**
	 * Get the html of the filter's list for ajax consumer
	 */
	public static function ajax_get_filters_html(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );
		$html     = self::print_filtri();
		$response = array(
			'filters_html' => $html,
		);
		wp_send_json_success( $response );
	}

	/**
	 * Stampa la lista dei filtri presenti nel db.
	 *
	 * @since 2.2.0
	 * @return string
	 */
	private static function print_filtri() {
		$list = self::get_list_filtri();

		$html  = '<table class="wp-list-table widefat fixed striped table-view-list">';
		$html .= '<caption class="screen-reader-text">' . esc_html__( 'Table of existent filters', 'campi-moduli-italiani' ) . '</caption>';
		$html .= '<thead><tr><td colspan="3">' . esc_html__( 'Filter\'s list', 'campi-moduli-italiani' ) . '</td></tr></thead>';
		$html .= '<tbody>';
		foreach ( $list as $value ) {
			$html .= '<tr>';
			$html .= '<td><span class="gcmi-fb-filters-name">' . trim( $value, '_' ) . '</span></td>';
			$html .= '<td><div class="gcmi-fb-button-delete-wrapper"><button value="delete" class="button gcmi-fb-button gcmi-fb-button-delete-filter" name="gcmi-fb-delete-filter-' . trim( $value, '_' ) . '" id="gcmi-fb-delete-filter-' . trim( $value, '_' ) . '">';
			$html .= '<span class="dashicons dashicons-trash"></span>' . esc_html__( 'Delete', 'campi-moduli-italiani' ) . '</button></div></td>';
			$html .= '<td><div class="gcmi-fb-button-edit-wrapper"><button value="edit" class="button button-secondary gcmi-fb-button gcmi-fb-button-edit-filter" name="gcmi-fb-edit-filter-' . trim( $value, '_' ) . '" id="gcmi-fb-edit-filter-' . trim( $value, '_' ) . '">';
			$html .= '<span class="dashicons dashicons-edit-large"></span>' . esc_html__( 'Edit', 'campi-moduli-italiani' ) . '</button></div></td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '<tfoot><tr><td colspan="2"></td><td>';
		$html .= '<div class="gcmi-fb-button-new-wrapper">';
		$html .= '<button value="add new" class="button button-primary gcmi-fb-button gcmi-fb-button-addnew-filter" id="gcmi-fb-addnew-filter">';
		$html .= '<span class="dashicons dashicons-plus-alt"></span>' . esc_html__( 'Add New', 'campi-moduli-italiani' ) . '</button>';
		$html .= '</div>';
		$html .= '</td></tr></tfoot>';
		$html .= '</table>';
		return $html;
	}

	/**
	 * Ottiene la lista delle regioni presenti nel database
	 *
	 * @param string        $use_cessati "true" per usare i cessati.
	 * @param array<string> $selected Array dei codici regione selezionati.
	 *
	 * @return array<string, object{"i_cod_regione": string, "i_den_regione": string, "selected": string}>
	 */
	private static function get_list_regioni( $use_cessati = 'true', $selected = array() ) {
		global $wpdb;
		$cache_key    = 'gcmi_fb_list_regioni';
		$list_regioni = wp_cache_get( $cache_key, GCMI_CACHE_GROUP );
		if ( false === $list_regioni ) {
			$list_regioni = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT DISTINCT CONCAT ("R", `i_cod_regione`) AS `i_cod_regione`, `i_den_regione`, true AS `selected` ' .
					'FROM `%1$s` WHERE 1',
					GCMI_SVIEW_PREFIX . 'comuni_attuali'
				),
				OBJECT_K
			);
			wp_cache_set( $cache_key, $list_regioni, GCMI_CACHE_GROUP, GCMI_CACHE_EXPIRE_SECS );
		}

		// Quando viene passato true dalla chiamata AJAX, diventa una stringa.
		if ( 'true' === $use_cessati ) {
			$list_regioni['R70'] = (object) array(
				'i_cod_regione' => 'R70',
				'i_den_regione' => '_ Istria e Dalmazia',
				'selected'      => '1',
			);
		}
		uasort( $list_regioni, ( array( __CLASS__, 'cmp_regione' ) ) );
		if ( 0 < count( $selected ) ) {
			foreach ( $list_regioni as $key => $regione ) {
				if ( in_array( $key, $selected ) ) {
					$regione->selected = '1';
				} else {
					$regione->selected = '0';
				}
			}
		}
		return $list_regioni;
	}

	/**
	 * Stampa la lista delle regioni
	 *
	 * @param array<string, object{"i_cod_regione": string, "i_den_regione": string, "selected": string}> $list_regioni The array of objects returned by get_list_regioni (OBJECT_K format).
	 * @return string
	 */
	private static function print_regioni( $list_regioni ) {

		$html  = '<div class="gcmi-fb-regioni-container" id="gcmi-fb-regioni-container">';
		$html .= '<div class="gcmi-fb-checkall-regioni-container"><input type="checkbox" id="fb-gcmi-chkallreg" checked>';
		$html .= '<span class="gcmi-fb-regioni-all">' . esc_html__( 'Select all', 'campi-moduli-italiani' ) . '</span>';
		$html .= '</div>';

		foreach ( $list_regioni as $key => $regione ) {
			$html .= '<div class="gcmi-fb-regione-item">' .
				'<input type="checkbox" id="fb-gcmi-reg-' . $regione->i_cod_regione . '" ' .
				'name="' . $regione->i_cod_regione . '" ' .
				'value="' . $regione->i_cod_regione . '"';
			if ( '1' === $regione->selected ) {
				$html .= ' checked';
			}
			$html .= '><label for="' . $regione->i_cod_regione . '">' . stripslashes( $regione->i_den_regione ) . '</label>'
				. '</div>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Ottiene la lista delle province presenti nel database
	 *
	 * @param string        $use_cessati "true" per usare i cessati.
	 * @param array<string> $selected elenco delle province selezionate.
	 *
	 * @return array<string, object{"i_cod_unita_territoriale": string, "i_cod_regione": string, "i_den_unita_territoriale": string, "i_den_regione": string, "selected": string}>|false
	 */
	private static function get_list_province( $use_cessati = 'true', $selected = array() ) {
		global $wpdb;
		$cache_key       = 'gcmi_fb_list_province_a';
		$list_province_a = wp_cache_get( $cache_key, GCMI_CACHE_GROUP );
		if ( false === $list_province_a ) {
			$list_province_a = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT DISTINCT CONCAT("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale`, ' .
					'CONCAT("R", `i_cod_regione`) AS `i_cod_regione`, ' .
					'`i_den_unita_territoriale`, `i_den_regione`, true AS `selected` ' .
					'FROM `%1$s` WHERE 1 ORDER BY `i_cod_unita_territoriale`',
					GCMI_SVIEW_PREFIX . 'comuni_attuali'
				),
				OBJECT_K
			);
			wp_cache_set( $cache_key, $list_province_a, GCMI_CACHE_GROUP, GCMI_CACHE_EXPIRE_SECS );
		}

		if ( 'true' === $use_cessati ) {
			$cache_key       = 'gcmi_fb_list_province_s';
			$list_province_s = wp_cache_get( $cache_key, GCMI_CACHE_GROUP );
			if ( false === $list_province_s ) {
				$list_province_s = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT DISTINCT CONCAT( "P", `%1$s`.`i_cod_unita_territoriale`) AS `i_cod_unita_territoriale`, ' .
						'CONCAT( "R", `%2$s`.`i_cod_regione`) AS `i_cod_regione`, `%3$s`.`i_den_unita_territoriale`, ' .
						'`%4$s`.`i_den_regione`, true AS `selected` FROM ' .
						'`%5$s` LEFT JOIN `%6$s` ' .
						'ON `%7$s`.`i_sigla_automobilistica`=`%8$s`.`i_sigla_automobilistica`',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi',
						GCMI_SVIEW_PREFIX . 'comuni_attuali',
						GCMI_SVIEW_PREFIX . 'comuni_attuali',
						GCMI_SVIEW_PREFIX . 'comuni_attuali',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi',
						GCMI_SVIEW_PREFIX . 'comuni_attuali',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi',
						GCMI_SVIEW_PREFIX . 'comuni_attuali'
					),
					OBJECT_K
				);
				wp_cache_set( $cache_key, $list_province_s, GCMI_CACHE_GROUP, GCMI_CACHE_EXPIRE_SECS );

				if ( array_key_exists( 'P040', $list_province_s ) ) {
					$list_province_s['P040']->i_cod_regione            = 'R08';
					$list_province_s['P040']->i_den_regione            = 'Emilia-Romagna';
					$list_province_s['P040']->i_den_unita_territoriale = 'Forlì-Cesena';
					$list_province_s['P040']->selected                 = '1';
				}

				if ( array_key_exists( 'P041', $list_province_s ) ) {
					$list_province_s['P041']->i_cod_regione            = 'R11';
					$list_province_s['P041']->i_den_regione            = 'Marche';
					$list_province_s['P041']->i_den_unita_territoriale = 'Pesaro e Urbino';
					$list_province_s['P041']->selected                 = '1';
				}

				if ( array_key_exists( 'P701', $list_province_s ) ) {
					$list_province_s['P701']->i_cod_regione            = 'R70';
					$list_province_s['P701']->i_den_regione            = '_ Istria e Dalmazia';
					$list_province_s['P701']->i_den_unita_territoriale = 'Fiume';
					$list_province_s['P701']->selected                 = '1';
				}
				if ( array_key_exists( 'P702', $list_province_s ) ) {
					$list_province_s['P702']->i_cod_regione            = 'R70';
					$list_province_s['P702']->i_den_regione            = '_ Istria e Dalmazia';
					$list_province_s['P702']->i_den_unita_territoriale = 'Pola';
					$list_province_s['P702']->selected                 = '1';
				}
				if ( array_key_exists( 'P703', $list_province_s ) ) {
					$list_province_s['P703']->i_cod_regione            = 'R70';
					$list_province_s['P703']->i_den_regione            = '_ Istria e Dalmazia';
					$list_province_s['P703']->i_den_unita_territoriale = 'Zara';
					$list_province_s['P703']->selected                 = '1';
				}
			}
			$list_province = array_unique( array_merge( $list_province_a, $list_province_s ), SORT_REGULAR );
		} else {
			$list_province = $list_province_a;
		}

		/**
		 * Ordino per regione e per ordine alfabetico nella regione.
		 */

		// Vengono ordinate per regione.
		uasort( $list_province, ( array( __CLASS__, 'cmp_regione' ) ) );

		$array_temporaneo = array();
		$array_finale     = array();
		$regione_corrente = '';

		while ( ! empty( $list_province ) ) {
			$first_element = array_shift( $list_province );

			// inizializzazione.
			if ( '' === $regione_corrente ) {
				$regione_corrente = $first_element->i_den_regione;
			}

			if ( $first_element->i_den_regione === $regione_corrente ) {
				$index                      = strval( $first_element->i_cod_unita_territoriale );
				$array_temporaneo[ $index ] = $first_element;
			} else {
				uasort( $array_temporaneo, ( array( __CLASS__, 'cmp_provincia' ) ) );

				$array_finale = array_merge( $array_finale, $array_temporaneo );

				$regione_corrente = $first_element->i_den_regione;
				$array_temporaneo = array(
					strval( $first_element->i_cod_unita_territoriale ) => $first_element,
				);
			}
		}

		$array_finale = array_merge( $array_finale, $array_temporaneo );

		if ( 0 < count( $selected ) ) {
			foreach ( $array_finale as $key => $provincia ) {
				if ( in_array( $key, $selected ) ) {
					$provincia->selected = '1';
				} else {
					$provincia->selected = '0';
				}
			}
		}
		if ( gcmi_is_list_pr_array( $array_finale ) ) {
			return $array_finale;
		} else {
			return false;
		}
	}

	/**
	 * Stampa la lista delle province
	 *
	 * @param false|array<string, object{"i_cod_unita_territoriale": string, "i_cod_regione": string, "i_den_unita_territoriale": string, "i_den_regione": string, "selected": string}> $list_province Array di oggetti restituito da get_list_province (OBJECT_K).
	 * @return string
	 */
	private static function print_province( $list_province ) {
		$regione_corrente = '';
		$html             = '<div class="gcmi-fb-province-container">';
		if ( false === $list_province ) {
			return '';
		}
		while ( ! empty( $list_province ) ) {
			$first_element = array_shift( $list_province );
			// inizializzazione.
			if ( '' === $regione_corrente ) {
				$regione_corrente = $first_element->i_den_regione;
				$html            .= '<div class="gcmi-fb-regione-blocco" id="gcmi-fb-regione-blocco-' . $first_element->i_cod_regione . '">';
				$html            .= '<div class="gcmi-fb-checkall-container"><input type="checkbox" id="fb-gcmi-chkallpr-' . $first_element->i_cod_regione . '" checked>';
				$html            .= '<span class="gcmi-fb-regione-name">' . stripslashes( $regione_corrente ) . '</span>';
				$html            .= '</div>';
			}
			if ( $first_element->i_den_regione !== $regione_corrente ) {
				$regione_corrente = $first_element->i_den_regione;
				$html            .= '</div>';
				$html            .= '<div class="gcmi-fb-regione-blocco" id="gcmi-fb-regione-blocco-' . $first_element->i_cod_regione . '">';
				$html            .= '<div class="gcmi-fb-checkall-container"><input type="checkbox" id="fb-gcmi-chkallpr-' . $first_element->i_cod_regione . '" checked>';
				$html            .= '<span class="gcmi-fb-regione-name">' . stripslashes( $regione_corrente ) . '</span>';
				$html            .= '</div>';
			}

			$html .= '<div class="gcmi-fb-provincia-item cod-reg-' . $first_element->i_cod_regione . '">' .
				'<input type="checkbox" id="fb-gcmi-prov-' . $first_element->i_cod_unita_territoriale . '" ' .
				'name="' . $first_element->i_cod_unita_territoriale . '" ' .
				'value="' . $first_element->i_cod_unita_territoriale . '"';
			if ( '1' === $first_element->selected ) {
				$html .= ' checked';
			}
			$html .= '><label for="' . $first_element->i_cod_unita_territoriale . '">' .
				stripslashes( $first_element->i_den_unita_territoriale ) .
				' (' . $first_element->i_cod_unita_territoriale . ')' .
				' </label>' .
				'</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Ottiene la lista dei comuni presenti nel database
	 *
	 * @param string        $use_cessati "true" per usare i cessati.
	 * @param array<string> $selected An array of codici comune.
	 *
	 * @return array<string, object{"i_cod_comune": string, "i_cod_unita_territoriale": string, "i_denominazione_full": string, "selected": string}>
	 */
	private static function get_list_comuni( $use_cessati = 'true', $selected = array() ) {
		global $wpdb;
		if ( 'true' === $use_cessati ) {
			$cache_key     = 'gcmi_fb_list_comuni_s';
			$list_comuni_s = wp_cache_get( $cache_key, GCMI_CACHE_GROUP );
			if ( false === $list_comuni_s ) {
				$list_comuni_s = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT `i_cod_comune`, `i_cod_unita_territoriale`, `i_denominazione_full`, true AS `selected` FROM (' .
						'SELECT CONCAT("C", `i_cod_comune`) AS `i_cod_comune`, CONCAT("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale`, ' .
						'`i_denominazione_full`, true AS `selected` ' .
						'FROM `%1$s` ' .
						'UNION ALL ' .
						'SELECT CONCAT("C", `i_cod_comune`) AS `i_cod_comune`, CONCAT("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale`, ' .
						'CONCAT(`i_denominazione_full`, \'%2$s\'), true AS `selected` ' .
						'FROM `%3$s` ' .
						') AS union_comuni ORDER BY `i_denominazione_full`;',
						GCMI_SVIEW_PREFIX . 'comuni_attuali',
						' - (' . esc_html__( 'sopp.', 'campi-moduli-italiani' ) . ')',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi'
					),
					OBJECT_K
				);
				wp_cache_set( $cache_key, $list_comuni_s, GCMI_CACHE_GROUP, GCMI_CACHE_EXPIRE_SECS );
			}
			$array_finale = $list_comuni_s;
		} else {
			$cache_key     = 'gcmi_fb_list_comuni_a';
			$list_comuni_a = wp_cache_get( $cache_key, GCMI_CACHE_GROUP );
			if ( false === $list_comuni_a ) {
				$list_comuni_a = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT CONCAT("C", `i_cod_comune`) AS `i_cod_comune`, CONCAT("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale`, ' .
						'`i_denominazione_full`, true AS `selected` ' .
						'FROM `%1$s` WHERE 1 ORDER BY `i_denominazione_full`',
						GCMI_SVIEW_PREFIX . 'comuni_attuali'
					),
					OBJECT_K
				);
				wp_cache_set( $cache_key, $list_comuni_a, GCMI_CACHE_GROUP, GCMI_CACHE_EXPIRE_SECS );
			}
			$array_finale = $list_comuni_a;
		}
		if ( 0 < count( $selected ) ) {
			foreach ( $array_finale as $key => $comune ) {
				if ( in_array( $key, $selected ) ) {
					$comune->selected = '1';
				} else {
					$comune->selected = '0';
				}
			}
		}
		return $array_finale;
	}

	/**
	 * Stampa la lista dei comuni
	 *
	 * @param array<string, object{"i_cod_comune": string, "i_cod_unita_territoriale": string, "i_denominazione_full": string, "selected": string}> $list_comuni Lista comuni restituita da get_list_comuni (OBJECT_K).
	 * @return string
	 */
	private static function print_comuni( $list_comuni ) {
		$lettera_corrente = '';
		$html             = '<div class="gcmi-fb-comuni-container">';

		while ( ! empty( $list_comuni ) ) {
			$first_element = array_shift( $list_comuni );
			// inizializzazione.
			if ( '' === $lettera_corrente ) {
				$lettera_corrente = strtoupper( mb_substr( $first_element->i_denominazione_full, 0, 1 ) );
				$html            .= '<div class="gcmi-fb-lettera-blocco" id="gcmi-fb-lettera-blocco-' . stripslashes( $lettera_corrente ) . '">';
				$html            .= '<div class="gcmi-fb-checkall-container"><span class="gcmi-fb-lettera-comune">' . $lettera_corrente . '</span>';
				$html            .= '<input type="checkbox" id="fb-gcmi-chkallcom-' . stripslashes( $lettera_corrente ) . '" checked></div>';
			}
			if ( strtoupper( mb_substr( $first_element->i_denominazione_full, 0, 1 ) ) !== $lettera_corrente ) {
				$lettera_corrente = strtoupper( mb_substr( $first_element->i_denominazione_full, 0, 1 ) );
				$html            .= '</div>';
				$html            .= '<div class="gcmi-fb-lettera-blocco" id="gcmi-fb-lettera-blocco-' . stripslashes( $lettera_corrente ) . '">';
				$html            .= '<div class="gcmi-fb-checkall-container"><span class="gcmi-fb-lettera-comune">' . stripslashes( $lettera_corrente ) . '</span>';
				$html            .= '<input type="checkbox" id="fb-gcmi-chkallcom-' . stripslashes( $lettera_corrente ) . '" checked></div>';
			}

			$html .= '<div class="gcmi-fb-comune-item" name="gcmi-com-cod-pro-' . $first_element->i_cod_unita_territoriale . '">' .
				'<input type="checkbox" id="fb-gcmi-com-' . $first_element->i_cod_comune . '" ' .
				'name="' . $first_element->i_cod_comune . '" ' .
				'value="' . $first_element->i_cod_comune . '"';
			if ( '1' === $first_element->selected ) {
				$html .= ' checked';
			}
			$html .= '><label for="' . $first_element->i_cod_comune . '">' .
				stripslashes( $first_element->i_denominazione_full ) .
				'</label>' .
				'</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Stampa la sezione con i pulsanti per salvare il nuovo filtro
	 *
	 * @param string $filter_name The name of the filter.
	 * @return string
	 */
	private static function print_commit_buttons( $filter_name = '' ) {
		if ( '' !== $filter_name ) {
			$clean = self::sanitize_table_name( $filter_name );
		}
		$html = '<div class="gcmi-fb-commit-buttons-wrapper">';

		$html .= '<div class="gcmi-fb-table-name-wrapper">';
		$html .= '<label for="fb_gcmi_filter_name">' . esc_html__( 'Filter name:', 'campi-moduli-italiani' ) . '</label>';
		$html .= '<input type="text" id="fb_gcmi_filter_name" maxlength="20" value="';
		$html .= isset( $clean ) ? $clean . '">' : '">';
		$html .= '</div>';

		$html .= '<div class="gcmi-fb-button-save-wrapper">';
		$html .= '<button class="button button-primary gcmi-fb-button gcmi-fb-button-save" id="gcmi-fb-button-save" value="save">';
		$html .= '<span class="dashicons dashicons-saved"></span>' . esc_html__( 'Save', 'campi-moduli-italiani' ) . '</button>';
		$html .= '</div>';

		$html .= '<div class="gcmi-fb-button-reset-wrapper">';
		$html .= '<button class="button button-secondary gcmi-fb-button gcmi-fb-button-cancel" id="gcmi-fb-button-cancel" value="cancel">';
		$html .= '<span class="dashicons dashicons-exit"></span>' . esc_html__( 'Cancel', 'campi-moduli-italiani' ) . '</button>';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '<div class="gcmi-fb-note"><p>' . esc_html__( 'Please use only lowercase non accented letters, numbers, and single underscores in the middle. Limit to 20 characters.', 'campi-moduli-italiani' ) . '</p></div>';

		return $html;
	}

	/**
	 * Crea le WHERE clause per le view
	 *
	 * @param array<string> $list_comuni La lista dei comuni selezionati.
	 * @param bool          $cessati Se crea la WHERE clause per i comuni soppressi.
	 * @return string
	 */
	private static function create_filter_sql( $list_comuni, $cessati = false ) {
		global $wpdb;

		// Se l'array dei comuni è vuoto, inserisci tutti i comuni nella view.
		if ( 0 === count( $list_comuni ) ) {
			return 'WHERE 1';
		}

		// Rimuovo la "C" dai valori e gli aggiungo gli apici.
		$cod_comuni = array_map(
			function ( $codice ) {
				return "'" . substr( $codice, 1 ) . "'";
			},
			$list_comuni
		);

		$in_string = implode( ',', $cod_comuni );

		$found_codes = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'SELECT `i_cod_comune` FROM %1$s WHERE `i_cod_comune` IN ( ' . $in_string . ' )',
				GCMI_SVIEW_PREFIX . ( $cessati ? 'comuni_soppressi' : 'comuni_attuali' )
			)
		);
		if ( 0 < count( $found_codes ) ) {
			return 'WHERE `i_cod_comune` IN (' . implode( ',', $found_codes ) . ')';
		} else {
			return 'WHERE FALSE'; // una view senza nessun record.
		}
	}

	/**
	 * Crea un filtro del database
	 *
	 * La funzione è utilizzata per la creazione di un filtro con singolo invio di codici
	 * Funzione richiamata da una chiamata AJAX.
	 *
	 * @return void
	 */
	public static function ajax_create_filter(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );
		$error = self::check_filter_creation_fields( $_POST );

		if ( $error->has_errors() ) {
			wp_send_json_error( $error, 422 );
		}
		$sanitized_name = self::sanitize_table_name( sanitize_key( wp_unslash( $_POST['filtername'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$codici         = $_POST['codici']; // phpcs:ignore
		if ( false !== $sanitized_name ) {
			self::save_filter( $sanitized_name, $codici );
		}
	}

	/**
	 * Crea un filtro del database
	 *
	 * La funzione è utilizzata per la creazione di un filtro con multipli invii di codici
	 * Funzione richiamata da una chiamata AJAX.
	 *
	 * @return void
	 */
	public static function ajax_create_filters_multi(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );
		$error = self::check_filter_creation_fields_multi( $_POST );
		if ( $error->has_errors() ) {
			wp_send_json_error( $error, 422 );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$total_slices = gcmi_safe_intval( sanitize_text_field( wp_unslash( $_POST['total'] ) ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$total_codici = gcmi_safe_intval( sanitize_text_field( wp_unslash( $_POST['count'] ) ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$sanitized_name = self::sanitize_table_name( sanitize_key( wp_unslash( $_POST['filtername'] ) ) );
		$codici         = array();

		for ( $i = 0; $i < $total_slices; $i++ ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$option_name  = 'gcmi-fb-com-' . $sanitized_name . '-' . strval( $i + 1 ) . '_' . sanitize_text_field( wp_unslash( $_POST['total'] ) );
			$option_value = get_option( $option_name, false );
			if ( false === $option_value ) {
				$error->add( '-12', esc_html__( 'Request to store the partial filter was refused', 'campi-moduli-italiani' ) );
				wp_send_json_error( $error, 422 );
			}
			if ( ! is_array( $option_value ) ) {
				$error->add( '-13', esc_html__( 'Request to store the partial filter was refused', 'campi-moduli-italiani' ) );
				wp_send_json_error( $error, 422 );
			}
			if ( is_array( $option_value['codici'] ) ) {
				$codici = array_merge( $codici, $option_value['codici'] );
			}
			$delete_array[] = $option_name;
		}
		if ( count( $codici ) !== $total_codici ) {
			$error->add( '-14', esc_html__( 'Retrieved number of codes doesn\'t match expected', 'campi-moduli-italiani' ) );
			wp_send_json_error( $error, 422 );
		}

		if ( ( false !== $sanitized_name ) && ( isset( $delete_array ) ) ) {
			foreach ( $delete_array as $delete_option ) {
				$delete = delete_option( $delete_option );
				if ( false === $delete ) {
					$error->add( '-15', esc_html__( 'Error in deleting option', 'campi-moduli-italiani' ) );
					wp_send_json_error( $error, 422 );
				}
			}
			self::save_filter( $sanitized_name, $codici );
		}
	}

	/**
	 * Salva il filtro nel database
	 *
	 * Utilizzata dalle due funzioni: ajax_create_filters e ajax_create_filters_multi.
	 *
	 * @param string        $sanitized_name Nome del filtro.
	 * @param array<string> $codici array con i codici dei comuni.
	 * @return void
	 */
	private static function save_filter( $sanitized_name, $codici ) {
		$view_result = self::create_view( $sanitized_name, $codici );

		if ( false === $view_result ) {
			$error = new WP_Error();
			$error->add( '-8', esc_html__( 'The creation of the filter failed.', 'campi-moduli-italiani' ) );
			wp_send_json_error( $error, 422 );
		}

		$input_t  = count( $codici ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$count_1  = self::count_view_entries( $sanitized_name, false );
		$count_2  = self::count_view_entries( $sanitized_name, true );
		$output_t = $count_1 + $count_2;

		$response = array(
			'num_in'  => $input_t,
			'attuali' => $count_1,
			'cessati' => $count_2,
			'num_out' => $output_t,
		);
		wp_send_json_success( $response, 200 );
	}

	/**
	 * Controlla che i valori passati per la creazione di un filtro multi siano corretti.
	 *
	 * @param array<mixed> $posted The $_POST array.
	 * @return WP_Error
	 */
	private static function check_filter_creation_fields_multi( $posted ) {
		$error        = self::check_filter_creation_common_fields( $posted );
		$error_string = esc_html__( 'Received an incomplete request to create a filter.', 'campi-moduli-italiani' );
		if (
			! is_array( $posted ) ||
			! array_key_exists( 'total', $posted ) ||
			! array_key_exists( 'count', $posted )
		) {
			$error->add( '-1', $error_string );
			return $error; // necessario perché in PHP7 l'assegnazione successiva, genera un error.
		}

		if ( ! is_numeric( $posted['total'] ) ||
			0 > gcmi_safe_intval( $posted['total'] ) ||
			! is_numeric( $posted['count'] ) ||
			0 > gcmi_safe_intval( $posted['count'] )
		) {
			$error->add( '-1', $error_string );
		}
		return $error;
	}

	/**
	 * Salva un'opzione nel database, contenente il pezzo dell'array dei codici
	 *
	 * @return void
	 */
	public static function ajax_save_filters_slice(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );
		$error = self::check_save_filter_slices_fields( $_POST );

		if ( $error->has_errors() ) {
			$error->add( '-10', esc_html__( 'Request to store the partial filter not processed', 'campi-moduli-italiani' ) );
			wp_send_json_error( $error, 422 );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$response = sanitize_text_field( wp_unslash( $_POST['slice'] ) ) . '_' . sanitize_text_field( wp_unslash( $_POST['total'] ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$filtername  = self::sanitize_table_name( sanitize_key( wp_unslash( $_POST['filtername'] ) ) );
		$option_name = 'gcmi-fb-com-' . $filtername . '-' . $response;

		$option_value = array(
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			'includi'    => strval( sanitize_text_field( wp_unslash( $_POST['includi'] ) ) ),
			'filtername' => $filtername,
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			'codici'     => array_map( 'sanitize_text_field', wp_unslash( $_POST['codici'] ) ),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			'total'      => gcmi_safe_intval( sanitize_text_field( wp_unslash( $_POST['total'] ) ) ),
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			'slice'      => gcmi_safe_intval( sanitize_text_field( wp_unslash( $_POST['slice'] ) ) ),
		);

		$option  = update_option( $option_name, $option_value, false );
		$ret_val = get_option( $option_name );
		if ( true === $option && $option_value === $ret_val ) {
			wp_send_json_success( $response, 200 );
		} else {
			$error->add(
				'-11',
				sprintf(
					// translators: %s is the name of the temporary option.
					esc_html__( 'Request to store the partial filter was refused. Option name: %s', 'campi-moduli-italiani' ),
					$option_name
				)
			);
			wp_send_json_error( $error, 422 );
		}
	}

	/**
	 * Controlla che i valori passati nelle richieste parziali
	 * per la creazione di un filtro sliced siano corretti.
	 *
	 * @param array<mixed> $posted The $_POST array.
	 * @return WP_Error
	 */
	private static function check_save_filter_slices_fields( $posted ) {
		$error = self::check_filter_creation_fields( $posted );

		if (
			! array_key_exists( 'total', $posted ) ||
			! array_key_exists( 'slice', $posted )
		) {
			$error->add( '-9', esc_html__( 'Received an incomplete request to create a filter.', 'campi-moduli-italiani' ) );
			return $error; // necessario perché in PHP7 l'assegnazione successiva, genera un error.
		}

		if (
			( ! is_numeric( $posted['total'] ) ) ||
			( ! is_numeric( $posted['slice'] ) ) ||
			( gcmi_safe_intval( $posted['slice'] ) > gcmi_safe_intval( $posted['total'] ) ) ||
			( 0 === gcmi_safe_intval( $posted['total'] ) ) ||
			( 0 === gcmi_safe_intval( $posted['slice'] ) )
		) {
			$error->add( '-10', esc_html__( 'Received an invalid slice of codes.', 'campi-moduli-italiani' ) );
		}
		return $error;
	}

	/**
	 * Controlla che i valori passati per la creazione di un filtro siano corretti.
	 *
	 * @param array<mixed> $posted The $_POST array.
	 * @return WP_Error
	 */
	private static function check_filter_creation_fields( $posted ) {
		$error = self::check_filter_creation_common_fields( $posted );
		$error->merge_from( self::check_filter_creation_codici( $posted ) );
		return $error;
	}

	/**
	 * Controlla che i valori comuni passati per la creazione di un filtro siano corretti.
	 *
	 * @param array<mixed> $posted The $_POST array.
	 * @return WP_Error
	 */
	private static function check_filter_creation_common_fields( $posted ) {
		$error = new WP_Error();

		if (
			! is_array( $posted ) ||
			! array_key_exists( 'filtername', $posted ) ||
			! array_key_exists( 'includi', $posted )
		) {
			$error->add( '-1', esc_html__( 'Received an incomplete request to create a filter.', 'campi-moduli-italiani' ) );
			return $error; // necessario perché in PHP7 l'assegnazione successiva, genera un error.
		}

		$filter_name = gcmi_safe_strval( $posted['filtername'] );
		$use_cessati = gcmi_safe_strval( $posted['includi'] );

		$sanitized_name = self::sanitize_table_name( $filter_name );
		if ( false === $sanitized_name ) {
			$error->add( '-2', esc_html__( 'The filter name is not valid. Please use only lowercase alphanumeric characters and single underscores.', 'campi-moduli-italiani' ) );
		} elseif ( 20 < strlen( $sanitized_name ) ) {
			$error->add( '-3', esc_html__( 'No more than 20 characters are allowed for the filter\'s name.', 'campi-moduli-italiani' ) );
		}

		if ( 'true' !== $use_cessati && 'false' !== $use_cessati ) {
			$error->add( '-4', esc_html__( 'Unexpected value for parameter use_cessati.', 'campi-moduli-italiani' ) );
		}
		return $error;
	}

	/**
	 * Controlla che i codici passati per la crazione di un filtro siano corretti.
	 *
	 * @param array<mixed> $posted The $_POST array.
	 * @return WP_Error
	 */
	private static function check_filter_creation_codici( $posted ) {
		$error = new WP_Error();
		if (
			! is_array( $posted ) ||
			! array_key_exists( 'codici', $posted ) ||
			! is_array( $posted['codici'] )
		) {
			$error->add( '-1', esc_html__( 'Received an incomplete request to create a filter.', 'campi-moduli-italiani' ) );
			return $error; // necessario perché in PHP7 l'assegnazione successiva, genera un error.
		}
		$codici = $posted['codici'];
		if ( count( $codici ) === 0 ) {
			$error->add( '-5', esc_html__( 'The array of the codes of the municipalities is empty.', 'campi-moduli-italiani' ) );
		}

		if ( false === gcmi_is_one_dimensional_string_array( $codici ) ) {
			$error->add( '-6', esc_html__( 'The array of the codes of the municipalities is invalid.', 'campi-moduli-italiani' ) );
		}
		$check_codici = true;
		foreach ( $codici as $val ) {
			if ( ! is_string( $val ) ||
				strlen( $val ) !== 7 ||
				'C' !== mb_substr( $val, 0, 1 ) ||
				false === ctype_digit( mb_substr( $val, 1 ) )
			) {
				$check_codici = false;
			}
		}
		if ( false === $check_codici ) {
			$error->add( '-7', esc_html__( 'The array with the codes of the municipalities contains incorrect values.', 'campi-moduli-italiani' ) );
		}
		return $error;
	}

	/**
	 * Verifica se un filtro utilizza i comuni cessati
	 *
	 * @global type $wpdb
	 * @param string $filter_name Il nome del filtro.
	 * @return bool
	 */
	private static function has_view_cessati( $filter_name ) {
		global $wpdb;
		$full_view_name = GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name;
		if ( false === self::check_view_exists( $full_view_name ) ) {
			return false;
		} else {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(`id`) AS \'NUM\' FROM `%1$s` WHERE 1',
					$full_view_name
				)
			);
			if ( gcmi_safe_intval( $count ) > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Restituisce un array dei codici regione (preceduti da R) utilizzati nel filtro
	 *
	 * @global type $wpdb
	 * @param string $filter_name Il nome del filtro.
	 * @return array<string>
	 */
	private static function get_cod_regioni_in_view( $filter_name ) {
		global $wpdb;

		$lista_regioni_attuali = array_map(
			'strval',
			$wpdb->get_col(
				$wpdb->prepare(
					'SELECT DISTINCT CONCAT ("R", `i_cod_regione`) AS `i_cod_regione` FROM `%1$s`',
					GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $filter_name
				)
			)
		);

		$lista_regioni_cessati = array();
		$full_view_name        = GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name;
		if ( self::check_view_exists( $full_view_name ) ) {
			$lista_regioni_cessati = array_filter(
				array_map(
					'strval',
					$wpdb->get_col(
						$wpdb->prepare(
							'SELECT DISTINCT CONCAT ("R", `i_cod_regione`) AS `i_cod_regione` FROM `%1$s` LEFT JOIN `%2$s` ON ' .
							'`%3$s`.`i_cod_unita_territoriale` = `%4$s`.`i_cod_unita_territoriale` WHERE 1',
							GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name,
							GCMI_SVIEW_PREFIX . 'comuni_attuali',
							GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name,
							GCMI_SVIEW_PREFIX . 'comuni_attuali'
						)
					)
				)
			);

			$province_cessati = array_filter(
				array_map(
					'strval',
					$wpdb->get_col(
						$wpdb->prepare(
							'SELECT DISTINCT `i_cod_unita_territoriale` FROM `%1$s`',
							GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name
						)
					)
				)
			);
			$filtered         = array_filter(
				$province_cessati,
				function ( $pro ) {
					return '7' === $pro[0];
				}
			);
			if ( 0 < count( $filtered ) ) {
				$lista_regioni_cessati[] = 'R70';
			}
		}
		if ( ! empty( $lista_regioni_cessati ) ) {
			$lista_regioni = array_unique(
				array_merge( $lista_regioni_attuali, $lista_regioni_cessati ),
				SORT_STRING
			);
			return $lista_regioni;
		}
		return $lista_regioni_attuali;
	}

	/**
	 * Restituisce un array dei codici provincia (preceduti da P) utilizzati nel filtro
	 *
	 * @global type $wpdb
	 * @param string $filter_name Il nome del filtro.
	 * @return array<string>
	 */
	private static function get_cod_province_in_view( $filter_name ) {
		global $wpdb;
		$lista_province_attuali = array_map(
			'strval',
			$wpdb->get_col(
				$wpdb->prepare(
					'SELECT DISTINCT CONCAT ("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale` FROM `%1$s`',
					GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $filter_name
				)
			)
		);
		$lista_province_cessati = array();
		$full_view_name         = GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name;
		if ( self::check_view_exists( $full_view_name ) ) {
			$lista_province_cessati = array_map(
				'strval',
				$wpdb->get_col(
					$wpdb->prepare(
						'SELECT DISTINCT CONCAT ("P", `i_cod_unita_territoriale`) AS `i_cod_unita_territoriale` FROM `%1$s`',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name
					)
				)
			);
		}
		if ( ! empty( $lista_province_cessati ) ) {
			$lista_province = array_unique(
				array_merge( $lista_province_attuali, $lista_province_cessati ),
				SORT_STRING
			);
			return $lista_province;
		}
		return $lista_province_attuali;
	}

	/**
	 * Restituisce un array dei codici comune (preceduti da C) utilizzati nel filtro
	 *
	 * @global type $wpdb
	 * @param string $filter_name Il nome del filtro.
	 * @return array<string>
	 */
	private static function get_cod_comuni_in_view( $filter_name ) {
		global $wpdb;
		$lista_comuni_attuali = array();
		$full_view_name       = GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $filter_name;
		if ( self::check_view_exists( $full_view_name ) ) {
			$lista_comuni_attuali = array_map(
				'strval',
				$wpdb->get_col(
					$wpdb->prepare(
						'SELECT DISTINCT CONCAT ("C", `i_cod_comune`) AS `i_cod_comune` FROM `%1$s`',
						GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $filter_name
					)
				)
			);
		}

		$lista_comuni_cessati = array();
		$full_view_name       = GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name;
		if ( self::check_view_exists( $full_view_name ) ) {
			$lista_comuni_cessati = array_map(
				'strval',
				$wpdb->get_col(
					$wpdb->prepare(
						'SELECT DISTINCT CONCAT ("C", `i_cod_comune`) AS `i_cod_comune` FROM `%1$s`',
						GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $filter_name
					)
				)
			);
		}

		if ( ! empty( $lista_comuni_cessati ) ) {
			$lista_comuni = array_unique(
				array_merge( $lista_comuni_attuali, $lista_comuni_cessati ),
				SORT_STRING
			);
			return $lista_comuni;
		}
		return $lista_comuni_attuali;
	}

	/**
	 * Crea una view
	 *
	 * @param string        $viewname The name of the view.
	 * @param array<string> $codici The array of codes.
	 *
	 * @global type $wpdb
	 * @return bool
	 */
	private static function create_view( $viewname, $codici ) {
		global $wpdb;

		$where_clause_attuali = self::create_filter_sql( $codici, false );
		$view_attuali         = $wpdb->query(
			$wpdb->prepare(
				'CREATE OR REPLACE VIEW %1$s AS SELECT * FROM %2$s',
				GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $viewname,
				GCMI_SVIEW_PREFIX . 'comuni_attuali ' . $where_clause_attuali
			)
		);
		$where_clause_cessati = self::create_filter_sql( $codici, true );
		$view_soppressi       = $wpdb->query(
			$wpdb->prepare(
				'CREATE OR REPLACE VIEW %1$s AS SELECT * FROM %2$s',
				GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $viewname,
				GCMI_SVIEW_PREFIX . 'comuni_soppressi ' . $where_clause_cessati
			)
		);
		return ( $view_attuali || $view_soppressi );
	}

	/**
	 * Conta il numero di record presenti in una view
	 *
	 * @param string $viewname Il nome della view.
	 * @param bool   $cessati Se è relativa ai comuni soppressi.
	 * @return int
	 */
	private static function count_view_entries( $viewname, $cessati = false ) {
		global $wpdb;
		$full_view_name = GCMI_SVIEW_PREFIX . ( $cessati ? 'comuni_soppressi_' : 'comuni_attuali_' ) . $viewname;
		if ( self::check_view_exists( $full_view_name ) ) {
			$wpdb->get_col(
				$wpdb->prepare(
					'SELECT `i_cod_comune` FROM %1$s WHERE 1',
					GCMI_SVIEW_PREFIX . ( $cessati ? 'comuni_soppressi_' : 'comuni_attuali_' ) . $viewname
				)
			);
			return $wpdb->num_rows;
		} else {
			return 0;
		}
	}

	/**
	 * Return true if a view exists in the database
	 *
	 * @global type $wpdb
	 * @param string $full_view_name The full name of the view.
	 * @return bool
	 */
	private static function check_view_exists( $full_view_name ) {
		global $wpdb;
		if ( 0 < count(
			$wpdb->get_results(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $full_view_name ),
				ARRAY_A
			)
		) ) {
			return true;
		}
		return false;
	}

	/**
	 * Elimina un filtro da comuni_attuali e comuni_soppressi
	 *
	 * @param string $viewname_raw The name of the view to delete.
	 * @global type $wpdb
	 * @return bool
	 */
	private static function delete_filter( $viewname_raw ) {
		global $wpdb;
		$viewname = self::sanitize_table_name( $viewname_raw );
		if ( false === $viewname ) {
			return $viewname;
		}
		if ( $viewname !== $viewname_raw ) {
			return false;
		}
		$dropped = $wpdb->query(
			$wpdb->prepare(
				'DROP VIEW IF EXISTS %1$s, %2$s',
				GCMI_SVIEW_PREFIX . 'comuni_attuali_' . $viewname,
				GCMI_SVIEW_PREFIX . 'comuni_soppressi_' . $viewname
			)
		);
		return $dropped;
	}

	/**
	 * Elimina una vista e restituisce la risposta JSON
	 */
	public static function ajax_delete_filter(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );

		if ( ! isset( $_POST['filtername'] ) ) {
			/* translators: %s: The name of the filter for which elimination failed */
			$error_string = esc_html__( 'No filter name sent.', 'campi-moduli-italiani' );
			$status_code  = 422;
			$error        = new WP_Error( '-9', $error_string );
			wp_send_json_error(
				$error,
				$status_code
			);
		}

		$filter_name = sanitize_key( wp_unslash( $_POST['filtername'] ) );

		$delete = self::delete_filter( $filter_name );
		if ( true === $delete ) {
			$response = array(
				'deleted_view' => $filter_name,
			);
			wp_send_json_success(
				$response,
				200
			);
		} else {
			/* translators: %s: The name of the filter for which elimination failed */
			$error_string = sprintf( esc_html__( 'Impossible to eliminate the filter %s.', 'campi-moduli-italiani' ), $filter_name );
			$status_code  = 422;
			$error        = new WP_Error( '-9', $error_string );
			wp_send_json_error(
				$error,
				$status_code
			);
		}
	}

	/**
	 * Sanitize a table name string.
	 *
	 * Used to make sure that a table name value meets MySQL expectations.
	 *
	 * Applies the following formatting to a string:
	 * - Trim whitespace
	 * - No accents
	 * - No special characters
	 * - No hyphens
	 * - No double underscores
	 * - No trailing underscores
	 *
	 * @credits https://plugins.trac.wordpress.org/browser/easy-digital-downloads/trunk/includes/database/engine/class-base.php
	 *
	 * @param string $name The name of the database table.
	 *
	 * @return string|false Sanitized database table name
	 */
	protected static function sanitize_table_name( $name = '' ) {
		// Bail if empty or not a string.
		if ( empty( $name ) || ! is_string( $name ) ) {
			return false;
		}
		$unspace = trim( $name ); // Trim spaces off the ends.
		$accents = remove_accents( $unspace ); // Only non-accented table names (avoid truncation).
		$lower   = sanitize_key( $accents ); // Only lowercase characters, hyphens, and dashes (avoid index corruption).
		$under   = str_replace( '-', '_', $lower ); // Replace hyphens with single underscores.
		$single  = str_replace( '__', '_', $under ); // Single underscores only.
		$clean   = trim( $single, '_' ); // Remove trailing underscores.
		// Bail if table name was garbaged.
		if ( empty( $clean ) ) {
			return false;
		}

		// Return the cleaned table name.
		return $clean;
	}

	/**
	 * Function to sort the array of objects regione
	 *
	 * @param object{"i_den_regione": string } $a Un oggetto di uno degli array dei tipi restituiti da get_list_regioni o get_list_province.
	 * @param object{"i_den_regione": string } $b Un oggetto di uno degli array dei tipi restituiti da get_list_regioni o get_list_province.
	 * @return integer
	 */
	private static function cmp_regione( $a, $b ) {
		return strcmp( $a->i_den_regione, $b->i_den_regione );
	}

	/**
	 * Function to sort the array of objects province
	 *
	 * @param object{"i_den_unita_territoriale": string } $a Un oggetto di uno degli array del tipo restituito da get_list_province.
	 * @param object{"i_den_unita_territoriale": string } $b Un oggetto di uno degli array del tipo restituito da get_list_province.
	 * @return integer
	 */
	private static function cmp_provincia( $a, $b ) {
		return strcmp( $a->i_den_unita_territoriale, $b->i_den_unita_territoriale );
	}

	/**
	 * Sends the locale string to AJAX consumer
	 */
	public static function ajax_get_locale(): void {
		check_ajax_referer( 'gcmi_fb_nonce' );
		$locale   = get_locale();
		$response = array(
			'locale' => $locale,
		);
		wp_send_json( $response );
	}

	/**
	 * Stampa l'html per lo spinner
	 *
	 * @return string
	 */
	private static function print_spinner(): string {
		return '<div id="gcmi-spinner-blocks" class="gcmi-spinner-blocks hidden"><div class="gcmi-spinner-tools">' .
		'<div class="gcmi-spinner-box-1"></div><div class="gcmi-spinner-box-2"></div><div class="gcmi-spinner-box-3"></div>' .
		'<div class="gcmi-spinner-box-4"></div><div class="gcmi-spinner-box-C"></div><div class="gcmi-spinner-box-5"></div>' .
		'<div class="gcmi-spinner-box-6"></div><div class="gcmi-spinner-box-7"></div><div class="gcmi-spinner-box-8"></div>' .
		'</div></div>';
	}
}
