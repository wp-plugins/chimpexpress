<?php
/**
 * Copyright (C) 2015 freakedout (www.freakedout.de)
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

$archiveDirAbs = ABSPATH . 'archive/';
$archiveDirRel = get_option('home') . '/archive/';

?>
<div class="wrap" id="CEwrap">
<div id="archive">
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery("#gotoDashboard").click( function(){
		window.location = 'admin.php?page=ChimpExpressDashboard';
	});
});

function editLP(){
	var lpid = document.getElementsByName('lpid[]');
	var isChecked = false;
	for(i=0;i<lpid.length;i++){
		if( lpid[i].checked == true ){
			isChecked = true;
		}
	}
	if( !isChecked ){
		alert('<?php _e('Please select a landing page from the list!', 'chimpexpress');?>');
	} else {
		document.forms["wp_chimpexpress"].submit();
	}
}

function deleteLP(){
    var lpid = document.getElementsByName('lpid[]');
    var isChecked = false;
    for(i=0;i<lpid.length;i++){
	    if( lpid[i].checked == true ){
		    isChecked = true;
	    }
    }
    if( !isChecked ){
	    alert('<?php _e('Please select one or more landing pages from the list!', 'chimpexpress');?>');
    } else {
	if( confirm( '<?php _e('Are you sure you want to delete the selected landing pages? There is no undo!', 'chimpexpress');?>' ) ){
	    var filenames = new Array();
	    for(i=0;i<lpid.length;i++){
		    if( lpid[i].checked == true ){
			    filenames.push( lpid[i].value );
		    }
	    }
	    var data = { action: "archive_deleteLP",
			 filenames : filenames
	    };
	    jQuery.post(ajaxurl, data, function(response) {
		    window.location = 'admin.php?page=ChimpExpressArchive';
	    });
	}
    }
}
</script>
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
	global $wp_filesystem;
	if( $wp_filesystem->method != 'direct' ){
	$chimpexpress = new chimpexpress;
	$ftpstream = @ftp_connect( $chimpexpress->_settings['ftpHost'] );
	$login = @ftp_login($ftpstream, $chimpexpress->_settings['ftpUser'], $chimpexpress->_settings['ftpPasswd']);
	$ftproot = @ftp_chdir($ftpstream, $chimpexpress->_settings['ftpPath'] );
	$adminDir = @ftp_chdir($ftpstream, 'wp-admin' );
	if (   $wp_filesystem->method != 'direct'
		&& (
		!$chimpexpress->_settings['ftpHost']
		|| !$chimpexpress->_settings['ftpUser']
		|| !$chimpexpress->_settings['ftpPasswd']
		|| !$ftpstream
		|| !$login
		|| !$ftproot
		|| !$adminDir
		)
	 ){ ?>
	<div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
		<a href="options-general.php?page=ChimpExpressConfig"><?php _e('Direct file access not possible. Please enter valid ftp credentials in the configuration!', 'chimpexpress');?></a>
	</div>
	<?php }
	@ftp_close($ftpstream);
	}
	?>
	<div style="display:block;height:3em;"></div>

	<h3><?php _e('Landing Page Archive', 'chimpexpress');?></h3>
	<hr />
	<br />
	<form action="admin.php?page=ChimpExpressEditLandingPage" method="post" id="wp_chimpexpress">
	<?php
	// get a list of existing archive files
	if( is_dir( $archiveDirAbs ) ) {
		$files = getDirectoryList( $archiveDirAbs );
		usort( $files, 'cmp' );
	}
	if( isset( $files[0] ) ){
	?>
	<table width="100%" class="widefat">
	    <thead>
		<tr>
		    <th width="20"></th>
		    <th><?php _e('Campaign Subject', 'chimpexpress');?></th>
		    <th width="150" nowrap="nowrap" style="text-align:center;"><?php _e('created / modified', 'chimpexpress');?></th>
		</tr>
	    </thead>
	    <tfoot>
		<tr>
		    <th colspan="20"></th>
		</tr>
	    </tfoot>
	    <tbody>
	<?php
	foreach( $files as $f ){
	?>
		<tr>
		    <td style="vertical-align:middle;">
			<input type="checkbox" name="lpid[]" value="<?php echo $f['name'];?>" />
		    </td>
		    <td>
			<a href="<?php echo $archiveDirRel.$f['name'];?>" target="_blank"><?php echo $f['name'];?></a>
		    </td>
		    <td align="center" nowrap="nowrap">
			<?php echo strftime('%b %d, %Y %H:%M', $f['created'] );?>
		    </td>
		</tr>
	<?php
	}
	?>
	    </tbody>
	</table>
	<br />
	<a href="javascript:editLP()" class="button">&nbsp;&nbsp;<?php _e('Edit', 'chimpexpress');?>&nbsp;&nbsp;</a>
	&nbsp;&nbsp;
	<a href="javascript:deleteLP()" class="button" style="color:#A50000;float:right;">&nbsp;&nbsp;<?php _e('Delete', 'chimpexpress');?>&nbsp;&nbsp;</a>
	<div style="clear:both;"></div>
	<br />
	<br />
	</form>
	<?php
	} else {
	    _e('No landing pages found!', 'chimpexpress');
	    echo '<br />';
	} ?>

	<a id="gotoDashboard" class="button" style="float:right;" href="javascript:void(0);" title="<?php _e('Dashboard', 'chimpexpress');?>"><?php _e('Dashboard', 'chimpexpress');?></a>
</div>
<?php include( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'footer.php' ); ?>
</div>


<?php
function getDirectoryList( $directory ) {
    // create an array to hold directory list
    $results = array();
    $gmtOffset=get_option('gmt_offset');
    $serverOffset = date("O") / 100;
    // create a handler for the directory
    $handler = opendir($directory);

    // open directory and walk through the filenames
    while ($file = readdir($handler)) {
	// if file isn't this directory or its parent, add it to the results
	if ($file != "." && $file != "..") {
	    // only return .html files
	    if( substr( $file, -5) == '.html' ){
		$creationTime = ( $gmtOffset != $serverOffset ) ? (filemtime( $directory . $file ) + ( $gmtOffset * 60 * 60)) : filemtime( $directory . $file );
		$results[] = array('name' => $file, 'created' => $creationTime );
	    }
	}
    }
    // close the directory handler
    closedir($handler);

    return $results;
}

function cmp($a, $b)
{
    if ($a['created'] == $b['created']) {
	return 0;
    }
    return ($a['created'] < $b['created']) ? 1 : -1;
}

