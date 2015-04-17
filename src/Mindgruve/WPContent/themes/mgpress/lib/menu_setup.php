<?php

/**
 * Menus
 */
class DriscollHealthPlanMenus
{
    protected static $menusArray = array(
        // add more menus here...

    );

    public static function init()
    {
        // Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus)
        register_nav_menus(self::$menusArray);
        add_filter('acf/load_field/name=wp_menu', array('DriscollHealthPlanMenus', 'acf_load_wp_menu_field_choices'));
    }

    public static function acf_load_wp_menu_field_choices($field)
    {

        // reset choices
        $field['choices'] = array();

        foreach (self::$menusArray as $key => $value) {

            $field['choices'][$key] = $value;

        }

        // return the field
        return $field;

    }
}

DriscollHealthPlanMenus::init();
