<?php
/**
 * Blog posts index (used when a static front page is set).
 * Delegates to index.php for a single source of truth.
 *
 * @package Samvedna
 */

defined( 'ABSPATH' ) || exit;

require locate_template( 'index.php' );
