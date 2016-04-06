{assign var="tmpmod" value=$mod}
{assign var="tmpactionid" value=$actionid}

{$startform}
<fieldset>
    <h3>{$mod->Lang('items')}</h3>

    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('copy_admin')}: </p>
        <p class="pageinput">{$input_copy_admin} *{$mod->Lang('note')}: {$mod->Lang('copy_admin_info')}</p>
    </div>

    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('preview_admin')}:</p>
        <p class="pageinput">{$input_preview_admin}</p>
    </div>
</fieldset>
<fieldset>
    <h3>{$mod->Lang('gallery')}</h3>
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('has_gallery')}:</p>
        <p class="pageinput">{$input_has_gallery}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('gallery_sortorder')}:</p>
        <p class="pageinput">{$input_gallery_sortorder}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_custom_fields_default}:</p>
        <p class="pageinput">
            <select name="{$tmpactionid}custom_fields_gallery_default[]" size="6" multiple="multiple">
                {html_options options=$fields_gallery_viewable|default:'' selected=$custom_fields_gallery_default}
            </select>
        </p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('gallery_in_defaulttemplate')}:</p>
        <p class="pageinput">{$input_gallery_in_defaulttemplate}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('gallery_defaulttemplate_limit')}:</p>
        <p class="pageinput">{$input_gallery_defaulttemplate_limit}</p>
    </div>
</fieldset>


<fieldset>
    <h3>{$mod->Lang('editing')}</h3>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_title_edit}:</p>
        <p class="pageinput">{$input_item_title_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_alias_edit}:</p>
        <p class="pageinput">{$input_item_alias_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_url_edit}:</p>
        <p class="pageinput">{$input_item_url_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_category_edit}:</p>
        <p class="pageinput">{$input_item_category_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_date_edit}:</p>
        <p class="pageinput">{$input_item_date_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_recursive}:</p>
        <p class="pageinput">{$input_recursive}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_date_end_edit}:</p>
        <p class="pageinput">{$input_item_date_end_edit}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_featured_edit}:</p>
        <p class="pageinput">{$input_item_featured_edit}</p>
    </div>
    <input type="hidden" name="{$tmpactionid}tab" value="optioneditingtab" />
</fieldset>
<br />
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$submit}</p>
</div>

{$endform}
