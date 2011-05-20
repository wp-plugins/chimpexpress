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

class joomailerEditor {
	
	function the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2) {
		$rows = get_option('default_post_edit_rows');
		if (($rows < 3) || ($rows > 100))
			$rows = 15;

		if ( !current_user_can( 'upload_files' ) )
			$media_buttons = false;

		$richedit =  user_can_richedit();
		$class = '';

		if ( $richedit || $media_buttons ) { ?>
		<div id="editor-toolbar">
	<?php
		if ( $richedit ) {
			$wp_default_editor = wp_default_editor(); ?>
			<div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('<?php echo $id; ?>')" /></div>
	<?php	if ( 'html' == $wp_default_editor ) {
				add_filter('the_editor_content', 'wp_htmledit_pre'); ?>
				<a id="edButtonHTML" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
				<a id="edButtonPreview" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
	<?php	} else {
				$class = " class='theEditor'";
				add_filter('the_editor_content', 'wp_richedit_pre'); ?>
				<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
				<a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
	<?php	}
		}

		if ( $media_buttons ) { ?>
			<div id="media-buttons" class="hide-if-no-js">
	<?php	do_action( 'media_buttons' ); ?>
			</div>
	<?php
		} ?>
		</div>
	<?php
		}
	?>
		<div id="quicktags">
		<script type="text/javascript">edToolbar()</script>
		</div>

	<?php
		$the_editor = apply_filters('the_editor', "<div id='editorcontainer'><textarea rows='$rows'$class cols='40' name='$id' tabindex='$tab_index' id='$id'>%s</textarea></div>\n");
		$the_editor_content = apply_filters('the_editor_content', $content);

		printf($the_editor, $the_editor_content);

	?>
		<script type="text/javascript">
		edCanvas = document.getElementById('<?php echo $id; ?>');
		</script>
	<?php
	}
}
