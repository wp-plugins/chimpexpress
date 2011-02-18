<?php
/**
 * Copyright (C) 2010  freakedout (www.freakedout.de)
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

if ( isset($_POST['type']) ) {
	$type = $_POST['type'];
} else if ( isset($_GET['type']) ) {
	$type = $_GET['type'];
} else {
	$type = 1;
}
?>
<div class="wrap">
	<div id="loggedInStatus">
	<?php if ( $_SESSION['MCping'] ){
		echo sprintf(__('connected as <a href="options-general.php?page=ChimpexpressConfig">%s</a>', 'chimpexpress'), $_SESSION['MCusername']);
	} else {
		_e('<a href="options-general.php?page=ChimpexpressConfig">connect your MailChimp account</a>', 'chimpexpress');
	}?>
	</div>
	<h2 class="componentHeading">Chimpexpress</h2>
	<div class="clr"></div>
	<?php if ( ! $_SESSION['MCping'] ){ ?>
	<div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
		<a href="options-general.php?page=ChimpexpressConfig"><?php _e('Please connect your MailChimp account!', 'chimpexpress');?></a>
	</div>
	<?php }?> 
	<div style="display:block;height:3em;"></div>
	
	<h3><?php _e('Import', 'chimpexpress');?></h3>
	<hr />
	<br />

<script type="text/javascript" src="<?php echo plugins_url( 'js' . DS . 'php.default.min.js', __FILE__ );?>"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery("#cancel").click( function(){
		window.location = 'admin.php?page=ChimpexpressDashboard';
	});
	<?php /*
	jQuery("#gotoArchive").click( function(){
		window.location = 'admin.php?page=ChimpexpressArchive';
	});
	*/ ?>
	jQuery("#next").click( function(){
		if( jQuery('#typePost').is(':checked') || jQuery('#typePage').is(':checked') ){
			
			jQuery('#cancelContainer').html( '<img src="<?php echo plugins_url( '/images/ajax-loader.gif', __FILE__ );?>" style="position: relative;top: 1px;"/>' );
			
			if ( jQuery('#typePost').is(':checked') ){
				var type = 'post';
			} else {
				var type = 'page';
			}
			if ( jQuery('#typeHTML').is(':checked') ){
				var datatype = 'html';
			} else {
				var datatype = 'text';
			}
			var data = {
				action: "import",
				type: type,
				datatype: datatype,
				cid : jQuery('#cid').val(),
				title: campaigns[jQuery('#cid').val()]['title'],
				subject: campaigns[jQuery('#cid').val()]['subject'],
				fileName: htmlentities( jQuery('#fileName').val() )
			};
			jQuery.post(ajaxurl, data, function(response) {
				if( type == 'post' ){
					window.location = 'post.php?post='+response+'&action=edit';
				} else {
				//	jQuery('#gotoarchive').html('<form action="admin.php?page=ChimpexpressEditLandingPage" method="post" id="wp_chimpexpress_import"><input type="checkbox" name="lpid[]" value="'+htmlentities( jQuery('#fileName').val() )+'.html" /></form>');
					jQuery('#lpid').val( htmlentities( jQuery('#fileName').val() )+'.html' );
					document.forms["wp_chimpexpress_import"].submit();
				}
			});
		} else {
			window.location = 'admin.php?page=ChimpexpressImport';
		}
	});
	
	jQuery('#fileName').val( html_entity_decode( campaigns[jQuery('#cid').val()]['subject'] ) );
	
	jQuery('#cid').change( function(){
		jQuery('#fileName').val( html_entity_decode( campaigns[jQuery(this).val()]['subject'] ) );
	});
	
	if( jQuery('#typePage').is(':checked') ){
		jQuery('#fileNameContainer').css( 'display', 'block' );
		jQuery('#datatypeContainer').css( 'display', 'none' );
	} else {
		jQuery('#fileNameContainer').css( 'display', 'none' );
		jQuery('#datatypeContainer').css( 'display', 'block' );
	}
	
	jQuery('#typePage').change( function(){
		if( jQuery(this).is(':checked') ){
			jQuery('#fileNameContainer').css( 'display', 'block' );
			jQuery('#datatypeContainer').css( 'display', 'none' );
		}
	});
	jQuery('#typePost').change( function(){
		if( jQuery(this).is(':checked') ){
			jQuery('#fileNameContainer').css( 'display', 'none' );
			jQuery('#datatypeContainer').css( 'display', 'block' );
		}
	});
});
</script>
<?php

require_once( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'class-MCAPI.php' );
$MCAPI = new chimpexpressMCAPI;
if($type == 1){
	step1();
} else if( $type == 'post' ) {
	typePost();
} else if( $type == 'page' ) {
	typePage();
}

function step1(){
$MCAPI = new chimpexpressMCAPI;
$campaigns = $MCAPI->campaigns( array('status' => 'sent'), 0, 1000 );
?>
<label for="cid" class="bold"><?php _e('select campaign content to import', 'chimpexpress');?></label><br />
<select name="cid" id="cid">
<?php
$js = "var campaigns = new Array();\n";
foreach($campaigns['data'] as $c){
	echo '<option value="'.$c['id'].'">'.$c['title'].' ('.$c['subject'].')</option>';
	$js .= "campaigns['".$c['id']."'] = new Array();\n";
	$js .= "campaigns['".$c['id']."']['title'] = '".esc_attr($c['title'])."';\n";
	$js .= "campaigns['".$c['id']."']['subject'] = '".esc_attr($c['subject'])."';\n";
}
?>
</select>
<script type="text/javascript"><?php echo $js;?></script>
<br />
<br />
<label class="bold"><?php _e('create a new ...', 'chimpexpress');?></label><br />
<input type="radio" name="type" id="typePost" value="post" checked="checked" />&nbsp;<label for="typePost"><?php _e('blog post', 'chimpexpress');?></label>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="type" id="typePage" value="page" />&nbsp;<label for="typePage"><?php _e('landing page', 'chimpexpress');?></label>

<br />
<br />

<div id="datatypeContainer">
	<label class="bold"><?php _e('import as ...', 'chimpexpress');?></label><br />
	<input type="radio" name="datatype" id="typeText" value="text" checked="checked" />&nbsp;<label for="typeText"><?php _e('text', 'chimpexpress');?></label>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="datatype" id="typeHTML" value="html" />&nbsp;<label for="typeHTML"><?php _e('HTML', 'chimpexpress');?></label>
</div>

<div id="fileNameContainer" style="display:none;">
	<label class="bold"><?php _e('page title', 'chimpexpress');?></label><br />
	<input type="text" name="fileName" id="fileName" value="" size="45"/>
</div>

<br />
<br />
<br />
<a class="button" id="next" href="javascript:void(0);" title="<?php _e('next &raquo;', 'chimpexpress');?>"><?php _e('next &raquo;', 'chimpexpress');?></a>
<span id="cancelContainer">
<a id="cancel" class="grey" href="javascript:void(0);" title="<?php _e('cancel', 'chimpexpress');?>"><?php _e('cancel', 'chimpexpress');?></a>
</span>
<div id="gotoarchive" style="display:none;">
	<form action="admin.php?page=ChimpexpressEditLandingPage" method="post" id="wp_chimpexpress_import">
	<input type="hidden" name="lpid[]" id="lpid" value="" />
	</form>
</div>
<?php /*
<a id="gotoArchive" class="button" href="javascript:void(0);" title="<?php _e('Landing Page Archive', 'chimpexpress');?>"><?php _e('Landing Page Archive', 'chimpexpress');?></a>
*/ ?>
<?php
}

function typePost(){
	echo 'post';
}

function typePage(){
	echo 'page';
}
?>
<?php include( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'footer.php' ); ?>
</div>
<?php
$MCAPI->showMessages();
