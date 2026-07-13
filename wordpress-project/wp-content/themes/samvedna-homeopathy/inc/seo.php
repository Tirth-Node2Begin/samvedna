<?php
/**
 * SEO: meta description, Open Graph, Twitter cards, and JSON-LD structured data.
 *
 * All output is skipped automatically when Yoast SEO, Rank Math, or All in One
 * SEO is active so there is never duplicate metadata.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a dedicated SEO plugin is handling head metadata.
 *
 * @return bool
 */
function samvedna_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )
		|| class_exists( 'RankMath' )
		|| defined( 'AIOSEO_VERSION' )
		|| class_exists( 'AIOSEO\\Plugin\\AIOSEO' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Output meta description, canonical, Open Graph and Twitter tags.
 */
function samvedna_seo_meta() {
	if ( samvedna_seo_plugin_active() ) {
		return;
	}

	$default_desc = '20+ years of specialized homeopathic support for Autism, ADHD, Speech Delay, Learning Disability, Developmental Delay, Genetic Disorders and Neurological Disorders. Worldwide online consultations.';
	$description  = (string) samvedna_option( 'meta_description', $default_desc );
	$canonical    = home_url( '/' );
	$og_type      = 'website';
	$title        = wp_get_document_title();
	$image        = samvedna_image_url( samvedna_option( 'og_image', 'dr-krunal-kosada.jpg' ) );

	if ( is_singular() ) {
		$obj       = get_queried_object();
		$canonical = get_permalink( $obj );
		$og_type   = is_single() ? 'article' : 'website';
		if ( $obj && ! empty( $obj->post_excerpt ) ) {
			$description = wp_strip_all_tags( $obj->post_excerpt );
		} elseif ( $obj ) {
			$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $obj->post_content ) ), 30, '…' );
		}
		if ( has_post_thumbnail( $obj ) ) {
			$image = get_the_post_thumbnail_url( $obj, 'large' );
		}
	} elseif ( is_archive() ) {
		$canonical   = '';
		$description  = wp_strip_all_tags( get_the_archive_description() ) ?: $description;
	}

	echo "\n<!-- Samvedna SEO -->\n";
	printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
	}

	// Open Graph.
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $og_type ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ? $canonical : home_url( '/' ) ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( 'en_IN' ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	}

	// Twitter.
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}
	echo "<!-- /Samvedna SEO -->\n";
}
add_action( 'wp_head', 'samvedna_seo_meta', 1 );

/**
 * The clinic's postal address node, reused across schemas.
 *
 * @return array
 */
function samvedna_jsonld_address() {
	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => (string) samvedna_option( 'address_street', '261, The Galleria Shopping Hub, Sanjeevkumar Auditorium Road, Pal' ),
		'addressLocality' => (string) samvedna_option( 'address_locality', 'Surat' ),
		'addressRegion'   => (string) samvedna_option( 'address_region', 'Gujarat' ),
		'postalCode'      => (string) samvedna_option( 'address_postal', '395009' ),
		'addressCountry'  => (string) samvedna_option( 'address_country', 'IN' ),
	);
}

/**
 * Output the five JSON-LD blocks on the front page (ports lib/seo/jsonld.ts).
 */
function samvedna_jsonld() {
	if ( samvedna_seo_plugin_active() ) {
		return;
	}

	$home    = home_url( '/' );
	$phone   = samvedna_contact( 'phone_primary' );
	$email   = samvedna_contact( 'email' );
	$address = samvedna_jsonld_address();
	$same_as = wp_list_pluck( samvedna_social_links(), 'href' );

	$schemas = array();

	if ( is_front_page() ) {
		$schemas[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'MedicalBusiness',
			'name'            => 'Samvedna Homeopathy',
			'url'             => $home,
			'image'           => samvedna_image_url( 'dr-krunal-kosada.jpg' ),
			'telephone'       => $phone,
			'email'           => $email,
			'address'         => $address,
			'geo'             => array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => 21.185443,
				'longitude' => 72.783439,
			),
			'medicalSpecialty'         => array( 'Pediatric', 'Homeopathic', 'DevelopmentalDisorder', 'Neurologic' ),
			'openingHoursSpecification' => array(
				array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
					'opens'     => '10:00',
					'closes'    => '19:00',
				),
			),
			'areaServed'      => array( 'India', 'United Arab Emirates', 'United Kingdom', 'United States', 'Canada', 'Australia', 'Singapore', 'Germany' ),
		);

		$schemas[] = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Physician',
			'name'             => 'Dr. Krunal Kosada',
			'honorificPrefix'  => 'Dr.',
			'url'              => $home . '#founder',
			'image'            => samvedna_image_url( 'dr-krunal-kosada.jpg' ),
			'medicalSpecialty' => array( 'Homeopathy', 'Pediatric Neurodevelopmental Care' ),
			'hasCredential'    => array( 'BHMS', 'FCAH' ),
			'affiliation'      => array(
				'@type' => 'Organization',
				'name'  => 'Samvedna Homeopathy',
				'url'   => $home,
			),
			'address'          => $address,
			'telephone'        => $phone,
		);

		$schemas[] = array(
			'@context'     => 'https://schema.org',
			'@type'        => 'Organization',
			'name'         => 'Samvedna Homeopathy',
			'url'          => $home,
			'logo'         => samvedna_image_url( 'samvedna-logo.webp' ),
			'foundingDate' => '2002',
			'founder'      => array( '@type' => 'Person', 'name' => 'Dr. Krunal Kosada' ),
			'sameAs'       => array_values( $same_as ),
			'contactPoint' => array(
				array(
					'@type'             => 'ContactPoint',
					'telephone'         => $phone,
					'contactType'       => 'appointments',
					'areaServed'        => 'Worldwide',
					'availableLanguage' => array( 'English', 'Hindi', 'Gujarati' ),
				),
			),
		);

		$faq_entities = array();
		foreach ( samvedna_default_faq() as $item ) {
			$faq_entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $item['answer'] ),
			);
		}
		$schemas[] = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $faq_entities,
		);

		$schemas[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Conditions', 'item' => $home . '#conditions' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => 'About Dr. Kosada', 'item' => $home . '#founder' ),
				array( '@type' => 'ListItem', 'position' => 4, 'name' => 'Contact', 'item' => $home . '#consultation' ),
			),
		);
	}

	foreach ( $schemas as $schema ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}
add_action( 'wp_head', 'samvedna_jsonld', 5 );
