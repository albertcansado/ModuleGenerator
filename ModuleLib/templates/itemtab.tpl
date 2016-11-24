{assign var="oldactionid" value=$actionid}

{if $module->GetPreference('mode') == "simple"}
    {literal}
        <style type="text/css">
            .scrollable {
                display: none;
                width: 90%;
            }

        </style>

        <script type="text/javascript">
            $(document).ready(function(){
                $('#itemlist').tableDnD();
                $('#sort_items form').submit(function() {
                    var data = new Array;
                    $('table#itemlist tr').each(function(i,tr) {
                        data.push($(tr).attr('id'));
                    });
                    data = data.join(',');
                    $('#serialdata').val(data);
                });
            });
        </script>
    {/literal}
{/if}


<div class="form">
    {if $module->GetPreference('filter')}
        {$formstart}

        <legend>{$module->Lang('filters')}:&nbsp;</legend>

        {if $module->GetPreference('filter_categories')}
            <div class="pageoverflow">
                <p class="pagetext">{$module->Lang('hierarchy')}:</p>
                <p class="pageinput">{$input_hierarchy}&nbsp;{$module->Lang('include_children')} {$input_children}</p>
            </div>
        {/if}
        {if $module->GetPreference('mode') != "simple"}
            <div class="pageoverflow">
                <p class="pagetext">{$module->Lang('prompt_summary_sorting')}:</p>
                <p class="pageinput">{$input_sortby}</p>
            </div>
            <div class="pageoverflow">
                <p class="pagetext">{$module->Lang('prompt_summary_sortorder')}:</p>
                <p class="pageinput">{$input_sortorder}</p>
            </div>
            <div class="pageoverflow">
                <p class="pagetext">{$module->Lang('page_limit')}:</p>
                <p class="pageinput">{$input_pagelimit}</p>
            </div>
        {/if}
        {if $module->GetPreference('filter_date')}
            <div class="pageoverflow">
                <p class="pagetext">{$module->Lang('date')}</p>
                <p class="pageinput">
                    {$module->Lang('from')}
                {capture assign="idfromdate"}{$module->GetActionId()}datefrom{/capture}
                {html_select_date field_order=DMY prefix=$idfromdate time=$date_from start_year="-10" end_year="+1"}
                &nbsp;
                {$module->Lang('to')}
            {capture assign="idtodate"}{$module->GetActionId()}dateto{/capture}
            {html_select_date field_order=DMY prefix=$idtodate time=$date_to start_year="-10" end_year="+1"}
        </p>
    </div>
{/if}

{if isset($custom_fielddef)}
    {foreach from=$custom_fielddef item='field'}
        {if $field->field}
            <div class="pageoverflow">
                <p class="pagetext 1">{$field->prompt}:</p>
                <p class="pageinput">
                {if !empty($field->help)}({$field->help})<br />{/if}
                {$field->field}
                {if isset($field->filename)}<br />
                    <br />
                    {$field->delete_file} {$field->filename}<br />
                {/if}
                </p>
            </div>
        {else}
            <div class="pageoverflow">
                <p class="pagetext">{$field->prompt}</p>
            </div>
        {/if}
    {/foreach}
{/if}

{if $module->GetPreference('item_category_edit', 0) || isset($custom_fielddef) || $module->GetPreference('filter_date') || $module->GetPreference('mode') != "simple"}
    <div class="pageoverflow">
        <p class="pagetext">&nbsp;</p>
        <p class="pageinput"><input type="submit" name="{$module->GetActionId()}submit" value="{$module->Lang('submit')}"></p>
    </div>
{/if}

</fieldset>
{$formend}
{/if}
</div>

<br style="clear:both;" />
<div class="pageoptions">
    <p class="pageoptions">{$addlink}</p>
</div>

{if $itemcount > 0}
    <div id="productlist">
        <script type="text/javascript">
            {literal}
            $(document).ready(function(){
                $('#select_all_items').click(function(){
                    var checked = $(this).attr('checked');
                    $('.multiselect').attr('checked',checked == undefined ? false : checked);
                });
                $('#bulkaction_submit').click(function(){
                    var len = $('#itemlist tbody input:checkbox:checked').length;
                    if( len == 0 )
                    {
                        alert('{/literal}{$module->Lang('nothing_selected')}{literal}');
                        return false;
                    }
                    return true;
                });
            });
            {/literal}
        </script>
        {$formstart2}
            {if isset($firstpage_url)}
                <a href="{$firstpage_url}" title="{$module->Lang('firstpage')}">{$module->Lang('firstpage')}</a>
                <a href="{$prevpage_url}" title="{$module->Lang('prevpage')}">{$module->Lang('prevpage')}</a>
            {/if}
            {if isset($firstpage_url) || isset($lastpage_url)}
                {$module->Lang('page_of',$pagenumber,$pagecount)}
            {/if}
            {if isset($lastpage_url)}
                <a href="{$nextpage_url}" title="{$module->Lang('nextpage')}">{$module->Lang('nextpage')}</a>
                <a href="{$lastpage_url}" title="{$module->Lang('lastpage')}">{$module->Lang('lastpage')}</a>
            {/if}


            <table cellspacing="0" class="pagetable cms_sortable tablesorter" id="itemlist">
                <thead>
                    <tr>
                        <th>{$idtext}</th>
                        {if $module->GetPreference('item_title_edit')}
                            <th>{$module->GetPreference('item_title')}</th>
                        {/if}
                        {if $module->GetPreference('item_date_edit', 0)}
                            <th>{$module->Lang('date')}</th>
                        {/if}
                        {if $module->GetPreference('item_date_end_edit', 0)}
                            <th>{$module->Lang('date_end')}</th>
                        {/if}
                        {if $module->GetPreference('item_category_edit', 0)}
                            <th>{$module->Lang('category')}</th>
                        {/if}
                        {if isset($custom_fields)}
                            {foreach from=$custom_fields item='fid'}
                                <th>{$fields_viewable.$fid}</th>
                            {/foreach}
                        {/if}
                        {if $mod->GetPreference('item_featured_edit')}
                            <th class="pageicon">{$module->Lang('featured')}</th>
                        {/if}
                        <th class="pageicon">&nbsp;</th>
                        <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                        <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                        <th class="pageicon {literal}{sorter: false}{/literal}">
                            <input type="checkbox" title="{$module->Lang('select_all')}" value="1" name="select_all" id="select_all_items">
                        </th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$items item=entry}
                    {cycle values="row1,row2" assign='rowclass'}
                    {*<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">*}
                    <tr id="{$entry->item_id}">
                        <td>{$entry->item_id}</td>
                        {if $module->GetPreference('item_title_edit')}
                            <td>
                                <a href="{$entry->edit_url}" title="{$module->Lang('edit')}">{$entry->title}</a>
                            </td>
                        {/if}
                        {if $module->GetPreference('item_date_edit', 0)}
                            <td>{$entry->item_date|cms_date_format}</td>
                        {/if}
                        {if $module->GetPreference('item_date_end_edit', 0)}
                            <td>{$entry->item_date_end|cms_date_format}</td>
                        {/if}
                        {if $module->GetPreference('item_category_edit', 0)}
                            <td>{$entry->long_name}</td>
                        {/if}
                        {if isset($custom_fields)}
                            {foreach from=$custom_fields item='fid'}
                                <td>
                                    {capture assign='tmp'}{literal}{{/literal}$entry->Fld__{$field_names.$fid}{literal}}{/literal}{/capture}
                                    {eval var=$tmp assign="tmpres"}
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
                        {if $mod->GetPreference('item_featured_edit')}
                            <td>{$entry->featured}</td>
                        {/if}
                        <td>{$entry->approve}</td>
                        <td>{$entry->editlink}</td>
                        <td>{$entry->deletelink}</td>
                        <td>
                            <input type="checkbox" class="multiselect" name="{$oldactionid}multiselect[]" value="{$entry->item_id}">
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>

            <div class="pageoptions">
                <p class="pageoptions">{$addlink}</p>
            </div>
            {if $itemcount gt 0}
                <div style="text-align: right; width: 40%; float: right; margin-top: 0.5em;">
                    {$module->Lang('with_selected')}:
                    <select name="{$oldactionid}bulkaction">{html_options options=$bulkactions}</select>
                    <input type="submit" id="bulkaction_submit" name="{$oldactionid}submit" value="{$module->Lang('go')}"/>
                </div>
            {/if}
        {$formend2}
    </div>
{/if}

{if $module->GetPreference('mode') == "simple"}
    <div id="sort_items">{$module->CreateFormStart($oldactionid, 'admin_moveitem')}
        <input id="serialdata" type="hidden" name="{$oldactionid}serialdata" value=""/>
        <div class="pageoverflow">
            <p class="pagetext"></p>
            <p class="pageinput">
                <input class="save_items" type="submit" name="{$oldactionid}submit" value="{$module->Lang('save_order')}"/>
            </p>
        </div>
        {$module->CreateFormEnd()}
    </div>
{/if}

<br style="clear:both" />