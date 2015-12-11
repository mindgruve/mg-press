<?php


class QueryProxy
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var
     */
    protected $argsHistory;
    

    /**
     * @var string
     */
    protected $order;

    /**
     * @var array
     */
    protected $posts;

    /**
     * @var array
     */
    protected $pagination;

    public function __construct($postType = null)
    {
        $this->args    = array();
        $this->page    = 1;
        $this->offset  = 0;
        $this->limit   = 10;
        $this->orderBy = 'title';
        $this->order   = 'ASC';

        if ($postType) {
            $this->args['post_type'] = $postType;
        }
    }

    public function setArg($key, $value)
    {
        $this->args[$key] = $value;

        return $this;
    }

    public function setPage($page)
    {
        $this->args['paged'] = $page;

        return $this;
    }

    public function setOrderBy($orderBy)
    {
        $this->args['orderby'] = $orderBy;

        return $this;
    }

    public function setOrder($order)
    {
        $this->args['order'] = $order;
        $this->order         = $order;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->args['posts_per_page'] = max($limit, -1);

        return $this;
    }

    public function parsePageFromUrl()
    {
        $this->setPage(max(1, get_query_var('paged')));

        return $this;
    }

    public function getPosts()
    {
        /** By default wp will return all posts... override functionality so it returns no posts */
        if (isset($this->args['post__in']) && empty($this->args['post__in'])) {
            $this->posts = array();
            $this->pagination = array();
            return $this->posts;
        }
        
        if($this->args == $this->argsHistory){
            return $this->posts;
        }
        
        // push args to history
        $this->argsHistory = $this->args;
        
        //  query posts
        $wpQuery = new WP_Query($this->args);
        $this->posts = $this->generateProxies($wpQuery->get_posts());

        global $paged;
        global $wp_rewrite;
        
        $this->pagination                   = $this->calculatePagination(array('mid_size' => '1'), $wpQuery, $wp_rewrite, $paged);
        $this->pagination['found_posts']    = $wpQuery->found_posts;
        $this->pagination['max_num_pages']  = $wpQuery->max_num_pages;
        $this->pagination['post_count']     = $wpQuery->post_count;
        $this->pagination['posts_per_page'] = isset($wpQuery->query['posts_per_page']) ? $wpQuery->query['posts_per_page'] : 10;
        $this->pagination['page']           = isset($wpQuery->query['paged']) ? $wpQuery->query['paged'] : 1;
        $this->pagination['lower_index']    =  ($this->pagination['page']-1)*$this->pagination['post_count']+1;
        $this->pagination['upper_index']    =  ($this->pagination['page'])*$this->pagination['post_count'];

        return $this->posts;
    }

    /**
     * Duplicated logic for Timber Pagination uses args instead of globals to allow indirection from the wordpress loop
     * @param $prefs
     * @param $wp_query
     * @param $wp_rewrite
     * @param $paged
     * @return mixed
     */
    protected function calculatePagination($prefs, $wp_query, $wp_rewrite, $paged)
    {
        $args = array();
        $args['total'] = ceil($wp_query->found_posts / $wp_query->query_vars['posts_per_page']);
        if ($wp_rewrite->using_permalinks()){
            $url = explode('?', get_pagenum_link(0));
            if (isset($url[1])){
                parse_str($url[1], $query);
                $args['add_args'] = $query;
            }
            $args['format'] = 'page/%#%';
            $args['base'] = trailingslashit($url[0]).'%_%';
        } else {
            $big = 999999999;
            $args['base'] = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
        }
        $args['type'] = 'array';
        $args['current'] = max( 1, get_query_var('paged') );
        $args['mid_size'] = max(9 - $args['current'], 3);
        $args['prev_next'] = false;
        if (is_int($prefs)){
            $args['mid_size'] = $prefs - 2;
        } else {
            $args = array_merge($args, $prefs);
        }
        $data['pages'] = TimberHelper::paginate_links($args);
        $next = get_next_posts_page_link($args['total']);
        if ($next){
            $data['next'] = array('link' => untrailingslashit($next), 'class' => 'page-numbers next');
        }
        $prev = previous_posts(false);
        if ($prev){
            $data['prev'] = array('link' => untrailingslashit($prev), 'class' => 'page-numbers prev');
        }
        if ($paged < 2){
            $data['prev'] = '';
        }
        return $data;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getItems()
    {
        return $this->getPosts();
    }

    public function getPagination()
    {
        return $this->pagination;
    }

    public function generateProxies(array $posts)
    {
        $return = array();
        foreach ($posts as $post) {
            $return[] = $this->generateProxy($post);
        }

        return $return;
    }
    
    static public function generateProxy($post)
    {
        /** @var TimberPost $post */
        switch ($post->post_type) {
            case 'post':
                return new PostTimberProxy($post);
                break;
            case 'page':
                return new PageTimberProxy($post);
                break;
            default:
                return $post;
        }
    }
}
