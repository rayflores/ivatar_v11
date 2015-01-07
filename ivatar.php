<?php
/*
 * Plugin Name: iVatar
 * Plugin URI: http://rayflores/plugins/ivatar/
 * Description: Replaces Users Avatar or Gravatar with Initials from Commenter or registered User First Name Last Name
 * Author: Ray Flores
 * Author URI: http://rayflores.com
 * Version: 1.1
 */
if(!defined('ABSPATH')){
  die(__('You are not allowed to call this page directly.'));
  @header('Content-Type:'.get_option('html_type').';charset='.get_option('blog_charset'));
}

define('IVATAR_VERSION', ' 1.1');
define('IVATAR_FOLDER', basename(dirname(__FILE__)));
define('IVATAR_ABSPATH', trailingslashit(str_replace('\\', '/', WP_PLUGIN_DIR.'/'.IVATAR_FOLDER)));
define('IVATAR_URLPATH', trailingslashit(plugins_url(IVATAR_FOLDER)));

// Include WordPress functions
require_once(ABSPATH.'wp-admin/includes/file.php');
require_once(ABSPATH.'wp-admin/includes/image.php');
require_once(ABSPATH.'wp-admin/includes/media.php');
require_once(ABSPATH.'wp-admin/includes/screen.php');
require_once(ABSPATH.'wp-admin/includes/template.php');

// Define global variables
$avatar_default = get_option('avatar_default');
$show_avatars = get_option('show_avatars');
$ivatar_avatar_default = get_option('avatar_default_ivatar');
$ivatar_disable_gravatar = get_option('ivatar_disable_gravatar');



// Check for updates
$ivatar_default_avatar_updated = get_option('ivatar_default_avatar_updated');
$ivatar_users_updated = get_option('ivatar_users_updated');
$ivatar_media_updated = get_option('ivatar_media_updated');

// Initialize default settings
register_activation_hook(IVATAR_ABSPATH.'ivatar.php', 'ivatar_options');

// Remove subscribers edit_posts capability
register_deactivation_hook(IVATAR_ABSPATH.'ivatar.php', 'ivatar_deactivate');

// Settings saved to wp_options
function ivatar_options(){
  add_option('avatar_default_ivatar', "");
  add_option('ivatar_disable_gravatar', '0');
}
add_action('admin_init', 'ivatar_options');

 // disable the admin bar
show_admin_bar(false);
 
 add_action('admin_menu', 'ivatar_admin_add_page');
function ivatar_admin_add_page() {
add_options_page('iVatar Settings Page', 'iVatar Settings', 'manage_options', 'ivatar', 'ivatar_options_page');
}
function ivatar_options_page() {
?>
<div>
<h2>iVatar Settings</h2>
Here you can define the font-size, font-color and background-color.  More options coming soon...
<form action="options.php" method="post">
<?php settings_fields('ivatar_options'); ?>
<?php do_settings_sections('ivatar'); ?>
 
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>
 
<?php
}
add_action('admin_init', 'ivatar_admin_init');
function ivatar_admin_init(){
register_setting( 'ivatar_options', 'ivatar_options', 'ivatar_options_validate' );
add_settings_section('ivatar_main', 'Main Settings', 'ivatar_section_text', 'ivatar');
//add_settings_field('ivatar_text_string', 'Font Size', 'ivatar_setting_string', 'ivatar', 'ivatar_main');
add_settings_field('ivatar_color', 'Background Color', 'ivatar_setting_color', 'ivatar', 'ivatar_main');
add_settings_field('ivatar_scheme', 'Color Scheme', 'ivatar_setting_scheme', 'ivatar', 'ivatar_main');
}
function ivatar_section_text() {
$options = get_option('ivatar_options');
if( $options['ivatar_scheme'] == 'Light' ) { 
  $color = '#FFFFFF'; } 
	elseif( $options['ivatar_scheme'] == 'Dark' ) { 
	$color = '#000000'; } 
		elseif( $options['ivatar_scheme'] == 'Shade' ) { 
		$color = '#999999'; };
echo '<p>Take note that these settings apply for every user.</p>';
echo '<p>To apply the below settings, copy and paste the below declarations into your stylesheet</p>';
echo '<textarea rows="2" cols="60">' . '.ivatar { background:' . $options["bg_color"] . ';font-size:20px;color:' . $color . ';}</textarea>';
echo '<p>Preview:</p>';
$styles = 'background:' . $options["bg_color"] . ';font-size:20px;color:' . $color .';width:34px;padding:10px;font-family:Open Sans;height:34px;line-height:34px';
echo '<div style="' . $styles . '" class="avatar ivatar photo avatar-default">WP</div>';
} 
function ivatar_setting_string() {
$options = get_option('ivatar_options');
echo "<input id='ivatar_text_string' name='ivatar_options[font_size]' size='5' type='text' value='{$options['font_size']}' />";
}
function ivatar_setting_color() {
$options = get_option('ivatar_options');
echo "<input id='ivatar_color' name='ivatar_options[bg_color]' size='40' type='text' value='{$options['bg_color']}' class='bg_color'/>";
echo "<div>actual value:";
echo  $options['bg_color'];
echo "</div>";
}
function ivatar_setting_scheme() {
$options = get_option('ivatar_options');
	$items = array("Light", "Dark", "Shade");
	echo "<select id='ivatar_color_scheme' name='ivatar_options[ivatar_scheme]'>";
	foreach($items as $item) {
		$selected = ($options['ivatar_scheme']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

// validate our options
function ivatar_options_validate($input) {
$options = get_option('ivatar_options');
$options['ivatar_scheme'] = trim($input['ivatar_scheme']);
$options['bg_color'] = trim($input['bg_color']);
//$options['font_size'] = trim($input['font_size']);
return $options;
}
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $ivatar_settings ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'ivatar-script', plugins_url('ivatar-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

function new_ivatar_filter($avatar, $id_or_email="", $size="", $default="", $alt="") {
 global $comment, $author;
 if (!empty($comment->user_id)) {
 
  if (!is_admin()) {
//$user = get_userdata($comment->user_id)
 
  //  foreach($users as $user){
        $user=get_userdata($comment->user_id);
       	$getFname = $user->first_name;
		$getLname = $user->last_name;
		$firstInit = $getFname[0];
		$lastInit = $getLname[0];

        $custom_avatar = $firstInit . $lastInit; 

		$styles = 'position:absolute;left:0;padding:5px 10px;font-family:Open Sans;margin-left:-10px;width:38px;text-align:center;text-transform:uppercase;height:44px;line-height:44px;'; 

		$return = '<div alt="'.$alt.'" class="ivatar" style="'.$styles.'" />'.$custom_avatar.'</div>';
		return $return;
		} 
		else {
		$return = $avatar;
		return $return;
		}
}
else { 
  if (!is_admin()) {
	 $comment_ID = 0;  //reset comment ID?
	$comment = get_comment( $comment_ID );
	
	$name_author = $comment->comment_author;

	$words = explode(" ", $name_author);
	$acronym = "";

	foreach ($words as $w) {
	$acronym .= $w[0];
	}

				$styles = 'position:absolute;left:0;padding:5px 10px;font-family:Open Sans;margin-left:-10px;width:38px;text-align:center;text-transform:uppercase;height:44px;line-height:44px;';
				
		$return = '<div alt="'.$alt.'" class="ivatar" style="'.$styles.'" />'.$acronym.'</div>';
		return $return;
		} }
}
add_filter('get_avatar', 'new_ivatar_filter', 10, 6);


  // Add default avatar to list
 
add_filter( 'avatar_defaults', 'insert_new_ivatar' );
function insert_new_ivatar ($avatar_defaults) {
    remove_filter('get_avatar', 'new_ivatar_filter');
	$ivatar_default = new_ivatar_filter($avatar_defaults);
	
$insert_ivatar = $ivatar_default;
$avatar_defaults[$insert_ivatar] = 'New iVatar - Users Initials <a href="/wp-admin/users.php?page=ivatar">Settings</a>';
return $avatar_defaults;
}
function ivatar_customize_css()
{
    $options = get_option('ivatar_options');
  if( $options['ivatar_scheme'] == 'Light' ) { 
  $color = '#FFFFFF'; } 
	elseif( $options['ivatar_scheme'] == 'Dark' ) { 
	$color = '#000000'; } 
		elseif( $options['ivatar_scheme'] == 'Shade' ) { 
		$color = '#999999'; } // end if/else 
	?>
	<style type="text/css">
            @import url(http://fonts.googleapis.com/css?family=Open+Sans);
.ivatar { background-color:<?php echo $options['bg_color']; ?>;
			font-size:20px;
			color:<?php echo $color; ?>;
			}
         </style>
    <?php
}
add_action( 'wp_head', 'ivatar_customize_css');
