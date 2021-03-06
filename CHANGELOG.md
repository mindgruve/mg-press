CHANGELOG for MG Press theme
============================

This changelog references the relevant changes (feature, security, config, logic, data, content, style and bug updates) done
for the MG Press WordPress theme.

* 1.0.0 (2017-11-28)
  * Feature: Initial release

* 1.0.1 (2018-04-06)
  * Feature: Add comment submit flash messages for user feedback
  * Logic: Refactor comment templates to match WordPress comment_form() logic and hooks
  * Logic: Move get_sidebar action from base template to layout with sidebar

* 1.0.2 (2018-05-02)
  * Content: Add MIT License
  * Bug: Remove Whoops error handling from WordPress admin

* 1.0.3 (2018-05-24)
  * Logic: Autoload PHP files in /models directory
  * Logic: Load list- and detail- controllers on demand from /controllers directory
  * Bug: Handle post not found exception for list views (archives)

* 1.0.4 (2018-06-17)
  * Bug: Bug fix to correctly call add_theme_support with 1 or 2 arguments

* 1.1.0 (2019-01-04)
  * Feature: Add Advanced Custom Fields flexible layout block system.
  * Feature: Add WP admin table column management features. 
  * Logic: Add taxonomy archive controller option.
  * Logic: Load post after controller for single post views.
  * Bug: Fix assets class skipping minimized resources. 
