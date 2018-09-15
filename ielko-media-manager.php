<?php
/*
Plugin Name: Ielko Media Manager
Plugin URI: https://github.com/upggr/ielko-media-manager/releases/latest
Description: Media manager for Roku, tvOS, iOS, android, windows, ionic, osx clients
Version: 0.1.7
Author: Ioannis Kokkinis
Author URI: http://ielko.com
License: Commercial
*/


require_once('ielko-updater.php');
if (is_admin()) {
    new IelkoUpdater(__FILE__, 'upggr', "ielko-media-manager");
}

if (! class_exists('WC_CPInstallCheck')) {
    class WC_CPInstallCheck
    {
        public static function install()
        {
            if (!in_array('categories-images/categories-images.php', apply_filters('active_plugins', get_option('active_plugins')))
            ||
                    !in_array('wp-cors/wp-cors.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                deactivate_plugins(__FILE__);
                $error_message = __('This plugin requires <a target="_blank" href="https://wordpress.org/plugins/categories-images/">Categories Images</a> and <a target="_blank" href="https://wordpress.org/plugins/wp-cors/">WP Cors</a> &amp; plugins to be active! Install them, activate them and come back here :) ', 'categories-images');
                die($error_message);
            }
        }
    }
}
register_activation_hook(__FILE__, array('WC_CPInstallCheck', 'install'));


function ielko_wp_media_manager()
{
    $labels = array(
        'name'                  => _x('Media Items', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Media Item', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Ielko Media', 'text_domain'),
        'name_admin_bar'        => __('Ielko Media', 'text_domain'),
        'archives'              => __('Item Archives', 'text_domain'),
        'attributes'            => __('Item Attributes', 'text_domain'),
        'parent_item_colon'     => __('Parent Item:', 'text_domain'),
        'all_items'             => __('All Items', 'text_domain'),
        'add_new_item'          => __('Add New Item', 'text_domain'),
        'add_new'               => __('Add New', 'text_domain'),
        'new_item'              => __('New Item', 'text_domain'),
        'edit_item'             => __('Edit Item', 'text_domain'),
        'update_item'           => __('Update Item', 'text_domain'),
        'view_item'             => __('View Item', 'text_domain'),
        'view_items'            => __('View Items', 'text_domain'),
        'search_items'          => __('Search Item', 'text_domain'),
        'not_found'             => __('Not found', 'text_domain'),
        'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
        'featured_image'        => __('Featured Image', 'text_domain'),
        'set_featured_image'    => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image'    => __('Use as featured image', 'text_domain'),
        'insert_into_item'      => __('Insert into item', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
        'items_list'            => __('Items list', 'text_domain'),
        'items_list_navigation' => __('Items list navigation', 'text_domain'),
        'filter_items_list'     => __('Filter items list', 'text_domain'),
    );
    $args = array(
        'label'                 => __('Media Item', 'text_domain'),
        'description'           => __('Media (video or audio)', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array( 'title', 'custom-fields','thumbnail' ),
        'taxonomies'            => array( 'category'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
    );
    register_post_type('media_item', $args);
}




function replace_featured_image_box()
{
    remove_meta_box('postimagediv', 'media_item', 'side');
    add_meta_box('postimagediv', __('Media Image'), 'post_thumbnail_meta_box', 'media_item', 'normal', 'high');
}

function custom_admin_post_thumbnail_html($content)
{
    return $content = str_replace(__('Set featured image'), __('Set the media image thumbnail'), $content);
}
add_filter('admin_post_thumbnail_html', 'custom_admin_post_thumbnail_html');


function wpse_98269_script()
{
    if (isset($GLOBALS['post'])) {
        $post_type = get_post_type($GLOBALS['post']);
        if (post_type_supports($post_type, 'custom-fields')) {
            ?>
                <script>
                    var $metakeyinput = jQuery('#metakeyinput'),
                        $metakeyselect = jQuery('#metakeyselect');
                    if ($metakeyinput.length && ( ! $metakeyinput.hasClass('hide-if-js'))) {
                        $metakeyinput.addClass('hide-if-js'); // Using WP admin class.
                        $metakeyselect = jQuery('<select id="metakeyselect" name="metakeyselect">').appendTo('#newmetaleft');
                        $metakeyselect.append('<option value="#NONE#">— Select —</option>');
                    }
                    if (jQuery("[value='media_url']").length < 1) {
                        $metakeyselect.append("<option value='media_url'>Media URL</option>");
                    }
                    if (jQuery("[value='media_thumb']").length < 1) {
                        $metakeyselect.append("<option value='media_thumb'>Media Thumbnail</option>");
                    }
                </script>

            <?php
        }
    }
}



//meta box
function media_meta_box($post)
{
    $id = 'media_info';
    $title = 'Media Information';
    $callback = 'create_work_meta';
    $screen = 'media_item';
    $context = 'normal';
    $priority = 'low';
    add_meta_box($id, $title, $callback, $screen, $context, $priority);
}

function checkactive($thevar, $thecheck)
{
    if ($thevar == $thecheck) {
        return 'checked';
    } elseif ($thevar =! $thecheck) {
        return '';
    }
}


function create_work_meta($post)
{
    wp_nonce_field(basename(__FILE__), 'media_meta_box_nonce');
    $media_url = get_post_meta($post->ID, 'media_url', true);
    $media_description = get_post_meta($post->ID, 'media_description', true);
    $media_active = get_post_meta($post->ID, 'media_active', true);
    $media_type = get_post_meta($post->ID, 'media_type', true);
    $media_qty = get_post_meta($post->ID, 'media_qty', true);
    $media_excl_check = get_post_meta($post->ID, 'media_excl_check', true);
    $media_excl_lists = get_post_meta($post->ID, 'media_excl_lists', true);
    $media_excl_general = get_post_meta($post->ID, 'media_excl_general', true);
    $media_excl_premium = get_post_meta($post->ID, 'media_excl_premium', true);
    echo '<div>
        <p>
            <label for=\'media_url\'>Media URL (could be the url of a  video you uploaded in the media library, a youtube link, an m3u8 link etc..) - If this is a youtube link make sur is in the form : https://youtube....=xxxxxx Also note that you dont need to upload an image if you have supplied a youtube video :</label>
            <br />
            <input type=\'url\' name=\'media_url\' value=\'' . $media_url .'\' required style=\'width:97%\' />
        </p>

        <p>
            <label for=\'media_description\'>Media Description:</label>
            <br />
            <input type=\'text\' name=\'media_description\' value=\''. $media_description . '\' style=\'width:97%\' />
        </p>

        <p>
            <label for=\'media_active\'>Is it Active? :</label>
            <br />
            <input type=\'radio\' name=\'media_active\' value=\'1\' '.checkactive($media_active, 1).'> Yes<br>
            <input type=\'radio\' name=\'media_active\' value=\'0\' '.checkactive($media_active, 0).'> No<br>
        </p>

        <p>
            <label for=\'media_type\'>Media Type :</label>
            <br />
            <input type=\'radio\' name=\'media_type\' value=\'1\' '.checkactive($media_type, 1).'> VIDEO<br>
            <input type=\'radio\' name=\'media_type\' value=\'0\' '.checkactive($media_type, 0).'> AUDIO<br>
        </p>
        <p>
            <label for=\'media_qty\'>Media Quality (for video) :</label>
            <br />
            <input type=\'radio\' name=\'media_qty\' value=\'1\' '.checkactive($media_qty, 1).'> SD <br>
            <input type=\'radio\' name=\'media_qty\' value=\'0\' '.checkactive($media_qty, 0).'> HD <br>
        </p>
				<p>
						<label for=\'media_excl_check\'>Exclude from dead link checks :</label>
						<br />
						<input type=\'radio\' name=\'media_excl_check\' value=\'1\' '.checkactive($media_excl_check, 1).'> Yes <br>
						<input type=\'radio\' name=\'media_excl_check\' value=\'0\' '.checkactive($media_excl_check, 0).'> No <br>
				</p>

				<p>
						<label for=\'media_excl_lists\'>Exclude from update lists :</label>
						<br />
						<input type=\'radio\' name=\'media_excl_lists\' value=\'1\' '.checkactive($media_excl_lists, 1).'> Yes <br>
						<input type=\'radio\' name=\'media_excl_lists\' value=\'0\' '.checkactive($media_excl_lists, 0).'> No <br>
				</p>

				<p>
						<label for=\'media_excl_general\'>Exclude from all feeds :</label>
						<br />
						<input type=\'radio\' name=\'media_excl_general\' value=\'1\' '.checkactive($media_excl_general, 1).'> Yes <br>
						<input type=\'radio\' name=\'media_excl_general\' value=\'0\' '.checkactive($media_excl_general, 0).'> No <br>
				</p>

				<p>
						<label for=\'media_excl_premium\'>Premium (hide url on feeds) :</label>
						<br />
						<input type=\'radio\' name=\'media_excl_premium\' value=\'1\' '.checkactive($media_excl_premium, 1).'> Yes <br>
						<input type=\'radio\' name=\'media_excl_premium\' value=\'0\' '.checkactive($media_excl_premium, 0).'> No <br>
				</p>


    </div>';
}




function save_media_meta($post_id)
{
    if (!isset($_POST['media_meta_box_nonce']) || !wp_verify_nonce($_POST['media_meta_box_nonce'], basename(__FILE__))) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    } elseif (!current_user_can('edit_page', $post_id)) {
        return;
    }
    if (isset($_REQUEST['media_url'])) {
        update_post_meta($post_id, 'media_url', sanitize_text_field($_POST['media_url']));
    }

    if (isset($_REQUEST['media_description'])) {
        update_post_meta($post_id, 'media_description', sanitize_text_field($_POST['media_description']));
    }
    if (isset($_REQUEST['media_active'])) {
        update_post_meta($post_id, 'media_active', sanitize_text_field($_POST['media_active']));
    }
    if (isset($_REQUEST['media_type'])) {
        update_post_meta($post_id, 'media_type', sanitize_text_field($_POST['media_type']));
    }
    if (isset($_REQUEST['media_qty'])) {
        update_post_meta($post_id, 'media_qty', sanitize_text_field($_POST['media_qty']));
    }
    if (isset($_REQUEST['media_excl_check'])) {
        update_post_meta($post_id, 'media_excl_check', sanitize_text_field($_POST['media_excl_check']));
    }
    if (isset($_REQUEST['media_excl_lists'])) {
        update_post_meta($post_id, 'media_excl_lists', sanitize_text_field($_POST['media_excl_lists']));
    }
    if (isset($_REQUEST['media_excl_general'])) {
        update_post_meta($post_id, 'media_excl_general', sanitize_text_field($_POST['media_excl_general']));
    }
    if (isset($_REQUEST['media_excl_premium'])) {
        update_post_meta($post_id, 'media_excl_premium', sanitize_text_field($_POST['media_excl_premium']));
    }
}


function channel_list()
{
    query_posts("post_type=media_item");
    $wpb_all_query = new WP_Query(array('post_type'=>'media_item', 'post_status'=>'publish', 'posts_per_page'=>-1));
    if ($wpb_all_query->have_posts()) :
while ($wpb_all_query->have_posts()) : $wpb_all_query->the_post();

    $thetitle = get_the_title();
    $theurl = get_post_meta(get_the_ID(), 'media_url', true);
    $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
    $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
    $theimg =  $theimg[0];
    echo '<a target="_blank" href="http://greektv.upg.gr/upg_player.html?play='.$theurl.'">'.$thetitle.'</a><br />';
    endwhile;
    wp_reset_postdata(); else :
    _e('Nothing to display.');
    endif;
}

add_shortcode('ielko_channels', 'channel_list');



function rokuXML()
{
    add_feed('roku', 'rokuXMLFunc');
}

function rokuXMLFunc()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
    echo '<categories>';

    $cats = get_categories();
    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        $thecategoryimg = z_taxonomy_image_url($cat->term_id);
        if ($thecategory != 'Uncategorized') {
            query_posts("cat=$thecatid&posts_per_page=100&post_type='media_item");

            echo '<category title="'.$thecategory.'" description="'.$thecategorydesc.'" sd_img="'.$thecategoryimg.'" hd_img="'.$thecategoryimg.'">';
            echo '<feed title="'.$thecategory.'" description="'.$thecategorydesc.'" sd_img="'.$thecategoryimg.'" hd_img="'.$thecategoryimg.'">';
            if (have_posts()) : while (have_posts()) : the_post();
            $thetitle = get_the_title();
            $theurl = get_post_meta(get_the_ID(), 'media_url', true);
            $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
            $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
            $theimg =  $theimg[0];
            $thefrmt = 'hls';
            $thestrg = 'full-adaptation';
            $thequality = get_post_meta(get_the_ID(), 'media_qty', true);
            if ($thequality == 1) {
                $thequality_ = 'SD';
            } elseif ($thequality == 0) {
                $thequality_ = 'HD';
            }
            $thebitrate = '0';

            if (strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false) {
                echo '<item sdImg="'.$theimg.'" hdImg="'.$theimg.'">
          <title>'.$thetitle.'</title>
          <description>'.$thedescription.'</description>
          <streamFormat>'.$thefrmt.'</streamFormat>
          <switchingStrategy>'.$thestrg.'</switchingStrategy>
          <media>
          <streamFormat>'.$thefrmt.'</streamFormat>
          <streamQuality>'.$thequality_.'</streamQuality>
          <streamBitrate>'.$thebitrate.'</streamBitrate>
          <streamUrl>'.$theurl.'</streamUrl>
          </media>
          </item>';
            }
            endwhile;
            endif;
            echo '</feed>';
            echo '</category>';
        }
    }
    echo '</categories>';
}
function rokuXMLcats()
{
    add_feed('roku_cats', 'rokuXMLcats_f');
}

function rokuXMLcats_f()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'" standalone="yes"?>';
    echo '<categories>';
    global $wpdb;
    $args = array(
  'orderby' => 'name',
  'parent' => 0
  );
    $categories = get_categories($args);
    $first = true;

    foreach ($categories as $category) {
        if ($category->cat_name != 'Uncategorized') {
            echo '<category title="'.$category->cat_name.'" description="'.$category->description.'" sd_img="'.z_taxonomy_image_url($category->term_id).'"  hd_img="'.z_taxonomy_image_url($category->term_id).'"  >';
            $theid = $category->term_id;
            $children = $wpdb->get_results("SELECT term_id FROM $wpdb->term_taxonomy WHERE parent=$theid");
            $no_children = count($children);
            if ($no_children > 0) {
                $args2 = array(
         'orderby' => 'name',
         'parent' => 2
         );
                $args2["parent"]=$category->term_id;
                $categories2 = get_categories($args2);
                foreach ($categories2 as $category2) {
                    $thesiteurl = get_site_url();
                    $the_cat_name = $category2->cat_name;
                    $the_cat_id = $category2->cat_ID;
                    $theurlforthecatfeed = htmlspecialchars($thesiteurl.'/?feed=roku_by_cat&cat='.$the_cat_id);
                    echo '<categoryLeaf title="'.$category2->cat_name.'" description="'.$category2->cat_description.'" feed="'.$theurlforthecatfeed.'"/>';
                }
            } else {
                //  echo '</category>';
            }
            echo '</category>';
        }
    }
    echo '</categories>';
}


function genimg()
{
    add_feed('gen_img', 'genimg_f');
}


function genimg_f()
{
    $orig = $_GET['orig'];
    $wi = $_GET['wi'];
    $he = $_GET['he'];
    $txt = $_GET['txt'];
    header("Content-type:image/png");
    //header("Content-disposition: attachment; filename=".$txt.".png");
    $txt = substr_replace($txt, "", -4);
    $txt = preg_replace('/\s+/', '_', $txt);

    if ($orig=="") {
        $options = get_option('ivc_settings');
        $orig = str_replace(get_site_url(), "", $options['ivc_image_field_5']);
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $orig = str_replace('/wp-content/uploads', $upload_dir, $orig);
    }
    $fontsize = $_GET['fontsize'];
    $imagetobewatermark=imagecreatefrompng($orig);
    $watermarktext = $txt;
    $font= plugin_dir_path(__FILE__) . 'font/cent.ttf';
    $white = imagecolorallocate($imagetobewatermark, 255, 0, 0);
    imagettftext($imagetobewatermark, $fontsize, 0, 170, 250, $white, $font, $watermarktext);
    imagepng($imagetobewatermark);
    imagedestroy($imagetobewatermark);

    exit;
}



function rokuDP()
{
    add_feed('roku_dp', 'rokuDP_f');
}

function ionic()
{
    add_feed('ionic', 'ionic_f');
}
function ionic_dev()
{
    add_feed('ionic_dev', 'ionic_f_dev');
}


function ionic_f_bkp()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: application/json');

    $themainarray = array(
    "providerName" =>  get_bloginfo('name'),
    "language" => "en-US",
    "lastUpdated" => mysql2date(
        'Y-m-d\TH:i:s\Z',
        get_lastpostmodified('GMT'),
        false
    )
);

    $cats = get_categories();

    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        $thecategoryimg = z_taxonomy_image_url($cat->term_id);
        query_posts("cat=$thecatid&posts_per_page=1000&post_type=media_item");
        if (have_posts()) :
                    while (have_posts()) : the_post();
        $thetitle = get_the_title();
        $theurl = get_post_meta(get_the_ID(), 'media_url', true);
        $thedescription = get_post_meta(get_the_ID(), 'media_description', true);

        $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
        $theimg =  $theimg[0];

        $thefrmt = 'hls';
        $thestrg = 'full-adaptation';
        $thequality = get_post_meta(get_the_ID(), 'media_qty', true);
        $ispremium = get_post_meta(get_the_ID(), 'media_excl_premium', true);
        $isactive = get_post_meta(get_the_ID(), 'media_active', true);
        if ($thequality == 1) {
            $thequality_ = 'SD';
        } elseif ($thequality == 0) {
            $thequality_ = 'HD';
        }
        $thebitrate = '0';

        //    if (  (strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false)  && $isactive == 1) {
        if ($isactive == 1) {
            if ($ispremium == 1) {
                $theurl_checked	= 'http://non.disclosed.com';
            } else {
                $theurl_checked = $theurl;
            }

            $genres = array("special");
            $tags = array($thecategory);
            $captions = array();
            if ($theimg === null) {
                if (strpos($theurl, 'youtube') === false) {
                    $thetitle_ = preg_replace('/\s+/', '_', $thetitle);
                    $theimg = get_site_url().'/?feed=gen_img&wi=800&orig=&he=450&fontsize=30&txt='.$thetitle_.'.png';
                } else {
                    $thevideo = explode("=", $theurl);
                    $theimg = "https://img.youtube.com/vi/".$thevideo[1]."/hqdefault.jpg";
                }
            }
            if (!$thedescription) {
                $thedescription = 'Enjoy '.$thetitle.' from the '.$thecategory.' category. You may also view it on your computer using VLC or any other hls compatible audio/video player from : '.$theurl_checked;
            }
            $theitemarray = array(
    "id" => hash('ripemd160', $thetitle.$theimg.$thecatid),
    "title" => $thetitle,
    "shortDescription" => $thedescription,
    "thumbnail" => $theimg,
    "genres" => $genres,
    "tags" => $tags,
    "releaseDate" => get_the_modified_date('Y-m-d'),
    "content" => array(
        "dateAdded" => get_the_modified_date('Y-m-d\TH:i:s\Z'),
        "captions" => $captions,
        "duration" => 999,
    )
);

            $theitemarray['content']['videos'][] = array(
    "url" => $theurl,
    "quality" => $thequality_,
    "videoType" => $thefrmt
);
            $themainarray['tvSpecials'][] = $theitemarray;
        }

        endwhile;
        endif;
    }


    $json_resp = json_encode($themainarray);
    echo $json_resp;
}

function ionic_f()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: application/json');

    $themainarray = array(
    "providerName" =>  get_bloginfo('name'),
    "language" => "en-US",
    "lastUpdated" => mysql2date(
        'Y-m-d\TH:i:s\Z',
        get_lastpostmodified('GMT'),
        false
    )
);

    $cats = get_categories();

    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        $thecategoryimg = z_taxonomy_image_url($cat->term_id);

        $cat_array = array(
                    "category_id" => $thecatid,
                    "category_name" => $thecategory,
                    "category_desc" => $thecategorydesc,
                    "category_img" => $thecategoryimg,
                );
        $themainarray['categories'][] = $cat_array;
    }

    query_posts("posts_per_page=1000&post_type=media_item&orderby=date&order=ASC");
    if (have_posts()) :  while (have_posts()) : the_post();
    $thetitle = get_the_title();
    $theurl = get_post_meta(get_the_ID(), 'media_url', true);
    $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
    $category = get_the_category();
    $thecategory = $category[0]->cat_name;
    $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
    $theimg =  $theimg[0];

    $thefrmt = 'hls';
    $thestrg = 'full-adaptation';
    $thequality = get_post_meta(get_the_ID(), 'media_qty', true);
    $ispremium = get_post_meta(get_the_ID(), 'media_excl_premium', true);
    $isactive = get_post_meta(get_the_ID(), 'media_active', true);
    if ($thequality == 1) {
        $thequality_ = 'SD';
    } elseif ($thequality == 0) {
        $thequality_ = 'HD';
    }
    $thebitrate = '0';

    //    if (  (strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false)  && $isactive == 1) {
    if ($isactive == 1) {
        if ($ispremium == 1) {
            $theurl_checked	= 'http://non.disclosed.com';
        } else {
            $theurl_checked = $theurl;
        }

        $genres = array("special");
        $tags = array($thecategory);
        $captions = array();
        if ($theimg === null) {
            if (strpos($theurl, 'youtube') === false) {
                $thetitle_ = preg_replace('/\s+/', '_', $thetitle);
                $theimg = get_site_url().'/?feed=gen_img&wi=800&orig=&he=450&fontsize=30&txt='.$thetitle_.'.png';
            } else {
                $thevideo = explode("=", $theurl);
                $theimg = "https://img.youtube.com/vi/".$thevideo[1]."/hqdefault.jpg";
            }
        }
        if (!$thedescription) {
            $thedescription = 'Enjoy '.$thetitle.' from the '.$thecategory.' category. You may also view it on your computer using VLC or any other hls compatible audio/video player from : '.$theurl_checked;
        }
        $theitemarray = array(
"id" => hash('ripemd160', $thetitle.$theimg.$thecatid),
"title" => $thetitle,
"shortDescription" => $thedescription,
"thumbnail" => $theimg,
"genres" => $genres,
"tags" => $tags,
"releaseDate" => get_the_modified_date('Y-m-d'),
"content" => array(
        "dateAdded" => get_the_modified_date('Y-m-d\TH:i:s\Z'),
        "captions" => $captions,
        "duration" => 999,
)
);

        $theitemarray['content']['videos'][] = array(
"url" => $theurl,
"quality" => $thequality_,
"videoType" => $thefrmt
);
        $themainarray['tvSpecials'][] = $theitemarray;
    }

    endwhile;
    endif;

    //	 echo '<pre>';
    //  print_r($themainarray);
    //	 echo '</pre>';

    $json_resp = json_encode($themainarray);
    echo $json_resp;
}

function rokuDP_f()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: application/json');

    $themainarray = array(
    "providerName" =>  get_bloginfo('name'),
    "language" => "en-US",
    "lastUpdated" => mysql2date(
        'Y-m-d\TH:i:s\Z',
        get_lastpostmodified('GMT'),
        false
    )
);

    $cats = get_categories();

    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        $thecategoryimg = z_taxonomy_image_url($cat->term_id);
        query_posts("cat=$thecatid&posts_per_page=1000&post_type=media_item");
        if (have_posts()) :
                    while (have_posts()) : the_post();
        $thetitle = get_the_title();
        $theurl = get_post_meta(get_the_ID(), 'media_url', true);
        $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
        $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
        $theimg =  $theimg[0];
        $thefrmt = 'hls';
        $thestrg = 'full-adaptation';
        $thequality = get_post_meta(get_the_ID(), 'media_qty', true);
        $ispremium = get_post_meta(get_the_ID(), 'media_excl_premium', true);
        $isactive = get_post_meta(get_the_ID(), 'media_active', true);
        if ($thequality == 1) {
            $thequality_ = 'SD';
        } elseif ($thequality == 0) {
            $thequality_ = 'HD';
        }
        $thebitrate = '0';

        if ((strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false)  && $isactive == 1) {
            if ($ispremium == 1) {
                $theurl_checked	= 'http://non.disclosed.com';
            } else {
                $theurl_checked = $theurl;
            }

            $genres = array("special");
            $tags = array($thecategory);
            $captions = array();
            if ($theimg === null) {
                $thetitle_ = preg_replace('/\s+/', '_', $thetitle);
                $theimg = get_site_url().'/?feed=gen_img&wi=800&orig=&he=450&fontsize=30&txt='.$thetitle_.'.png';
            }
            if (!$thedescription) {
                $thedescription = 'Enjoy '.$thetitle.' from the '.$thecategory.' category. You may also view it on your computer using VLC or any other hls compatible audio/video player from : '.$theurl_checked;
            }
            $theitemarray = array(
    "id" => hash('ripemd160', $thetitle.$theimg.$thecatid),
    "title" => $thetitle,
    "shortDescription" => $thedescription,
    "thumbnail" => $theimg,
    "genres" => $genres,
    "tags" => $tags,
    "releaseDate" => get_the_modified_date('Y-m-d'),
    "content" => array(
        "dateAdded" => get_the_modified_date('Y-m-d\TH:i:s\Z'),
        "captions" => $captions,
        "duration" => 999,
    )
);

            $theitemarray['content']['videos'][] = array(
    "url" => $theurl,
    "quality" => $thequality_,
    "videoType" => $thefrmt
);
            $themainarray['tvSpecials'][] = $theitemarray;
        }

        endwhile;
        endif;
    }
    $json_resp = json_encode($themainarray);
    echo $json_resp;
}




function rokuXMLbycat()
{
    add_feed('roku_by_cat', 'rokuXMLbycat_f');
}

function rokuXMLbycat_f()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'" standalone="yes"?'.'>';
    echo '<feed>';
    $cats = get_categories();

    foreach ($cats as $cat) {
        $thecatid = $cat->cat_ID;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        $thecategoryimg = z_taxonomy_image_url($cat->term_id);
        if ($thecatid == $_GET['cat']) {
            query_posts("cat=$thecatid&posts_per_page=1000&post_type=media_item");

            if (have_posts()) :
                        $thePosts = query_posts("cat=$thecatid&posts_per_page=1000&post_type=media_item");
            global $wp_query;
            $noposts= $wp_query->found_posts;
            $noposts= 7;
            echo '<resultLength>'.$noposts.'</resultLength>';
            echo '<endIndex>'.$noposts.'</endIndex>';
            while (have_posts()) : the_post();
            $thetitle = get_the_title();
            $theurl = get_post_meta(get_the_ID(), 'media_url', true);
            $theurl =  htmlspecialchars($theurl);
            $ispremium = get_post_meta(get_the_ID(), 'media_excl_premium', true);
            $isactive = get_post_meta(get_the_ID(), 'media_active', true);
            if ((strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false) && $isactive == 1) {
                if ($ispremium == 1) {
                    $theurl_checked	= 'http://non.disclosed.com';
                } else {
                    $theurl_checked = $theurl;
                }

                $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
                if (!$thedescription) {
                    $thedescription = 'Enjoy '.$thetitle.' from the '.$thecategory.' category. You may also view it on your computer using VLC or any other hls compatible audio/video player from : '.$theurl_checked;
                }
                $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
                $theimg =  $theimg[0];
                if ($theimg === null) {
                    $thetitle_ = preg_replace('/\s+/', '_', $thetitle);
                    $theimg = get_site_url().'/?feed=gen_img&wi=800&orig=&he=450&fontsize=30&txt='.$thetitle_.'.png';
                }

                $thefrmt = 'hls';
                $thestrg = 'full-adaptation';
                $thequality = get_post_meta(get_the_ID(), 'media_qty', true);
                if ($thequality == 1) {
                    $thequality_ = 'SD';
                } elseif ($thequality == 0) {
                    $thequality_ = 'HD';
                }
                $thebitrate = '0';

                echo '<item sdImg="'.htmlspecialchars($theimg).'" hdImg="'.htmlspecialchars($theimg).'">
          <title>'.$thetitle.'</title>
					<contentId>'.hash('ripemd160', $theurl).'</contentId>
					<contentType>TV Specials</contentType>
					<contentQuality>'.$thequality_.'</contentQuality>
					<streamFormat>'.$thefrmt.'</streamFormat>
          <media>
					<streamQuality>'.$thequality_.'</streamQuality>
          <streamBitrate>'.$thebitrate.'</streamBitrate>
          <streamUrl>'.$theurl.'</streamUrl>
          </media>
					<synopsis>'.$thedescription.'</synopsis>
					<genres>special</genres>
					<runtime>99</runtime>
          </item>';
            }
            endwhile;
            endif;
        }
    }
    echo '</feed>';
}




function tvosXML()
{
    add_feed('tvos', 'tvosXMLFunc');
}

function tvosXMLFunc()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    $options = get_option('ivc_settings');
    $thetitle = $options['ivc_text_field_1'];
    header('Content-Type: text');
    echo 'var Template = function() { return `<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
    echo '<document><catalogTemplate><banner><title>'.$thetitle.'</title></banner><list>';

    $cats = get_categories();
    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        if ($thecategory != 'Uncategorized') {
            query_posts("cat=$thecatid&posts_per_page=100&post_type='media_item");

            echo '<section><listItemLockup><title>'.$thecategory.'</title><decorationLabel>'.$thecategorydesc.'</decorationLabel><relatedContent><grid><section>';
            if (have_posts()) : while (have_posts()) : the_post();
            $theurl = get_post_meta(get_the_ID(), 'media_url', true);
            $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
            $theimg =  $theimg[0];
            if (strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false) {
                echo '<lockup videoURL="'.$theurl.'"><img src="'.$theimg.'" width="250" height="150" /></lockup>';
            }
            endwhile;
            endif;
            echo '</section></grid></relatedContent></listItemLockup></section>';
        }
    }
    echo '</list></catalogTemplate></document>`}';
}


function remote_updater()
{
    add_feed('remoteupdate', 'remoteUpdateFunc');
}

function remoteUpdateFunc()
{
    header('Content-Type: text/html');
    $data = array();
    $rfeed = $_GET['remotefeed'];
    if (strpos($rfeed, '.m3u') !== false) {
        echo 'this is m3u<br />';
        $rawData = file($rfeed, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($rawData as $line) {
            if (strpos(trim($line), '#EXTM3U') === 0) {
                continue;
            }
            if (strpos(trim($line), '#EXTINF') === 0) {
                preg_match('/#EXTINF:.*,\s*(.*)/', $line, $matches);
            } else {
                $data[] = array(
      'title'  => $matches[1],
      'url'    => trim($line)
    );
            }
        }
    }
    if (strpos($rfeed, '.xml') !== false) {
        echo 'this is xml<br />';
        $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
        $xml = file_get_contents($rfeed, false, $context);
        $xml = simplexml_load_string($xml);
        //print_r($xml);
        foreach ($xml->channel as $item) {
            $thearr = array(
        'title' => (string)$item->name,
        'url'   => (string)$item->url
    );
            $data[] = $thearr;
        }
    }
    //echo return_url_from_media_title('CARTOON TV',$data);
    if ($data) {
        query_posts("post_type='media_item&posts_per_page=1000");
        if (have_posts()) : while (have_posts()) : the_post();
        $thetitle = get_the_title();
        $theurl = get_post_meta(get_the_ID(), 'media_url', true);
        $isexcluded = get_post_meta(get_the_ID(), 'media_excl_lists', true);
        $r_url = return_url_from_media_title($thetitle, $data);
        if ($r_url) {
            if ($r_url != 'nomatch') {
                echo 'found one match for '.$thetitle.'<br />';
                echo 'comparing our url ('.$theurl.') with remote url ('.$r_url.') for '.$thetitle.'<br />';
                if ($theurl == $r_url) {
                    echo '<span style="color:green;">url is the same, exiting ('.$r_url.' vs '.$theurl.')</span><br />';
                } else {
                    if ($isexcluded == 1) {
                        echo '<span style="color:blue;">I am not updating this as it is excluded</span><br />';
                    } else {
                        echo '<span style="color:red;">I am updating this ('.$r_url.' vs '.$theurl.')</span><br />';
                        update_post_meta(get_the_ID(), 'media_url', $r_url, $theurl);
                    }
                }
            } else {
                echo '<span style="color:blue;">there was no match found in the remote file, skipping ('.$thetitle.')</span><br />';
            }
        }

        echo '<br />';
        endwhile;
        endif;
    } else {
        echo 'problems parsing data for '.$rfeed.'<br />';
    }
}

function return_url_from_media_title($searchterm, $data_array)
{
    $key = array_search($searchterm, array_column($data_array, 'title'));
    if ($key) {
        return $data_array[$key]['url'];
    } else {
        return 'nomatch';
    }
}

function check_dead_links()
{
    add_feed('checkdead', 'checkDeadFunc');
}


function checkDeadFunc()
{
    header('Content-Type: text/html');
    query_posts("post_type='media_item&posts_per_page=1000");
    if (have_posts()) : while (have_posts()) : the_post();
    $theurl = get_post_meta(get_the_ID(), 'media_url', true);
    $thestatus = get_post_meta(get_the_ID(), 'media_active', true);
    $isexcluded = get_post_meta(get_the_ID(), 'media_excl_check', true);

    if (strpos($theurl, 'm3u8') !== false || strpos($theurl, 'mp4') !== false) {
        $thecurrentstatus = checkurl_($theurl);
        if ($thestatus != checkurl_($theurl)) {
            echo 'there will be some updating from '.$thestatus.' to '.$thecurrentstatus.' for '.$theurl.'<br />';
            if ($isexcluded == 1) {
                echo 'I am not updating this as it is excluded<br />';
            } else {
                echo 'I am updating this ('.$thecurrentstatus.' vs '.$thestatus.')<br />';
                update_post_meta(get_the_ID(), 'media_active', $thecurrentstatus, $thestatus);
            }
        } else {
            echo 'the '.$theurl.' is up to date ! ('.$thestatus.' = '.$thecurrentstatus.')<br />';
        }
    } else {
        echo 'this is not an m3u8 link, will skip ('.$theurl.')<br />';
    }
    endwhile;
    endif;
}

function checkurl($url)
{
    ini_set('default_socket_timeout', 2);
    $headers = @get_headers($url);
    $headers = (is_array($headers)) ? implode("\n ", $headers) : $headers;
    return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
}

function checkurl_($url)
{
    if (checkurl($url)) {
        return "1";
    } else {
        return "0";
    }
}

function android1XML()
{
    add_feed('android1', 'android1XMLFunc');
}

function android1XMLFunc()
{
    $postCount = 1000;
    $posts = query_posts('showposts=' . $postCount);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
    echo '<channels updated="10-07-2017">';

    $cats = get_categories();
    foreach ($cats as $cat) {
        $thecatid = $cat->term_id;
        $thecategory = $cat->name;
        $thecategorydesc = $cat->description;
        if ($thecategory != 'Uncategorized') {
            query_posts("cat=$thecatid&posts_per_page=100&post_type='media_item");
            if (have_posts()) : while (have_posts()) : the_post();
            $thetitle = get_the_title();
            $thedescription = get_post_meta(get_the_ID(), 'media_description', true);
            $theurl = get_post_meta(get_the_ID(), 'media_url', true);
            $theimg =  wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail');
            $theimg =  $theimg[0];
            echo '<channel enable=""><name>'.$thetitle.'</name><group>'.$thecategory.'</group><logo>'.$theimg.'</logo><url>'.$theurl.'</url><info>'.$thedescription.'</info></channel>';
            endwhile;
            endif;
        }
    }
    echo '</channels>';
}






function inject_res($filename, $injector1, $injector2)
{
    $extension_pos = strrpos($filename, '.');
    $thenew = substr($filename, 0, $extension_pos) . '-'.$injector1 .'x'.$injector2 . substr($filename, $extension_pos);
    return $thenew;
}








function ivc_add_admin_menu()
{
    add_submenu_page('edit.php?post_type=media_item', __('Settings & Output', 'menu-test'), __('Settings & Output', 'menu-test'), 'manage_options', 'ielko_wp_media_manager', 'ivc_options_page');
    //add_menu_page( 'Ielko Settings', 'Ielko Settings', 'manage_options', 'ielko_wp_media_manager', 'ivc_options_page' );
}


function ivc_settings_init()
{
    register_setting('pluginPage', 'ivc_settings');

    add_settings_section(
        'ivc_pluginPage_section',
        __('Instructions and feeds', 'wordpress'),
        'ivc_settings_section_intro',
        'pluginPage'
    );

    add_settings_field(
    'ivc_text_field_1',
    __('Application Name', 'wordpress'),
    'ivc_text_field_1_render',
    'pluginPage',
    'ivc_pluginPage_section'
  );

    add_settings_field(
    'ivc_text_field_2',
    __('Application Slogan', 'wordpress'),
    'ivc_text_field_2_render',
    'pluginPage',
    'ivc_pluginPage_section'
  );

    add_settings_field(
        'ivc_image_field_0',
        __('icon (1024x1024)', 'wordpress'),
        'ivc_image_field_0_render',
        'pluginPage',
        'ivc_pluginPage_section'
    );

    add_settings_field(
        'ivc_image_field_1',
        __('splash (2732x2732)', 'wordpress'),
        'ivc_image_field_1_render',
        'pluginPage',
        'ivc_pluginPage_section'
    );

    add_settings_field(
        'ivc_image_field_5',
        __('default image placeholder (800x450)', 'wordpress'),
        'ivc_image_field_5_render',
        'pluginPage',
        'ivc_pluginPage_section'
    );

    add_settings_field(
        'ivc_checkbox_field_0',
        __('test checkbox', 'wordpress'),
        'ivc_checkbox_field_0_render',
        'pluginPage',
        'ivc_pluginPage_section'
    );
}


function ivc_checkbox_field_0_render()
{
    $options = get_option('ivc_settings');
    if (isset($options['ivc_checkbox_field_0'])) {
        ?>
<input type='checkbox' name='ivc_settings[ivc_checkbox_field_0]' <?php checked($options['ivc_checkbox_field_0'], 1); ?> value='1'>
<?php
    } else {
        ?>
<input type='checkbox' name='ivc_settings[ivc_checkbox_field_0]' value='1'>
<?php
    } ?>
<?php
}


function ivc_checkbox_field_1_render()
{
    $options = get_option('ivc_settings');
    if (isset($options['ivc_checkbox_field_1'])) {
        ?>
	<input type='checkbox' name='ivc_settings[ivc_checkbox_field_1]' <?php checked($options['ivc_checkbox_field_1'], 1); ?> value='1'>
	<?php
    } else {
        ?>
	<input type='checkbox' name='ivc_settings[ivc_checkbox_field_1]' value='1'>
	<?php
    } ?>
	<?php
}

function ivc_text_field_1_render()
{
    $options = get_option('ivc_settings');
    if (isset($options['ivc_text_field_1'])) {
        ?>
	<input type='text' name='ivc_settings[ivc_text_field_1]' value='<?php echo $options['ivc_text_field_1']; ?>' style='width:50%'>
	<?php
    } else {
        ?>
	<input type='text' name='ivc_settings[ivc_text_field_1]' value='' style='width:50%'>
	<?php
    } ?>
	<?php
}

function ivc_text_field_2_render()
{
    $options = get_option('ivc_settings');
    if (isset($options['ivc_text_field_2'])) {
        ?>
	<input type='text' name='ivc_settings[ivc_text_field_2]' value='<?php echo $options['ivc_text_field_2']; ?>' style='width:50%'>
	<?php
    } else {
        ?>
	<input type='text' name='ivc_settings[ivc_text_field_2]' value='' style='width:50%'>
	<?php
    } ?>
	<?php
}
function ivc_image_field_0_render()
{
    $options = get_option('ivc_settings'); ?>
	 <input type="text" name="ivc_settings[ivc_image_field_0]" id="image_url" class="regular-text" value="<?php echo $options['ivc_image_field_0']; ?>">
	 <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">
	<?php
}

function ivc_image_field_1_render()
{
    $options = get_option('ivc_settings'); ?>
	 <input type="text" name="ivc_settings[ivc_image_field_1]" id="image_url1" class="regular-text" value="<?php echo $options['ivc_image_field_1']; ?>">
	 <input type="button" name="upload-btn1" id="upload-btn1" class="button-secondary" value="Upload Image">
	<?php
}



function ivc_image_field_5_render()
{
    $options = get_option('ivc_settings'); ?>
	 <input type="text" name="ivc_settings[ivc_image_field_5]" id="image_url5" class="regular-text" value="<?php echo $options['ivc_image_field_5']; ?>">
	 <input type="button" name="upload-btn5" id="upload-btn5" class="button-secondary" value="Upload Image">
	<?php
}

function ivc_settings_section_intro()
{
    $options = get_option('ivc_settings');
    echo __('Thank you for installing the IELKO plugin.
  <br />
	  <br />
		<p>
		Note that this is still in beta mode. The roku app should work ok provided you complete the fields bellow. the tvos app, will compile fine on your mac and will run fine on the simulator or your tvos device as soon as you press play, but I have not created the logic to incorporate the media assets yet, so you will just see some images (splash screens, icons) from an existing app I am using.

		</p>
		<br />
		  <br />
  <b>STEP 1</b> : Add media sources using the Ielko Media menu in the menu, as if you were creating a new wordpress post. Complete the title of the media (for example "my awesome video"), and at least the source (for example http://mydomain.com/my_awesome_video.mp4). You will need to define a category for each item you create, and you can upload an image for the categories using the interface. You will need to have a top category (for example Live Video) and then under this the subcategories. for the direct publisher it doesnt matter but for the others it does!.
    <br />
    <br />
		<b>STEP 2</b> : Complete the fields bellow on this page.
			<br />
			<br />
			<b>STEP 3</b> : 	<span id="roku_app" fname="'.hash('ripemd160', get_site_url()).'" url="'.get_site_url().'" title="'.$options['ivc_text_field_1'].'" subtitle="'.$options['ivc_text_field_2'].'" version="1" mm_icon_focus_hd="'.$options['ivc_image_field_0'].'"
				mm_icon_focus_sd="'.$options['ivc_image_field_0'].'"
				mm_icon_side_hd="'.$options['ivc_image_field_1'].'"
				mm_icon_side_sd="'.$options['ivc_image_field_1'].'"

				>Download your ROKU app by clicking here  (Watch out for your popup blocker)</span>.
				<br />
				<br />
				<b>STEP 4</b> : 	<span id="tvos_app" fname="'.hash('ripemd160', get_site_url()).'" url="'.get_site_url().'" title="'.$options['ivc_text_field_1'].'" subtitle="'.$options['ivc_text_field_2'].'" version="1" mm_icon_focus_hd="'.$options['ivc_image_field_0'].'"
					mm_icon_focus_sd="'.$options['ivc_image_field_0'].'"
					mm_icon_side_hd="'.$options['ivc_image_field_1'].'"
					mm_icon_side_sd="'.$options['ivc_image_field_1'].'"
					>Download your TVOS app by clicking here  (Watch out for your popup blocker)</span>.
					<br />
					<br />
  Your ROKU feed is accessible from <a href="'.get_site_url().'/?feed=roku">'.get_site_url().'/?feed=roku</a><br />
	Your categoryleaf based ROKU feed is accessible from <a href="'.get_site_url().'/?feed=roku_cats">'.get_site_url().'/?feed=roku_cats</a><br />
	Your direct publisher ROKU feed is accessible from <a href="'.get_site_url().'/?feed=roku_dp">'.get_site_url().'/?feed=roku_dp</a><br />
		Your ios/android ionic feed is accessible from <a href="'.get_site_url().'/?feed=ionic">'.get_site_url().'/?feed=ionic</a><br />
  Your TVOS feed is accessible from <a href="'.get_site_url().'/?feed=tvos">'.get_site_url().'/?feed=tvos</a><br />
	Your Android (Variant 1) feed is accessible from <a href="'.get_site_url().'/?feed=android1">'.get_site_url().'/?feed=android1</a><br />
	The dead link checker is at :  <a href="'.get_site_url().'/?feed=checkdead">'.get_site_url().'/?feed=checkdead</a><br />
		The remote updater is at :  '.get_site_url().'/?feed=remoteupdate&remotefeed=http://THEREMOTEFEEDTOCOMPARE<br />
	<br />
    ', 'wordpress');
}


//<form action="http://factory.upg.gr/index.php" method="post">
//<input type="hidden" value="test" name="test" />
//<button type="submit" >Download Roku app</button>
//</form>
function ivc_options_page()
{
    ?>
	<form action='options.php' method='post'>

		<h2>Instructions and basic settings</h2>

		<?php
        settings_fields('pluginPage');
    do_settings_sections('pluginPage');
    submit_button(); ?>

	</form>
	<?php
}


function file_replace()
{
    $upload_dir = wp_upload_dir();
    $js_dirname = $upload_dir['basedir'] . '/' . 'js';
    if (!file_exists($js_dirname)) {
        wp_mkdir_p($js_dirname);
    }

    $plugin_dir = plugin_dir_path(__FILE__) . 'js/application.js';
    $uploads_dir = km_get_wordpress_uploads_directory_path() . '/js/application.js';
    if (!copy($plugin_dir, $uploads_dir)) {
        echo "failed to copy $plugin_dir to $uploads_dir...\n";
    }

    $plugin_dir = plugin_dir_path(__FILE__) . 'js/Presenter.js';
    $uploads_dir = km_get_wordpress_uploads_directory_path() . '/js/Presenter.js';
    if (!copy($plugin_dir, $uploads_dir)) {
        echo "failed to copy $plugin_dir to $uploads_dir...\n";
    }

    $plugin_dir = plugin_dir_path(__FILE__) . 'js/ResourceLoader.js';
    $uploads_dir = km_get_wordpress_uploads_directory_path() . '/js/ResourceLoader.js';
    if (!copy($plugin_dir, $uploads_dir)) {
        echo "failed to copy $plugin_dir to $uploads_dir...\n";
    }

    $path_to_file = km_get_wordpress_uploads_directory_path() . '/js/application.js';
    $file_contents = file_get_contents($path_to_file);
    $file_contents = str_replace("-TVOSFEED-", get_site_url().'/?feed=tvos', $file_contents);
    file_put_contents($path_to_file, $file_contents);
}

function km_get_wordpress_uploads_directory_path()
{
    $upload_dir = wp_upload_dir();
    return trailingslashit($upload_dir['basedir']);
}


function set_custom_edit_media_item_columns($columns)
{
    $columns['Type'] = __('Type', 'your_text_domain');
    $columns['Active'] = __('Active', 'your_text_domain');
    return $columns;
}
function custom_media_item_column($column, $post_id)
{
    switch ($column) {

        case 'Type':
                $typ = get_post_meta($post_id, 'media_type', true);
                if ($typ == 1) {
                    $ur = get_post_meta($post_id, 'media_url', true);
                    if (strpos($ur, 'm3u8') !== false) {
                        echo 'M3U8 VIDEO';
                    } else {
                        echo 'VIDEO';
                    }
                } elseif ($typ == 0) {
                    $ur = get_post_meta($post_id, 'media_url', true);
                    if (strpos($ur, 'm3u8') !== false) {
                        echo 'M3U8 RADIO';
                    } else {
                        echo 'RADIO';
                    }
                };
                break;

        case 'Active':
                $act = get_post_meta($post_id, 'media_active', true);
            if ($act == 1) {
                echo 'YES';
            } elseif ($act == 0) {
                echo 'NO';
            };
            break;

    }
}

function load_wp_media_files()
{
    wp_enqueue_script('ielko', plugin_dir_url(__FILE__) . '/js/ielko.js', array( 'jquery' ), 1.1, true);
    wp_enqueue_media();
}
add_action('save_post_media_item', 'save_media_meta', 10, 2);
add_action('init', 'ielko_wp_media_manager', 0);
add_action('init', 'file_replace');
add_action('do_meta_boxes', 'replace_featured_image_box');
add_action('add_meta_boxes_media_item', 'media_meta_box');
add_action('init', 'check_dead_links');
add_action('init', 'remote_updater');
add_action('init', 'rokuXML');
add_action('init', 'genimg');
add_action('init', 'rokuDP');
add_action('init', 'ionic');
add_action('init', 'ionic_dev');
add_action('init', 'rokuXMLcats');
add_action('init', 'rokuXMLbycat');
add_action('init', 'tvosXML');
add_action('init', 'android1XML');
add_action('admin_menu', 'ivc_add_admin_menu');
add_action('admin_init', 'ivc_settings_init');
add_filter('manage_media_item_posts_columns', 'set_custom_edit_media_item_columns');
add_action('manage_media_item_posts_custom_column', 'custom_media_item_column', 10, 2);
add_theme_support('post-thumbnails');
//add_image_size( 'ielko_focus_hd', 336, 210, false );
//add_image_size( 'ielko_focus_sd', 248, 140, false );
//add_image_size( 'ielko_side_hd', 108, 69, false );
//add_image_size( 'ielko_side_sd', 80, 46, false );
//add_image_size( 'ielko_overhang_hd', 234, 104, false );
//add_image_size( 'ielko_overhang_sd', 131, 58, false );
//add_image_size( 'ielko_splash_hd', 1280, 720, false );
//add_image_size( 'ielko_splash_sd', 740, 480, false );
//add_image_size( 'ielko_store_sd', 290, 218, false );
//add_image_size( 'ielko_store_sd', 214, 144, false );

add_action('admin_enqueue_scripts', 'load_wp_media_files');



?>