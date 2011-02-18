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

?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery("#gotoArchive").click( function(){
		window.location = 'admin.php?page=ChimpexpressArchive';
		
	});
});
</script>
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
	
	<p><?php _e('Pull content from MailChimp email campaigns into Wordpress or compose an email campaign in Wordpress, then pass it to MailChimp.', 'chimpexpress');?></p>
	<p><a href="http://www.chimpexpress.com" target="_blank">Chimpexpress.com</a></p>
	
	<table width="750" id="dashboardTable">
		<tr>
			<td width="425">
				<div class="mainOption">
					<h3><?php _e('Import', 'chimpexpress');?></h3>
					<hr />
					<div class="mainText"><?php _e('Import content from your MailChimp Account', 'chimpexpress');?></div>
					<form action="admin.php?page=ChimpexpressImport" method="post" id="wp_chimpexpress">
					<input type="submit" class="button-primary" size="4" value="<?php _e('go', 'chimpexpress');?>" />
					</form>
				</div>
			</td>
			<td width="325">
				<div class="mainOption">
					<h3><?php _e('Compose', 'chimpexpress');?></h3>
					<hr />
					<div class="mainText"><?php _e('Compose an email, then pass it to MailChimp for delivery.', 'chimpexpress');?></div>
					<form action="admin.php?page=ChimpexpressCompose" method="post" id="wp_chimpexpress">
					<input type="submit" class="button-primary" size="4" value="<?php _e('go', 'chimpexpress');?>" />
					</form>
				</div>
			</td>

		</tr>
	</table>
	
	<br />
	<br />
	<a id="gotoArchive" class="button" href="javascript:void(0);" style="float:none;" title="<?php _e('Landing Page Archive', 'chimpexpress');?>"><?php _e('Landing Page Archive', 'chimpexpress');?></a>
	
	<?php include( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'footer.php' ); ?>
</div>
