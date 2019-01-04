<?php

/**
 * MGPressBlocks class
 *
 * Add support for custom blocks using Advanced Custom Fields plugin.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.1
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressBlocks')) {
    class MGPressBlocks
    {

        /**
         * Block settings to initialize feature with
         * @var array
         */
        protected static $blockSettings;

        /**
         * Container for flexible content template filenames.
         * @var array
         */
        protected static $flexibleContentTemplates = [];

        /**
         * Initialize MGPressBlocks class.
         *
         * @since MGPress 1.1
         *
         * @return null
         */
        public static function init(array $blockSettings = null)
        {
            if (is_array($blockSettings) && count($blockSettings)) {

                // store block settings locally
                self::$blockSettings = $blockSettings;
            }

            if (isset($blockSettings['acf_pro'])
                && $blockSettings['acf_pro']
                && function_exists('acf_add_local_field_group')
                && function_exists('acf_render_field_wrap')
                && function_exists('acf_get_field_groups'))
            {
                self::getFlexibleContentTemplates();
                add_action('init', array('MGPressBlocks', 'registerBlocks'));
                //self::registerBlocks();
                add_action('acf/render_field_group_settings', array('MGPressBlocks', 'blockGroupSettings'), 10, 1);
                add_filter('acf/location/rule_values/post_type', array('MGPressBlocks', 'addAcfDummyBlockPostType'));
                add_filter('timber_context', array('MGPressBlocks', 'addBlockTemplateToContext'));
                add_action('admin_init', array('MGPressBlocks', 'hidePostEditor'));
            }
        }

        /**
         * Add templates to Page Attributes metabox in admin UI.
         *
         * @since MGPress 1.1
         *
         * @return array
         */
        public static function registerBlocks()
        {

            // Load Blocks
            $acfJsonDir = get_stylesheet_directory() . '/acf-json/';
            if (is_dir($acfJsonDir)) {

                $dir = new DirectoryIterator($acfJsonDir);

                $flexibleContentBase = [
                    "key"                   => "group_flexible_content",
                    "title"                 => "Flexible Content",
                    "fields"                => [
                        [
                            "key"               => "field_body_content",
                            "label"             => "Body Content",
                            "name"              => "body_content",
                            "type"              => "flexible_content",
                            "instructions"      => "",
                            "required"          => 0,
                            "conditional_logic" => 0,
                            "wrapper"           => [
                                "width" => "",
                                "class" => "",
                                "id"    => ""
                            ],
                            "button_label"      => "Add Block",
                            "min"               => "",
                            "max"               => "",
                            "layouts"           => []
                        ]
                    ],
                    "location"              => [],
                    "menu_order"            => 1,
                    "position"              => "normal",
                    "style"                 => "default",
                    "label_placement"       => "top",
                    "instruction_placement" => "label",
                    "hide_on_screen"        => ""
                ];

                // add flexible content section to FC templates only
                if (count(self::$flexibleContentTemplates)) {
                    foreach (self::$flexibleContentTemplates as $template) {
                        $flexibleContentBase['location'][] = [[
                                                                  "param"    => "post_template",
                                                                  "operator" => "==",
                                                                  "value"    => $template
                                                              ]];
                    }
                }

                // add flexible layout to custom post types defined in settings
                if (is_array(self::$blockSettings)
                    && isset(self::$blockSettings['flexible_layout_post_types'])
                    && is_array(self::$blockSettings['flexible_layout_post_types']))
                {
                    foreach (self::$blockSettings['flexible_layout_post_types'] as $postType) {
                        $flexibleContentBase['location'][] = [[
                                                                  "param"    => "post_type",
                                                                  "operator" => "==",
                                                                  "value"    => $postType
                                                              ]];
                    }
                }

                foreach ($dir as $file) {
                    if (!$file->isDot() && 'json' == $file->getExtension()) {
                        $array = json_decode(file_get_contents($file->getPathname()), true);

                        if (!isset($array['mg_block']) || !$array['mg_block']) {
                            continue;
                        }

                        $layout = array(
                            'key'        => 'flex'.$array['key'],
                            'name'       => $array['key'],
                            'label'      => $array['title'],
                            'display'    => 'block',
                            'sub_fields' => $array['fields'],
                        );

                        $flexibleContentBase['fields'][0]['layouts'][] = $layout;
                    }
                }

                acf_add_local_field_group($flexibleContentBase);
            }
        }

        /**
         * Find custom templates used for Flexible Content and add to internal array.
         *
         * @since MGPress 1.1
         *
         * @return null
         */
        public static function getFlexibleContentTemplates()
        {

            // init var with template directory location
            $templatesDir = get_template_directory() . '/' .
                (is_array(MGPressSite::$dirname) ? MGPressSite::$dirname[0] : MGPressSite::$dirname)
                . '/template';

            // add custom twig templates to location array
            if (is_dir($templatesDir)) {
                if ($handle = opendir($templatesDir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if (preg_match('/(.*)\.twig$/', $entry, $matches)) {

                            // check if template filename has "flexible content"
                            $templateName = $matches[1];
                            if (strpos(strtolower(preg_replace('/[^\w]/', '', $templateName)), 'flexiblecontent') !== false) {
                                self::$flexibleContentTemplates[] = $templateName;
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }

        /**
         * Add custom ACF Field Group options for template blocks.
         *
         * @since MGPress 1.1
         *
         * @param array $field_group
         * @return null
         */
        public static function blockGroupSettings($field_group)
        {
            $blockTemplateFolder = '/views/block';
            $blockTemplateExtension = '.twig';

            acf_render_field_wrap(array(
                'label'             => __('Block Group', 'acf'),
                'instructions'      => __('Will this field group be used as a template block for flexible content.', 'acf'),
                'type'              => 'true_false',
                'name'              => 'mg_block',
                'prefix'            => 'acf_field_group',
                'value'             => (isset($field_group['mg_block'])) ? $field_group['mg_block'] : '',
                'default_value'     => false, // true | false
                'ui'                => true, // true | false
            ));

            $blockTemplatePaths = glob(get_stylesheet_directory() . $blockTemplateFolder . '/*' . $blockTemplateExtension);

            foreach ($blockTemplatePaths as $path) {
                if (preg_match('/#\s+Block Name:\s+(.*)$/mi', file_get_contents($path), $match)) {
                    $templateName = ucwords(strtolower($match[1]));
                } else {
                    $templateName = basename($path);
                }

                $templateChoices[basename($path)] = $templateName;
            }

            if (isset($templateChoices) && $templateChoices) {
                asort($templateChoices);
                $templateChoices = array_merge(array('' => __('--Select--', 'acf')), $templateChoices);

                acf_render_field_wrap(array(
                    'label'             => __('Block Template', 'acf'),
                    'instructions'      => __('Select the tempate for this block.<br />Template path: <b>'.$blockTemplateFolder.'</b>', 'acf'),
                    'type'              => 'select',
                    'name'              => 'mg_block_template',
                    'prefix'            => 'acf_field_group',
                    'value'             => (isset($field_group['mg_block_template'])) ? $field_group['mg_block_template'] : '',
                    'choices'           => $templateChoices,
                ));
            }
        }

        /**
         * Add a dummy post type "Template Block" to the ACF post type rule to make sure the fields do not show up
         * directly in the post edit screen.
         *
         * @since MGPress 1.1
         *
         * @param array $result
         * @return array
         */
        public static function addAcfDummyBlockPostType($result)
        {
            $result['_mg_template_block'] = 'Template Block';
            return $result;
        }

        /**
         * Create an array with template block ACF group field as a twig variable to be able to match a template
         * filename and group keys.
         *
         * @since MGPress 1.1
         *
         * @param array $data
         * @return array
         */
        public static function addBlockTemplateToContext($data)
        {
            $values = [];
            foreach (acf_get_field_groups() as $group) {
                if (isset($group['mg_block']) && $group['mg_block'] && $group['mg_block_template']) {
                    $values[$group['key']] = $group['mg_block_template'];
                }
            }

            $data['block_templates'] = $values;
            return $data;
        }

        /**
         * Hide post editor for Flexible Content templates in admin.
         *
         * @since MGPress 1.1
         *
         * @return null
         */
        public static function hidePostEditor()
        {

            // get the Post ID.
            $postId = isset($_GET['post']) ? $_GET['post'] : (isset($_POST['post_ID']) ? $_POST['post_ID'] : null);
            if(!isset($postId) ) {
                return;
            }

            // get the name of the Page Template file.
            $templateFile = get_post_meta($postId, '_wp_page_template', true);

            // remove editor if template in FC list
            if (in_array($templateFile, self::$flexibleContentTemplates)) {
                remove_post_type_support('page', 'editor');
            }
        }
    }

    MGPressBlocks::init(isset($MgPressSettings['blocks']) ? $MgPressSettings['blocks'] : null);
}
