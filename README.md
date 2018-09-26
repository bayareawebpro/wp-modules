# WpModules
Modular Approach to WP Content

### Install the CustomPostType & Shortcode:
```
/** Content Type Modules. */
require_once __DIR__.'/modules/AbstractWpModule.php';
require_once __DIR__.'/modules/testimonials/TestimonialsModule.php';
TestimonialsModule::install();
```

### Render the Module in a Template:
Render Directly in Template with custom parameters.
```
TestimonialsModule::make(array(
    'template' => 'aside',
    'search' => 'My New',
    'paginate' => 3,
    'pagination' => true,
))->render();
```

### Render the Module in a post:
Add a shortcode for the Content Editor.
```
[module_testimonials "template"="main" search="Find me"]
```
### Module File Structure
```
modules
    AbstractWpModule.php
    testimonials
        TestimonialsModule.php
        assets
            script.js
            styles.css
        views
            aside.php
            main.php
```

## Define a Module:
```
class TestimonialsModule extends AbstractWpModule{
 /**
     * Shortcode Name (optional)
     * @var $shortcode string|false
     */
    public static $shortcode = 'module_testimonials';
    
    /**
     * PostType Attributes (optional)
     * Defines a new Post Type.
     * @var $shortcode string|false
     */
    public static $postType = array(
        'type'                  => 'module_testimonials',
        'label'                 => 'Testimonials',
        'description'           => 'Post Type Description',

        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'menu_position'         => 5,
        'can_export'            => true,

        'public'                => true,
        'publicly_queryable'    => true,
        'exclude_from_search'   => false,

        'capability_type'       => 'page',
        'hierarchical'          => false,
        'has_archive'           => true,

        'supports' => array(
            'title',
            'editor',
            'author',
            'thumbnail'
        ),

        'taxonomies' => array(
            'category',
            'post_tag'
        ),
    );

    /**
     * Default Inline Assets.
     * @var $defaultAttributes array
     */
    public $assets = array(
        'script.js',
        'styles.css'
    );

    /**
     * With Additional Data
     * @var $with array
     */
    public $with = array('meta', 'images');

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
     * Default Content Type Parameters
     * Apply transformations to every post object...
     * @var $defaultAttributes array
     */
    public $transformations = array(
        'replaceHeader'
    );

    /**
     * Transformation: Replace a string in the header.
     * @param $post WP_Post
     * @return void
     */
    protected function replaceHeader(WP_Post $post)
    {
        $post->post_title = str_replace('My New Review', '', $post->post_title);
    }
}
```
