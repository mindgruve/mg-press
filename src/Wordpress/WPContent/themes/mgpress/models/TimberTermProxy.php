<?php

class TimberTermProxy
{

    /**
     * @var TimberTerm
     */
    protected $term;

    public function __construct($termOrID = null)
    {
        if ($termOrID instanceof TimberTerm) {
            $this->term = $termOrID;
        } elseif (is_numeric($termOrID)) {
            $this->post = new TimberTerm($termOrID);
        } else {
            $this->post = new TimberTerm();
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func(array($this->term, $name), $arguments);
    }

    public function __get($name)
    {
        return $this->term->$name;
    }

    public function __set($name, $value)
    {
        $this->term->$name = $value;
    }

    function __toString()
    {
        return $this->term->name;
    }


    public function get_edit_url()
    {
        return $this->term->get_edit_url();
    }


    public function get_meta_field($field_name)
    {
        return $this->term->get_meta_field($field_name);
    }

    public function get_path()
    {
        return $this->term->get_path();
    }

    public function get_link()
    {
        return $this->term->get_link();
    }

    public function get_posts($numberposts = 10, $post_type = 'any', $PostClass = '')
    {
        return $this->term->get_posts($numberposts, $post_type, $PostClass);
    }

    public function get_children()
    {
        return $this->term->get_children();
    }

    function update($key, $value)
    {
        $this->term->update($key, $value);
    }

    public function children()
    {
        return $this->term->children();
    }

    public function edit_link()
    {
        return $this->term->edit_link();
    }

    public function get_url()
    {
        return $this->term->get_url();
    }

    public function link()
    {
        return $this->term->link();
    }

    public function meta($field_name)
    {
        return $this->term->meta($field_name);
    }

    public function path()
    {
        return $this->term->path();
    }

    public function posts($numberposts_or_args = 10, $post_type_or_class = 'any', $post_class = '')
    {
        return $this->term->posts($numberposts_or_args, $post_type_or_class, $post_class);
    }

    public function title()
    {
        return $this->term->title();
    }

    public function url()
    {
        return $this->term->url();
    }

    function get_page($i)
    {
        return $this->term->get_page($i);
    }

} 
