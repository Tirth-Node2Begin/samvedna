<?php
/**
 * Search form.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="flex w-full items-center gap-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="samvedna-search"><?php esc_html_e( 'Search', 'samvedna' ); ?></label>
	<input
		type="search"
		id="samvedna-search"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search articles…', 'samvedna' ); ?>"
		class="block w-full rounded-control border border-border bg-white px-4 py-2.5 text-base text-text shadow-sm transition placeholder:text-muted/60 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
	/>
	<button type="submit" class="inline-flex h-11 shrink-0 items-center justify-center rounded-control border border-transparent bg-gradient-to-r from-primary to-secondary px-5 text-sm font-semibold text-white shadow-md transition hover:opacity-90">
		<?php esc_html_e( 'Search', 'samvedna' ); ?>
	</button>
</form>
