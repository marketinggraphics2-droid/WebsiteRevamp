<?php
/**
 * Job opening from the live site's own `careers` post type (/careers/<slug>/, present when the
 * theme runs on a copy of the dynamiqes.com database). Same layout as the theme's dq_career
 * openings — one template, two post types (inc/careers.php picks the source).
 *
 * @package dynamiqes
 */

require get_template_directory() . '/single-dq_career.php';
