<?php
/**
 * MgRecipeModel
 *
 * A WordPress custom post type definition
 *
 * @package MG Recipes
 * @author kchevalier@mindgruve.com
 * @version 1.0
 */

defined('ABSPATH') or die();

class ACFFieldsProxy
{

    /* METHODS */

    /**
     * Init
     *G
     * @return null
     */
    public static function init()
    {
        self::loadACFData();
    }


    public static function loadACFData()
    {
        if(function_exists('acf_add_local_field_group')){
            $dir = new DirectoryIterator( get_template_directory() . '/models/json/' );
            foreach( $dir as $file )
            {
                // var_dump( $file );
                if ( !$file->isDot() && 'json' == $file->getExtension() )
                {
                    $array = json_decode( file_get_contents( $file->getPathname() ), true );
                    acf_add_local_field_group( $array );
                }
            }
        }
    }
}
