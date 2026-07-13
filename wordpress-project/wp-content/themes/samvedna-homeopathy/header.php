<?php
/**
 * Site header: <head>, opening <body>, navigation.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="overflow-x-hidden">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'font-body antialiased overflow-x-hidden' ); ?>>
<?php wp_body_open(); ?>

<a class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[200] focus:rounded-control focus:bg-white focus:px-4 focus:py-2 focus:text-primary focus:shadow-premium" href="#main">
	<?php esc_html_e( 'Skip to content', 'samvedna' ); ?>
</a>

<?php get_template_part( 'template-parts/global/navbar' ); ?>

<main id="main" class="site-main<?php echo is_front_page() ? '' : ' pt-20'; ?>">
