# WpModules
Modular Approach to WP Content

Features Included:
* WpQuery (accepts any standard argument or the ones provided)
* Entry Transformations (transform each entry before it’s sent to the template)
* Multiple Templates (specify a different template for each context)
* Meta (automatically appended as properties)
* Images (automatically appended as properties)
* Assets Pushed to Footer & Inlined
* Automatic Pagination


### Install the CustomPostType & Shortcode:
Require the Abstract Module Class and your Modules which extend it in the functions file.
```
/** Content Type Modules. */
require_once __DIR__.'/modules/AbstractWpModule.php';
require_once __DIR__.'/modules/testimonials/TestimonialsModule.php';
TestimonialsModule::install();
```

### Render the Module in a Template:
Render directly in template with custom parameters.
```
TestimonialsModule::make(array(
    'template' => 'aside',
    'search' => 'My New',
    'paginate' => 3,
    'pagination' => true,
));
```

### Render the Module in a post:
Add a shortcode to the content editor.
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

### Working with Views
Note the context of _$this_ is the module class itself.  
Feel free to add additional methods if needed.
```
<div class="module-testimonials">
    <?php if ($this->entries): ?>
        <ul class="list-group testimonials-list-shortcode">
            <?php foreach ($this->entries as $postObject): ?>
                <li class="list-group-item">
                    <?= $postObject->post_title; ?>
                    <?= $postObject->post_content; ?>

                    <h6>Images</h6>
                    <?php foreach ($postObject->images as $image): ?>
                        <p>Image Name: <?= $image->post_title; ?></p>
                        <?= $image->resized->thumbnail; ?>
                        <?= $image->resized->medium; ?>
                        <?= $image->resized->medium_large; ?>
                        <?= $image->resized->large; ?>
                        <?= $image->resized->homepage; ?>
                    <?php endforeach; ?>

                    <h6>Custom Meta</h6>
                    <?= $postObject->meta->_edit_last; ?>
                    <?= $postObject->meta->_edit_lock; ?>

                </li>
            <?php endforeach; ?>
        </ul>

        <?= $this->paginationAdvanced(); ?>
        <?= $this->paginationSimple(); ?>
    <?php endif; ?>
</div>
```
