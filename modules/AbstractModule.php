<?php
/**
 * Content Module.
 * Do not use this class directly.
 * Extend it and override anything needed.
 */
abstract class AbstractWpContent
{
    protected
        $content,
        $wpdb,
        $template = null,
        $shouldRender = true,
        $rendered = null,
        $assetsQueued = false,
        $attributes = array(),
        $entries = array();

    /**
     * Shortcode Name (optional)
     * @var $shortcode string|false
     */
    public static $shortcode = false;

    /**
     * PostType Attributes (optional)
     * Defines a new Post Type.
     * @var $shortcode string|false
     */
    public static $postType = false;

    /**
     * Default Inline Assets.
     * @var $defaultAttributes array
     */
    public $assets = array();

    /**
     * With Additional Data
     * @var $with array
     */
    public $with = array('meta', 'images');

    /**
     * Default Content Transformations
     * @var $defaultAttributes array
     */
    public $transformations = array();

    /**
     * Default Content Type Parameters
     * @var $defaultAttributes array
     */
    public $defaultAttributes = array(
        'id' => null,
        'search' => null,
        'slug' => null,
        'type' => 'post',
        'sort' => 'DESC',
        'orderBy' => 'modified',
        'paginate' => 2,
        'cache' => false,
        'updateMetaCache' => false,
        'updateTermCache' => false,
        'template' => 'default',
    );

    /**
     * Should Render Pagination by Default?
     * @var $shouldRenderPagination bool
     */
    protected $shouldRenderPagination = false;

    /**
     * Attribute Input Map for clearly legible named parameters.
     * @var $inputMap array
     */
    protected $inputMap = array(
        'id' => 'p',
        'search' => 's',
        'slug' => 'name',
        'type' => 'post_type',
        'sort' => 'order',
        'orderBy' => 'orderby',
        'paginate' => 'posts_per_page',
        'cache' => 'cache_results',
        'updateMetaCache' => 'update_post_meta_cache',
        'updateTermCache' => 'update_post_term_cache',
        'template' => 'template',
    );

    /**
     * Install the Module.
     * @return void
     */
    public static function install() {
        if(static::$postType){
            register_post_type(static::$postType['type'], static::$postType);
        }
        if(static::$shortcode) {
            add_shortcode(static::$shortcode, new static);
        }
    }

    /**
     * Static Render Method (for direct usage instead of shortcode)
     * @param $attributes mixed
     * @param $content string
     * @return $this
     */
    public static function make($attributes, $content = "")
    {
        $instance = new static();
        $instance->shouldRender = false;
        $instance->__invoke($attributes, $content);
        return $instance;
    }

    /**
     * Invoke Method for Shortcode Usage
     * @param $attributes mixed
     * @param $content string
     * @return mixed
     * @throws \Throwable
     */
    public function __invoke($attributes, $content = "")
    {
        $this->filterAttributes($attributes);
        $this->filterContent($content);
        $this->executeQuery();
        $this->render();
        return $this->rendered;
    }

    /**
     * Filter the Shortcode Content.
     * (for future use if needed)
     * @param string $content
     * @return void
     */
    protected function filterContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * Transform Content
     * @param $post WP_Post
     * @return void
     */
    protected function transformContent(WP_Post $post)
    {
        foreach($this->transformations as $transformation){
            if(method_exists($this, $transformation)){
                $this->$transformation($post);
            }
        }
    }

    /**
     * Filter the Shortcode Attributes.
     * @param mixed $attributes
     * @return void
     */
    protected function filterAttributes($attributes)
    {

        if(count($attributes) && strpos($attributes[0], '=')){
            $this->attributes = shortcode_atts($this->defaultAttributes, $attributes);
        }else{
            //Merge the Attributes with the defaults.
            $this->attributes = array_merge($this->defaultAttributes, $attributes);
        }

        //Pluck the template setting from the attributes.
        if (isset($this->attributes['template'])) {
            $this->template = $attributes['template'];
            unset($this->attributes['template']);
        }

        //Pluck the pagination setting from the attributes.
        if (isset($this->attributes['pagination'])) {
            $this->shouldRenderPagination = $this->attributes['pagination'];
            unset($this->attributes['pagination']);
        }

        //Parse and Map the Attributes to WpQuery args.
        foreach ($this->attributes as $key => $value) {


            //Remove empty values.
            if(empty($value)){
                unset($this->attributes[$key]);
                continue;
            }

            //Use input map if required.
            if(isset($this->inputMap[$key])){
                unset($this->attributes[$key]);
                $this->attributes[$this->inputMap[$key]] = $value;
            }
            else{
                //Use argument as it is.
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * Execute the Database Query.
     * @return void
     */
    protected function executeQuery()
    {
        $this->attributes['paged'] = $this->getCurrentPage();
        $this->wpdb = new WP_Query($this->attributes);
        if (count($this->wpdb->posts)) {
            $this->entries = $this->wpdb->posts;
            foreach ($this->entries as $entry) {
                $entry->post_content = apply_filters('the_content', $entry->post_content);
                $entry->permalink = get_the_permalink($entry->ID);
                $this->getMeta($entry);
                $this->getImages($entry);
                $this->transformContent($entry);
            }
        }
    }

    /**
     * Get the Entry's Custom Meta Fields.
     * @param $entry WP_Post
     * @return void
     */
    protected function getMeta(WP_Post $entry)
    {
        if (in_array('meta', $this->with)) {
            $customMeta = get_post_meta($entry->ID);
            $entry->meta = (object) array();
            foreach($customMeta as $key => $meta){
                if(count($meta) > 1){
                    $entry->meta->$key = $meta;
                }else{
                    $entry->meta->$key = $meta[0];
                }
            }
        }
    }

    /**
     * Get the Entry's image attachments.
     * @param $entry WP_Post
     * @return void
     */
    protected function getImages(WP_Post $entry)
    {
        if (in_array('images', $this->with)) {
            $sizes = get_intermediate_image_sizes();
            $entry->images = get_attached_media('image', $entry->ID);
            if(count($entry->images)){
                foreach($entry->images as $index => $attachment){
                    $entry->images[$index] = (object) $attachment;
                    $attachment->resized = array();
                    foreach($sizes as $size){
                        $attachment->resized->$size = wp_get_attachment_image_src($attachment->ID, $size)[0];
                    }
                }
            }
        }
    }

    /**
     * Get the module directory name.
     * @return string
     */
    protected function moduleDirectory()
    {
        try {
            $reflector = new ReflectionClass(get_class($this));
            return basename(dirname($reflector->getFileName()));
        } catch (\Throwable $e) {
            wp_die($e->getMessage());
        }
    }

    /**
     * Render the view.
     * @return void
     */
    public function render()
    {
        if($this->shouldRender){
            $template = 'modules/' . $this->moduleDirectory() . '/views/' . $this->template . '.php';
            $path = locate_template($template, false, false);
            //Include the Template in Isolation & Echo the String.
            ob_start();
            require($path);
            $this->rendered = ob_get_clean();
            $this->renderAssets();
        }
        wp_reset_postdata();
    }

    /**
     * Render the assets.
     * @return void
     */
    protected function renderAssets(){

        if($this->assetsQueued) return;
        add_action('wp_print_footer_scripts', function(){
            try{
                $assetPath =  get_stylesheet_directory().'/modules/'.$this->moduleDirectory().'/assets/';
                foreach($this->assets as $asset){
                    $path = $assetPath.$asset;
                    if(strpos($asset, '.js') && $script = file_get_contents($path)){
                        echo '<script type="text/javascript">'.$script.'</script>';
                    }elseif(strpos($asset, '.css') && $style = file_get_contents($path)){
                        echo '<style type="text/css">'.$style.'</style>';
                    }
                }
            }catch (\Exception $e){
                wp_die($e->getMessage());
            }
        }, (PHP_INT_MAX-100));
        $this->assetsQueued = true;
    }

    /**
     * Has Pagination Previous?
     * @return string
     */
    public function hasPrevPage()
    {
        return $this->attributes['paged'] > 1 && ($this->attributes['paged'] - 1) > 0;
    }

    /**
     * Has Pagination Next?
     * @return string
     */
    public function hasNextPage()
    {
        return $this->wpdb->max_num_pages > 1 && $this->attributes['paged'] < $this->wpdb->max_num_pages;
    }

    /**
     * Has Pagination?
     * @return string
     */
    public function hasPagination(){
        return $this->shouldRenderPagination && ($this->hasPrevPage() || $this->hasNextPage());
    }

    /**
     * Get Current Page
     * @return int
     */
    public function getCurrentPage(){
        $page = 1;
        if (get_query_var('paged')) {
            $page = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $page = get_query_var('page');
        }
        return $page;
    }

    /**
     * Get Advanced Pagination.
     * @return string
     */
    public function paginationAdvanced()
    {
        if(!$this->hasPagination()) return null;

        $urlBase = esc_url(get_pagenum_link($this->wpdb->max_num_pages));
        $urlBase = str_replace($this->wpdb->max_num_pages, '%#%', $urlBase);

        $links = '<div class="pagination">';

        $links .= paginate_links(array(
            'base' => $urlBase,
            'total' => $this->wpdb->max_num_pages,
            'current' => max(1, $this->attributes['paged']),
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'plain',
            'end_size' => 2,
            'mid_size' => 1,
            'prev_next' => true,
            'add_args' => true,
            'add_fragment' => '',
        ));

        $links .= '</div>';
        return $links;
    }

    /**
     * Get Simple Pagination.
     * @return string
     */
    public function paginationSimple()
    {
        if(!$this->hasPagination()) return null;

        $links = '<div class="pagination">';

        if ($this->hasPrevPage()) {
            $prevLink = previous_posts(false);
            $links .= '<a href="'.$prevLink.'" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Previous</a>';
        }

        if ($this->hasNextPage()) {
            $nextLink =  next_posts($this->wpdb->max_num_pages, false);
            $links .= '<a href="'.$nextLink .'" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Next</a>';
        }

        $links .= '</div>';
        return $links;
    }
}