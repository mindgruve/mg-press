<?php

/**
 * Comments
 */

/**
 * Render Comment
 *
 * @param object $comment
 * @param array $args
 * @param integer $depth
 */
function render_comment($comment, $args, $depth) {

    $GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);

    $args['depth']       = $depth;
    $args['comment']     = new TimberComment($comment);
    $args['date_format'] = get_option('date_format');
    $args['time_format'] = get_option('time_format');
    $args['args']        = $args;

    Timber::render('includes/comment.html.twig', $args);
}

/**
 * List Comments
 *
 * @param TimberPost $post
 * @global integer $cpage
 * @global boolean $overridden_cpage
 */
function list_comments($post) {

    global $cpage, $overridden_cpage;

    $cpage               = $cpage ? $cpage : 1;
    $overridden_cpage    = true;
    $commentsPerPage     = (int) get_option('comments_per_page');
    $commentsOrder       = get_option('comment_order');
    $defaultCommentsPage = get_option('default_comments_page');
    $commentPage         = $cpage;
    $lastPage            = ceil($post->comment_count / $commentsPerPage);

    if ($defaultCommentsPage == 'newest') {
        $commentPage = $lastPage - $cpage + 1;
    }

    wp_list_comments(
        array(
            'callback'    => 'render_comment',
        ),
        get_comments(
            array(
                'post_id' => $post->ID,
                'number'  => $commentsPerPage,
                'offset'  => (int) ($commentPage - 1) * $commentsPerPage,
                'order'   => $commentsOrder,
            )
        )
    );
}

// Create a timber function wrapper for list comments
TimberHelper::function_wrapper('list_comments', array(), true);

/**
 * Return Previous Comments Link
 *
 * @param TimberPost $post
 * @param string $label
 * @global integer $cpage
 * @return string
 */
function return_previous_comments_link($post, $label = '') {

    global $cpage;

    if ( !is_singular() || !get_option('page_comments') ) {
        return;
    }

    if ( intval($cpage) <= 1 ) {
        return;
    }

    $prevpage = intval($cpage) - 1;

    if ( empty($label) ) {
        $label = __('&laquo; Older Comments');
    }

    /**
     * Filter the anchor tag attributes for the previous comments page link.
     *
     * @since 2.7.0
     *
     * @param string $attributes Attributes for the anchor tag.
     */
    return '<a href="' . esc_url( get_comments_pagenum_link( $prevpage ) ) . '" '
        . apply_filters( 'previous_comments_link_attributes', '' ) . '>'
        . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
}

// Create a timber function wrapper for prvious comments link
TimberHelper::function_wrapper('return_previous_comments_link', null, false);

/**
 * Return Next Comments Link
 *
 * @param TimberPost $post
 * @param string $label
 * @param integer $max_page
 * @global integer $cpage
 * @return string
 */
function return_next_comments_link($post, $label = '', $max_page = 0) {

    global $cpage;

	if ( !is_singular() || !get_option('page_comments') ) {
        return;
    }

    $commentsPerPage = (int) get_option('comments_per_page');
    $nextpage        = intval($cpage) + 1;

	if ( empty($max_page) ) {
        $max_page = get_comment_pages_count($post->comments, $commentsPerPage);
    }

	if ( $nextpage > $max_page ) {
        return;
    }

	if ( empty($label) ) {
        $label = __('Newer Comments &raquo;');
    }

	/**
	 * Filter the anchor tag attributes for the next comments page link.
	 *
	 * @since 2.7.0
	 *
	 * @param string $attributes Attributes for the anchor tag.
	 */
	return '<a href="' . esc_url( get_comments_pagenum_link( $nextpage ) ) . '" '
        . apply_filters( 'next_comments_link_attributes', '' ) . '>'
        . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
}

// Create a timber function wrapper for next comments link
TimberHelper::function_wrapper('return_next_comments_link', null, false);
