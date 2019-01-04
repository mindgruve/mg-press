<?php

/**
 * MGAdmin table classes
 *
 * Customize WP admin table columns.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.1
 * @author      sdelta@mindgruve.com
 */

defined('ABSPATH') or die();

if (!class_exists('MGAdminOrderColumn')) {
    class MGAdminOrderColumn
    {
        /**
         * @var array
         */
        static private $post_types;

        /**
         * Initialize MGAdminOrderColumn class.
         *
         * @param array $post_types
         * @return null
         */
        public static function init(array $post_types)
        {
            self::$post_types = $post_types;

            add_filter('manage_posts_columns', array('MGAdminOrderColumn', 'addColumn'));
            add_action('manage_posts_custom_column', array('MGAdminOrderColumn', 'retrieveContent'), 10, 2);
            add_action('admin_head', array('MGAdminOrderColumn', 'resizeColumn'));

            foreach (self::$post_types as $post_type) {
                add_filter('manage_edit-'.$post_type.'_sortable_columns', array('MGAdminOrderColumn', 'enableSort'));
            }
        }

        /**
         * Add the Order column in the admin post list table.
         *
         * @param array $defaults
         * @return array
         */
        public static function addColumn(array $defaults)
        {
            $post_type = get_post_type();

            $date = $defaults['date'];
            unset($defaults['date']);

            if (in_array($post_type, self::$post_types)) {
                $defaults['menu_order'] = __('Order', 'theme');
            }

            $defaults['date'] = $date;

            return $defaults;
        }

        /**
         * Retrieve and output the content of the Order column.
         *
         * @param string $column_name
         * @param int $post_ID
         * @return void
         */
        public static function retrieveContent($column_name, $post_ID)
        {
            if ('menu_order' === $column_name) {
                echo get_post_field('menu_order', $post_ID) ?: '';
            }
        }

        /**
         * Resize the Order column.
         *
         * @return void
         */
        public static function resizeColumn()
        {
            $post_type = get_post_type();

            if (in_array($post_type, self::$post_types)) {
                echo '<style type="text/css">';
                echo '.widefat th.column-menu_order, td.column-menu_order {width:11%;}';
                echo '</style>';
            }
        }

        /**
         * Make Order column sortable.
         *
         * @param array $columns
         * @return array
         */
        public static function enableSort(array $columns)
        {
            $columns['menu_order'] = 'menu_order';
            return $columns;
        }
    }

    MGAdminOrderColumn::init(isset($MgPressSettings['admin']['order_column'])
        ? $MgPressSettings['admin']['order_column']
        : null
    );
}

if (!class_exists('MGAdminTaxonomyColumn')) {
    class MGAdminTaxonomyColumn
    {
        /**
         * @var array
         */
        static private $post_types;

        /**
         * Initialize MGAdminTaxonomyColumn class.
         *
         * @param array $post_types
         * @return null
         */
        public static function init(array $post_types)
        {
            self::$post_types = $post_types;

            add_filter('manage_posts_columns', array('MGAdminTaxonomyColumn', 'addColumn'));
            add_action('manage_posts_custom_column', array('MGAdminTaxonomyColumn', 'retrieveContent'), 10, 2);
            add_action('admin_head', array('MGAdminTaxonomyColumn', 'resizeColumn'));
            add_action('restrict_manage_posts', array('MGAdminTaxonomyColumn', 'addTaxonomyFilter'));
            add_filter('parse_query', array('MGAdminTaxonomyColumn', 'submitFilter'));
        }

        /**
         * Add the new column in the admin post list table.
         *
         * @param array $defaults
         * @return array
         */
        public static function addColumn(array $defaults)
        {
            $post_type = get_post_type();

            // Save then unset the column date
            $date = $defaults['date'];
            unset($defaults['date']);

            // If present, save then unset the column menu_order
            if (isset($defaults['menu_order'])) {
                $menu_order = $defaults['menu_order'];
                unset($defaults['menu_order']);
            } else {
                $menu_order = false;
            }

            if (in_array($post_type, self::$post_types)) {
                $taxonomies = get_object_taxonomies($post_type, 'objects');

                foreach ($taxonomies as $tax) {
                    // Only add taxonomy column if it is hierarchical
                    if (is_taxonomy_hierarchical($tax->name)) {
                        $defaults[$tax->name] = __($tax->labels->singular_name, 'theme');
                    }
                }
            }

            // If present add back the column menu_order second to last
            if ($menu_order) {
                $defaults['menu_order'] = $menu_order;
            }

            // Add back the column date in the last position
            $defaults['date'] = $date;

            return $defaults;
        }

        /**
         * Retrieve and output the content of the column.
         *
         * @param string $column_name
         * @param int $post_ID
         * @return void
         */
        public static function retrieveContent($column_name, $post_ID)
        {
            $post_type = get_post_type();

            if (in_array($post_type, self::$post_types)) {
                $taxonomies = get_object_taxonomies($post_type);
                foreach ($taxonomies as $tax) {
                    if ($tax ===  $column_name) {
                        $terms = get_the_terms($post_ID, $tax);
                        if ($terms) {
                            $terms =
                                array_map(function ($term) {
                                    return $term->name;
                                }, $terms);
                            echo count($terms) ? implode(', ', $terms) : '';
                        }
                    }
                }
            }
        }

        /**
         * Resize the Order column.
         *
         * @return void
         */
        public static function resizeColumn()
        {
            $post_type = get_post_type();
            $classes = array();
            $count = 0;

            if (in_array($post_type, self::$post_types)) {
                $taxonomies = get_object_taxonomies($post_type);
                foreach ($taxonomies as $tax) {
                    if (is_taxonomy_hierarchical($tax)) {
                        $count++;

                        // Generate an array of CSS classes which affect the column size
                        $classes[] = '.widefat th.column-'.$tax;
                        $classes[] = 'td.column-'.$tax;
                    }
                }
            }

            // Only set column size if there is less than 4 taxonomy columns
            if (4 > $count) {
                echo '<style type="text/css">';
                echo implode(', ', $classes).' {width:15%;}';
            }
            echo '</style>';
        }

        /**
         * Add taxonomies list table filter.
         *
         * @param string $post_type
         * @return void
         */
        public static function addTaxonomyFilter($post_type)
        {
            if (in_array($post_type, self::$post_types)) {
                $taxonomies = get_object_taxonomies($post_type, 'objects');
                foreach ($taxonomies as $tax) {
                    if (is_taxonomy_hierarchical($tax->name)) {

                        // Get the taxonomy terms
                        $terms = get_terms(array('taxonomy' => $tax->name));
                        // Convert taxonomy terms to a key-value array
                        $terms = array_map(function ($term) {
                            return array('label' => $term->name, 'value' => $term->slug);
                        }, $terms);

                        // Output the HTML select element
                        echo '<select name="'.$tax->name.'_filter">';
                        echo '<option value="">'.__('All '.$tax->label.' ', 'theme').'</option>';
                        $current_value = isset($_GET[$tax->name.'_filter'])? $_GET[$tax->name.'_filter']:'';
                        foreach ($terms as $term) {
                            printf(
                                '<option value="%s"%s>%s</option>',
                                $term['value'],
                                $term['value'] === $current_value ? ' selected="selected"' : '',
                                $term['label']
                            );
                        }
                        echo '</select>';
                    }
                }
            }
        }

        /**
         * Handle filter submission.
         *
         * @param WP_Query $query
         * @return WP_Query
         */
        public static function submitFilter(WP_Query $query)
        {
            global $pagenow;
            $post_type = '';
            if (isset($_GET['post_type'])) {
                $post_type = $_GET['post_type'];
            }
            if (in_array($post_type, self::$post_types) && is_admin() && 'edit.php' === $pagenow) {
                $taxonomies = get_object_taxonomies($post_type);
                foreach ($taxonomies as $tax) {
                    if (is_taxonomy_hierarchical($tax)) {
                        // Test valid taxonomy filter for GET request variable
                        if (isset($_GET[$tax.'_filter']) && $_GET[$tax.'_filter'] != '') {
                            $tax_query = array(array(
                                                   'taxonomy' => $tax,
                                                   'field' => 'slug',
                                                   'terms' => $_GET[$tax.'_filter'],
                                               ));
                            $query->set('tax_query', $tax_query);
                        }
                    }
                }
            }

            return $query;
        }
    }

    MGAdminTaxonomyColumn::init(isset($MgPressSettings['admin']['taxonomy_column'])
        ? $MgPressSettings['admin']['taxonomy_column']
        : null
    );
}