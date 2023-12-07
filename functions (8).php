<?php

/*
* Add your own functions here. You can also copy some of the theme functions into this file. 
* Wordpress will use those functions instead of the original functions then.
*/

// Global

require_once get_stylesheet_directory() . '/config-templatebuilder/avia-template-builder/php/class-shortcode-template.php';
require_once get_stylesheet_directory() . '/config-templatebuilder/avia-shortcodes/postslider/postslider.php';
require_once get_stylesheet_directory() . '/includes/helper-social-media.php';

function avf_alb_supported_post_types_mod( array $supported_post_types )
{
	$supported_post_types[] = 'music_release';
	$supported_post_types[] = 'single_release';
	$supported_post_types[] = 'new';
	$supported_post_types[] = 'client';
	$supported_post_types[] = 'provider';
	$supported_post_types[] = 'thing';
	$supported_post_types[] = 'playlist';
	$supported_post_types[] = 'member';
    $supported_post_types[] = 'essential';
	return $supported_post_types;
}
add_filter('avf_alb_supported_post_types', 'avf_alb_supported_post_types_mod', 10, 1);

class newMusicFridayPageTemplate {

	private static $instance;

	protected $templates;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new newMusicFridayPageTemplate();
		} 

		return self::$instance;

	} 

	private function __construct() {

		$this->templates = array();


		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);

		$this->templates = array(
			'new-music-friday-template.php' => 'New Music Friday'
		);
			
	} 

	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_project_templates( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		wp_cache_delete( $cache_key , 'themes');

		$templates = array_merge( $templates, $this->templates );

		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	public function view_project_template( $template ) {
		
		global $post;

		if ( ! $post ) {
			return $template;
		}

		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'newMusicFridayPageTemplate', 'get_instance' ) );

function syntax_user_social_links( $user_contact ) {
   
   $user_contact['instagram'] = __('Instagram Link', 'syntax');
   $user_contact['facebook'] = __('Facebook Link', 'syntax');
   $user_contact['twitter'] = __('Twitter Link', 'syntax');
   $user_contact['youtube'] = __('Yoututbe Link', 'syntax');
   $user_contact['linkedin'] = __('LinkedIn Link', 'syntax');

   return $user_contact;
}
add_filter('user_contactmethods', 'syntax_user_social_links');

// Post Formats

function syntax_post_formats_taxonomies() {
	$labels = array(
		'name'              => _x( 'Format', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Format', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Formats', 'textdomain' ),
		'all_items'         => __( 'All Formats', 'textdomain' ),
		'parent_item'       => __( 'Parent Format', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Format:', 'textdomain' ),
		'edit_item'         => __( 'Edit Format', 'textdomain' ),
		'update_item'       => __( 'Update Format', 'textdomain' ),
		'add_new_item'      => __( 'Add New Format', 'textdomain' ),
		'new_item_name'     => __( 'New Format Name', 'textdomain' ),
		'menu_name'         => __( 'Format', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => 'format',
		'rewrite'           => array( 'slug' => 'format' ),
		'capabilities' => array(
			'manage_terms' => '',
			'edit_terms' => '',
			'delete_terms' => '',
			'assign_terms' => 'edit_posts'
		),
		'public' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud' => false,
	);
	register_taxonomy( 'format', array( 'post' ), $args );
}
add_action( 'init', 'syntax_post_formats_taxonomies', 0 );

function syntax_insert_default_format() {
	wp_insert_term(
		'Default',
		'format',
		array(
		  'description'	=> '',
		  'slug' 		=> 'default'
		)
	);
}
add_action( 'init', 'syntax_insert_default_format' );

function syntax_insert_music_release_format() {
	wp_insert_term(
		'Music Release',
		'format',
		array(
		  'description'	=> 'Posts containing Music Rleases.',
		  'slug' 		=> 'music-release'
		)
	);
}
add_action( 'init', 'syntax_insert_music_release_format' );

function syntax_insert_single_release_format() {
	wp_insert_term(
		'Single Release',
		'format',
		array(
		  'description'	=> 'Posts containing Single Rleases.',
		  'slug' 		=> 'single-release'
		)
	);
}
add_action( 'init', 'syntax_insert_single_release_format' );

function syntax_insert_news_format() {
	wp_insert_term(
		'News',
		'format',
		array(
		  'description'	=> 'Posts containing News.',
		  'slug' 		=> 'new'
		)
	);
}
add_action( 'init', 'syntax_insert_news_format' );

function syntax_insert_client_format() {
	wp_insert_term(
		'Client',
		'format',
		array(
		  'description'	=> 'Posts containing Clients.',
		  'slug' 		=> 'client'
		)
	);
}
add_action( 'init', 'syntax_insert_client_format' );

function syntax_insert_provider_format() {
	wp_insert_term(
		'Provider',
		'format',
		array(
		  'description'	=> 'Posts containing Providers.',
		  'slug' 		=> 'provider'
		)
	);
}
add_action( 'init', 'syntax_insert_provider_format' );

function syntax_insert_thing_format() {
	wp_insert_term(
		'Things',
		'format',
		array(
		  'description'	=> 'Posts containing Things.',
		  'slug' 		=> 'thing'
		)
	);
}
add_action( 'init', 'syntax_insert_thing_format' );

function syntax_insert_playlist_format() {
	wp_insert_term(
		'Playlist',
		'format',
		array(
		  'description'	=> 'Posts containing Playlists.',
		  'slug' 		=> 'playlist'
		)
	);
}
add_action( 'init', 'syntax_insert_playlist_format' );




function syntax_insert_member_format() {
	wp_insert_term(
		'Member',
		'format',
		array(
		  'description'	=> 'Posts containing Members.',
		  'slug' 		=> 'member'
		)
	);
}
add_action( 'init', 'syntax_insert_member_format' );





function syntax_default_format_term( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
            'format' => 'default'
            );
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}
add_action( 'save_post', 'syntax_default_format_term', 100, 2 );

function admin_js() { ?>
    <script type="text/javascript">

    jQuery(document).ready( function () { 
        jQuery('form#post').find('#formatchecklist input').each(function() {
            var new_input = jQuery('<input type="radio" />'),
            attrLen = this.attributes.length;

            for (i = 0; i < attrLen; i++) {
                if (this.attributes[i].name != 'type') {
                    new_input.attr(this.attributes[i].name.toLowerCase(), this.attributes[i].value);
                }
            }

            jQuery(this).replaceWith(new_input);
        });
    });

</script>
<style>
#format-tabs .hide-if-no-js {
    display: none;
}
#format-tabs .tabs {
    border: none;
    display: none;
}
#format-all {
    border: none;
    background-color: white;
}
</style>
<?php }
add_action('admin_head', 'admin_js');

function syntax_remove_formats()
{
   remove_theme_support('post-formats');
}
add_action('after_setup_theme', 'syntax_remove_formats', 100);

function toogle_post_meta() {
    if ( is_admin() ) {
        $script = <<< EOF
<script type='text/javascript'>
    jQuery(document).ready(function($) {
        $('#syntax_post_newsthing_meta_box').hide();
		$('#syntax_post_clientprovider_meta_box').hide();
		$('#syntax_post_musicrelease_meta_box').hide();
		$('#syntax_post_singlerelease_meta_box').hide();
		$('#syntax_post_social_meta_box').hide();
		$('#syntax_post_releasedate_meta_box').hide();
		$('#syntax_post_playlist_meta_box').hide();
		$('#syntax_playlist_social_meta_box').hide();
		$('#syntax_post_member_meta_box').hide();
		$('#ls_pp_meta_box').hide();
		$('#post_citydiv').show();
		$('#post_statediv').show();
		$('#post_genrediv').show();
		$('#post_countrydiv').show();
		$('#post_artistdiv').show();
		$('#post_labeldiv').show();
		$('#post_providerdiv').show();
		$('#formatdiv').show();
		$('#categorydiv').show();
		$('#tagsdiv-post_tag').show();
		$('#tagsdiv-post_city').show();
		$('#tagsdiv-post_state').show();
		$('#tagsdiv-post_country').show();
		$('#tagsdiv-post_artist').show();
		$('#tagsdiv-post_label').show();
		$('#in-format-3010').is(':checked') ? $('#syntax_post_newsthing_meta_box').show() : $('#').hide();
		$('#in-format-3010').click(function() {
            $('#syntax_post_newsthing_meta_box').show();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3011').is(':checked') ? $('#syntax_post_clientprovider_meta_box').show() : $('#').hide();
		$('#in-format-3011').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').show();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3008').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3009').is(':checked') ? $('#syntax_post_musicrelease_meta_box').show() : $('#').hide();
		$('#in-format-3009').is(':checked') ? $('#syntax_post_social_meta_box').show() : $('#').hide();
		$('#in-format-3009').is(':checked') ? $('#syntax_post_releasedate_meta_box').show() : $('#').hide();
		$('#in-format-3009').is(':checked') ? $('#ls_pp_meta_box').show() : $('#').hide();
		$('#in-format-3009').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').show();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').show();
			$('#syntax_post_releasedate_meta_box').show();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').show();
        });
		$('#in-format-10690').is(':checked') ? $('#syntax_post_singlerelease_meta_box').show() : $('#').hide();
		$('#in-format-10690').is(':checked') ? $('#syntax_post_social_meta_box').show() : $('#').hide();
		$('#in-format-10690').is(':checked') ? $('#syntax_post_releasedate_meta_box').show() : $('#').hide();
		$('#in-format-10690').is(':checked') ? $('#ls_pp_meta_box').show() : $('#').hide();
		$('#in-format-10690').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').show();
			$('#syntax_post_social_meta_box').show();
			$('#syntax_post_releasedate_meta_box').show();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').show();
        });
		$('#in-format-3014').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3012').is(':checked') ? $('#syntax_post_clientprovider_meta_box').show() : $('#').hide();
		$('#in-format-3012').is(':checked') ? $('#syntax_post_social_meta_box').show() : $('#').hide();
		$('#in-format-3012').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').show();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').show();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3013').is(':checked') ? $('#syntax_post_newsthing_meta_box').show() : $('#').hide();
		$('#in-format-3013').click(function() {
            $('#syntax_post_newsthing_meta_box').show();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-3014').is(':checked') ? $('#syntax_post_playlist_meta_box').show() : $('#').hide();
		$('#in-format-3014').is(':checked') ? $('#syntax_playlist_social_meta_box').show() : $('#').hide();
		$('#in-format-3014').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').hide();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').show();
			$('#syntax_playlist_social_meta_box').show();
			$('#syntax_post_member_meta_box').hide();
			$('#post_citydiv').show();
			$('#post_statediv').show();
			$('#post_genrediv').show();
			$('#post_countrydiv').show();
			$('#post_artistdiv').show();
			$('#post_labeldiv').show();
			$('#post_providerdiv').show();
			$('#tagsdiv-post_city').show();
			$('#tagsdiv-post_state').show();
			$('#tagsdiv-post_country').show();
			$('#tagsdiv-post_artist').show();
			$('#tagsdiv-post_label').show();
			$('#ls_pp_meta_box').hide();
        });
		$('#in-format-10773').is(':checked') ? $('#syntax_post_member_meta_box').show() : $('#').hide();
		$('#in-format-10773').is(':checked') ? $('#syntax_post_social_meta_box').show() : $('#').hide();
		$('#in-format-10773').is(':checked') ? $('#post_citydiv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_statediv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_genrediv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_countrydiv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_artistdiv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_labeldiv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#post_providerdiv').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#tagsdiv-post_city').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#tagsdiv-post_state').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#tagsdiv-post_country').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#tagsdiv-post_artist').hide() : $('#').show();
		$('#in-format-10773').is(':checked') ? $('#tagsdiv-post_label').hide() : $('#').show();
		$('#in-format-10773').click(function() {
            $('#syntax_post_newsthing_meta_box').hide();
			$('#syntax_post_clientprovider_meta_box').hide();
			$('#syntax_post_musicrelease_meta_box').hide();
			$('#syntax_post_singlerelease_meta_box').hide();
			$('#syntax_post_social_meta_box').show();
			$('#syntax_post_member_meta_box').show();
			$('#syntax_post_releasedate_meta_box').hide();
			$('#syntax_post_playlist_meta_box').hide();
			$('#syntax_playlist_social_meta_box').hide();
			$('#post_citydiv').hide();
			$('#post_statediv').hide();
			$('#post_genrediv').hide();
			$('#post_countrydiv').hide();
			$('#post_artistdiv').hide();
			$('#post_labeldiv').hide();
			$('#post_providerdiv').hide();
			$('#tagsdiv-post_city').hide();
			$('#tagsdiv-post_state').hide();
			$('#tagsdiv-post_country').hide();
			$('#tagsdiv-post_artist').hide();
			$('#tagsdiv-post_label').hide();
			$('#ls_pp_meta_box').hide();
        });
    });
</script>
EOF;
        echo $script;
    }
}
add_action('admin_footer', 'toogle_post_meta');

function syntax_taxonomies_posts() {
  $labels = array(
    'name'              => _x( 'Genre', 'taxonomy general name' ),
    'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Genre' ),
    'all_items'         => __( 'All Genres' ),
    'parent_item'       => __( 'Parent Genre' ),
    'parent_item_colon' => __( 'Parent Genre:' ),
    'edit_item'         => __( 'Edit Genre' ), 
    'update_item'       => __( 'Update Genre' ),
    'add_new_item'      => __( 'Add Genre' ),
    'new_item_name'     => __( 'New Genre' ),
    'menu_name'         => __( 'Genres' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_genre', 'post', $args );
  
  $labels = array(
    'name'              => _x( 'City', 'taxonomy general name' ),
    'singular_name'     => _x( 'City', 'taxonomy singular name' ),
    'search_items'      => __( 'Search City' ),
    'all_items'         => __( 'All Cities' ),
    'parent_item'       => __( 'Parent City' ),
    'parent_item_colon' => __( 'Parent City:' ),
    'edit_item'         => __( 'Edit City' ), 
    'update_item'       => __( 'Update City' ),
    'add_new_item'      => __( 'Add City' ),
    'new_item_name'     => __( 'New City' ),
    'menu_name'         => __( 'Cities' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_city', 'post', $args ); 
	
  $labels = array(
    'name'              => _x( 'State', 'taxonomy general name' ),
    'singular_name'     => _x( 'State', 'taxonomy singular name' ),
    'search_items'      => __( 'Search State' ),
    'all_items'         => __( 'All States' ),
    'parent_item'       => __( 'Parent State' ),
    'parent_item_colon' => __( 'Parent State:' ),
    'edit_item'         => __( 'Edit State' ), 
    'update_item'       => __( 'Update State' ),
    'add_new_item'      => __( 'Add State' ),
    'new_item_name'     => __( 'New State' ),
    'menu_name'         => __( 'States' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_state', 'post', $args );
  
  $labels = array(
    'name'              => _x( 'Country', 'taxonomy general name' ),
    'singular_name'     => _x( 'Country', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Country' ),
    'all_items'         => __( 'All Countries' ),
    'parent_item'       => __( 'Parent Country' ),
    'parent_item_colon' => __( 'Parent Country:' ),
    'edit_item'         => __( 'Edit Country' ), 
    'update_item'       => __( 'Update Country' ),
    'add_new_item'      => __( 'Add Country' ),
    'new_item_name'     => __( 'New Country' ),
    'menu_name'         => __( 'Countries' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_country', 'post', $args );

  $labels = array(
    'name'              => _x( 'Artist', 'taxonomy general name' ),
    'singular_name'     => _x( 'Artist', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Artist' ),
    'all_items'         => __( 'All Artists' ),
    'parent_item'       => __( 'Parent Artist' ),
    'parent_item_colon' => __( 'Parent Artist:' ),
    'edit_item'         => __( 'Edit Artist' ), 
    'update_item'       => __( 'Update Artist' ),
    'add_new_item'      => __( 'Add Artist' ),
    'new_item_name'     => __( 'New Artist' ),
    'menu_name'         => __( 'Artists' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_artist', 'post', $args ); 
  
  $labels = array(
    'name'              => _x( 'Label', 'taxonomy general name' ),
    'singular_name'     => _x( 'Label', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Label' ),
    'all_items'         => __( 'All Labels' ),
    'parent_item'       => __( 'Parent Label' ),
    'parent_item_colon' => __( 'Parent Label:' ),
    'edit_item'         => __( 'Edit Label' ), 
    'update_item'       => __( 'Update Label' ),
    'add_new_item'      => __( 'Add Label' ),
    'new_item_name'     => __( 'New Label' ),
    'menu_name'         => __( 'Labels' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_label', 'post', $args );

  $labels = array(
    'name'              => _x( 'Provider', 'taxonomy general name' ),
    'singular_name'     => _x( 'Provider', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Provider' ),
    'all_items'         => __( 'All Providers' ),
    'parent_item'       => __( 'Parent Provider' ),
    'parent_item_colon' => __( 'Parent Provider:' ),
    'edit_item'         => __( 'Edit Provider' ), 
    'update_item'       => __( 'Update Provider' ),
    'add_new_item'      => __( 'Add Provider' ),
    'new_item_name'     => __( 'New Provider' ),
    'menu_name'         => __( 'Providers' ),
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true
  );
  register_taxonomy( 'post_provider', 'post', $args );  
}
add_action( 'init', 'syntax_taxonomies_posts', 0 );

function syntax_post_social_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_social_meta_box', __( 'Social Links' ), 'syntax_post_social_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_social_add_meta_boxes' );

function syntax_post_social_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_social_meta_box_nonce' );
	$current_website = get_post_meta( $post->ID, 'post_website', true );
	$current_contact = get_post_meta( $post->ID, 'post_contact', true );
	$current_instagram = get_post_meta( $post->ID, 'post_instagram', true );
	$current_facebook = get_post_meta( $post->ID, 'post_facebook', true );
	$current_twitter = get_post_meta( $post->ID, 'post_twitter', true );
	$current_youtube = get_post_meta( $post->ID, 'post_youtube', true );
	$current_linkedin = get_post_meta( $post->ID, 'post_linkedin', true );
	$current_tiktok = get_post_meta( $post->ID, 'post_tiktok', true );
	?>
	<div class='inside'>
		<p>
		<label for="instagram"><b style="font-size:14px;">Instagram:</b><br>
		<input style="width:100%;" type="text" name="instagram" value="<?php echo $current_instagram; ?>" id="instagram"/>
		</label><br><br>
		<label for="facebook"><b style="font-size:14px;">Facebook:</b><br>
		<input style="width:100%;" type="text" name="facebook" value="<?php echo $current_facebook; ?>" id="facebook"/>
		</label><br><br>
		<label for="twitter"><b style="font-size:14px;">Twitter:</b><br>
		<input style="width:100%;" type="text" name="twitter" value="<?php echo $current_twitter; ?>" id="twitter"/>
		</label><br><br>
		<label for="youtube"><b style="font-size:14px;">Youtube:</b><br>
		<input style="width:100%;" type="text" name="youtube" value="<?php echo $current_youtube; ?>" id="youtube"/>
		</label><br><br>
		<label for="linkedin"><b style="font-size:14px;">Linkedin:</b><br>
		<input style="width:100%;" type="text" name="linkedin" value="<?php echo $current_linkedin; ?>" id="linkedin"/>
		</label><br><br>
		<label for="tiktok"><b style="font-size:14px;">TikTok:</b><br>
		<input style="width:100%;" type="text" name="tiktok" value="<?php echo $current_tiktok; ?>" id="tiktok"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_post_social_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_social_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_social_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['instagram'] ) ) {
		update_post_meta( $post_id, 'post_instagram', sanitize_text_field( $_POST['instagram'] ) );
	}
	if ( isset( $_REQUEST['facebook'] ) ) {
		update_post_meta( $post_id, 'post_facebook', sanitize_text_field( $_POST['facebook'] ) );
	}
	if ( isset( $_REQUEST['twitter'] ) ) {
		update_post_meta( $post_id, 'post_twitter', sanitize_text_field( $_POST['twitter'] ) );
	}
	if ( isset( $_REQUEST['youtube'] ) ) {
		update_post_meta( $post_id, 'post_youtube', sanitize_text_field( $_POST['youtube'] ) );
	}
	if ( isset( $_REQUEST['linkedin'] ) ) {
		update_post_meta( $post_id, 'post_linkedin', sanitize_text_field( $_POST['linkedin'] ) );
	}
	if ( isset( $_REQUEST['tiktok'] ) ) {
		update_post_meta( $post_id, 'post_tiktok', sanitize_text_field( $_POST['tiktok'] ) );
	}
}
add_action( 'save_post', 'syntax_post_social_save_meta_box_data' );

function syntax_post_musicrelease_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_musicrelease_meta_box', __( 'Music Release Details' ), 'syntax_post_musicrelease_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_musicrelease_add_meta_boxes' );

function syntax_post_musicrelease_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_musicrelease_meta_box_nonce' );
	$current_upc = get_post_meta( $post->ID, 'post_upc', true );
	$current_key_selling_points = get_post_meta( $post->ID, 'post_key_selling_points', true );
	$current_track_listing = get_post_meta( $post->ID, 'post_track_listing', true );
	$current_focus_track = get_post_meta( $post->ID, 'post_focus_track', true );
	$current_streambuy_link = get_post_meta( $post->ID, 'post_streambuy_link', true );
	$current_moreinfo_link = get_post_meta( $post->ID, 'post_moreinfo_link', true );
	?>
	<div class='inside'>
		<p>
		<label for="upc"><b style="font-size:14px;">UPC:</b><br>
		<input style="width:100%;" type="text" name="upc" value="<?php echo $current_upc; ?>" id="upc"/>
		</label>
		<br><br>
		<label for="key-selling-points"><b style="font-size:14px;">Key Selling Points:</b><br>
		<?php 
		$content   = $current_key_selling_points;
		$editor_id = 'key-selling-points';
		$settings  = array( 'media_buttons' => true );
		 
		wp_editor( $content, $editor_id, $settings ); ?><br><br>
		<label for="track-listing"><b style="font-size:14px;">Track Listing:</b><br>
		<?php 
		$content   = $current_track_listing;
		$editor_id = 'track-listing';
		$settings  = array( 'media_buttons' => true );
		 
		wp_editor( $content, $editor_id, $settings ); ?><br><br>
		<label for="focus-track"><b style="font-size:14px;">Video/Audio Link </b>(Embedded Youtube OR SoundCloud video/audio link in iframe)<br>
		<textarea style="width:100%;" rows="10" name="focus-track" id="focus-track"><?php echo htmlspecialchars_decode($current_focus_track); ?></textarea><br><br>
		<label for="streambuy-link"><b style="font-size:14px;">Stream/Buy Link:</b><br>
		<input style="width:100%;" type="text" name="streambuy-link" value="<?php echo $current_streambuy_link; ?>" id="streambuy-link"/>
		</label><br><br>
		<label for="moreinfo-link"><b style="font-size:14px;">More Info Link:</b><br>
		<input style="width:100%;" type="text" name="moreinfo-link" value="<?php echo $current_moreinfo_link; ?>" id="moreinfo-link"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_post_musicrelease_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_musicrelease_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_musicrelease_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['upc'] ) ) {
		update_post_meta( $post_id, 'post_upc', sanitize_text_field( $_POST['upc'] ) );
	}
	if ( isset( $_REQUEST['key-selling-points'] ) ) {
		update_post_meta( $post_id, 'post_key_selling_points', $_POST['key-selling-points'] );
	}
	if ( isset( $_REQUEST['track-listing'] ) ) {
		update_post_meta( $post_id, 'post_track_listing', $_POST['track-listing'] );
	}
	if ( isset( $_REQUEST['focus-track'] ) ) {
		update_post_meta( $post_id, 'post_focus_track', htmlspecialchars( $_POST['focus-track'] ) );
	}
	if ( isset( $_REQUEST['streambuy-link'] ) ) {
		update_post_meta( $post_id, 'post_streambuy_link', sanitize_text_field( $_POST['streambuy-link'] ) );
	}
	if ( isset( $_REQUEST['moreinfo-link'] ) ) {
		update_post_meta( $post_id, 'post_moreinfo_link', sanitize_text_field( $_POST['moreinfo-link'] ) );
	}
}
add_action( 'save_post', 'syntax_post_musicrelease_save_meta_box_data' );

function syntax_post_singlerelease_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_singlerelease_meta_box', __( 'Single Release Details' ), 'syntax_post_singlerelease_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_singlerelease_add_meta_boxes' );

function syntax_post_singlerelease_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_singlerelease_meta_box_nonce' );
	$current_isrc = get_post_meta( $post->ID, 'post_isrc', true );
	$current_key_selling_points = get_post_meta( $post->ID, 'post_key_selling_points2', true );
	$current_focus_track = get_post_meta( $post->ID, 'post_focus_track2', true );
	$current_streambuy_link = get_post_meta( $post->ID, 'post_streambuy_link2', true );
	$current_moreinfo_link = get_post_meta( $post->ID, 'post_moreinfo_link2', true );
	$current_linked_playlist = get_post_meta( $post->ID, 'post_linked_playlist2', true );
	?>
	<div class='inside'>
		<p>
		<label for="isrc"><b style="font-size:14px;">ISRC:</b><br>
		<input style="width:100%;" type="text" name="isrc" value="<?php echo $current_isrc; ?>" id="isrc"/>
		</label><br><br>
		<label for="linkedplaylist2"><b style="font-size:14px;">Linked Playlist:</b><br>
		<select id='linkedplaylist2' style="width: 50%;" name="linkedplaylist2">
		<option value='None' <?php selected( $current_linked_playlist, 'None' ); ?> >None</option>
		<?php
		$lmquery = new WP_Query( array( 'post_type' => 'post', 'tax_query' => array( array( 'taxonomy' => 'format', 'field' => 'slug', 'terms' => 'playlist' ) ) ) );
		while( $lmquery->have_posts() ) : $lmquery->the_post();
		$postname = get_the_title();
		$postid = get_the_ID();
		$fullpost = $postname . " - " . $postid;
		?>
		  <option value='<?php echo $postid; ?>' <?php selected( $current_linked_playlist, $postid ); ?> ><?php echo $postname; ?></option>
		<?php
		endwhile;
		?>
		</select>
		</label>
		<script>
		$(document).ready(function(){
		$("#linkedplaylist2").select2({
			placeholder: "Select a Playlist",
			allowClear: false,
			theme: "classic",
			width: 'resolve'
		});
		});
		</script>
		<br><br>
		<label for="key-selling-points2"><b style="font-size:14px;">Key Selling Points:</b><br>
		<?php 
		$content   = $current_key_selling_points;
		$editor_id = 'key-selling-points2';
		$settings  = array( 'media_buttons' => true );
		 
		wp_editor( $content, $editor_id, $settings ); ?><br><br>
		<label for="focus-track2"><b style="font-size:14px;">Video/Audio Link </b>(Embedded Youtube OR SoundCloud video/audio link in iframe)<br>
		<textarea style="width:100%;" rows="10" name="focus-track2" id="focus-track2"><?php echo htmlspecialchars_decode($current_focus_track); ?></textarea><br><br>
		<label for="streambuy-link2"><b style="font-size:14px;">Stream/Buy Link:</b><br>
		<input style="width:100%;" type="text" name="streambuy-link2" value="<?php echo $current_streambuy_link; ?>" id="streambuy-link2"/>
		</label><br><br>
		<label for="moreinfo-link2"><b style="font-size:14px;">More Info Link:</b><br>
		<input style="width:100%;" type="text" name="moreinfo-link2" value="<?php echo $current_moreinfo_link; ?>" id="moreinfo-link2"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_post_singlerelease_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_singlerelease_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_singlerelease_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['isrc'] ) ) {
		update_post_meta( $post_id, 'post_isrc', sanitize_text_field( $_POST['isrc'] ) );
	}
	if ( isset( $_REQUEST['linkedplaylist2'] ) ) {
		update_post_meta( $post_id, 'post_linked_playlist2', sanitize_text_field( $_POST['linkedplaylist2'] ) );
	}
	if ( isset( $_REQUEST['key-selling-points2'] ) ) {
		update_post_meta( $post_id, 'post_key_selling_points2', $_POST['key-selling-points2'] );
	}
	if ( isset( $_REQUEST['focus-track2'] ) ) {
		update_post_meta( $post_id, 'post_focus_track2', htmlspecialchars( $_POST['focus-track2'] ) );
	}
	if ( isset( $_REQUEST['streambuy-link2'] ) ) {
		update_post_meta( $post_id, 'post_streambuy_link2', sanitize_text_field( $_POST['streambuy-link2'] ) );
	}
	if ( isset( $_REQUEST['moreinfo-link2'] ) ) {
		update_post_meta( $post_id, 'post_moreinfo_link2', sanitize_text_field( $_POST['moreinfo-link2'] ) );
	}
}
add_action( 'save_post', 'syntax_post_singlerelease_save_meta_box_data' );

function syntax_post_clientprovider_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_clientprovider_meta_box', __( 'Client/Provider Details' ), 'syntax_post_clientprovider_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_clientprovider_add_meta_boxes' );

function syntax_post_clientprovider_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_clientprovider_meta_box_nonce' );
	$current_website = get_post_meta( $post->ID, 'post_website', true );
	$current_contact = get_post_meta( $post->ID, 'post_contact', true );
	?>
	<div class='inside'>
		<p>
		<label for="website"><b style="font-size:14px;">Website:</b><br>
		<input style="width:100%;" type="text" name="website" value="<?php echo $current_website; ?>" id="website"/>
		</label><br><br>
		<label for="contact"><b style="font-size:14px;">Contact:</b><br>
		<input style="width:100%;" type="text" name="contact" value="<?php echo $current_contact; ?>" id="contact"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_post_clientprovider_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_clientprovider_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_clientprovider_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['website'] ) ) {
		update_post_meta( $post_id, 'post_website', sanitize_text_field( $_POST['website'] ) );
	}
	if ( isset( $_REQUEST['contact'] ) ) {
		update_post_meta( $post_id, 'post_contact', sanitize_text_field( $_POST['contact'] ) );
	}
}
add_action( 'save_post', 'syntax_post_clientprovider_save_meta_box_data' );

function syntax_post_newsthing_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_newsthing_meta_box', __( 'News/Thing Details' ), 'syntax_post_newsthing_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_newsthing_add_meta_boxes' );

function syntax_post_newsthing_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_newsthing_meta_box_nonce' );
	$current_video_link = get_post_meta( $post->ID, 'post_video_link', true );
	?>
	<div class='inside'>
		<p>
		<label for="video-link"><b style="font-size:14px;">Video/Audio Link </b>(Embedded Youtube OR SoundCloud video/audio link in iframe)<br>
		<textarea style="width:100%;" rows="7" name="video-link" id="video-link"><?php echo htmlspecialchars_decode($current_video_link); ?></textarea><br><br>
		</p>
	</div>
	<?php
}

function syntax_post_newsthing_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_newsthing_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_newsthing_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['video-link'] ) ) {
		update_post_meta( $post_id, 'post_video_link', htmlspecialchars( $_POST['video-link'] ) );
	}
}
add_action( 'save_post', 'syntax_post_newsthing_save_meta_box_data' );

function syntax_post_releasedate_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_releasedate_meta_box', __( 'Release Date' ), 'syntax_post_releasedate_build_meta_box', 'post', 'side', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_releasedate_add_meta_boxes' );

function syntax_post_releasedate_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_releasedate_meta_box_nonce' );
	$current_releasedate = get_post_meta( $post->ID, 'post_releasedate', true );
	?>
	<script>
	  $( function() {
		$('#releasedate').datepicker({
        dateFormat: 'mm/dd/yy',
        changeMonth: true,
        changeYear: true
		});
	  } );
	</script>
	<div class='inside'>
		<p>
		<input style="width:100%;" type="text" name="releasedate" value="<?php echo $current_releasedate; ?>" id="releasedate" autocomplete="off"/>
		</p>
	</div>
	<?php
}

function syntax_post_releasedate_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_releasedate_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_releasedate_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['releasedate'] ) ) {
		update_post_meta( $post_id, 'post_releasedate', sanitize_text_field( $_POST['releasedate'] ) );
	}
}
add_action( 'save_post', 'syntax_post_releasedate_save_meta_box_data' );

if ( !class_exists( 'ReduxFramework' ) && file_exists( dirname( __FILE__ ) . '/ReduxCore/framework.php' ) ) {
    require_once( dirname( __FILE__ ) . '/ReduxCore/framework.php' );
}
if ( !isset( $redux_syntax ) && file_exists( dirname( __FILE__ ) . '/format-settings.php' ) ) {
    require_once( dirname( __FILE__ ) . '/format-settings.php' );
}

function syntax_playlist_social_add_meta_boxes( $post ){
	add_meta_box( 'syntax_playlist_social_meta_box', __( 'Playlist Options' ), 'syntax_playlist_social_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_playlist_social_add_meta_boxes' );

function syntax_playlist_social_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_playlist_social_meta_box_nonce' );
	$current_applemusic = get_post_meta( $post->ID, 'post_applemusic', true );
	$current_youtubemusic = get_post_meta( $post->ID, 'post_youtubemusic', true );
	$current_soundcloud = get_post_meta( $post->ID, 'post_soundcloud', true );
	$current_deezer = get_post_meta( $post->ID, 'post_deezer', true );
	$current_spotify = get_post_meta( $post->ID, 'post_spotify', true );
	$current_tidal = get_post_meta( $post->ID, 'post_tidal', true );
	$current_napster = get_post_meta( $post->ID, 'post_napster', true );
	$current_pandora = get_post_meta( $post->ID, 'post_pandora', true );
	$current_customclickhere = get_post_meta( $post->ID, 'post_customclickhere', true );
	$current_focus_track_link = get_post_meta( $post->ID, 'post_focus_track_link', true );
	?>
	<div class='inside'>
		<p>
		<label for="applemusic"><b style="font-size:14px;">Apple Music:</b><br>
		<input style="width:100%;" type="text" name="applemusic" value="<?php echo $current_applemusic; ?>" id="applemusic"/>
		</label><br><br>
		<label for="youtubemusic"><b style="font-size:14px;">YouTube Music:</b><br>
		<input style="width:100%;" type="text" name="youtubemusic" value="<?php echo $current_youtubemusic; ?>" id="youtubemusic"/>
		</label><br><br>
		<label for="soundcloud"><b style="font-size:14px;">SoundCloud:</b><br>
		<input style="width:100%;" type="text" name="soundcloud" value="<?php echo $current_soundcloud; ?>" id="soundcloud"/>
		</label><br><br>
		<label for="deezer"><b style="font-size:14px;">Deezer:</b><br>
		<input style="width:100%;" type="text" name="deezer" value="<?php echo $current_deezer; ?>" id="deezer"/>
		</label><br><br>
		<label for="spotify"><b style="font-size:14px;">Spotify:</b><br>
		<input style="width:100%;" type="text" name="spotify" value="<?php echo $current_spotify; ?>" id="spotify"/>
		</label><br><br>
		<label for="tidal"><b style="font-size:14px;">TIDAL:</b><br>
		<input style="width:100%;" type="text" name="tidal" value="<?php echo $current_tidal; ?>" id="tidal"/>
		</label><br><br>
		<label for="napster"><b style="font-size:14px;">Napster:</b><br>
		<input style="width:100%;" type="text" name="napster" value="<?php echo $current_napster; ?>" id="napster"/>
		</label><br><br>
		<label for="pandora"><b style="font-size:14px;">Pandora:</b><br>
		<input style="width:100%;" type="text" name="pandora" value="<?php echo $current_pandora; ?>" id="pandora"/>
		</label><br><br>
		<label for="focus-track-link"><b style="font-size:14px;">Video/Audio Link </b>(Embedded Youtube OR SoundCloud video/audio link in iframe)<br>
		<textarea style="width:100%;" rows="10" name="focus-track-link" id="focus-track-link"><?php echo htmlspecialchars_decode($current_focus_track_link); ?></textarea>
		</label><br><br>
		<label for="customclickhere"><b style="font-size:14px;">Custom "Click Here" text:</b><br>
		<input style="width:100%;" type="text" name="customclickhere" value="<?php echo $current_customclickhere; ?>" id="customclickhere"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_playlist_social_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_playlist_social_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_playlist_social_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['applemusic'] ) ) {
		update_post_meta( $post_id, 'post_applemusic', sanitize_text_field( $_POST['applemusic'] ) );
	}
	if ( isset( $_REQUEST['youtubemusic'] ) ) {
		update_post_meta( $post_id, 'post_youtubemusic', sanitize_text_field( $_POST['youtubemusic'] ) );
	}
	if ( isset( $_REQUEST['soundcloud'] ) ) {
		update_post_meta( $post_id, 'post_soundcloud', sanitize_text_field( $_POST['soundcloud'] ) );
	}
	if ( isset( $_REQUEST['deezer'] ) ) {
		update_post_meta( $post_id, 'post_deezer', sanitize_text_field( $_POST['deezer'] ) );
	}
	if ( isset( $_REQUEST['spotify'] ) ) {
		update_post_meta( $post_id, 'post_spotify', sanitize_text_field( $_POST['spotify'] ) );
	}
	if ( isset( $_REQUEST['tidal'] ) ) {
		update_post_meta( $post_id, 'post_tidal', sanitize_text_field( $_POST['tidal'] ) );
	}
	if ( isset( $_REQUEST['napster'] ) ) {
		update_post_meta( $post_id, 'post_napster', sanitize_text_field( $_POST['napster'] ) );
	}
	if ( isset( $_REQUEST['pandora'] ) ) {
		update_post_meta( $post_id, 'post_pandora', sanitize_text_field( $_POST['pandora'] ) );
	}
	if ( isset( $_REQUEST['focus-track-link'] ) ) {
		update_post_meta( $post_id, 'post_focus_track_link', htmlspecialchars( $_POST['focus-track-link'] ) );
	}
	if ( isset( $_REQUEST['customclickhere'] ) ) {
		update_post_meta( $post_id, 'post_customclickhere', sanitize_text_field( $_POST['customclickhere'] ) );
	}
}
add_action( 'save_post', 'syntax_playlist_social_save_meta_box_data' );

function syntax_post_member_add_meta_boxes( $post ){
	add_meta_box( 'syntax_post_member_meta_box', __( 'Member Options' ), 'syntax_post_member_build_meta_box', 'post', 'normal', 'low' );
}
add_action( 'add_meta_boxes_post', 'syntax_post_member_add_meta_boxes' );

function syntax_post_member_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_post_member_meta_box_nonce' );
	$current_jobtitle = get_post_meta( $post->ID, 'post_jobtitle', true );
	?>
	<div class='inside'>
		<p>
		<label for="jobtitle"><b style="font-size:14px;">Job Title:</b><br>
		<input style="width:100%;" type="text" name="jobtitle" value="<?php echo $current_jobtitle; ?>" id="jobtitle"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_post_member_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_post_member_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_post_member_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['jobtitle'] ) ) {
		update_post_meta( $post_id, 'post_jobtitle', sanitize_text_field( $_POST['jobtitle'] ) );
	}
}
add_action( 'save_post', 'syntax_post_member_save_meta_box_data' );

function syntax_enqueue_scripts_styles() {
	wp_register_style( 'lm-jquery-ui', get_stylesheet_directory_uri() . '/css/lm-jquery-ui.css', false, 'all' );
	wp_register_style( 'syntax_admin', get_stylesheet_directory_uri() . '/css/syntax-styles.css', false, 'all' );
	wp_register_style( 'select2', get_stylesheet_directory_uri() . '/css/select2.css', false, 'all' );
	wp_register_script( 'syntax_admin', get_stylesheet_directory_uri() . '/js/syntax-scripts.js' , true );
	wp_register_script( 'select2', get_stylesheet_directory_uri() . '/js/select2.js' , true );
	wp_enqueue_style( 'syntax_admin' );
	wp_enqueue_style( 'select2' );
	wp_enqueue_style( 'lm-jquery-ui' );
	wp_enqueue_script( 'syntax_admin' );
	wp_enqueue_script( 'select2' );
}
add_action( 'admin_enqueue_scripts', 'syntax_enqueue_scripts_styles' );

function lm_change_aviajs() {
wp_dequeue_script( 'avia-default', get_template_directory().'/js/avia.js', array('jquery'));
wp_deregister_script(  'avia-default', get_template_directory().'/js/avia.js', array('jquery'));
wp_enqueue_script( 'avia-default', get_stylesheet_directory_uri().'/js/avia.js', array('jquery'), 2, true);

}
add_action( 'wp_enqueue_scripts', 'lm_change_aviajs', 100 );

function syntax_profile_img_fields( $user ) {
	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}

	$url             = get_the_author_meta( 'syntax_meta', $user->ID );
	$upload_url      = get_the_author_meta( 'syntax_upload_meta', $user->ID );
	$upload_edit_url = get_the_author_meta( 'syntax_upload_edit_meta', $user->ID );
	$button_text     = $upload_url ? 'Change Current Image' : 'Upload New Image';

	if ( $upload_url ) {
		$upload_edit_url = get_site_url() . $upload_edit_url;
	}
	?>

	<div id="syntax_container">
		<h3><?php _e( 'Custom User Profile Photo', 'custom-user-profile-photo' ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="syntax_meta"><?php _e( 'Profile Photo', 'custom-user-profile-photo' ); ?></label></th>
				<td>
					<div id="current_img">
						<?php if ( $upload_url ): ?>
							<img class="syntax-current-img" src="<?php echo esc_url( $upload_url ); ?>"/>

							<div class="edit_options uploaded">
								<a class="remove_img">
									<span><?php _e( 'Remove', 'custom-user-profile-photo' ); ?></span>
								</a>

								<a class="edit_img" href="<?php echo esc_url( $upload_edit_url ); ?>" target="_blank">
									<span><?php _e( 'Edit', 'custom-user-profile-photo' ); ?></span>
								</a>
							</div>
						<?php elseif ( $url ) : ?>
							<img class="syntax-current-img" src="<?php echo esc_url( $url ); ?>"/>
							<div class="edit_options single">
								<a class="remove_img">
									<span><?php _e( 'Remove', 'custom-user-profile-photo' ); ?></span>
								</a>
							</div>
						<?php else : ?>
							<img class="syntax-current-img placeholder"
							     src="<?php echo esc_url( get_stylesheet_directory_uri() . '/img/syntax-placeholder.gif' ); ?>"/>
						<?php endif; ?>
					</div>

					<div id="syntax_options">
						<input type="radio" id="upload_option" name="img_option" value="upload" class="tog" checked>
						<label
								for="upload_option"><?php _e( 'Upload New Image', 'custom-user-profile-photo' ); ?></label><br>

						<input type="radio" id="external_option" name="img_option" value="external" class="tog">
						<label
								for="external_option"><?php _e( 'Use External URL', 'custom-user-profile-photo' ); ?></label><br>
					</div>

					<div id="syntax_upload">
						<input class="hidden" type="hidden" name="syntax_placeholder_meta" id="syntax_placeholder_meta"
						       value="<?php echo esc_url( get_stylesheet_directory_uri() . '/img/placeholder.gif' ); ?>"/>
						<input class="hidden" type="hidden" name="syntax_upload_meta" id="syntax_upload_meta"
						       value="<?php echo esc_url_raw( $upload_url ); ?>"/>
						<input class="hidden" type="hidden" name="syntax_upload_edit_meta" id="syntax_upload_edit_meta"
						       value="<?php echo esc_url_raw( $upload_edit_url ); ?>"/>
						<input id="uploadimage" type='button' class="syntax_wpmu_button button-primary"
						       value="<?php _e( esc_attr( $button_text ), 'custom-user-profile-photo' ); ?>"/>
						<br/>
					</div>

					<div id="syntax_external">
						<input class="regular-text" type="text" name="syntax_meta" id="syntax_meta"
						       value="<?php echo esc_url_raw( $url ); ?>"/>
					</div>

					<span class="description">
						<?php
						_e(
							'Upload a custom photo for your user profile or use a URL to a pre-existing photo.',
							'custom-user-profile-photo'
						);
						?>
					</span>
					<p class="description">
						<?php _e( 'Update Profile to save your changes.', 'custom-user-profile-photo' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>

	<?php
	wp_enqueue_media();
}

add_action( 'show_user_profile', 'syntax_profile_img_fields' );
add_action( 'edit_user_profile', 'syntax_profile_img_fields' );

function syntax_save_img_meta( $user_id ) {
	if ( ! current_user_can( 'upload_files', $user_id ) ) {
		return;
	}

	$values = array(
		'syntax_meta'             => filter_input( INPUT_POST, 'syntax_meta', FILTER_SANITIZE_STRING ),

		'syntax_upload_meta'      => filter_input( INPUT_POST, 'syntax_upload_meta', FILTER_SANITIZE_URL ),

		'syntax_upload_edit_meta' => filter_input( INPUT_POST, 'syntax_upload_edit_meta', FILTER_SANITIZE_URL ),
	);

	foreach ( $values as $key => $value ) {
		update_user_meta( $user_id, $key, $value );
	}
}

add_action( 'personal_options_update', 'syntax_save_img_meta' );
add_action( 'edit_user_profile_update', 'syntax_save_img_meta' );

function get_syntax_meta( $user_id, $size = 'thumbnail' ) {
	global $post;

	if ( ! $user_id || ! is_numeric( $user_id ) ) {
		$user_id = $post->post_author;
	}

	$attachment_upload_url = esc_url( get_the_author_meta( 'syntax_upload_meta', $user_id ) );

	if ( $attachment_upload_url ) {
		$attachment_id = attachment_url_to_postid( $attachment_upload_url );

		$image_thumb = wp_get_attachment_image_src( $attachment_id, $size );

		return isset( $image_thumb[0] ) ? $image_thumb[0] : '';
	}

	$attachment_ext_url = esc_url( get_the_author_meta( 'syntax_meta', $user_id ) );

	return $attachment_ext_url ? $attachment_ext_url : '';
}

function syntax_get_user_by_id_or_email( $identifier ) {
	if ( is_numeric( $identifier ) ) {
		return get_user_by( 'id', (int) $identifier );
	}

	if ( is_object( $identifier ) && property_exists( $identifier, 'ID' ) ) {
		return get_user_by( 'id', (int) $identifier->ID );
	}

	if ( is_object( $identifier ) && property_exists( $identifier, 'user_id' ) ) {
		return get_user_by( 'id', (int) $identifier->user_id );
	}

	return get_user_by( 'email', $identifier );
}

function syntax_avatar( $avatar, $identifier, $size, $alt ) {
	if ( $user = syntax_get_user_by_id_or_email( $identifier ) ) {
		if ( $custom_avatar = get_syntax_meta( $user->ID, 'thumbnail' ) ) {
			return "<img alt='{$alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		}
	}

	return $avatar;
}

add_filter( 'get_avatar', 'syntax_avatar', 1, 5 );

function lm_posts_search_title_starts_with( $where, $query ) {
    global $wpdb;

    $starts_with = esc_sql( $query->get( 'starts_with' ) );

    if ( $starts_with ) {
        $where .= " AND $wpdb->posts.post_title LIKE '$starts_with%'";
    }

    return $where;
}
add_filter( 'posts_where', 'lm_posts_search_title_starts_with', 10, 2 );

///Creates Issue with permalinks
///function lm_custom_pre_get_posts( $query ) {  
///if( $query->is_main_query() && !$query->is_feed() && !is_admin() && is_category()) {  
///    $query->set( 'paged', str_replace( '/', '', get_query_var( 'page' ) ) );  }  } 
///
///add_action('pre_get_posts','lm_custom_pre_get_posts'); 

///Develops an issue with nextpage links in custom single pages
///function lm_custom_request($query_string ) { 
///     if( isset( $query_string['page'] ) ) { 
///         if( ''!=$query_string['page'] ) { 
///             if( isset( $query_string['name'] ) ) { unset( $query_string['name'] ); } } } return $query_string; } 
///
///add_filter('request', 'lm_custom_request');


function lm_get_custom_single_taxonomy_template($single_template) {
    global $post;

    if ($post->post_type == 'post') {
        $terms = get_the_terms($post->ID, 'format');
        if($terms && !is_wp_error( $terms )) {
            foreach($terms as $term){
                if($term->slug != 'essential'){
                    
                    $single_template = dirname( __FILE__ ) . '/single-format_'.$term->slug.'.php';   
            
                }else{
                    $single_template = dirname( __FILE__ ) . '/';   
                }   
            }
        }
     }
     echo $single_template;
     return $single_template;
}

add_filter( "single_template", "lm_get_custom_single_taxonomy_template" ) ;

add_action('admin_head', 'lm_custom_admin_css');

function lm_custom_admin_css() {
  echo '<style>
    #format-all {
    height: 250px;
    max-height: 250px;
    }
    #formatdiv {
        height: 300px;
    }
  </style>';
}

function get_the_content_with_formatting ($more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
	$content = get_the_content($more_link_text, $stripteaser, $more_file);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}

class newCustomHomePageTemplate {

	private static $instance;

	protected $templates;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new newCustomHomePageTemplate();
		} 

		return self::$instance;

	} 

	private function __construct() {

		$this->templates = array();


		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);

		$this->templates = array(
			'ls-custom-home.php' => 'Home Custom'
		);
			
	} 

	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_project_templates( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		wp_cache_delete( $cache_key , 'themes');

		$templates = array_merge( $templates, $this->templates );

		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	public function view_project_template( $template ) {
		
		global $post;

		if ( ! $post ) {
			return $template;
		}

		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'newCustomHomePageTemplate', 'get_instance' ) );



// Register new icon as a theme icon
function avia_add_tiktok_icon($icons) {
	$icons['tiktok'] = array( 'font' =>'entypo-fontello', 'icon' => 'ue800' , 'display_name' => 'TikTok');
	return $icons;
}
add_filter('avf_default_icons','avia_add_tiktok_icon', 10, 1);

// Add new icon as an option for social icons
function avia_add_tiktok_social_icon($icons) {
	$icons['TikTok'] = 'tiktok';
	return $icons;
}
add_filter('avf_social_icons_options','avia_add_tiktok_social_icon', 10, 1);

function ls_pp_load_wp_admin_style() {
        wp_enqueue_media();
		wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        // admin always last
        wp_enqueue_style( 'ls_pp_admin_css', get_stylesheet_directory_uri() . '/css/ls_pp_admin.css' );
        wp_enqueue_script( 'ls_pp_admin_script', get_stylesheet_directory_uri() . '/js/ls_pp_admin.js' );
}
add_action( 'admin_enqueue_scripts', 'ls_pp_load_wp_admin_style' );

// Add the Meta Box
function ls_add_custom_meta_box() {
    add_meta_box(
        'ls_pp_meta_box', // $id
        'Press Photos', // $title
        'ls_show_custom_meta_box', // $callback
        'post', // $post
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'ls_add_custom_meta_box');

// Field Array
$prefix = 'ls_';
$custom_meta_fields = array(
    array(
        'label'=> 'Images',
        'desc'  => 'Images for Press Photos of the post.',
        'id'    => $prefix.'gallery',
        'type'  => 'gallery'
    ),
);

// The Callback
function ls_show_custom_meta_box($object) {
        global $custom_meta_fields, $post;
        // Use nonce for verification
        echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

        // Begin the field table and loop
        echo '<table class="form-table">';
        foreach ($custom_meta_fields as $field) {
                // get value of this field if it exists for this post
                $meta = get_post_meta($post->ID, $field['id'], true);
                // begin a table row with
                echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                        case 'gallery':
                        $meta_html = null;
                        if ($meta) {
                                $meta_html .= '<ul class="ls_gallery_list">';
                                $meta_array = explode(',', $meta);
                                foreach ($meta_array as $meta_gall_item) {
                                        $meta_html .= '<li style="padding:0 10px;"><div class="ls_gallery_container"><span class="ls_gallery_close"><img style="width:-webkit-fill-available;" id="' . esc_attr($meta_gall_item) . '" src="' . wp_get_attachment_thumb_url($meta_gall_item) . '"></span></div></li>';
                                }
                                $meta_html .= '</ul>';
                        }
                        echo '<input id="ls_gallery" type="hidden" name="ls_gallery" value="' . esc_attr($meta) . '" />
                        <span id="ls_gallery_src">' . $meta_html . '</span>
                        <div class="ls_gallery_button_container"><input style="float:left;margin-right:10px;" id="ls_gallery_button" type="button" value="Add Image(s)" /><input id="ls_remove_all_images_button" type="button" value="Remove All Images" /></div>';
                        break;
                } //end switch
                echo '</td></tr>';
        } // end foreach
        echo '</table>'; // end table
}

// Save the Data
function ls_save_custom_meta($post_id) {
	if($_POST) {
		global $custom_meta_fields;

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['custom_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}
		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Loop through meta fields
		foreach ( $custom_meta_fields as $field ) {
			$new_meta_value = esc_attr( $_POST[ $field['id'] ] );
			$meta_key       = $field['id'];
			$meta_value     = get_post_meta( $post_id, $meta_key, true );

			// If theres a new meta value and the existing meta value is empty
			if ( $new_meta_value && $meta_value == null ) {
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
				// If theres a new meta value and the existing meta value is different
			} elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			} elseif ( $new_meta_value == null && $meta_value ) {
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}
}

add_action('save_post', 'ls_save_custom_meta');

add_theme_support('avia_template_builder_page_split_element');
  
function custom_post_type_testimonial() {
  
    $labels = array(
        'name'                => _x( 'Testimonials', 'Post Type General Name', 'syntax' ),
        'singular_name'       => _x( 'Testimonial', 'Post Type Singular Name', 'syntax' ),
        'menu_name'           => __( 'Testimonials', 'syntax' ),
        'parent_item_colon'   => __( 'Parent Testimonial', 'syntax' ),
        'all_items'           => __( 'All Testimonials', 'syntax' ),
        'view_item'           => __( 'View Testimonial', 'syntax' ),
        'add_new_item'        => __( 'Add New Testimonial', 'syntax' ),
        'add_new'             => __( 'Add New', 'syntax' ),
        'edit_item'           => __( 'Edit Testimonial', 'syntax' ),
        'update_item'         => __( 'Update Testimonial', 'syntax' ),
        'search_items'        => __( 'Search Testimonial', 'syntax' ),
        'not_found'           => __( 'Not Found', 'syntax' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'syntax' ),
    );
      
    $args = array(
        'label'               => __( 'testimonial', 'syntax' ),
        'description'         => __( 'Testimonial Description', 'syntax' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
        'taxonomies'          => array( 'genres' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
  
    );
	
    register_post_type( 'testimonial', $args );
  
}
  
add_action( 'init', 'custom_post_type_testimonial', 0 );

function syntax_testimonial_company_add_meta_boxes( $post ){
	add_meta_box( 'syntax_testimonial_company_meta_box', __( 'Company Information' ), 'syntax_testimonial_company_build_meta_box', 'testimonial', 'normal', 'low' );
}
add_action( 'add_meta_boxes_testimonial', 'syntax_testimonial_company_add_meta_boxes' );

function syntax_testimonial_company_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'syntax_testimonial_company_meta_box_nonce' );
	$current_company = get_post_meta( $post->ID, 'tbtestimonial_company', true );
	$current_company_url = get_post_meta( $post->ID, 'tbtestimonial_company_url', true );
	?>
	<div class='inside'>
		<p>
		<label for="testimonial_company"><b style="font-size:14px;">Company Name:</b><br>
		<input style="width:100%;" type="text" name="testimonial_company" value="<?php echo $current_company; ?>" id="testimonial_company"/>
		</label><br><br>
		<label for="testimonial_company_url"><b style="font-size:14px;">Company URL:</b><br>
		<input style="width:100%;" type="text" name="testimonial_company_url" value="<?php echo $current_company_url; ?>" id="testimonial_company_url"/>
		</label>
		</p>
	</div>
	<?php
}

function syntax_testimonial_company_save_meta_box_data( $post_id ){
	if ( !isset( $_POST['syntax_testimonial_company_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['syntax_testimonial_company_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_REQUEST['testimonial_company'] ) ) {
		update_post_meta( $post_id, 'tbtestimonial_company', sanitize_text_field( $_POST['testimonial_company'] ) );
	}
	if ( isset( $_REQUEST['testimonial_company_url'] ) ) {
		update_post_meta( $post_id, 'tbtestimonial_company_url', sanitize_text_field( $_POST['testimonial_company_url'] ) );
	}
}
add_action( 'save_testimonial', 'syntax_testimonial_company_save_meta_box_data' );

// Custom Shortcode for Testimonials
function ls_testimonial_shortcode() {

$args = array(
			'post_type' => 'testimonial',
			'post_status' => 'publish',
			'posts_per_page' => 2,
			'orderby' => 'rand',
		);
$loop = new WP_Query( $args );
$i = 0;

echo '
	<style>
	.testimonial-box {
		width: 100%;
		position: relative;
		margin: 40px auto;
		background: white;
		font-weight: bold;
		border: 4px solid #ec008c!Important;
		height: 200px;
		text-align: center;
		border-radius: 20px;
	}
	.testimonial-box.bottom-right:before {
		top: 100%;
		left: 90%;
		border: solid transparent;
		content: "";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
	}
	.testimonial-box.bottom-right:after {
		top: 95%;
		left: 89.6%;
		border: solid transparent;
		content: "";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
	}
	.testimonial-box.bottom-right:after {
		border-color: rgba(0, 0, 0, 0);
		border-width: 10px;
		border-top-color: white;
		margin-left: -10.2px;
		border-top: solid 50px white;
		border-left: solid 50px transparent;
		border-right: solid 0px transparent;
	}
	.testimonial-box.bottom-right:before {
		border-color: rgba(0, 0, 0, 0);
		border-width: 10px;
		border-top-color: black;
		margin-left: -9px;
		border-top: solid 50px #ec008c;
		border-left: solid 50px transparent;
		border-right: solid 0px transparent;
	}
	.testimonial-box.bottom-left:before {
		top: 100%;
		left: 7%;
		border: solid transparent;
		content: "";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
	}
	.testimonial-box.bottom-left:after {
		top: 95%;
		left: 7.6%;
		border: solid transparent;
		content: "";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
	}
	.testimonial-box.bottom-left:after {
		border-color: rgba(0, 0, 0, 0);
		border-width: 10px;
		border-top-color: white;
		margin-left: -10.2px;
		border-top: solid 50px white;
		border-left: solid 0px transparent;
		border-right: solid 50px transparent;
	}
	.testimonial-box.bottom-left:before {
		border-color: rgba(0, 0, 0, 0);
		border-width: 10px;
		border-top-color: black;
		margin-left: -9px;
		border-top: solid 50px #ec008c;
		border-left: solid 0px transparent;
		border-right: solid 50px transparent;
	}
	.testimoial-title {
		text-align: left;
		font-size: 28px;
		text-transform: uppercase;
		color: white;
		background-color: #ec008c;
		border-top-left-radius: 15px;
		border-top-right-radius: 15px;
		padding-left: 20px;
		height: fit-content;
		line-height: 60px;
	}
	.testimonial-quote-left, .testimonial-quote-right {
		width: 8%;
		float: left;
		margin: auto;
	}
	.testimonial-excerpt {
		width: 82%;
		float: left;
		margin: auto;
		line-height: 1;
		font-size: 18px;
		padding: 0 15px;
		vertical-align: middle;
		position: relative;
		display: inline-block;
		height: 71px;
		top: 3vh;
	}
	.testimonial-div {
		width: 100%;
		height: 10vh;
		padding: 10px 0 15px 15px;
	}
	.testimonial-author {
		color: #ec008c;
		line-height: 1;
		vertical-align: middle;
		position: absolute;
		right: 15px;
		bottom: 8%;
		font-size: 22px;
		font-weight: bold;
		font-style: italic;
	}
	</style>';

while ( $loop->have_posts() ) : $loop->the_post();
	$title = get_the_title();
	$excerpt = get_the_excerpt();
	$leftquote = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
				 viewBox="0 0 66.000000 64.000000"
				 preserveAspectRatio="xMidYMid meet">

				<g transform="translate(0.000000,64.000000) scale(0.100000,-0.100000)"
				fill="#ec008c" stroke="none">
				<path d="M102 427 l-73 -152 1 -122 0 -123 130 0 130 0 0 130 0 130 -38 0
				c-21 0 -41 3 -45 6 -3 3 16 69 43 145 l49 139 -62 0 -62 -1 -73 -152z"/>
				<path d="M462 428 l-72 -153 0 -122 0 -123 130 0 130 0 0 130 0 130 -38 0
				c-21 0 -41 3 -45 6 -3 3 16 69 43 145 l49 139 -62 0 -62 0 -73 -152z"/>
				</g>
				</svg>';
	$rightquote = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
					 viewBox="0 0 66.000000 66.000000"
					 preserveAspectRatio="xMidYMid meet">

					<g transform="translate(0.000000,66.000000) scale(0.100000,-0.100000)"
					fill="#ec008c" stroke="none">
					<path d="M10 460 l0 -130 45 0 45 0 -50 -142 c-28 -77 -50 -143 -50 -145 0 -1
					27 -3 60 -3 l61 0 74 156 75 157 0 118 0 119 -130 0 -130 0 0 -130z"/>
					<path d="M370 460 l0 -130 45 0 44 0 -49 -141 c-28 -77 -50 -143 -50 -145 0
					-2 27 -4 60 -4 l60 0 75 157 75 156 0 119 0 118 -130 0 -130 0 0 -130z"/>
					</g>
					</svg>';
	if ($i == 0) {
	echo '<div id="container">
			<div class="testimonial-box bottom-right">
				<div class="testimoial-title">'
				. $title . 
				'</div>
				<div class="testimonial-div">
					<div class="testimonial-quote-left">'
					. $leftquote . 
					'</div>
					<div class="testimonial-excerpt">'
					. $excerpt . 
					'</div>
					<div class="testimonial-quote-right">'
					. $rightquote . 
					'</div>
				</div>
				<div class="testimonial-author">- '
				. $title . 
				'</div>
			</div>
		</div>';
	}
	else {
		echo '<div id="container">
			<div class="testimonial-box bottom-left">
				<div class="testimoial-title">'
				. $title . 
				'</div>
				<div class="testimonial-div">
					<div class="testimonial-quote-left">'
					. $leftquote . 
					'</div>
					<div class="testimonial-excerpt">'
					. $excerpt . 
					'</div>
					<div class="testimonial-quote-right">'
					. $rightquote . 
					'</div>
				</div>
				<div class="testimonial-author">- '
				. $title . 
				'</div>
			</div>
		</div>';
	}
	$i++;
endwhile;
wp_reset_postdata();

}

add_shortcode('testimonial', 'ls_testimonial_shortcode');

class PressKitPageTemplate {

	private static $instance;

	protected $templates;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PressKitPageTemplate();
		} 

		return self::$instance;

	} 

	private function __construct() {

		$this->templates = array();


		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);

		$this->templates = array(
			'press-kit-template.php' => 'Press Kit'
		);
			
	} 

	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_project_templates( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		wp_cache_delete( $cache_key , 'themes');

		$templates = array_merge( $templates, $this->templates );

		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	public function view_project_template( $template ) {
		
		global $post;

		if ( ! $post ) {
			return $template;
		}

		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'PressKitPageTemplate', 'get_instance' ) );






















