<?php

/**
 * MGPressComments class
 *
 * WordPress has many options under Settings > Discussion that should be respected, and Timber's comment handling
 * is minimal/broken. This class adds better comment handling to the theme.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0.1
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressComments')) {
    class MGPressComments
    {

        /**
         * Settings > Discussion: require_name_email
         * @var bool
         */
        protected static $requireNameEmail;

        /**
         * Settings > Discussion: comment_registration
         * @var bool
         */
        protected static $commentRegistration;

        /**
         * Settings > Discussion: close_comments_for_old_posts
         * @var bool
         */
        protected static $closeCommentsForOldPosts;

        /**
         * Settings > Discussion: close_comments_days_old
         * @var int
         */
        protected static $closeCommentsDaysOld;

        /**
         * Settings > Discussion: thread_comments
         * @var bool
         */
        protected static $threadComments;

        /**
         * Settings > Discussion: thread_comments_depth
         * @var int
         */
        protected static $threadCommentsDepth;

        /**
         * Settings > Discussion: page_comments
         * @var bool
         */
        protected static $pageComments;

        /**
         * Settings > Discussion: comments_per_page
         * @var int
         */
        protected static $commentsPerPage;

        /**
         * Settings > Discussion: default_comments_page
         * @var string
         */
        protected static $defaultCommentsPage;

        /**
         * Settings > Discussion: comment_order
         * @var string
         */
        protected static $commentOrder;

        /**
         * Settings > Discussion: show_avatars
         * @var string
         */
        protected static $showAvatars;

        /**
         * User logged in flag
         * @var bool
         */
        protected static $isLoggedIn;

        /**
         * Cache of count of top-level comments for pagination
         * @var int
         */
        protected static $topLevelCommentCount;

        /**
         * Global comments on flag
         * @var bool
         */
        protected static $commentsOn;

        /**
         * Flash messages
         * @var array
         */
        protected static $flashMessages;

        /**
         * Initialize MGPressComments class.
         *
         * @since MGPress 1.0
         *
         * @param array $comments
         * @return null
         */
        public static function init(array $comments = null)
        {

            // set options
            if (isset($comments) && is_array($comments)) {
                self::$commentsOn = isset($comments['comments_on']) ? $comments['comments_on'] : true;
                self::$flashMessages = isset($comments['flash_messages']) && is_array($comments['flash_messages'])
                    ? $comments['flash_messages']
                    : array();
            }

            add_filter('timber_context', array('MGPressComments', 'addToContext'));
            add_filter('timber/twig/functions', array('MGPressComments', 'addToTwig'));

            // hooks for comment submissions
            if (self::$commentsOn && count(self::$flashMessages)) {
                add_action('set_comment_cookies', array('MGPressComments', 'setCommentCookies'), 10, 2);
                add_action('init', array('MGPressComments', 'checkCommentCookies'));
                add_filter('comment_post_redirect', array('MGPressComments', 'commentPostRedirect'), 10, 2);
            }
        }

        /**
         * Add comment settings to local vars and twig context.
         *
         * @since MGPress 1.0
         *
         * @param array $context
         * @return array
         */
        public static function addToContext(array $context)
        {

            // determine if user is logged in
            self::$isLoggedIn = isset($context['user']) && is_a($context['user'], 'Timber\User');

            // add comment settings from admin Settings > Discussion to local property
            self::$requireNameEmail = get_option('require_name_email');
            self::$commentRegistration = get_option('comment_registration');
            self::$closeCommentsForOldPosts = get_option('close_comments_for_old_posts');
            self::$closeCommentsDaysOld = get_option('close_comments_days_old');
            self::$threadComments = get_option('thread_comments');
            self::$threadCommentsDepth = get_option('thread_comments_depth');
            self::$pageComments = get_option('page_comments');
            self::$commentsPerPage = get_option('comments_per_page');
            self::$defaultCommentsPage = get_option('default_comments_page');
            self::$commentOrder = get_option('comment_order');
            self::$showAvatars = get_option('show_avatars');

            // add comment settings from local property to site
            if (isset($context['site']) && property_exists($context['site'], 'comments')) {
                $context['site']->comments['require_name_email'] = self::$requireNameEmail;
                $context['site']->comments['comment_registration'] = self::$commentRegistration;
                $context['site']->comments['close_comments_for_old_posts'] = self::$closeCommentsForOldPosts;
                $context['site']->comments['close_comments_days_old'] = self::$closeCommentsDaysOld;
                $context['site']->comments['thread_comments'] = self::$threadComments;
                $context['site']->comments['thread_comments_depth'] = self::$threadCommentsDepth;
                $context['site']->comments['page_comments'] = self::$pageComments;
                $context['site']->comments['comments_per_page'] = self::$commentsPerPage;
                $context['site']->comments['default_comments_page'] = self::$defaultCommentsPage;
                $context['site']->comments['comment_order'] = self::$commentOrder;
                $context['site']->comments['show_avatars'] = self::$showAvatars;
            }

            return $context;
        }

        /**
         * Add extensions to Twig.
         *
         * @since MGPress 1.0
         *
         * @param Twig_Environment $twig
         * @return Twig_Environment
         */
        public static function addToTwig(\Twig_Environment $twig)
        {
            $twig->addFunction(new Timber\Twig_Function('can_comment', array('MGPressComments', 'canComment')));
            $twig->addFunction(new Timber\Twig_Function('get_comments', array('MGPressComments', 'getComments')));
            $twig->addFunction(new Timber\Twig_Function('next_comments_link', array('MGPressComments', 'nextCommentsLink')));
            $twig->addFunction(new Timber\Twig_Function('previous_comments_link', array('MGPressComments', 'previousCommentsLink')));
            return $twig;
        }

        /**
         * Check if user can comment on a Post or Comment.
         *
         * @since MGPress 1.0
         *
         * @param \Timber\Post     $post     (required) The post to check if comments are allowed.
         * @param \Timber\Comment  $comment  If using nested comments, the comment to check if comments are allowed.
         * @return bool
         */
        public static function canComment($post, Timber\Comment $comment = null)
        {

            // check param type
            // we do this here to support manual template data overrides, such as providing an array of values
            if (!is_object($post) || 'Timber\Post' !== get_class($post)) {
                return false;
            }

            // comments manually closed
            if (!self::$commentsOn || $post->comment_status == 'closed') {
                return false;
            }

            // comments closed from timeout
            if (self::$closeCommentsForOldPosts) {
                $now = new DateTime();
                $postDate = new DateTime($post->post_date);
                $postDate->add(new DateInterval('P' . self::$closeCommentsDaysOld . 'D'));
                if ($postDate < $now) {
                    $post->comment_status = 'closed'; // for checks from UI
                    return false;
                }
            }

            // comments closed to public
            if (self::$commentRegistration && !self::$isLoggedIn) {
                return false;
            }

            // check nested comments
            if (!is_null($comment) && is_a($comment, 'Timber\Comment')) {

                // threaded comments turned off
                if (!self::$threadComments) {
                    return false;
                } elseif ($comment->depth() >= self::$threadCommentsDepth) { // nested too deep
                    return false;
                }
            }

            return true;
        }

        /**
         * Get list of child comments from Post.
         *
         * Timber\CommentThread is basically broke at the time of writing (ordering and pagination not supported),
         * so I made my own comment getter.
         *
         * @since MGPress 1.0
         *
         * @param Timber\Post  $post  (required) The post to return comments for.
         * @return array Timber\Comment
         */
        public static function getComments($post)
        {

            // check param type
            // we do this here to support manual template data overrides, such as providing an array of values
            if (!is_object($post) || 'Timber\Post' !== get_class($post)) {
                return false;
            }

            // comments turned off
            if (!self::$commentsOn) {
                return false;
            }

            // get comments from WordPress function and return hierarchical Timber\Comment array
            $comments = self::buildCommentList(get_comments(
                array(
                    'post_id' => $post->ID,
                    'number' => self::$pageComments ? self::$commentsPerPage : null,
                    'offset' => self::$pageComments ? (get_query_var('cpage', 1) * self::$commentsPerPage) - self::$commentsPerPage : 0,
                    'status' => 'approve',
                    'type' => 'comment',
                    'order' => (self::$defaultCommentsPage == 'newest') ? 'DESC' : 'ASC',
                    'hierarchical' => self::$threadComments
                )
            ));

            // reorder comments by date
            if (self::$commentOrder == 'asc') {
                usort($comments, array('MGPressComments', 'sortByAsc'));
            } else {
                usort($comments, array('MGPressComments', 'sortByDesc'));
            }

            return $comments;
        }

        /**
         * Create link for next comments.
         *
         * @since MGPress 1.0
         *
         * @param \Timber\Post  $post   (required) The parent post of comments to paginate.
         * @param string        $label  A label to use as the link anchor text.
         * @return bool|string
         */
        public static function nextCommentsLink($post, $label = 'load more comments')
        {

            // check param type
            // we do this here to support manual template data overrides, such as providing an array of values
            if (!is_object($post) || 'Timber\Post' !== get_class($post)) {
                return false;
            }

            $nextPageNumber = (get_query_var('cpage', 1) + 1);
            if (ceil(self::getTopLevelCommentCount($post) / self::$commentsPerPage) < $nextPageNumber) {
                return false;
            }
            return '<a href="' .
                $post->link() . (strpos($post->link(), '?') === false ? '?' : '&') . 'cpage=' . $nextPageNumber
                . '">' . $label . '</a>';
        }

        /**
         * Create link for previous comments.
         *
         * @since MGPress 1.0
         *
         * @param \Timber\Post  $post   (required) The parent post of comments to paginate.
         * @param string        $label  A label to use as the link anchor text.
         * @return bool|string
         */
        public static function previousCommentsLink($post, $label = 'load previous comments')
        {
            // check param type
            // we do this here to support manual template data overrides, such as providing an array of values
            if (!is_object($post) || 'Timber\Post' !== get_class($post)) {
                return false;
            }

            $nextPageNumber = (get_query_var('cpage', 1) - 1);
            if (0 >= $nextPageNumber) {
                return false;
            }
            return '<a href="' .
                $post->link() . (strpos($post->link(), '?') === false ? '?' : '&') . 'cpage=' . $nextPageNumber
                . '">' . $label . '</a>';
        }

        /**
         * Set cookies for comment submissions.
         *
         * @since MGPress 1.0.1
         *
         * @param WP_Comment $comment
         * @param WP_User $user
         */
        public static function setCommentCookies(WP_Comment $comment, WP_User $user)
        {
            setcookie('comment_submission_parent', $comment->comment_parent . ',' . $comment->comment_approved, 0, '/');
        }

        /**
         * Check cookies for recent comment submissions.
         *
         * @since MGPress 1.0.1
         */
        public static function checkCommentCookies()
        {
            if( isset( $_COOKIE['comment_submission_parent'] ) ) {
                if ($values = explode(',', $_COOKIE['comment_submission_parent'])) {
                    $commentParentId = (int) $values[0];
                    $flashMessageKey = $values[1] == 1 ? 'success' : 'pending';
                    $flashMessage = isset(self::$flashMessages[$flashMessageKey]) ? self::$flashMessages[$flashMessageKey] : null;

                    setcookie('comment_submission_parent', null, -1, '/');
                    unset($_COOKIE['comment_submission_parent']);
                    add_action('comment_form_before_id', function($commentId) use ($commentParentId, $flashMessage, $flashMessageKey) {
                        if ($commentId == $commentParentId && $flashMessage) {
                            echo "<div id='flash-message' class='" . $flashMessageKey . "'><p>"  . __($flashMessage, "theme") . "</p></div>";
                        }
                    });
                }
            }
        }

        /**
         * Redirect URL after comment submission.
         *
         * @since MGPress 1.0.1
         *
         * @param string $location
         * @param WP_Comment comment
         * @return string
         */
        public static function commentPostRedirect($location, WP_Comment $comment)
        {
            return get_permalink($comment->comment_post_ID) . '#flash-message';
        }

        /**
         * Build list of hierarchical comments recursively.
         *
         * @since MGPress 1.0
         *
         * @param array $comments
         * @param int $parentId
         * @param int $depth
         * @return array Timber\Comment
         */
        private static function buildCommentList(array $comments, $parentId = 0, $depth = 0)
        {
            $branch = array();
            $depth++;

            foreach ($comments as $comment) {
                $timber_comment = new Timber\Comment($comment);
                $timber_comment->_depth = $depth;
                if ($comment->comment_parent == $parentId) {
                    $children = self::buildCommentList($comments, $comment->comment_ID, $depth);
                    if (count($children)) {
                        foreach ($children as $child) {
                            $timber_comment->add_child($child);
                        }
                    }
                    $branch[] = $timber_comment;
                }
            }

            return $branch;
        }

        /**
         * Get the number of top-level comments for a post.
         *
         * @since MGPress 1.0
         *
         * @param \Timber\Post $post
         * @return int
         */
        private static function getTopLevelCommentCount(Timber\Post $post)
        {
            if (is_null(self::$topLevelCommentCount)) {
                global $wpdb;
                self::$topLevelCommentCount = (int) $wpdb->get_var("
                    SELECT COUNT(*) 
                    FROM $wpdb->comments
                    WHERE comment_parent = 0
                    AND comment_post_ID = $post->ID
                ");
            }
            return self::$topLevelCommentCount;
        }

        /**
         * Sort Comment array by date, asc.
         *
         * @since MGPress 1.0
         *
         * @param $a Timber\Comment
         * @param $b Timber\Comment
         * @return int
         */
        private static function sortByAsc($a, $b)
        {
            return strcmp($a->comment_date, $b->comment_date);
        }

        /**
         * Sort Comment array by date, desc.
         *
         * @since MGPress 1.0
         *
         * @param $a Timber\Comment
         * @param $b Timber\Comment
         * @return int
         */
        private static function sortByDesc($a, $b)
        {
            return strcmp($b->comment_date, $a->comment_date);
        }
    }

    MGPressComments::init(isset($MgPressSettings['comments']) ? $MgPressSettings['comments'] : null);
}
