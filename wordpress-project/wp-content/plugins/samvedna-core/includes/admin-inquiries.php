<?php
/**
 * Admin screen for stored consultation submissions + CSV export.
 *
 * @package Samvedna_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the admin menu page.
 */
function samvedna_inquiries_menu() {
	add_menu_page(
		__( 'Consultations', 'samvedna-core' ),
		__( 'Consultations', 'samvedna-core' ),
		'manage_options',
		'samvedna-inquiries',
		'samvedna_inquiries_page',
		'dashicons-email-alt',
		4
	);
}
add_action( 'admin_menu', 'samvedna_inquiries_menu' );

/**
 * Render the submissions table.
 */
function samvedna_inquiries_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table   = samvedna_inquiries_table();
	$per_page = 25;
	$paged   = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
	$offset  = ( $paged - 1 ) * $per_page;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery

	$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=samvedna_export_inquiries' ), 'samvedna_export' );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Consultation Requests', 'samvedna-core' ); ?></h1>
		<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'samvedna-core' ); ?></a>
		<hr class="wp-header-end" />

		<?php if ( empty( $rows ) ) : ?>
			<p><?php esc_html_e( 'No consultation requests yet.', 'samvedna-core' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Parent', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Age', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Concern', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Country', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Email', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Time', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Source', 'samvedna-core' ); ?></th>
						<th><?php esc_html_e( 'Message', 'samvedna-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->created_at ); ?></td>
							<td><?php echo esc_html( $row->parent_name ); ?></td>
							<td><?php echo esc_html( $row->child_age ); ?></td>
							<td><?php echo esc_html( $row->condition_type ); ?></td>
							<td><?php echo esc_html( $row->country ); ?></td>
							<td><a href="tel:<?php echo esc_attr( $row->phone ); ?>"><?php echo esc_html( $row->phone ); ?></a></td>
							<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
							<td><?php echo esc_html( ucfirst( $row->preferred_time ) ); ?></td>
							<td><?php echo esc_html( $row->source ); ?></td>
							<td><?php echo esc_html( $row->message ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php
			$total_pages = (int) ceil( $total / $per_page );
			if ( $total_pages > 1 ) {
				echo '<div class="tablenav"><div class="tablenav-pages">';
				echo wp_kses_post( paginate_links( array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'format'  => '',
					'current' => $paged,
					'total'   => $total_pages,
				) ) );
				echo '</div></div>';
			}
			?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Stream the inquiries table as a CSV download.
 */
function samvedna_export_inquiries() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'samvedna_export' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'samvedna-core' ) );
	}

	global $wpdb;
	$table = samvedna_inquiries_table();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=samvedna-consultations-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'ID', 'Date', 'Parent name', 'Child age', 'Concern', 'Country', 'Phone', 'Email', 'Preferred time', 'Source', 'Message' ) );
	foreach ( (array) $rows as $r ) {
		fputcsv( $out, array(
			$r['id'], $r['created_at'], $r['parent_name'], $r['child_age'], $r['condition_type'],
			$r['country'], $r['phone'], $r['email'], $r['preferred_time'], $r['source'], $r['message'],
		) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'admin_post_samvedna_export_inquiries', 'samvedna_export_inquiries' );
