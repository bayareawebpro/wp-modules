# WpModules
Modular Approach to WP Content

## Install the CustomPostType & Shortcode:
```
/** Content Type Modules. */
require_once __DIR__.'/modules/AbstractModule.php';
require_once __DIR__.'/modules/testimonials/module.php';
TestimonialsModule::install();
```

## Render the Module in a Template:
Render Directly in Template with custom parameters.
```
TestimonialsModule::make(array(
    'template' => 'aside',
    'search' => 'My New',
    'paginate' => 3,
    'pagination' => true,
))->render();
```

## Render the Module in a post:
Add a shortcode for the Content Editor.
```
[module_testimonials "template"="main" search="Find me"]
```
## Module File Structure
```
modules
AbstractModule.php
    testimonials
        module.php
        assets
            script.js
            styles.css
        views
            aside.php
            main.php
```

## Define a Module:
```
class TestimonialsModule extends AbstractWpContent{
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
     * Replace a string in the header.
     * @param $post WP_Post
     * @return void
     */
    protected function replaceHeader(WP_Post $post)
    {
        $post->post_title = str_replace('My New Review', '', $post->post_title);
    }
}
```
