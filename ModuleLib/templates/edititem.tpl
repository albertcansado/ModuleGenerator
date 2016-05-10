{assign var="module" value=$mod}
{assign var="oldactionid" value=$actionid}


{literal}
    <style type="text/css">
        .label__helper {
            color: #777;
            font-size: 11px;
            margin-left: 5px;
        }
        .scrollable {
            display: none;
            width: 90%;
        }
        .jt-hidden {
            display: none;
        }
        .jt-col {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .jt-input {
            width: 90%;
        }
        .jt-btn {
            background-color: transparent;
            border: 0 none;
            cursor: pointer;
            display: inline-block;
            margin: 0 5px;
            padding: 0;
        }
        .jt-options,
        .jt-options--edit {
            text-align: center;
            display: none;
        }
        .jt-options {
            display: block;
        }
        .jt-is-editing .jt-options {
            display: none;
        }
        .jt-is-editing .jt-options--edit {
            display: block;
        }

        .is-hidden {
            display: none;
        }

        .dropdown__btn {
            margin: 0;
            vertical-align: top;
        }

        .dropdown__msg {
            border: 1px solid transparent;
            margin-bottom: 10px;
            padding: 5px 10px;
            text-align: center;
        }
        .dropdown__msg--error {
            background-color: rgba(198, 17, 17, 0.65);
            border-color: rgb(233, 39, 39);
            color: #fff;
        }
        .dropdown__msg--success {
            background-color: rgba(79, 198, 17, 0.6);
            border-color: rgb(51, 209, 20);
        }

        .dropdown__add-form .input {
            margin: 5px 0;
        }
        .dropdown__add-form .input__label {
            display: block;
            padding-left: 5px;
        }
        .dropdown__add-form .input__field {
            width: 80%;
        }
        .dropdown__add-form .dropdown__add-showhide {
            color: #777;
            cursor: pointer;
            display: block;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function(){
            $('#itemlist, .js-tablesorter').tableDnD();
            $('#galleryform form').submit(function() {
                var data = new Array;
                $('table#itemlist tr').each(function(i,tr) {
                    data.push($(tr).attr('id'));
                });
                data = data.join(',');
                $('#serialdata').val(data);
            });
            $('.cms_color_input').spectrum({
                showInput: true,
                preferredFormat: "hex",
                cancelText: "{/literal}{'cancel'|lang}{literal}",
                chooseText: "{/literal}{'apply'|lang}{literal}",
            });
            new DropdownAdd({
                selector: ".js-dropdown-add",
                url: "{/literal}{$dropdownLink}{literal}",
                prefix: "{/literal}{$actionid}{literal}"
            });
        });

        function toggleDisplay(elem) {
            var el = document.getElementById(elem);
            var txt = el.style.display;
            if( txt == 'none' ) {
                txt = 'block';
            } else {
                txt = 'none';
            }
            el.style.display = txt;
        }
    </script>
{/literal}

<h3>{$title}</h3>
<div id="case">
{$startform}


<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{if isset($item_id)}{$item_id}{/if}{$submit}{$cancel}{$apply} {if isset($copy)}{$copy}{/if}{if $module->GetPreference('preview_admin')}<span style="position:relative; top:5px;">{$preview_view}</span>{/if}</p>
</div>


{$module->StartTabHeaders()}
{$module->SetTabHeader('article', $module->GetPreference('item_singular'))}
{if isset($custom_fielddef)}
    {foreach from=$custom_fielddef item='field'}
        {if $field->type == 'tab'}
            {$module->SetTabHeader($field->alias, $field->label)}
        {/if}            
    {/foreach}            
{/if}
{if $module->GetPreference('has_gallery') && isset($item_id)}{$module->SetTabHeader('gallery', $module->Lang('gallery'))}{/if}
{if isset($tabheader_preview)}{$tabheader_preview}{/if}
{$module->EndTabHeaders()}

{$module->StartTabContent()}
{$module->StartTab('article')}


<div class="pageoverflow">
    <p class="pagetext">{$prompt_active}:</p>
    <p class="pageinput">{$input_active}</p>
</div>

{if $mod->GetPreference('item_featured_edit')}
<div class="pageoverflow">
    <p class="pagetext">{$prompt_featured}:</p>
    <p class="pageinput">{$input_featured}</p>
</div>
{/if}


{if $mod->GetPreference('item_title_edit')}
    <div class="pageoverflow">
        <p class="pagetext">*{$prompt_title}:</p>
        <p class="pageinput">{$input_title}</p>
    </div>
{/if}

{if $mod->GetPreference('item_alias_edit')}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_alias}:</p>
        <p class="pageinput">{$input_alias}</p>
    </div>
{/if}

{if $mod->GetPreference('item_url_edit')}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_url}:</p>
        <p class="pageinput">{$input_url}</p>
    </div>
{/if}

{if $mod->GetPreference('item_category_edit')}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_category}:</p>
        <p class="pageinput">{$input_category}</p>
    </div>
{/if}

{if $mod->GetPreference('item_date_edit')}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_date}:</p>
        <p class="pageinput">{html_select_date prefix=$dateprefix time=$date start_year="-10" end_year="+15"} {html_select_time prefix=$dateprefix time=$date}</p>
    </div>
{/if}

{if $mod->GetPreference('item_date_end_edit')}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_date_end}:</p>
        <p class="pageinput">{html_select_date prefix=$dateendprefix time=$dateend start_year="-10" end_year="+15"} {html_select_time prefix=$dateendprefix time=$dateend}</p>
    </div>
{/if}

{if $mod->GetPreference('recursive')}

    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('recur_period')}:</p>
    {capture assign='tmp'}{$actionid}event_recur_period{/capture}
    <p class="pageinput">
        <select id="recurperiod" start_tab_headerstaactive="{$actionid}recur_period" onclick="handleDropdown();">
            {foreach from=$recur_options key=key item=value}
                <option value="{$value}" {if $value == $recursive}selected="selected"{/if}>{$key}</option>   
            {/foreach}
        </select>
    </p>
</div>

<div class="pageoverflow" id="recur_weekly">  
    <p class="pagetext">{$mod->Lang('weekdays')}:</p>
    <p class="pageinput">{$input_weekdays}</p>
</div>

{/if}

{if isset($custom_fielddef)}
    {foreach from=$custom_fielddef item='field'}
        {if $field->type == 'tab'}
            {$module->EndTab()}
            {$module->StartTab($field->alias)}                
        {elseif (!isset($item_id) && !$field->editview) || isset($item_id)}
            {if $field->field}
                <div class="pageoverflow">
                    {if !$field->hidename}<p class="pagetext">{$field->prompt}:</p>{/if}
                    <p class="pageinput">
                    {if !empty($field->help)}({$field->help})<br />{/if}
                    {$field->field}
                    {if isset($field->filename)}<br />
                    {capture assign="src"}{$field->file_location}/{$field->filename}{/capture}                     
                {capture assign="srcpath"}{$field->filepath_location}/{$field->filename}{/capture}                     
                {if $field->is_image}
                    {if $image_size_admin_width && $image_size_admin_height}
                        {cms_module module="CGSmartImage" src=$src alt=$field->filename filter_croptofit="`$image_size_admin_width`,`$image_size_admin_height`" style="margin:5px 0;"}    
                    {elseif $image_size_admin_width}
                        {cms_module module="CGSmartImage" src=$src alt=$field->filename filter_resize="w,`$image_size_admin_width`" style="margin:5px 0;"}    
                    {elseif $image_size_admin_height}
                        {cms_module module="CGSmartImage" src=$src alt=$field->filename filter_resize="h,`$image_size_admin_height`" style="margin:5px 0;"}    
                    {/if}
                {/if}
                <br />
                {$field->delete_file} {$field->filename}<br />
            {/if}
        </p>
    </div>
{else}
    <div class="pageoverflow">
        <p class="pagetext">{$field->prompt}</p>
        {if $field->extra}
            <p class="pageinput">            
                {$field->extra}
            </p>
        {/if}
    </div>
{/if}
{/if}
{/foreach}
{/if}
{*<div class="pageoverflow">
<p class="pagetext">&nbsp;</p>
<p class="pageinput">{if isset($item_id)}{$item_id}{/if}{$submit}{$cancel}{$apply} {if isset($copy)}{$copy}{/if}{if $module->GetPreference('preview_admin')}<span style="position:relative; top:5px;">{$preview_view}</span>{/if}</p>
</div>*}
{$module->CreateFormEnd()}
{$module->EndTab()}

{if isset($start_tab_preview)}

    {$start_tab_preview}
    <script type="text/javascript">{literal}
    jQuery(document).ready(function(){
    /*  jQuery('input[name=m1_apply]').click(function(){
        if( typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
        var data = jQuery('form').find('input:not([type=submit]), select, textarea').serializeArray();
        data.push({'name': 'm1_ajax', 'value': 1});
        data.push({'name': 'm1_apply', 'value': 1});
        data.push({'name': 'showtemplate', 'value': 'false'});
        var url = jQuery('form').attr('action');
        jQuery.post(url,data,function(resultdata,text){
          var resp = jQuery(resultdata).find('Response').text();
          var details = jQuery(resultdata).find('Details').text();
          var htmlShow = '';
          if( resp == 'Success' && details != '' )
          {
             htmlShow = '<div class="pagemcontainer"><p class="pagemessage">'+details+'<\/p><\/div>';
          }
          else
          {
             htmlShow = '<div class="pageerrorcontainer"><ul class="pageerror">';
             htmlShow += details;
             htmlShow += '<\/ul><\/div>';
          }
          jQuery('#editarticle_result').html(htmlShow);
        },'xml');
        return false;
      });*/


      function generator_dopreview()
      {
        if( typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
        var data = jQuery('#case form:first').find('input:not([type=submit]), input:checkbox, input:radio, select, textarea').serializeArray();
        data.push({'name': 'm1_ajax', 'value': 1});
        data.push({'name': 'm1_preview', 'value': 1});
        data.push({'name': 'showtemplate', 'value': 'false'});
        data.push({'name': 'm1_previewpage', 'value': jQuery('#preview_returnid').val()});
        data.push({'name': 'm1_detailtemplate', 'value': jQuery('#preview_template').val()});
        var url = jQuery('#case form:first').attr('action');
        jQuery.get(url,data,function(resultdata,text){
          var resp = jQuery(resultdata).find('Response').text();
          var details = jQuery(resultdata).find('Details').text();
          var htmlShow = '';
          if( resp == 'Success' && details != '' )
          {
             // preview worked... now the details should contain the url
             details = details.replace(/amp;/g,'');
             jQuery('#previewframe').attr('src',details);
          }
          else
          {
             if( details == '' ) details = 'An unknown error occurred';

             // preview save did not work.
             htmlShow = '<div class="pageerrorcontainer"><ul class="pageerror">';
             htmlShow += details;
             htmlShow += '<\/ul><\/div>';
             jQuery('#editarticle_result').html(htmlShow);
          }
        },'xml');
      }

      jQuery('#preview').click(function(){
          generator_dopreview()
        return false;
      });

      jQuery('#preview_returnid,#preview_template').change(function(){
        generator_dopreview();
        return false;
      });
    });
        {/literal}</script>

        {* display a warning *}
        <div class="pagewarning">{$warning_preview}</div>
        <fieldset>

            <label for="preview_template">{$prompt_detail_template}:</label>&nbsp;
            <select id="preview_template" name="preview_template">
                {html_options options=$detail_templates selected=$cur_detail_template}
            </select>&nbsp;

            <label for="preview_returnid">{$prompt_detail_page}:</label>&nbsp;
            {$preview_returnid}
        </fieldset>
        <br/>
        <iframe id="previewframe" style="height: 800px; width: 100%; border: 1px solid black; overflow: auto;" src=""></iframe>
        {$end_tab_preview}
        {/if}

            {if $module->GetPreference('has_gallery') && isset($item_id)}

                {$module->StartTab('gallery')}              

                <div id="container">
                    <div id="filelist"></div>
                    <a id="pickfiles" href="javascript:;">{$module->Lang('add_files')}</a> 
                </div>


                {literal}

                    <script type="text/javascript">
                   $(function() {
        var uploader = new plupload.Uploader({
                runtimes : 'gears,html5,flash,silverlight,browserplus',
                browse_button : 'pickfiles',
                container : 'container',
                max_file_size : '10mb',
                url : '../modules/ModuleGenerator/js/plupload/upload.php?gallery_path={/literal}{$gallery_path|urlencode}{literal}',
                flash_swf_url : '../modules/ModuleGenerator/js/plupload/plupload.flash.swf',
                silverlight_xap_url : '../modules/ModuleGenerator/js/plupload/plupload.silverlight.xap',
                filters : [
                        {title : "Image files", extensions : "jpg,gif,png"}
                ]
        });

        uploader.bind('Init', function(up, params) {
                //$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
        });

        $('#uploadfiles').click(function(e) {
                uploader.start();
                e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
                $.each(files, function(i, file) {
                        $('#filelist').append(
                                '<div id="' + file.id + '">' +
                                file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                        '</div>');
                });
                    
                up.refresh(); // Reposition Flash/Silverlight
                      uploader.start();
        });

        uploader.bind('UploadProgress', function(up, file) {
                $('#' + file.id + " b").html(file.percent + "%");
        });

        uploader.bind('Error', function(up, err) {
                $('#filelist').append("<div>Error: " + err.code +
                        ", Message: " + err.message +
                        (err.file ? ", File: " + err.file.name : "") +
                        "</div>"
                );

                up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
                $('#' + file.id + " b").html("100%");
                    
                       if( (uploader.total.uploaded ) == uploader.files.length)
         {
        window.location = '{/literal}{$redirect_url|replace:'&amp;':'&'}{literal}';
          }
        });
            
            
            
});
                    </script>
                {/literal}
                {$endform}
{*</div>*}
                {if $gallery|@count}



                    <div id="galleryform">                          
                        {$module->CreateFormStart($oldactionid, 'admin_moveimages')}
                        <input id="serialdata" type="hidden" name="{$oldactionid}serialdata" value=""/>
                        {$item_id}
                        <div class="pageoverflow">
                            <p class="pagetext"></p>
                            <p class="pageinput">
                                <input class="save_items" type="submit" name="{$oldactionid}submit" value="{$module->Lang('save_order')}"/>
                            </p>
                        </div>
                        {$module->CreateFormEnd()}
                    </div>
                {/if}

                {if $gallery|@count}
                    {$module->CreateFormStart($oldactionid, 'admin_imagesbulkaction')}
                    {$item_id}

                    <div style="text-align: right; width: 40%; float: right; margin-bottom: 1em;">
                        <input type="submit" id="bulkaction_submit" name="{$oldactionid}submit" value="{$module->Lang('delete_selected')}"/>
                    </div>
                    <table cellspacing="0" class="pagetable cms_sortable tablesorter" id="itemlist">
                        <thead>
                            <tr>
                                <th >{$module->Lang('filename')}</th>
                                {if isset($custom_fields_gallery)}
                                    {foreach from=$custom_fields_gallery item='fid'}
                                        <th>{$fields_viewable.$fid}</th>
                                    {/foreach}
                                {/if}

                                <th class=" {literal}{sorter: false}{/literal}">&nbsp;</th>                                
                                <th style="width:50px;" class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                                <th style="width:50px;" class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                                <th style="width:50px;"  class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>                
                            {foreach from=$gallery item='gal'}
                                {cycle values="row1,row2" assign='rowclass'}
                                <tr  id="{$gal.image_id}" class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
                                    <td>{$gal.filename}</td>

                                    {if isset($custom_fields_gallery)}
                                        {foreach from=$custom_fields_gallery item='fid'}
                                            <td>
                                                {assign var='tmpres' value=$gal.fieldsbyid.$fid.value}

                                                {if isset($field_modules.$fid)}
                                                    {if isset($tmpres) && $tmpres}
                                            {capture assign="tmpmodule"}{literal}{cms_module{/literal} module="{$field_modules.$fid}"  allrow="tmprows" filter_itemId_in="{$tmpres}"{literal}}{/literal}{/capture}
                                        {/if}
                                        {if isset($tmpmodule)}        
                                            {eval var=$tmpmodule}
                                        {/if}
                                        {if $tmprows}
                                            {foreach from=$tmprows item='tmprow' name="module"}
                                            {$tmprow->title}{if $smarty.foreach.module.last==false},{/if}
                                        {/foreach}
                                    {/if}
                                    {assign var="tmprows" value=""}
                                    {assign var="tmpmodule" value=""}
                                {elseif isset($field_images.$fid) && $tmpres}
                                {capture assign="src"}{$entry->file_location}/{$tmpres}{/capture}
                            {capture assign="srcpath"}{$entry->filepath_location}/{$tmpres}{/capture}
                            {if $srcpath|@getimagesize}
                                {cms_module module="CGSmartImage" src=$srcpath  filter_resize="h,50" style="margin:5px 0;"}    
                            {else}
                                <a href="{$entry->file_location}/{$tmpres}">{$tmpres}</a>
                            {/if}
                        {else}
                            {$tmpres}
                        {/if}
                    </td>
                {/foreach}
            {/if}

            <td>

            {capture assign="src"}{$gal.image_location}/{$gal.filename}{/capture}
        {capture assign="srcpath"}{$gal.imagepath_location}/{$gal.filename}{/capture}
        {if $srcpath|@getimagesize}
            {if $image_size_admin_width && $image_size_admin_height}
                {cms_module module="CGSmartImage" src=$srcpath  style="margin:5px 0;" filter_croptofit="`$image_size_admin_width`,`$image_size_admin_height`"}    
            {elseif $image_size_admin_width}
                {cms_module module="CGSmartImage" src=$srcpath  style="margin:5px 0;" filter_resize="w,`$image_size_admin_width`"}    
            {elseif $image_size_admin_height}
                {cms_module module="CGSmartImage" src=$srcpath  style="margin:5px 0;" filter_resize="h,`$image_size_admin_height`"}    
            {/if}
        {else}
            {*<a href="{$entry->file_location}/{$entry->$tmp}">{$entry->$tmp}</a>*}
        {/if}
    </td>
    <td><input type="checkbox" class="multiselect" name="{$oldactionid}multiselect[]" value="{$gal.image_id}"></td>
    <td>{$gal.editlink}</td>
    <td>{$gal.deletelink}</td>
</tr>
{/foreach}    
</tbody>
</table>
{$module->CreateFormEnd()}
{/if}
{$module->EndTab()}
{else}


    {$module->EndTab()}




{/if}

{$module->EndTabContent()}





