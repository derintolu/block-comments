<?php
/**
 * Plugin Name: Block Comments
 * Description: Replaces the comment form with a block editor
 * Author: Tom J Nowell
 * Version: 1.0
 */


add_action( 'wp_enqueue_scripts', 'tomjn_block_assets' );
add_action( 'admin_enqueue_scripts', 'tomjn_block_assets' );
function tomjn_block_assets(){
    //wp_enqueue_style('wp-edit-post');

    wp_register_script(
    	'tomjn_gb_js',
    	plugins_url( 'built.js', __FILE__ ),
    	[
    		'wp-editor',
    		'wp-core-data',
    		'wp-data',
    		'wp-block-library',
    		'wp-format-library'
    	],
    	filemtime( __DIR__.'/built.js'),
    	true
    );
    
    wp_register_style(
    	'tomjn_gb_css',
        plugins_url( 'editor.css', __FILE__ ),
        [
        	'media',
        	'l10n',
        	'buttons',
        	'wp-editor'
        ],
        filemtime( __DIR__.'/editor.css'),
        'all'
    );

    if ( !is_admin() ) {
        global $wp_scripts;
        // TEMPORARY: Core does not (yet) provide persistence migration from the
        // introduction of the block editor and still calls the data plugins.
        // We unset the existing inline scripts first.
        $wp_scripts->registered['wp-data']->extra['after'] = array();
        wp_add_inline_script(
            'wp-data',
            implode(
                "\n",
                array(
                    '( function() {',
                    '   var userId = ' . intval( get_current_user_ID() ) . ';',
                    '   var storageKey = "WP_DATA_USER_" + userId;',
                    '   wp.data',
                    '       .use( wp.data.plugins.persistence, { storageKey: storageKey } );',
                    '   wp.data.plugins.persistence.__unstableMigrate( { storageKey: storageKey } );',
                    '   wp.data.use( wp.data.plugins.controls );',
                    '} )();',
                )
            )
        );
    }
}

function wp_block_editor( $content, $name ) {
	wp_enqueue_script('tomjn_gb_js');
	wp_enqueue_style('tomjn_gb_css');
    ?>
    <input name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" type="hidden" />
    <script>
    document.addEventListener('DOMContentLoaded', function(event) {
        tomjn_wp_editor(
            '<?php echo esc_js( $name ); ?>',
            '<?php echo esc_js( $name ); ?>',
            '<?php echo esc_js( $content ); ?>'
        );
    });
    </script>
    <?php
}

add_action( 'wp_enqueue_scripts', 'tomjn_add_block_comment_form' );
function tomjn_add_block_comment_form() {
	wp_enqueue_script('tomjn_gb_js');
	wp_enqueue_style('tomjn_gb_css');
}