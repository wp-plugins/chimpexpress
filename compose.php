<?php
/**
 * Copyright (C) 2014 freakedout (www.freakedout.de)
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
defined( 'ABSPATH' ) or die('Restricted Access');

if ( isset($_POST['step']) ) {
    $step = $_POST['step'];

} else if ( isset($_GET['step']) ) {
    $step = $_GET['step'];

} else {
    $step = 1;

    add_action('admin_enqueue_scripts', 'my_admin_scripts');
    function my_admin_scripts() {
        if (isset($_GET['page']) && $_GET['page'] == 'my_plugin_page') {
            wp_enqueue_media();
            //wp_register_script('my-admin-js', WP_PLUGIN_URL.'/my-plugin/my-admin.js', array('jquery'));
            //wp_enqueue_script('my-admin-js');
        }
    }

    wp_enqueue_media();
    if (function_exists('wp_tiny_mce')) wp_tiny_mce();
    /*wp_enqueue_script( 'common' );
    wp_enqueue_script( 'jquery-color' );
    wp_print_scripts('editor');
    if (function_exists('add_thickbox')) add_thickbox();
    wp_print_scripts('media-upload');
    wp_enqueue_script('media-upload');

    wp_enqueue_script('utils');
    wp_admin_css();
    do_action("admin_print_styles-post-php");
    do_action('admin_print_styles');
    */
}

echo '<script type="text/javascript" src="'.plugins_url( 'js' . DS . 'innerxhtml.js', __FILE__ ).'"></script>';
echo '<script type="text/javascript" src="'.plugins_url( 'js' . DS . 'jquery.equalwidths.js', __FILE__ ).'"></script>';
echo '<script type="text/javascript" src="'.plugins_url( 'js' . DS . 'php.default.min.js', __FILE__ ).'"></script>';

require_once( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'class-MCAPI.php' );
$MCAPI = new chimpexpressMCAPI;
//require_once( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'class-editor.php' );

?>
<div id="chimpexpressCompose">

<script type="text/javascript">
    function gotoStep(from, to){
        if (parseInt(from) == 1 && (jQuery.trim( jQuery('#campaignName').val() ) == '' || jQuery.trim(jQuery('#campaignSubject').val()) == '')) {
            alert('<?php _e('Campaign name and subject line must be supplied!', 'chimpexpress');?>');

        } else if ((parseInt(to) == 1 && confirm('<?php _e('Are you sure you want to go back to step one? All entered content will be lost!', 'chimpexpress');?>')) || parseInt(to) != 1) {
            var stop = false;

            if (parseInt(from) == 1 && jQuery.trim( jQuery('#campaignName').val() ) != '' && jQuery.trim( jQuery('#campaignSubject').val() ) != '') {
                var data = {
                    action: 'sanitize',
                    string: jQuery.trim(jQuery('#campaignName').val() + '*|@|*' + jQuery.trim(jQuery('#campaignSubject').val()))
                };

                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    //async: false,
                    data: data,
                    beforeSend: function() {
                        jQuery('#ajaxLoader').show();
                    },
                    success: function(response) {
                        if (response == '') {
                            alert('<?php _e('Campaign name and subject can not consist exclusively of special characters!', 'chimpexpress'); ?>');
                            stop = true;
                        }
                    },
                    complete: function() {
                        jQuery('#ajaxLoader').hide();
                    }
                });
            }

            if(!stop) {
                var sections = jQuery('#sections').val();
                var editorContent = jQuery('#editorContent').val();
                if(parseInt(from) != 1 && parseInt(from) != (parseInt(sections) + 2) ){
                    editorContent = editorContent.split('|###|');
                    if(tinyMCE.activeEditor != undefined){
                        currentContent = tinyMCE.activeEditor.getContent();
                    }
                    editorContent[parseInt(from)-2] = currentContent;
                    editorContent = editorContent.join('|###|');
                }

                var data = {
                    action: "compose_gotoStep",
                    step: to,
                    listId: jQuery('#listId').val(),
                    default_from_name: jQuery('#default_from_name').val(),
                    default_from_email: jQuery('#default_from_email').val(),
                    template: jQuery('#template').val(),
                    templateName: jQuery('#templateName').val(),
                    sections: jQuery('#sections').val(),
                    sectionNames: jQuery('#sectionNames').val(),
                    skipSections: jQuery('#skipSections').val(),
                    editorContent: editorContent,
                    campaignName: htmlentities(jQuery('#campaignName').val()),
                    campaignSubject: htmlentities(jQuery('#campaignSubject').val()),
                    campaignId : jQuery('#campaignId').val()
                };

                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: data,
                    //async: false,
                    beforeSend: function() {
                        jQuery('#ajaxLoader').show();
                    },
                    success: function(response, a, b) {
                        jQuery('#CEwrap').html(response);

                    },
                    complete: function() {
                        jQuery('#ajaxLoader').hide();

                        //init quicktags
                        quicktags({id : theEditorID}); // REQUIRED to show quick tags and fix tabs
                        tinymce.execCommand('mceAddControl', true, theEditorID); // REQUIRED hack to fix editor created in AJAX
                    }
                });
            }
        }
    }

    function removeDraft(){
        var data = { action: "compose_removeDraft",
                     cid : jQuery('#campaignId').val()
        };
        jQuery.post(ajaxurl, data, function(response) {
            window.location = 'admin.php?page=ChimpExpressDashboard';
        });
    }


    jQuery(document).ready(function($) {
        jQuery('#preloaderContainer').slideUp();
        var sections = 0;
        jQuery('#template').change( function(){
            if(templates[this.value]['preview']){
                //jQuery('#preview a').css( 'visibility', '' );
                jQuery('#preview a').show();
                $('#preview a').colorbox({ html: templates[this.value]['preview'] });
            } else {
                //jQuery('#preview a').css( 'visibility', 'hidden' );
                jQuery('#preview a').hide();
            }
            jQuery('#preview').css( 'display', 'inline-block' );

            skip = substr_count(templates[this.value]['skipSections'], ',');
            if( skip > 0 ) { skip = skip + 1; }


    //        jQuery('#sectionsText #sectionsValue').html( ( templates[this.value]['sections'] - skip ) );
            jQuery('#sectionsText #sectionsValue').html( templates[this.value]['sections'] );
            jQuery('#sectionsText').css( 'display', 'inline-block' );
            jQuery('#sections').val( templates[this.value]['sections'] );
            jQuery('#skipSections').val( templates[this.value]['skipSections'] );

            jQuery('#tName').html( templates[this.value]['templateName'] );
            jQuery('#templateName').val( templates[this.value]['templateName'] );

            var sectionNames = '';
            for(i=0;i<templates[this.value]['sections'];i++){
                sectionNames += templates[this.value]['sectionNames'][i] + '|###|';
            }
            sectionNames = sectionNames.slice(0,-5);
            jQuery('#sectionNames').val( sectionNames );
            var editorContent = '';
            for(i=0;i<templates[this.value]['sections'];i++){
                editorContent += templates[this.value]['editorContent'][i] + '|###|';
            }
            editorContent = editorContent.slice(0,-5);
            jQuery('#editorContent').val( html_entity_decode( editorContent ) );

            var sections = templates[this.value]['sections'];
            createSteps( this.value, sections );
        });

        $('#preview_image a').colorbox();

        jQuery("#reloadCache a").unbind('click');
        jQuery("#reloadCache a").click( function(){
            var data = { action: "compose_clear_cache" };
            jQuery.post(ajaxurl, data, function(response) {
                window.location = 'admin.php?page=ChimpExpressCompose';
            });
        });

        jQuery("#cancel").unbind('click');
        jQuery("#cancel").click( function(){
            if(confirm('<?php _e('Are you sure you want to cancel? All entered content will be lost!', 'chimpexpress');?>')){
                window.location = 'admin.php?page=ChimpExpressDashboard';
            } else {
                return false;
            }
        });

        jQuery("#cancelCompose").unbind('click');
        jQuery("#cancelCompose").click( function(){
            if(confirm('<?php _e('Are you sure you want to cancel? All entered content will be lost!', 'chimpexpress');?>')){
                removeDraft();
            } else {
                return false;
            }
        });

        jQuery("#gotoMailChimp").unbind('click');
        jQuery("#gotoMailChimp").click( function(){
            window.location = 'admin.php?page=ChimpExpressDashboard';
        });


        jQuery('#listId').change( function(){
            jQuery('#listSubscribers #listSubscribersValue').html( lists[this.value]['member_count'] );
            jQuery('#listSubscribers').css( 'display', 'inline-block' );

            jQuery('#default_from_name').val( lists[this.value]['default_from_name'] );
            jQuery('#default_from_email').val( lists[this.value]['default_from_email'] );

        });

        jQuery('#campaignSubject').blur( function(){
            if( this.value ){
                jQuery('#subjectTitle').html( this.value );
            } else {
                jQuery('#subjectTitle').html( '&nbsp;' );
            }
        });
        jQuery('#campaignSubject').keyup( function(){
            if( this.value ){
                jQuery('#subjectTitle').html( this.value );
            } else {
                jQuery('#subjectTitle').html( '&nbsp;' );
            }
        });

    });

    function createSteps( id, sections ){
        var stepButton = '';
        if( !sections ){
            sections = templates[id]['sections'];
        }
        // add one step for each template section
        var thisStep = 2;
        var x = 2;

        for(i=0;i<sections;i++) {
            stepButton += '<div class="bgLine"></div><div id="step'+x+'" class="step ';

            if (thisStep == <?php echo $step;?>) {
                stepButton += 'activeStep';
            } else {
                stepButton += 'inactiveStep';
            }

            stepButton += '"><a href="javascript:gotoStep(<?php echo $step;?>,'+x+');" title="go to step '+thisStep+'">'+thisStep+'</a>';
            stepButton += '<div class="stepSubTitle">'+templates[id]['sectionNames'][i]+'</div>';
            stepButton += '</div>';
            thisStep++;
            x++;
        }

        // add last step
        var stepSubmit = parseInt(sections) + 2;
        stepButton = stepButton + '<div class="bgLine"></div><div id="step'+stepSubmit+'" class="step lastStep ';
        if (stepSubmit == <?php echo $step;?>) {
            stepButton += 'activeStep';
        } else {
            stepButton += 'inactiveStep';
        }

        stepButton += '"><a href="javascript:gotoStep(<?php echo $step;?>,'+stepSubmit+');" title="<?php _e('go to step', 'chimpexpress');?> '+stepSubmit+'">'+stepSubmit+'</a>';
        stepButton += '<div class="stepSubTitle"><?php _e('submit', 'chimpexpress');?></div>';
        stepButton += '</div>';

        jQuery('#stepsTemplateSections').html('').append( stepButton );

        if( <?php echo ($step);?> < ( parseInt(sections) + 2 ) ){
            jQuery("#nextStep").unbind('click');
            jQuery("#nextStep").click( function(){
                gotoStep( <?php echo $step;?>, <?php echo $step + 1;?> );
                return;
            });
            jQuery("#next").unbind('click');
            jQuery("#next").click(function() {
                gotoStep( <?php echo $step;?>, <?php echo $step + 1;?> );
            return;
            });
        } else {
            jQuery("#nextStep a").css('cursor','no-drop');
        }
        <?php if ( ($step - 1) >= 1 ){ ?>
        jQuery("#prevStep").unbind('click');
        jQuery("#prevStep").click( function(){
            gotoStep( <?php echo $step;?>, <?php echo $step - 1;?> );
        });
        <?php } else { ?>
        jQuery("#prevStep a").css('cursor','no-drop');
        <?php } ?>

        jQuery(".step").hover(
            function () {
                if (jQuery(this).attr('id') != 'step<?php echo $step;?>') {
                    jQuery(this).removeClass('inactiveStep');
                    jQuery(this).addClass('activeStep');
                }
            },
            function () {
                if(jQuery(this).attr('id') != 'step<?php echo $step;?>' ) {
                    jQuery(this).removeClass('activeStep');
                    jQuery(this).addClass('inactiveStep');
                }
            }
        );
        jQuery(".prevNext").hover(
            function () {
                jQuery(this).css('opacity', 1);
            },
            function () {
                jQuery(this).css('opacity', '');
            }
        );

        jQuery('.step').equalWidths();
    }

    var buttons = "";
    document.write=function(e){ buttons = buttons + e; jQuery("#quicktags").html(buttons); };
</script>
<!--[if lt IE 8]>
<style type="text/css">
#CEwrap .prevNext {     display:inline; top:-25px; }
#CEwrap .step {     display:inline; }
#CEwrap .bgLine {     display:inline; top:-33px; }
</style>
<![endif]-->
    <div id="loggedInStatus">
    <?php if ( $_SESSION['MCping'] ){
        echo sprintf(__('connected as <a href="options-general.php?page=ChimpExpressConfig">%s</a>', 'chimpexpress'), $_SESSION['MCusername']);
    } else {
        _e('<a href="options-general.php?page=ChimpExpressConfig">connect your MailChimp account</a>', 'chimpexpress');
    }?>
    </div>
    <h2 class="componentHeading">ChimpExpress</h2>
    <div class="clr"></div>
    <?php if (!$_SESSION['MCping']) { ?>
        <div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
            <a href="options-general.php?page=ChimpExpressConfig"><?php _e('Please connect your MailChimp account!', 'chimpexpress');?></a>
        </div>
    <?php }?>

    <?php
        global $wp_filesystem;
        if($wp_filesystem->method != 'direct') {
            $chimpexpress = new chimpexpress;
            $ftpstream = @ftp_connect( $chimpexpress->_settings['ftpHost'] );
            $login = @ftp_login($ftpstream, $chimpexpress->_settings['ftpUser'], $chimpexpress->_settings['ftpPasswd']);
            $ftproot = @ftp_chdir($ftpstream, $chimpexpress->_settings['ftpPath'] );
            $adminDir = @ftp_chdir($ftpstream, 'wp-admin' );

            if ($wp_filesystem->method != 'direct' &&
                    (!$chimpexpress->_settings['ftpHost'] || !$chimpexpress->_settings['ftpUser']
                        || !$chimpexpress->_settings['ftpPasswd'] || !$ftpstream || !$login || !$ftproot || !$adminDir)) {
    ?>
        <div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
            <a href="options-general.php?page=ChimpExpressConfig"><?php _e('Direct file access not possible. Please enter valid ftp credentials in the configuration!', 'chimpexpress');?></a>
        </div>
    <?php
            }
                @ftp_close($ftpstream);
        }
    ?>

    <div style="display:block;height:3em;"></div>
    <h3><?php _e('Compose', 'chimpexpress');?></h3>

    <hr />

    <h4 id="subjectTitle"><?php echo (isset($_POST['campaignSubject']))? $_POST['campaignSubject'] : '&nbsp;';?></h4>
    <div id="tName"><?php echo (isset($_POST['templateName']))? $_POST['templateName'] : '';?></div>

    <div id="stepsContainer">
        <div id="stepsContainerInner">
            <div id="prevStep" class="prevNext">
                <a href="javascript:void(0);" title="<?php _e('previous step', 'chimpexpress');?>"><?php _e('previous step', 'chimpexpress');?></a>
            </div>
            <div id="step1" class="step <?php echo ( $step == 1 ) ? 'activeStep' : 'inactiveStep';?>">
                <a href="javascript:gotoStep(<?php echo $step;?>,1);" title="<?php _e('go to step', 'chimpexpress');?> 1">1</a>
                <div class="stepSubTitle"><?php _e('settings', 'chimpexpress');?></div>
            </div>
            <div id="stepsTemplateSections"></div>
            <div id="nextStep" class="prevNext">
                <a href="javascript:void(0);" title="<?php _e('next step', 'chimpexpress');?>"><?php _e('next step', 'chimpexpress');?></a>
            </div>
            <div class="clr"></div>
        </div>
        <div class="clr"></div>
    </div>
    <div class="clr"></div>
    <div id="ajaxLoader" style="background-image: url(<?php echo plugins_url( '/images/ajax-loader.gif', __FILE__ ); ?>)"></div>

    <?php

        if($step == 1) {
            step1();

        } else if($step > ($_POST['sections']+1)) {
            stepSubmit();

        } else {
            stepContent($step);
        }

        function step1() {
            global $wp_filesystem;
            $MCAPI = new chimpexpressMCAPI;
            $cacheDir = 'wp-content' .DS. 'plugins' .DS. 'chimpexpress' .DS. 'cache';

            if ($wp_filesystem->method == 'direct') {
                $useFTP = false;
                $handler = $wp_filesystem;

                if(!is_dir(ABSPATH . $cacheDir)) {
                    $wp_filesystem->mkdir( ABSPATH . $cacheDir);
                }

            } else {
                $useFTP = true;
                $chimpexpress = new chimpexpress;
                $handler = @ftp_connect( $chimpexpress->_settings['ftpHost'] );
                $login = @ftp_login($handler, $chimpexpress->_settings['ftpUser'], $chimpexpress->_settings['ftpPasswd']);
                @ftp_chdir($handler, $chimpexpress->_settings['ftpPath'] );
                if( ! is_dir( ABSPATH . $cacheDir ) ){
                    @ftp_mkdir($handler, 'wp-content' .DS. 'plugins' .DS. 'chimpexpress' .DS. 'cache');
                }
            }

            $cache = new chimpexpressJG_Cache($cacheDir, $useFTP, $handler);

            $templates = $cache->get('templates');

            if ($templates === FALSE){
                $templates = $MCAPI->templates();
                $cache->set('templates', $templates);
            }
            $templateInfo = array();
            if( isset($templates['user']) ){
            foreach($templates['user'] as $t){
                $templateInfo[$t['id']] = $cache->get( 'templateInfo_'.$t['id'] );
                if ($templateInfo[$t['id']] === FALSE){
                $templateInfo[$t['id']] = $MCAPI->templateInfo($t['id']);
                $cache->set('templateInfo_'.$t['id'], $templateInfo[$t['id']]);
                }
            }
            }

            $lists = $cache->get('lists');
            if ($lists === FALSE){
            $MCAPI = new chimpexpressMCAPI;
            $result = true;
            $lists = array();
            $page = 0;
            $limit = 100;
            while( $result ){
                $result = $MCAPI->lists( '', $page, $limit );
                if( ! isset( $result['data'][0] ) ){
                $result = false;
                } else {
                $lists = array_merge( $lists, $result['data']);
                $page++;
                }
            }
            $cache->set('lists', $lists);
            }

            if( !$templates || !$lists ){
                _e('Templates and lists could not be retrieved! Please make sure you have set up templates and lists in MailChimp. <span id="reloadCache"><a href="javascript:void(0)">Click here to re-try.</a>', 'chimpexpress');
            exit;
            }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                if( $('#template').val() ){
                    $('#template').trigger('change');
                }

                if( $('#listId').val() ){
                    $('#listId').trigger('change');
                }
            });
        </script>

        <label for="template"><?php _e('select an email template', 'chimpexpress');?></label><br />
        <select name="template" id="template" style="float:left;">
            <?php
            $js = "var templates = new Array();\n";
            foreach($templates['user'] as $t){
            //    $templateInfo = $MCAPI->templateInfo($t['id']);

                $js .= "templates['".$t['id']."'] = new Array();\n";

                // remove header and footer from template's editable sections
                $i = 0;
                $skipSections = array();
                foreach ( $templateInfo[$t['id']]['sections'] as $tI ){
                    if ( $tI == 'header' || $tI == 'footer' ){
                        $skipSections[] = $i;
                    }
                    $i++;
                }
                $skipSectionsCount = count($skipSections);
                $skipSections = implode(',', $skipSections);

                $js .= "templates['".$t['id']."']['skipSections'] = '$skipSections'\n;";

                $selected = (isset($_POST['template']) && $_POST['template'] == $t['id']) ? ' selected="selected"' : '';
                echo '<option value="'.$t['id'].'"'.$selected.'>'.$t['name'].'</option>';

                $js .= "templates['".$t['id']."']['templateName'] = '".$t['name']."';\n";
                $js .= "templates['".$t['id']."']['sections'] = ".(count($templateInfo[$t['id']]['sections']) - $skipSectionsCount).";\n";
                $js .= "templates['".$t['id']."']['preview'] = '".str_replace(array("'", "\n", "\r"),array("\'", " ", " "), $templateInfo[$t['id']]['preview'])."';\n";
                $js .= "templates['".$t['id']."']['sectionNames'] = new Array();\n";
                $js .= "templates['".$t['id']."']['editorContent'] = new Array();\n";
                $x = 0;
                for($i=0;$i<count($templateInfo[$t['id']]['sections']);$i++){
                    if ( $templateInfo[$t['id']]['sections'][$i] != 'header' && $templateInfo[$t['id']]['sections'][$i] != 'footer' ){
                        $js .= "templates['".$t['id']."']['sectionNames'][$x] = '".$templateInfo[$t['id']]['sections'][$i]."';\n";
                        $js .= "templates['".$t['id']."']['editorContent'][$x] = '".trim( str_replace(array("'", "\n", "\r"),array("\'", " ", " "), esc_attr( $templateInfo[$t['id']]['default_content'][ $templateInfo[$t['id']]['sections'][$i] ] ) ) )."';\n";
                        $x++;
                    }
                }
            } ?>
        </select>
        <script type="text/javascript"><?php echo $js;?></script>
        <div id="preview" style="display:none;float:left;position:relative;top:3px;"><a class="button" href="" target="_blank"><?php _e('preview', 'chimpexpress');?></a></div>
        <div id="sectionsText" style="display:none;float:left;"><?php _e('this template has', 'chimpexpress');?>&nbsp;<span id="sectionsValue"></span>&nbsp;<?php _e('editable areas', 'chimpexpress');?></div>
        <div style="clear: both;"></div>
        <input type="hidden" name="sections" id="sections" value="" />
        <br />
        <br />
        <label for="listId"><?php _e('select a subscriber list', 'chimpexpress');?></label><br />
        <select id="listId" name="listId" style="float:left;">
            <?php
            $js = "var lists = new Array();\n";
            foreach ($lists as $l){
                $selected = (isset($_POST['listId']) && $_POST['listId'] == $l['id']) ? ' selected="selected"' : '';
                echo '<option value="'.$l['id'].'"'.$selected.'>'.$l['name'].'</option>';
                $js .= "lists['".$l['id']."'] = new Array();\n";
                $js .= "lists['".$l['id']."']['member_count'] = '".$l['stats']['member_count']."';\n";
                $js .= "lists['".$l['id']."']['default_from_name'] = '".$l['default_from_name']."';\n";
                $js .= "lists['".$l['id']."']['default_from_email'] = '".$l['default_from_email']."';\n";
            } ?>
        </select>
        <div id="listSubscribers" style="display:none;float:left;"><?php _e('this list has', 'chimpexpress');?>&nbsp;<span id="listSubscribersValue"></span>&nbsp;<?php _e('active subscribers', 'chimpexpress');?></div>
        <div style="clear: both;"></div>
        <script type="text/javascript"><?php echo $js;?></script>
        <br />
        <br />
        <label for="campaignName"><?php _e('campaign name', 'chimpexpress');?></label><br />
        <input type="text" size="75" maxlength="100" name="campaignName" id="campaignName" class="inputWide" value="<?php echo (isset($_POST['campaignName']))? $_POST['campaignName']:'';?>" /><br />
        <br />
        <label for="campaignSubject"><?php _e('subject line', 'chimpexpress');?></label><br />
        <input type="text" size="75" maxlength="100" name="campaignSubject" id="campaignSubject" class="inputWide" value="<?php echo (isset($_POST['campaignSubject']))? $_POST['campaignSubject']:'';?>" /><br />
        <br />
        <a class="button" id="next" href="javascript:void(0);" title="<?php _e('next &raquo;', 'chimpexpress');?>"><?php _e('next &raquo;', 'chimpexpress');?></a>
        <a id="cancel" class="grey" href="javascript:void(0);" title="<?php _e('cancel', 'chimpexpress');?>"><?php _e('cancel', 'chimpexpress');?></a>

        <input type='hidden' name='default_from_name' id='default_from_name' value="<?php echo (isset($_POST['default_from_name']))? $_POST['default_from_name'] : '';?>" />
        <input type='hidden' name='default_from_email' id='default_from_email' value="<?php echo (isset($_POST['default_from_email']))? $_POST['default_from_email'] : '';?>" />
        <input type='hidden' name='templateName' id='templateName' value='<?php echo (isset($_POST['templateName']))? $_POST['templateName'] : '';?>' />
        <input type='hidden' name='sectionNames' id='sectionNames' value="<?php echo (isset($_POST['sectionNames']))? $_POST['sectionNames'] : '';?>" />
        <input type='hidden' name='skipSections' id='skipSections' value="<?php echo $skipSections;?>" />
        <input type='hidden' name='editorContent' id='editorContent' value="<?php echo (isset($_POST['editorContent']))? str_replace('\"','',$_POST['editorContent']) : '';?>" />

        <input type='hidden' name='campaignId' id='campaignId' value="<?php echo (isset($_POST['campaignId']))? $_POST['campaignId'] : '0';?>" />

        <br />
        <br />
        <div id="reloadCache">
        <i>
        <?php _e('Note', 'chimpexpress');?>:
        <?php _e('Templates and lists are cached. <a href="javascript:void(0);" title="Reload cache">Reload cache</a> if an expected entry does not appear in the list.', 'chimpexpress');?>
        </i>
        </div>
<?php
            @ftp_close($ftpstream);
        }

        function stepContent( $step ){

            global $wpdb;

            $subject = (isset($_POST['campaignSubject']))? $_POST['campaignSubject'] : '';
            $editorClass = ( user_can_richedit() ) ? 'postdivrich' : 'postdiv';
            $editor = '';
            $template = (isset($_POST['template']))? $_POST['template'] : '';
            $templateName = (isset($_POST['templateName']))? $_POST['templateName'] : '';
            $sections = (isset($_POST['sections']))? $_POST['sections'] : '';
            $sectionNames = (isset($_POST['sectionNames']))? $_POST['sectionNames'] : array();
            $sectionsArray = explode('|###|', $sectionNames);
            $campaignName = (isset($_POST['campaignName']))? $_POST['campaignName'] : '';
            $campaignSubject = (isset($_POST['campaignSubject']))? $_POST['campaignSubject'] : '';

?>

<h4 id="sectionTitleWrapper"><span class="sectionTitle black"><?php echo $sectionsArray[($step-2)];?></span> <span class="grey"><?php _e('content section', 'chimpexpress');?></span></h4>
<link rel='stylesheet' id='thickbox-css'  href='<?php echo get_option('home');?>/wp-includes/js/thickbox/thickbox.css' type='text/css' media='all' />

<script type="text/javascript">
    var buttons = "";
    var sections = jQuery('#sections').val();
    createSteps( '<?php echo $template;?>', sections );
    function insertContent(value) {
        if(value != ''){
            newValue = decodeURIComponent((posts[value]+'').replace(/\+/g, '%20'));
            tinymce.get(theEditorID).setContent(tinymce.get(theEditorID).getContent() + newValue);
        }
    }
</script>
<?php
    wp_print_scripts('quicktags'); // REQUIRED!
?>
<div id="poststuff" class="postarea">
    <?php
        if( isset($_POST['editorContent']) ){
            $editorContent = $_POST['editorContent'];
            $content = explode('|###|',$editorContent);
            $index = $step - 2;
            $content = str_replace( array( "\'", '\"'),"'" ,$content[$index]);
            $content = str_replace( '\\', '',$content);
        } else {
            $editorContent = '';
            $content = '';
        }

        $id = uniqid('content_');

        $rows = get_option('default_post_edit_rows');
        $rows = (($rows < 3) || ($rows > 100)) ? 15 : $rows;

        wp_editor($content, $id, array(
            'tab_index' => '2',
            'teeny' => false,
            'textarea_rows' => $rows,
            'media_buttons' => true,
            'tinymce' => array(
                'plugins' => 'inlinepopups, fullscreen, wordpress, wplink, wpdialogs'
            )
        ));
    ?>
</div>

<script type="text/javascript">
    var theEditorID = '<?php echo $id ?>';
</script>

<?php
    $sql = "SELECT {$wpdb->posts}.* FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_status = 'publish'
                AND {$wpdb->posts}.post_type = 'post' AND {$wpdb->posts}.post_date < NOW()
                ORDER BY {$wpdb->posts}.post_date DESC";
    $posts = $wpdb->get_results($sql, OBJECT);
?>

<div id="insertPost">
    <?php _e('Insert content from blog post', 'chimpexpress');?>:
    <select id="insertPosts" onchange="insertContent(this.value)">
        <option value=""><?php _e('-- select post --', 'chimpexpress');?></option>
        <?php
        $js = "var posts = new Array();\n";
        foreach($posts as $p){
            echo '<option value="'.$p->ID.'" title="'.esc_attr(substr( $p->post_content, 0, 150)).' ...">'.$p->post_title.'</option>';
            $js .= "posts['".$p->ID."'] = '<h2>".esc_attr(urlencode($p->post_title))."</h2>".esc_attr(urlencode(str_replace(array("\n"),'',$p->post_content)))."';\n";
        }
        ?>
    </select>
    <script type="text/javascript">
        <?php echo $js; ?>
    </script>
</div>

<input type="hidden" name="listId" id="listId" value="<?php echo (isset($_POST['listId']))? $_POST['listId']:'';?>" />
<input type='hidden' name='default_from_name' id='default_from_name' value="<?php echo (isset($_POST['default_from_name']))? $_POST['default_from_name'] : '';?>" />
<input type='hidden' name='default_from_email' id='default_from_email' value="<?php echo (isset($_POST['default_from_email']))? $_POST['default_from_email'] : '';?>" />
<input type='hidden' name='template' id='template' value='<?php echo $template;?>' />
<input type='hidden' name='templateName' id='templateName' value='<?php echo (isset($_POST['templateName']))? $_POST['templateName'] : '';?>' />
<input type='hidden' name='sections' id='sections' value='<?php echo $sections;?>' />
<input type='hidden' name='sectionNames' id='sectionNames' value="<?php echo $sectionNames;?>" />
<input type='hidden' name='skipSections' id='skipSections' value="<?php echo $_POST['skipSections'];?>" />
<input type='hidden' name='editorContent' id='editorContent' value="<?php echo str_replace('\"','',$editorContent);?>" />

<input type='hidden' name='campaignName' id='campaignName' value='<?php echo $campaignName;?>' />
<input type='hidden' name='campaignSubject' id='campaignSubject' value='<?php echo $campaignSubject;?>' />

<input type='hidden' name='campaignId' id='campaignId' value="<?php echo (isset($_POST['campaignId']))? $_POST['campaignId'] : '0';?>" />

<a class="button" id="next" href="javascript:void(0);" title="<?php _e('next &raquo;', 'chimpexpress');?>"><?php _e('next &raquo;', 'chimpexpress');?></a>
<a id="cancel" class="grey" href="javascript:void(0);" title="<?php _e('cancel', 'chimpexpress');?>"><?php _e('cancel', 'chimpexpress');?></a>
<?php
}

function stepSubmit(){
    $MCAPI = new chimpexpressMCAPI;
    if( isset($_POST['campaignName']) && $_POST['campaignName'] != '' &&
        isset($_POST['campaignSubject']) && $_POST['campaignSubject'] != ''

    ){
        $options = array();
        $options['list_id'] = $_POST['listId'];
        $options['title'] = html_entity_decode($_POST['campaignName']);
        $options['subject'] = html_entity_decode($_POST['campaignSubject']);

        $replace = array('&#039;', '&amp;');
        $with     = array("'", '&');
        $options['title']   = str_replace($replace, $with, $options['title']);
        $options['subject'] = str_replace($replace, $with, $options['subject']);

        $options['template_id'] = $_POST['template'];

        $content = array();
        $sectionNames = explode('|###|', $_POST['sectionNames']);
        $editorContent = explode('|###|', $_POST['editorContent']);
        for($i=0;$i<count($sectionNames);$i++){
            if($sectionNames[$i] != 'header' && $sectionNames[$i] != 'footer'){
            $content['html_'.$sectionNames[$i]] = trim(str_replace(array('\\','\"',"\'"),array('','"',"'"), $editorContent[$i]));
            }
        }

        if( $_POST['campaignId'] ){
            foreach($options as $k => $v){
                $MCAPI->campaignUpdate( $_POST['campaignId'], $k, $v );
            }
            $MCAPI->campaignUpdate( $_POST['campaignId'], 'content', $content );

            $campaignId = $_POST['campaignId'];
        } else {
            $options['from_email'] = $_POST['default_from_email'];
            $options['from_name'] = $_POST['default_from_name'];
            $options['inline_css'] = true;
            $options['generate_text'] = true;
            $type = 'regular';
            $campaignId = $MCAPI->campaignCreate( $type, $options, $content );
        }
        if($campaignId){
            $campaign = $MCAPI->campaigns( array( 'campaign_id' => $campaignId ) );
            $campaignContent = $MCAPI->campaignContent( $campaignId, false );
        } else {
            $campaignId = '';
        }

        if($campaign){
            // create preview file
            global $wp_filesystem;
            $tmpDirAbs = WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'tmp';
            $tmpDirRel = get_option('home') . '/wp-content/plugins/chimpexpress/tmp/';

            if( $wp_filesystem->method == 'direct' ){
                // remove tmp folder
                if ( is_dir( $tmpDirAbs ) ){
                $wp_filesystem->delete( $tmpDirAbs, true);
                }
                // create new (empty) tmp folder
                $wp_filesystem->mkdir( $tmpDirAbs );
                // write preview file
                $wp_filesystem->put_contents( $tmpDirAbs .DS. sanitize_title( $_POST['campaignSubject'] ) . '.html', $campaignContent['html'] );
            } else {
                $chimpexpress = new chimpexpress;
                $ftpstream = @ftp_connect( $chimpexpress->_settings['ftpHost'] );
                $login = @ftp_login($ftpstream, $chimpexpress->_settings['ftpUser'], $chimpexpress->_settings['ftpPasswd']);
                @ftp_chdir($ftpstream, $chimpexpress->_settings['ftpPath'] );

                // remove tmp folder
                if ( is_dir( $tmpDirAbs ) ){
                    chimpexpress::ftp_delAll( $ftpstream, 'wp-content' .DS. 'plugins' .DS. 'chimpexpress' .DS. 'tmp' );
                }
                // create new (empty) tmp folder
                @ftp_mkdir($ftpstream, 'wp-content' .DS. 'plugins' .DS. 'chimpexpress' .DS. 'tmp');
                // write preview file
                $temp = tmpfile();
                fwrite($temp, $campaignContent['html']);
                rewind($temp);
                @ftp_chdir($ftpstream, 'wp-content' .DS. 'plugins' .DS. 'chimpexpress' .DS. 'tmp' );
                @ftp_fput($ftpstream, sanitize_title( $_POST['campaignSubject'] ) . '.html', $temp, FTP_ASCII);
                @ftp_close($ftpstream);
            }

            $link = $tmpDirRel . sanitize_title( $_POST['campaignSubject'] ) . '.html';

        //    $iframe = '<div id="monkey-ruler"><p><strong id="monkeyhead">Note: </strong>'.__("Your email shouldn't be much more than 600 pixels wide.", 'chimpexpress').'</p></div>';
            $iframe = '<iframe name="previewFrame" src="'.$link.'" width="100%" height="800" style="border:1px solid #bfbfbf;"></iframe>';

            /*
            $link = 'http://'.$MCAPI->dc.'.admin.mailchimp.com/campaigns/preview?id='.$campaign['data'][0]['web_id'];
        //    $link = $campaign['data'][0]['archive_url'];
            $iframe = '<iframe name="previewFrame" src="'.$link.'" width="100%" height="800" style="border:1px solid #bfbfbf;"></iframe>';
            */
        } else {
            $iframe = '';
        }
    } else {
        $MCAPI->_addError( array("error" => __('Campaign name and subject line must be supplied!', 'chimpexpress'), "code" => "-99") );
        $MCAPI->showMessages();
        $iframe = '';
        $campaignId = '';
    }
//    var_dump($campaign);
$dc = substr( strrchr($MCAPI->api_key, '-'), 1 );
?>
<script type="text/javascript">
buttons = "";
sections=jQuery('#sections').val();
createSteps( '<?php echo $_POST['template'];?>', sections );
<?php /*
jQuery(document).ready(function($) {
    if( jQuery('#chimpexpressCompose').width() > 1175 ){
        jQuery("#monkeyhead").addClass("scream");
    }
    $(window).resize(function() {
        if( jQuery('#chimpexpressCompose').width() > 1175 ){
            jQuery("#monkeyhead").addClass("scream");
        } else {
            jQuery("#monkeyhead").removeClass("scream");
        }
    });
});
*/ ?>
</script>
<?php if($iframe){ ?>
    <h3 style="font-family:Arial,sans-serif;font-style:normal;margin:0 0 2em;"><?php _e('High fives! Your campaign is in MailChimp ready to send.', 'chimpexpress');?></h3>
    <h4 style="color: #464646;margin:0 0 1em;"><?php _e('What do I do now?', 'chimpexpress');?></h4>
    <ul class="ul">
        <li><a href="http://<?php echo $dc;?>.admin.mailchimp.com/campaigns/" target="_blank" title="<?php _e('login to MailChimp', 'chimpexpress');?>"><?php _e('login to MailChimp', 'chimpexpress');?></a></li>
        <li><?php echo sprintf(__('open the campaign "%s" and click "send now"', 'chimpexpress'), $_POST['campaignName']); ?></li>
    </ul>
    <a class="button next" id="gotoMailChimp" href="http://<?php echo $dc;?>.admin.mailchimp.com/campaigns/" target="_blank" title="<?php _e('open MailChimp', 'chimpexpress');?>"><?php _e('open MailChimp', 'chimpexpress');?></a>
    <a id="cancelCompose" class="grey" href="javascript:void(0);" title="<?php _e('cancel (and remove draft from MailChimp)', 'chimpexpress');?>"><?php _e('cancel (and remove draft from MailChimp)', 'chimpexpress');?></a>
    <h4 style="margin: 3em 0 1em 0;"><?php _e('Preview', 'chimpexpress');?></h4>
    <?php echo $iframe; ?>
<?php } ?>

<input type="hidden" name="listId" id="listId" value="<?php echo (isset($_POST['listId']))? $_POST['listId']:'';?>" />
<input type='hidden' name='default_from_name' id='default_from_name' value="<?php echo (isset($_POST['default_from_name']))? $_POST['default_from_name'] : '';?>" />
<input type='hidden' name='default_from_email' id='default_from_email' value="<?php echo (isset($_POST['default_from_email']))? $_POST['default_from_email'] : '';?>" />
<input type="hidden" name="template" id="template" value="<?php echo (isset($_POST['template']))? $_POST['template']:'';?>" />
<input type='hidden' name='templateName' id='templateName' value='<?php echo (isset($_POST['templateName']))? $_POST['templateName'] : '';?>' />
<input type="hidden" name="sections" id="sections" value="<?php echo (isset($_POST['sections']))? $_POST['sections']:'';?>" />
<input type='hidden' name='sectionNames' id='sectionNames' value="<?php echo (isset($_POST['sectionNames']))? $_POST['sectionNames'] : '';?>" />
<input type='hidden' name='skipSections' id='skipSections' value="<?php echo $_POST['skipSections'];?>" />
<input type="hidden" name="campaignName" id="campaignName" value="<?php echo (isset($_POST['campaignName']))? $_POST['campaignName']:'';?>" />
<input type="hidden" name="campaignSubject" id="campaignSubject" value="<?php echo (isset($_POST['campaignSubject']))? $_POST['campaignSubject']:'';?>" />
<input type='hidden' name='editorContent' id='editorContent' value="<?php echo (isset($_POST['editorContent']))? str_replace('\"','',$_POST['editorContent']) : '';?>" />

<input type='hidden' name='campaignId' id='campaignId' value="<?php echo $campaignId;?>" />
<?php
}

?>

<?php include( WP_PLUGIN_DIR . DS . 'chimpexpress' . DS . 'footer.php' ); ?>

</div>
<?php
$MCAPI->showMessages();

function rrmdir($dir) {
    if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
        }
    }
    reset($objects);
    rmdir($dir);
    }
}
