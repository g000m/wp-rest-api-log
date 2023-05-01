<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_REST_API_Log_Common' ) ) {

	class WP_REST_API_Log_Common {

		const PLUGIN_NAME      = 'wp-rest-api-log';
		const VERSION          = WP_REST_API_LOG_VERSION;
		const TEXT_DOMAIN      = 'wp-rest-api-log';

		static public function current_milliseconds() {
			return self::microtime_to_milliseconds( microtime() );
		}

		static public function microtime_to_milliseconds( $microtime ) {
			list( $usec, $sec ) = explode( " ", $microtime );
			return ( ( (float)$usec + (float)$sec ) ) * 1000;
		}


		static public function valid_methods() {
			return apply_filters( self::PLUGIN_NAME . '-valid-methods', array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ) );
		}


		static public function is_valid_method( $method ) {
			return apply_filters( self::PLUGIN_NAME . '-is-method-valid', in_array( $method, self::valid_methods() ) );
		}

		/**
		 * Outputs a select dropdown for a taxonomy.
		 *
		 * @param  string $taxonomy The taxonomy name.
		 * @param  array  $args     Additional args.
		 * @return void
		 */
		static public function dropdown_terms( $taxonomy, $args = [] ) {

			if ( ! taxonomy_exists( $taxonomy ) ) {
				return;
			}

			$tax_obj = get_taxonomy( $taxonomy );

			$get = filter_var_array(
				$_GET,
				[
					$taxonomy => FILTER_SANITIZE_STRING,
				]
			);

			$args = wp_parse_args(
				$args,
				[
					// Selected term slug.
					'selected'   => '',
					'hide_empty' => false,
					'all_items'  => '',
				]
			);

			// Default the selected slug to the query string if nothing was passed.
			$selected_slug = ! empty( $args['selected'] ) ? $args['selected'] : $get[ $taxonomy ];

			$all_items = ! empty( $args['all_label'] ) ? $args['all_label'] : $tax_obj->labels->all_items;

			$term_query = new \WP_Term_Query(
				[
					'taxonomy' => $taxonomy,
					'orderby'  => 'count',
					'order'    => 'DESC',
				]
			);

			?>
			<label class="screen-reader-text" for="<?php echo esc_attr( esc_attr( $taxonomy ) ); ?>">
				<?php echo esc_html( $tax_obj->labels->filter_by_item ); ?>
			</label>

			<select name="<?php echo esc_attr( esc_attr( $taxonomy ) ); ?>" id="<?php echo esc_attr( esc_attr( $taxonomy ) ); ?>">

				<option value=""><?php echo esc_html( $all_items ); ?></option>

				<?php foreach ( $term_query->get_terms() as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_slug, $term->slug ); ?>  ><?php echo esc_html( $term->name ); ?> (<?php echo esc_html( number_format( $term->count ) ); ?>)</option>
				<?php endforeach; ?>

			</select>
			<?php
		}

		/**
         * Provides a helper function to work around a known PHP bug in filter_input()
         * https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=730094
         *
		 * @param string $input
		 * @param string $var_name
		 * @param string $filter
		 *
		 * @return mixed|null
		 */
		static public function filter_server_input( string $input, string $var_name, string $filter ) {
			if ( filter_has_var( $input, $var_name ) ) {
				$filtered_input = filter_input( $input, $var_name, $filter, FILTER_NULL_ON_FAILURE );
			} else {
				if ( isset( $_SERVER[ $var_name ] ) ) {
					$filtered_input = filter_var( $_SERVER[ $var_name ], $filter, FILTER_NULL_ON_FAILURE );
				} else {
					$filtered_input = null;
				}
			}

			return $filtered_input;
		}


	} // end class
}
