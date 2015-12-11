<?php


class TimberProxy
{

    /** @var  TimberPost $post */
    protected $post;

    /** Proxy values */
    protected $author;
    protected $modifiedAuthor;
    protected $categories;
    protected $comments;
    protected $pagedContent;
    protected $link;
    protected $name;
    protected $pagination;
    protected $parent;
    protected $path;
    protected $permalink;
    protected $tags;
    protected $thumbnail;
    protected $title;

    public function __construct($postOrID = null)
    {
        if ($postOrID instanceof TimberPost) {
            $this->post = $postOrID;
        } elseif (is_numeric($postOrID)) {
            $this->post = new TimberPost($postOrID);
        } elseif ($postOrID instanceof WP_Post) {
            $this->post = new TimberPost($postOrID->ID);
        } else {
            $this->post = new TimberPost();
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func(array($this->post, $name), $arguments);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'parent':
                return new TimberProxy($this->post->parent());
        }


        return $this->post->$name;
    }

    public function __set($name, $value)
    {
        $this->post->$name = $value;
    }

    /** Timber Proxy Methods */

    public function author()
    {
        if (!$this->title) {
            $this->title = $this->post->author();
        }

        return $this->title;
    }

    public function modified_author()
    {
        if (!$this->modifiedAuthor) {
            $this->modifiedAuthor = $this->post->modified_author();
        }

        return $this->modifiedAuthor;
    }

    public function categories()
    {
        if (!$this->categories) {
            $this->categories = $this->post->categories();
        }

        return $this->categories;
    }

    public function children($post_type = 'any', $childPostClass = false)
    {
        $children = $this->post->children($post_type, $childPostClass);

        $return = array();
        foreach ($children as $child) {
            $return[] = new TimberProxy($child);
        }

        return $return;
    }

    public function comments()
    {
        if (!$this->comments) {
            $this->comments = $this->post->comments();
        }

        return $this->comments;
    }

    public function content($page = 0)
    {
        return $this->post->content($page);
    }

    public function paged_content()
    {
        return $this->post->paged_content();
    }

    public function date($date_format = '')
    {
        return $this->post->date($date_format);
    }

    public function link()
    {
        if (!$this->link) {
            $this->link = $this->post->link();
        }

        return $this->link;
    }

    public function meta($field_name = null)
    {
        return $this->post->meta($field_name);
    }

    public function modified_date($date_format = '')
    {
        return $this->post->modified_date($date_format);
    }

    public function modified_time($time_format = '')
    {
        return $this->post->modified_time($time_format);
    }

    public function name()
    {
        return $this->get_title();
    }

    public function next($in_same_cat = false)
    {
        return $this->post->next($in_same_cat);
    }

    public function pagination()
    {
        if (!$this->pagination) {
            $this->pagination = $this->post->pagination();
        }

        return $this->pagination;
    }

    public function parent()
    {
        if (!$this->parent && $this->post->parent()) {
            $this->parent = new TimberProxy($this->post->parent());
        }

        return $this->parent;
    }

    public function path()
    {
        if (!$this->path) {
            $this->path = $this->post->path();
        }

        return $this->path;
    }

    public function permalink()
    {
        if (!$this->permalink) {
            $this->permalink = $this->post->permalink();
        }

        return $this->permalink;
    }

    public function prev($in_same_cat = false)
    {
        return $this->post->prev($in_same_cat);
    }

    public function terms($tax = '')
    {
        $return = array();
        $terms  = $this->post->terms($tax);
        foreach ($terms as $term) {
            $return[] = new TimberTermProxy($term);
        }

        return $return;
    }

    public function tags()
    {
        if (!$this->tags) {
            $return = array();
            $tags   = $this->post->tags();
            foreach ($tags as $tag) {
                $return = new TimberTermProxy($tag);
            }
            $this->tags = $return;
        }

        return $this->tags;
    }

    public function thumbnail()
    {
        if (!$this->thumbnail) {
            $this->thumbnail = $this->post->thumbnail();
        }

        return $this->thumbnail;
    }

    public function title()
    {
        return $this->get_title();
    }

    public function get_preview($len = 50, $force = false, $readmore = 'Read More', $strip = true)
    {
        return $this->post->get_preview($len, $force, $readmore, $strip);
    }

    public function get_title()
    {
        if (!$this->title) {
            $this->title = $this->post->title();
        }

        return $this->title;
    }
} 
