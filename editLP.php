<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/> 
 * or write to the Free Software Foundation, Inc., 51 Franklin St, 
 * Fifth Floor, Boston, MA  02110-1301  USA
**/

// no direct access
defined( 'ABSPATH' ) or die( 'Restricted Access' );

if( isset($_POST['task']) && $_POST['task'] == 'saveLP' ){
	
	$content = $_POST['head'] . $_POST['LPcontent'] . '</body></html>';
	$content = str_replace( array('\"', "\'"), array('"', "'"), $content);
	
	$archiveDirAbs = ABSPATH . 'archive/';
	$f = @fopen( $archiveDirAbs . $_POST['lpid'], 'w' );
	@fwrite( $f, $content );
	@fclose( $f );
	
	echo '<script type="text/javascript">window.location = "'.get_option('home') . '/wp-admin/admin.php?page=ChimpExpressArchive";</script>';
	
} else {
	
	add_action('admin_init', 'editor_admin_init');
	add_action('admin_head', 'editor_admin_head');
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('wp_tiny_mce')) wp_tiny_mce();
	if (function_exists('add_thickbox')) add_thickbox();
	wp_print_scripts('media-upload');
	wp_enqueue_script('utils');
	wp_admin_css();
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
?>
<div class="wrap">
	<div id="loggedInStatus">
	<?php if ( $_SESSION['MCping'] ){
		echo sprintf(__('connected as <a href="options-general.php?page=ChimpExpressConfig">%s</a>', 'chimpexpress'), $_SESSION['MCusername']);
	} else {
		_e('<a href="options-general.php?page=ChimpExpressConfig">connect your MailChimp account</a>', 'chimpexpress');
	}?>
	</div>
	<h2 class="componentHeading">ChimpExpress</h2>
	<div class="clr"></div>
	<?php if ( ! $_SESSION['MCping'] ){ ?>
	<div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
		<a href="options-general.php?page=ChimpExpressConfig"><?php _e('Please connect your MailChimp account!', 'chimpexpress');?></a>
	</div>
	<?php }?> 
	<?php 
	$chimpexpress = new chimpexpress;
	$ftpstream = @ftp_connect( $chimpexpress->_settings['ftpHost'] );
	$login = @ftp_login($ftpstream, $chimpexpress->_settings['ftpUser'], $chimpexpress->_settings['ftpPasswd']);
	$ftproot = @ftp_chdir($ftpstream, $chimpexpress->_settings['ftpPath'] );
	$adminDir = @ftp_chdir($ftpstream, 'wp-admin' );
	if (   !$chimpexpress->_settings['ftpHost'] 
		|| !$chimpexpress->_settings['ftpUser'] 
		|| !$chimpexpress->_settings['ftpPasswd'] 
		|| !$ftpstream
		|| !$login
		|| !$ftproot
		|| !$adminDir
	 ){ ?>
	<div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
		<a href="options-general.php?page=ChimpExpressConfig"><?php _e('Please enter valid ftp credentials!', 'chimpexpress');?></a>
	</div>
	<?php }
	@ftp_close($ftpstream);
	?>
	<div style="display:block;height:3em;"></div>
	
	<link media="all" type="text/css" href="<?php echo get_option('home');?>/wp-admin/css/colors-fresh.css" id="colors-css" rel="stylesheet">
	<h3><?php _e('Edit Landing Page', 'chimpexpress');?></h3>
	<hr />
	<br />
	<?php
	if( ! isset($_POST['lpid']) ){
		echo __('No landing page selected!', 'chimpexpress');
		echo ' <a href="admin.php?page=ChimpExpressArchive">'.__('Landing Page Archive', 'chimpexpress').'</a>';
	} else {
		$filename = sanitize_title( $_POST['lpid'][0] );
		$filename = str_replace('-html', '.html', $filename);
	?>
	<h3 style="float:left;"><?php echo $filename;?></h3>
	<?php
	$archiveDirAbs = ABSPATH . 'archive/';
	if( is_file(  $archiveDirAbs . $filename ) ){
		$f = @fopen( $archiveDirAbs . $filename, 'r' );
		$content = fread( $f, filesize( $archiveDirAbs . $filename ) );
		@fclose( $f );
		
		preg_match('#.*<body[^>]*>#is', $content, $head);
		$head = (isset($head[0])) ? str_replace('"',"'",$head[0]) : '';
		preg_match( '!<body[^>]*>(.*)</body>!is' , $content, $body );
		$bodyContent = $body[0];
		$bodyContent = preg_replace( '!<body[^>]*>!is', '', $bodyContent);
		$bodyContent = preg_replace( '!</body>!is', '', $bodyContent);
		$bodyContent = preg_replace( '!<style>(.*)</style>!is', '', $bodyContent);
		?>
		<div style="float:right;">
		<a href="javascript:document.forms['wp_chimpexpress'].submit()" class="button">&nbsp;<?php _e('Save', 'chimpexpress');?>&nbsp;</a>
		<a href="javascript:cancelEdit()" class="button"><?php _e('Cancel', 'chimpexpress');?></a>
		<script type="text/javascript">
		function cancelEdit(){
			if( confirm( '<?php _e('Are you sure you want to cancel this operation?', 'chimpexpress');?>' ) ){
				window.location = 'admin.php?page=ChimpExpressArchive';
			}
		}
		</script>
		</div>
		<div style="clear:both;"></div>
		<br />
		<form action="admin.php?page=ChimpExpressEditLandingPage" method="post" id="wp_chimpexpress">
		<div id="poststuff" class="postarea">
		<?php
		the_editor($bodyContent, $id = 'LPcontent', $prev_id = '', $media_buttons = true, $tab_index = 1);
		?>
		<input type="hidden" name="lpid" value="<?php echo $filename;?>" />
		<input type="hidden" name="task" value="saveLP" />
		<input type="hidden" name="head" value="<?php echo $head;?>" />
		</form>
		</div>
		<?php
		
	} else {
		?>
		<div style="clear:both;"></div>
		<br />
		<b><?php _e('Landing page not found! Please make sure the directory "archive" exists in your wordpress root and is writable by ftp.', 'chimpexpress');?></b>
		<?php
	}
?>
</div>
<?php }
}
