<?php
global $bp_media_admin_is_current;
$bp_media_admin_is_current = false;
/**
 * Add the BuddyPress Media's options menu in the BuddyPress' options subnavigation.
 * 
 * @since BP Media 2.0
 */
function bp_media_add_admin_menu() {
	global $bp;
	if (!is_super_admin())
        return false;
	
	add_menu_page( 'BP Media Component', 'BP Media', 'manage_options', 'bp-media-settings', 'bp_media_admin_menu' );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Settings', 'bp-media' ), __( 'Settings', 'bp-media' ), 'manage_options', 'bp-media-settings', "bp_media_admin_menu" );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Addons', 'bp-media' ), __( 'Addons', 'bp-media' ), 'manage_options', 'bp-media-addons', "bp_media_addons_list" );
	add_submenu_page( 'bp-media-settings', __( 'BP-Media Support', 'bp-media' ), __( 'Support ', 'bp-media' ), 'manage_options', 'bp-media-support', "bp_media_support" );
	
    $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
//    rtp_on_load_page();
    add_action('admin_print_styles-' . $tab, 'bp_media_admin_enqueue');
}
add_action(bp_core_admin_hook(), 'bp_media_add_admin_menu');


/**
     * Applies WordPress metabox funtionality to rtPanel metaboxes
     *
     * @since rtPanel 2.0
     **/
    function rtp_on_load_page() {
        
        /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );

        // Check to see which tab we are on
        $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
        
        switch ( $tab ) {
            case 'bp-media-addons' :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp-media-addons-list',__('BuddyPress Media Addons','bp-media'),'bp_media_addons_list','', 'normal', 'core' );
                break;
            case 'bp-media-support' :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                //add_meta_box( 'post_summaries_options', __('Post Summary Settings', 'rtPanel'), 'rtp_post_summaries_metabox', 'appearance_page_' . $tab, 'normal', 'core' );                                
                break;
            case $tab :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                //add_meta_box( 'post_summaries_options', __('Post Summary Settings', 'rtPanel'), 'rtp_post_summaries_metabox', 'appearance_page_' . $tab, 'normal', 'core' );                
                break;
        }
    }

/**
 * Displays and updates the options menu of BuddyPress Media
 * 
 * @since BP Media 2.0
 */
function bp_media_admin_menu() {
	$bp_media_errors=array();
	$bp_media_messages=array();
	global $bp_media_options;
	$bp_media_options = get_option('bp_media_options',array(
		'videos_enabled'	=>	true,
		'audio_enabled'		=>	true,
		'images_enabled'	=>	true,
	));
	if(array_key_exists('bp_media_refresh_count', $_GET)){
		check_admin_referer('bp_media_refresh_count','wp_nonce');
		if(!bp_media_update_count())
			$bp_media_errors[]="Recounting Failed";
		else
			$bp_media_messages[]="Recounting of media files done successfully";
	}
	if(array_key_exists('submit', $_POST)){
		
		if(array_key_exists('remove_linkback', $_POST)){
			if($_POST['remove_linkback']=='2'&&update_option('bp_media_remove_linkback', '2')){
				$bp_media_messages[0]="<b>Settings saved.</b>";
			}
			else if(update_option('bp_media_remove_linkback', '1')){
				$bp_media_messages[0]="<b>Settings saved.</b>";
			}
		}
		if(array_key_exists('enable_videos',$_POST)){
			$bp_media_options['videos_enabled'] = true;
		}
		else
		{
			$bp_media_options['videos_enabled'] = false;
		}
		if(array_key_exists('enable_audio',$_POST)){
			$bp_media_options['audio_enabled'] = true;
		}
		else
		{
			$bp_media_options['audio_enabled'] = false;
		}
		if(array_key_exists('enable_images',$_POST)){
			$bp_media_options['images_enabled'] = true;
		}
		else
		{
			$bp_media_options['images_enabled'] = false;
		}
		if(update_option('bp_media_options', $bp_media_options)){
			$bp_media_messages[0]="<b>Settings saved.</b>";
		}
		do_action('bp_media_save_options');
		$bp_media_messages = apply_filters('bp_media_settings_messages',$bp_media_messages);
		$bp_media_errors = apply_filters('bp_media_settings_errors',$bp_media_errors);
	}
	global $bp_media_admin_is_current;
	$bp_media_admin_is_current = true;
		?>
	
	<div class="wrap bp-media-admin">
		<?php //screen_icon( 'buddypress' ); ?>
		<div id="icon-buddypress" class="icon32"><br></div>
		<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Media', 'bp-media' ) ); ?></h2>
		
		<?php if(count($bp_media_errors)) { ?>
		<div class="error"><p><?php foreach($bp_media_errors as $error) echo $error.'<br/>'; ?></p></div>
		<?php } if(count($bp_media_messages)){?>
		<div class="updated"><p><?php foreach($bp_media_messages as $message) echo $message.'<br/>'; ?></p></div>
		<?php }?>
        <div class="metabox-holder columns-2">
            <div id="bp-media-settings-boxes">
            <form method="post" action="?page=bp-media-settings">
                <?php wp_nonce_field( 'bp_media_update_options' ); ?>
                        <div class="postbox">
                            <div class="handlediv" title="Click to toggle"><br></div>
                            <h3 class="hndle"><span>Media Types Enabled</span></h3>
                            <div class="inside">
                                <table class="form-table ">
                                    <tbody>
                                        <tr valign="top">
                                            <th scope="row"><label for="enable_videos">Videos</label></th>
                                            <td>
                                                <fieldset>
                                                    <legend class="screen-reader-text"><span>Enable Videos</span></legend>
                                                    <label for="enable_videos"><input name="enable_videos" type="checkbox" id="enable_videos" value="1" <?php global $bp_media_options;checked($bp_media_options['videos_enabled'],true) ?>> (Check to enable video upload functionality)</label>
                                                </fieldset>
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row"><label for="enable_audio">Audio</label></th>
                                            <td>
                                                <fieldset>
                                                    <legend class="screen-reader-text"><span>Enable Audio</span></legend>
                                                    <label for="enable_audio"><input name="enable_audio" type="checkbox" id="enable_audio" value="1" <?php checked($bp_media_options['audio_enabled'],true) ?>> (Check to enable audio upload functionality)</label>
                                                </fieldset>
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row"><label for="enable_images">Images</label></th>
                                            <td>
                                                <fieldset>
                                                    <legend class="screen-reader-text"><span>Enable Images</span></legend>
                                                    <label for="enable_images"><input name="enable_images" type="checkbox" id="enable_images" value="1" <?php checked($bp_media_options['images_enabled'],true) ?>> (Check to enable images upload functionality)</label>
                                                </fieldset>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="postbox">
                            <div class="handlediv" title="Click to toggle"><br></div>
                            <h3 class="hndle"><span>Spread the word</span></h3>
                            <div class="inside">
                <table class="form-table ">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="remove_linkback">Spread the word</label></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Yes, I want to support BuddyPress Media</span></legend>
                                    <label for="remove_linkback_yes"><input name="remove_linkback" type="radio" id="remove_linkback_yes" value="2" <?php if(get_option('bp_media_remove_linkback')=='2') echo 'checked="checked"' ?>> Yes, we support BuddyPress Media</label>
                                    <br/>
                                    <legend class="screen-reader-text"><span>No, I don't want to support BuddyPress Media</span></legend>
                                    <label for="remove_linkback_no"><input name="remove_linkback" type="radio" id="remove_linkback_no" value="1" <?php if(get_option('bp_media_remove_linkback')=='1') echo 'checked="checked"' ?>> No, we don't support BuddyPress Media</label>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
                                </div>
                        </div>
                <?php do_action('bp_media_extension_options'); ?>
                            <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>
            </form>
			
            <div class="postbox">
                <div class="handlediv" title="Click to toggle"><br></div>
                <h3 class="hndle"><span>Other Options</span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><label for="refresh_media_count">Re-Count Media Entries</label></th>
                                <td> <fieldset>
                                        <a id="refresh_media_count" href ="?page=bp-media-settings&bp_media_refresh_count=1&wp_nonce=<?php echo wp_create_nonce( 'bp_media_refresh_count' ); ?>" class="button" title="<?php printf(__('It will re-count all media entries of all users and correct any discrepancies.')); ?>">Re-Count</a>
                                    </fieldset></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="metabox-fixed metabox-holder alignright">
            <?php bp_media_default_admin_sidebar(); ?>
        </div>
    </div>
</div>        
	<?php
}



function bp_media_addons_list(){ ?>
    <div class="wrap bp-media-admin">
        <?php //screen_icon( 'buddypress' ); ?>
        <div id="icon-buddypress" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Media', 'bp-media' ) ); ?></h2>
        <div class="metabox-holder columns-2">
            <div id="bp-media-settings-boxes">
                <div class="postbox">
                    <div class="handlediv" title="Click to toggle"><br></div>
                    <h3 class="hndle"><span>Media Types Enabled</span></h3>
                    <div class="inside">
                        <div class="addon-list">
                            <ul class="products">

                                <li class="product first">
                                    <a href="http://rtcamp.com/store/buddypress-media-kaltura/" target="_blank" title="BuddyPress - Media Kaltura Add-on">
                                        <img width="240" height="184" title="BuddyPress - Media Kaltura Add-on" alt="BuddyPress - Media Kaltura Add-on" class="attachment-shop_catalog wp-post-image" src="http://rtcamp.com/files/2012/10/new-buddypress-media-kaltura-logo-240x184.png">
                                        <h3>BuddyPress &ndash; Media Kaltura Add-on</h3>		
                                        <span class="price"><span class="amount">$99</span></span>
                                    </a>
                                    <a class="add_to_cart_button  product_type_simple" target="_blank" data-product_id="15446" rel="nofollow" href="http://rtcamp.com/store/?add-to-cart=15446"><?php _e('Buy Now', 'bp-media'); ?></a>
                                </li>

                                <li class="product last">
                                    <a href="http://rtcamp.com/store/buddypress-media-ffmpeg-converter/" title="BuddyPress-Media FFMPEG Converter Plugin" target="_blank">
                                        <img width="240" height="184" title="BuddyPress-Media FFMPEG Converter Plugin" alt="BuddyPress-Media FFMPEG Converter Plugin" class="attachment-shop_catalog wp-post-image" src="http://rtcamp.com/files/2012/09/ffmpeg-logo-240x184.png">
                                        <h3>BuddyPress-Media FFMPEG Converter Plugin</h3>
                                        <span class="price"><span class="amount">$49</span></span>
                                    </a>
                                    <a class="add_to_cart_button  product_type_simple" target="_blank" data-product_id="13677" rel="nofollow" href="http://rtcamp.com/store/?add-to-cart=13677"><?php _e('Buy Now', 'bp-media'); ?></a>
                                </li>
                                
                            </ul><!-- .products -->
                        </div><!-- .addon-list -->
                    </div><!-- .inside -->
                </div>
            </div><!-- .bp-media-settings-boxes -->
            <div class="metabox-fixed metabox-holder alignright">
                <?php bp_media_default_admin_sidebar(); ?>
            </div>
        </div><!-- .metabox-holder -->
    </div><!-- .bp-media-admin -->
<?php }


function bp_media_support(){ ?>
    <div class="wrap bp-media-admin">
        <?php //screen_icon( 'buddypress' ); ?>
        <div id="icon-buddypress" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Media', 'bp-media' ) ); ?></h2>
        <div class="metabox-holder columns-2">
            <div id="bp-media-settings-boxes">
            </div>
            <div class="metabox-fixed metabox-holder alignright">
                <?php bp_media_default_admin_sidebar(); ?>
            </div>
        </div>
    </div><!-- .bp-media-admin -->
<?php } 


/**
 * Default BuddyPress Media admin sidebar with metabox styling
 * 
 * @since BP Media 2.0
 */
function bp_media_default_admin_sidebar() {
	?>
        <div class="rtmetabox postbox" id="branding">
            <div class="inside">
        	<a href="http://rtcamp.com" title="Empowering The Web With WordPress" id="logo"><img src="<?php echo plugins_url( '/img/rtcamp-logo.png', __FILE__ ); ?>" alt="rtCamp" /></a>
                <ul id="social">
                    <li><a href="<?php printf('%s', 'http://www.facebook.com/rtCamp.solutions/'); ?>" target="_blank" title="<?php _e('Become a fan on Facebook', 'bp-media'); ?>" class="bp-media-facebook bp-media-social"><?php _e('Facebook', 'bp-media'); ?></a></li>
                    <li><a href="<?php printf('%s', 'https://twitter.com/rtcamp/'); ?>" target="_blank" title="<?php _e('Follow us on Twitter', 'bp-media'); ?>" class="bp-media-twitter bp-media-social"><?php _e('Twitter', 'bp-media'); ?></a></li>
                    <li><a href="<?php printf('%s', 'http://feeds.feedburner.com/rtcamp/'); ?>" target="_blank" title="<?php _e('Subscribe to our feeds', 'bp-media'); ?>" class="bp-media-rss bp-media-social"><?php _e('RSS Feed', 'bp-media'); ?></a></li>
                </ul>
            </div>
        </div>

	<div class="rtmetabox postbox" id="support">
		
		<h3 class="hndle"><span><?php _e('Need Help?', 'bp-media'); ?></span></h3>
		<div class="inside"><p><?php printf(__(' Please use our <a href="%s">free support forum</a>.<br/><span class="bpm-aligncenter">OR</span><br/>
		<a href="%s">Hire us!</a> To get professional customisation/setup service.', 'bp-media'), 'http://rtcamp.com/support/forum/buddypress-media/','http://rtcamp.com/buddypress-media/hire/'); ?>.</p></div>
	</div>

	<div class="rtmetabox postbox" id="bp-media-premium-addons">
		
		<h3 class="hndle"><span><?php _e('Premium Addons', 'bp-media'); ?></span></h3>
		<div class="inside">
			<ul>
				<li><a href="http://rtcamp.com/store/buddy-press-media-ffmpeg/" title="BuddyPress Media FFMPEG">BPM-FFMPEG</a> - add FFMEG-based audio/video conversion support</li>
			</ul>
			<h4><?php printf(__('Are you a developer?','bp-media')) ?></h4>
			<p><?php printf(__('If you are developing a BuddyPress Media addon we would like to include it in above list. We can also help you sell them. <a href="%s">More info!</a>','bp-media'),'http://rtcamp.com/contact/') ?></p></h4>
		</div>
	</div>

	<div class="rtmetabox postbox" id="bp_media_latest_news">
		
		<h3 class="hndle"><span><?php _e('Latest News', 'bp-media'); ?></span></h3>
		<div class="inside"><img src ="<?php echo admin_url(); ?>/images/wpspin_light.gif" /> Loading...</div>
	</div><?php
}

/**
 * Enqueues the scripts and stylesheets needed for the BuddyPress Media's options page
 */
function bp_media_admin_enqueue() {
    $current_screen = get_current_screen();
    $admin_js = trailingslashit(site_url()).'?bp_media_get_feeds=1';
    wp_enqueue_script('bp-media-js',plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
    wp_localize_script('bp-media-js','bp_media_news_url',$admin_js);
    wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
//        if(isset($current_screen->base)&&$current_screen->base=='toplevel_page_bp-media-settings'){
            wp_enqueue_script('dashboard');
//    }
}
add_action('admin_enqueue_scripts', 'bp_media_admin_enqueue');



/**
 * Adds a tab for Media settings in the BuddyPress settings page
 * 
 * 
 */
function bp_media_admin_tab() {
    if(current_user_can('manage_options')){
        $tabs_html    = '';
        $idle_class   = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        $tabs = array();

        // Check to see which tab we are on
        $tab = isset( $_GET['page'] )  ? $_GET['page'] : "bp-media-settings";
        /* BP Media */
        $tabs[] = array(
            'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-settings'  ), 'admin.php' ) ),
            'name' => __( 'BP Media', 'bp-media' ),
            'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class        
            );

        /* Addons */
        $tabs[] = array(
            'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-addons'  ), 'admin.php' ) ),
            'name' => __( 'Addons', 'bp-media' ),
            'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class        
        );

        /* Support */
        $tabs[] = array(
            'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-media-support'  ), 'admin.php' ) ),
            'name' => __( 'Support', 'bp-media' ),
            'class' => ($tab == 'bp-media-support') ? $active_class : $idle_class        
        );

        foreach($tabs as $tab){
            $tabs_html.= '<a id="bp-media" href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
        }
        echo $tabs_html;	
    }
}

add_action('bp_admin_tabs','bp_media_admin_tab');


/**
 * Adds which function to execute when bp-media settings page is called
 * 
 * 
 */
function bp_media_add_menu() {
	global $bp;
	
	if ( !is_super_admin() )
		return false;
	
	$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
	
	$hook = add_submenu_page( $page, __( 'Media', 'bp-media' ), __( 'Media', 'bp-media' ), 'manage_options', 'bp-media-settings', "bp_media_admin_menu" );

	// Fudge the highlighted subnav item when on the BuddyPress Forums admin page
	add_action( "admin_head-$hook", 'bp_core_modify_admin_menu_highlight' );
}
//add_action( bp_core_admin_hook(), 'bp_media_add_menu' );

/**
 * Removes the Media submenu item from the settings/options-general page so that there will only be one BuddyPress option
 */
function bp_media_modify_admin_menu() {
 	$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
	remove_submenu_page( $page, 'bp-media-settings');
}
//add_action( 'admin_head', 'bp_media_modify_admin_menu', 999 );