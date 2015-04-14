# mg-press
Wordpress + Composer + Twig


## Installation
  
    1) Install Using Composer
    composer create-project mindgruve/mg-press path/to/project "~4.1"
  
    2) Ignore the following files and directories:
    application/config/wpConfig.yml
    www/wp
    vendor
  
    3) Update your configuration file located at config/wpConfig.yml with your database parameters, theme name, and updated secure hashes.


## Included Libraries
Wordpress 4.1.1 - https://wordpress.org/   
Timber - http://upstatement.com/timber/   
Twig - http://twig.sensiolabs.org/   
Symfony YAML - http://symfony.com/doc/current/components/yaml/introduction.html   
Symfony FileSystem - http://symfony.com/doc/current/components/filesystem/introduction.html   
Symfony Finder - http://symfony.com/doc/current/components/finder.html   
Woops - http://filp.github.io/whoops/   
Wordpress Search Replace Database - https://interconnectit.com/products/search-and-replace-for-wordpress-databases/   
Symfony Console Component - http://symfony.com/doc/current/components/console/introduction.html     
WP CLI - http://wp-cli.org/   

## Project Layout   
composer.json   
README.md   
config/wpConfig.yml   
src/Mindgruve/WPContent   
src/Mindgruve/WPContent/languages   
src/Mindgruve/WPContent/plugins   
src/Mindgruve/WPContent/mu-plugins   
src/Mindgruve/WPContent/themes   
www/wp  
vendor 
