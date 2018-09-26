<?php
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
