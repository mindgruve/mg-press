
# MGPress Starter WordPress Theme

Welcome to MG Press, a WordPress starter theme that uses the [Timber](https://wordpress.org/plugins/timber-library/) plugin to add support for [Twig](https://twig.symfony.com/) template files. It allows developers to make use of the [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) pattern (especially the *View-Controller*) while creating a WordPress theme, separating the business logic from the view logic.

## Installing the Theme

Install this theme as you would any other, and be sure the Timber plugin is activated. But hey, let's break it down into some bullets:

1. Make sure you have installed the plugin for the [Timber Library](https://wordpress.org/plugins/timber-library/) (and Advanced Custom Fields - they [play quite nicely](http://timber.github.io/timber/#acf-cookbook) together). 
2. Download the zip for this theme (or clone it) and move it to `wp-content/themes` in your WordPress installation. 
4. Activate the theme in Appearance >  Themes.
5. Do your thing! And read [the docs](https://timber.github.io/docs/).

## What's here?

`*.php` are the normal WordPress PHP template files that correspond to the [template hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/), such as `index.php` and `single.php`. These files act as the *controllers*: the system (WordPress) determines which file to load based on the URL; runs any necessary business logic; adds any data for the view layer to the `$context` variable; determines which view to render; renders the view by passing it the `$context` variable and sending the output to the browser. At the end of each PHP template, you'll notice a `Timber::render()` function whose first parameter is the Twig file(s) and the second parameter is the `$context` variable.

`views/` contains all of your Twig templates. The PHP template files determine which view to render.
* `views/base.twig` is the base template that provides a generic HTML document structure. This template should not render content directly.
* `views/layout/` contains templates that apply structural layout to a page, but do not generally render content directly themselves, such as `full-width.twig` and `left-sidebar.twig`
* `views/detail/` contains views for rendering single post types and pages, such as `post.twig` and `page.twig`.
* `views/list/` contains views for rendering lists of post types, such as `post.twig`, `category.twig` and `search.twig`.
* `views/template/` contains views for rendering custom templates, just add "Template Name: Some Custom Name" to the comment in the file header, like normal [WordPress custom templates](https://developer.wordpress.org/themes/template-files-section/page-template-files/#creating-custom-page-templates-for-global-use).
* `views/summary/` contains template includes for post type and page teasers, used in list views, such as `post.twig` and `page.twig`.
* `views/block/` contains template includes for rendering block-level elements.
* `views/menu/` contains template includes for rendering menus.
* `views/form/` contains template includes for rendering forms.
* `views/include/` contains template includes for rendering various HTML snippets, such as `header.twig` and `pagination.twig`.
* `views/exception/` contains views for exception handling, such as `404.twig`. 

`assets/` contains static assets, such as CSS, JavaScript, images, fonts, static HTML, etc.

`lib/` is where we keep the PHP library for MGPress features, like handling comments or asset management. Unless you know what you are doing it's probably best to leave these alone.

`custom/` contains custom PHP code for this theme - it serves the same function as WordPress's default `functions.php`. PHP files in this directory will be autoloaded by the `functions.php` file. You can also override classes from `lib/` here.

`controllers/` contains custom controllers for additional business logic that the PHP template files may not provide.

`lang/` location for the `.mo` translation files, specified by `load_theme_textdomain()` in `lib/site.php`. (optional)

## How Does This Work?

If you were expecting a normal WordPress theme, this may look a little strange - don't worry, it's actually easier to work with WordPress this way. Why? First of all, it separates the business logic from the presentation layer. Also, you get to use Twig. And - you don't have to work with [The Loop](https://codex.wordpress.org/The_Loop).

#### Example 1
 
In your WordPress *Settings* > *Reading* section you have the *Posts Page* set to `/blog`. When a visitor visits `/blog`, WordPress uses its template hierarchy to load the `archive.php` file. Inside the `archive.php` file is logic that creates a prioritized list of Twig templates from the `views/list/` directory to try to load, depending on the type of archive. In this case, it's a basic blog listing page, so `views/list/post.twig` has the highest priority.

As it happens, `views/list/post.twig` exists, so Timber passes the data (`$context`) to the template and renders it. The `views/list/post.twig` template defines the `content` block, which contains presentation logic that loops over the `posts` variable (from `$context`) and renders summary template includes from `views/summary/post.twig`. The `views/list/post.twig` template also extends the `views/layout/full-width.twig` template.

The `views/layout/full-width.twig` template applies structural markup for the page layout, displays the `content` block, defines the `layout` block, and extends the `views/base.twig` template. The `views/base.twig` template applies the base HTML document structure, such as the `<html>` and `<body>` tags, includes the `views/include/header.twig` and `views/include/footer.twig` snippets, and displays the `layout` block. Since `views/base.twig` doesn't extend any templates, the rendering is complete and the output is sent to the browser.

#### Example 2

Your website has a [Custom Post Type](https://codex.wordpress.org/Post_Types) called *Products* under the path `/products`. When a visitor visits a single product page at `/products/product-1` WordPress uses the template hierarchy to load the `single.php` file. That file creates a prioritized list of Twig templates from the `views/detail/` directory to try to load. The first template in the list follows the format "{POST-TYPE}-{SLUG}.twig", so it becomes `views/detail/product-product-1.twig`. This template does not exist, so it tries the next one, `views/detail/product.twig`. This one exists, so `single.php` passes the template the data (`$context`) and renders the view.
 
The `views/detail/product.twig` displays the content of Product 1 and extends the `views/layout/left-sidebar.twig` template. The `views/layout/left-sidebar.twig` template applies structural markup for the page layout - including rendering a dynamic sidebar in the left column - and extends the `views/base.twig` template, which in turn provides HTML document structural markup, the header and footer, etc. which is sent to the browser. 

## View Hierarchy

When choosing which Twig template to render, the system closely follows the default [WordPress template hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/), with some modifications:

* Archive Page
  * Author Archive
    * `views/list/author-{nicename}.twig`
    * `views/list/author-{id}.twig`
    * `views/list/author.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
  * Category Archive
    * `views/list/category-{slug}.twig`
    * `views/list/category-{id}.twig`
    * `views/list/category.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
  * Custom Post Type Archive
    * `views/list/{post-type}.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
  * Taxonomy Archive
    * `views/list/taxonomy-{slug}-{term}.twig`
    * `views/list/taxonomy-{slug}.twig`
    * `views/list/taxonomy.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
  * Date Archive
    * `views/list/date.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
  * Tag Archive
    * `views/list/tag-{slug}.twig`
    * `views/list/tag-{id}.twig`
    * `views/list/tag.twig`
    * `views/list/archive.twig`
    * `views/list/post.twig`
    * `views/list/single.twig`
    * `views/index.twig`
* Singular Page
  * Attachment Post
    * `views/detail/{mimetype}-{subtype}.twig`
    * `views/detail/{subtype}.twig`
    * `views/detail/{mimetype}.twig`
    * `views/detail/attachment.twig`
    * `views/detail/post.twig`
    * `views/detail/single.twig`
    * `views/index.twig`
  * Custom Post
    * `views/detail/{posttype}-{slug}.twig`
    * `views/detail/{posttype}.twig`
    * `views/detail/post.twig`
    * `views/detail/single.twig`
    * `views/index.twig`
  * Blog Post
    * `views/detail/post-{slug}.twig`
    * `views/detail/post.twig`
    * `views/detail/single.twig`
    * `views/index.twig`
  * Static Page
    * `views/template/{templatename}.twig`
    * `views/detail/page-{slug}.twig`
    * `views/detail/page-{id}.twig`
    * `views/detail/page.twig`
    * `views/index.twig`
  * Site Front Page
    * Page shown on front
      * `views/detail/front-page.twig`
      * `views/template/{templatename}.twig`
      * `views/detail/page-{slug}.twig`
      * `views/detail/page-{id}.twig`
      * `views/detail/page.twig`
      * `views/index.twig`
    * Posts shown on front
      * `views/list/home.twig`
      * `views/list/post.twig`
      * `views/list/single.twig`
      * `views/index.twig`
* Error 404 Page
  * `views/exception/404.twig`
  * `views/index.twig`
* Search Results Page
  * `views/list/search.twig`
  * `views/list/post.twig`
  * `views/list/single.twig`
  * `views/index.twig`

## Custom Twig Extensions

In addition to the standard [Twig API](https://twig.symfony.com/doc/1.x/), the following custom features are available to use in the templates.

### Functions

#### Asset Management

##### `add_stylesheet()`

Add Stylesheet to HTML Output.

```
* @param string  $handle        (required) A unique string ID to ensure the resource is loaded only once.
* @param string  $src           (required) The public path to the resource.
* @param array   $deps          Array of handles of any stylesheets that this stylesheet depends on.
* @param string  $ver           String specifying the stylesheet version number, if it has one.
* @param string  $media         String specifying the media for which this stylesheet has been defined. [Ex: 'all', 'screen', 'handheld', 'print']
* @param bool    $http2         True: load resource immediately in HTML; False: register resource with WordPress to load in the header or footer. Default is False.
* @param bool    $inline        True: print the contents of the file directly in HTML; False: load the file as an external resource. Default is False.
* @param array   $environments  Array of environments to restrict resource loading to, default is to load in all environments. ['dev', 'test', 'prod']
* @param string  $group         A group ID to aid resource grouping; assets can share groups.
* @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
* @return null
```

##### `add_style()`

Add Style to HTML Output.

```
* @param string  $handle        (required) A unique string ID to ensure the resource is loaded only once.
* @param string  $src           (required) String of CSS styles to output. [Ex: "p { color: #ff0000; }"]
* @param string  $media         String specifying the media for which this stylesheet has been defined. [Ex: 'all', 'screen', 'handheld', 'print']
* @param bool    $http2         True: print styles immediately in HTML; False: register styles with WordPress to load in the header or footer. Default is False.
* @param array   $environments  Array of environments to restrict style output to, default is to output in all environments. ['dev', 'test', 'prod']
* @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
* @return null
```

##### `add_script_file()`

Add Script File to HTML Output.

```
* @param string  $handle        (required) A unique string ID to ensure the resource is loaded only once.
* @param string  $src           (required) The public path to the resource.
* @param array   $deps          An array of registered script handles this script depends on.
* @param string  $ver           String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes.
* @param bool    $http2         True: load resource immediately in HTML; False: register resource with WordPress to load in the header or footer. Default is False.
* @param bool    $inline        True: print the contents of the file directly in HTML; False: load the file as an external resource. Default is False.
* @param array   $environments  Array of environments to restrict resource loading to, default is to load in all environments. ['dev', 'test', 'prod']
* @param string  $group         A group ID to aid resource grouping; assets can share groups.
* @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
* @return null
```

##### `add_script()`

Add Script to HTML Output.

```
* @param string  $handle        (required) A unique string ID to ensure the script is output only once.
* @param string  $src           (required) String of JavaScript to output. [Ex: "var foo = 'bar';"]
* @param bool    $http2         True: print script immediately in HTML; False: register script with WordPress to load in the header or footer. Default is False.
* @param array   $environments  Array of environments to restrict script output to, default is to output in all environments. ['dev', 'test', 'prod']
* @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
* @return null
```

##### `localize_script()`

Localize Script.

```
* @param string  $handle  (required) The registered script handle you are attaching the data for.
* @param string  $name    (required) The name of the variable which will contain the data.
* @param array   $data    (required) The data itself.
* @return null
```

#### Comments

##### `can_comment()`

Check if user can comment on a Post or Comment.

```
* @param \Timber\Post     $post     (required) The post to check if comments are allowed.
* @param \Timber\Comment  $comment  If using nested comments, the comment to check if comments are allowed.
* @return bool
```

##### `get_comments()`

Get list of child comments from Post.

```
* @param Timber\Post  $post  (required) The post to return comments for.
* @return array Timber\Comment
```

##### `next_comments_link()`

Create link for next comments.

```
* @param \Timber\Post  $post   (required) The parent post of comments to paginate.
* @param string        $label  A label to use as the link anchor text.
* @return bool|string
```

##### `previous_comments_link()`

Create link for previous comments.

```
* @param \Timber\Post  $post   (required) The parent post of comments to paginate.
* @param string        $label  A label to use as the link anchor text.
* @return bool|string
```

### Filters

At this time there are no additional custom filters.

## Other Resources

The [main Timber Wiki](https://github.com/jarednova/timber/wiki) is super great, so reference those often. Also, check out these articles and projects for more info:

* [This branch](https://github.com/laras126/timber-starter-theme/tree/tackle-box) of the starter theme has some more example code with ACF and a slightly different set up.
* [Twig for Timber Cheatsheet](http://notlaura.com/the-twig-for-timber-cheatsheet/)
* [Timber and Twig Reignited My Love for WordPress](https://css-tricks.com/timber-and-twig-reignited-my-love-for-wordpress/) on CSS-Tricks
* [A real live Timber theme](https://github.com/laras126/yuling-theme).
* [Timber Video Tutorials](http://timber.github.io/timber/#video-tutorials) and [an incomplete set of screencasts](https://www.youtube.com/playlist?list=PLuIlodXmVQ6pkqWyR6mtQ5gQZ6BrnuFx-) for building a Timber theme from scratch.

