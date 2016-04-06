{assign var="tmpmod" value=$mod}
{assign var="tmpactionid" value=$actionid}

<script type="text/javascript">
    $(document).ready(function(){

    if($('#filter input:checkbox').is(':checked')){
    $('#filter-case').show();
}
$('#filter input:checkbox').click(function(){
if($(this).is(':checked')){
$('#filter-case').show();
}
else
            {
            $('#filter-case').hide();
    }
})
})

</script>

{$startform}
<fieldset>
    <h3>{$mod->Lang('global')}</h3>

    <div class="pageoverflow">
        <p class="pagetext">{$prompt_url_prefix}:</p>
        <p class="pageinput">{$input_url_prefix}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_detail_returnid}:</p>
        <p class="pageinput">{$input_item_detail_returnid}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_category_returnid}:</p>
        <p class="pageinput">{$input_item_category_returnid}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_display_inline}:</p>
        <p class="pageinput">{$input_display_inline}</p>
    </div>
</fieldset>
<fieldset>
    <h3>{$mod->Lang('admin_area')}</h3>

    <div class="pageoverflow">
        <p class="pagetext">{$prompt_has_admin}:</p>
        <p class="pageinput">{$input_has_admin}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_admin_section}:</p>
        <p class="pageinput">{$input_admin_section}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$mod->Lang('images_size_admin')}:</p>
        <p class="pageinput"><input type="text" value="{$mod->GetPreference('images_size_admin')}" name="{$tmpactionid}images_size_admin" />
            <span> *{$mod->Lang('image_size_admin_info')}</span>
        </p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_friendlyname}:</p>
        <p class="pageinput">{$input_friendlyname}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_title}:</p>
        <p class="pageinput">{$input_item_title}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_singular}:</p>
        <p class="pageinput">{$input_item_singular}</p>
    </div>
    <div class="pageoverflow">
        <p class="pagetext">{$prompt_item_plural}:</p>
        <p class="pageinput">{$input_item_plural}</p>
    </div>

    <div class="pageoverflow">
        <p class="pagetext">{$prompt_usergroup_dependence}:</p>
        <p class="pageinput">{$input_usergroup_dependence} *{$tmpmod->Lang('info_usergroup_dependence')}</p>
    </div>

    <div class="pageoverflow">
        <p class="pagetext">{$prompt_custom_fields_default}:</p>
        <p class="pageinput">
            <select name="{$tmpactionid}custom_fields_default[]" size="6" multiple="multiple">
                {html_options options=$fields_viewable|default:'' selected=$custom_fields_default}
            </select>
        </p>
    </div>

    <h3>{$mod->Lang('filter')} <span id="filter">{$input_filter}</span> </h3>    
    <div id="filter-case" style="display:none">
        <div class="pageoverflow">
            <p class="pagetext">{$tmpmod->Lang('categories')}:</p>
            <p class="pageinput">{$input_filter_categories}</p>
        </div>
        <div class="pageoverflow">
            <p class="pagetext">{$tmpmod->Lang('date')}:</p>
            <p class="pageinput">{$input_filter_date}</p>
        </div>
        {foreach from=$filter_custom_fields key='prompt' item='item'}
            <div class="pageoverflow">
                <p class="pagetext">{$prompt}:</p>
                <p class="pageinput">{$item}</p>
            </div>
        {/foreach}
    </div>


    <input type="hidden" name="{$tmpactionid}tab" value="optiontab" />
</fieldset>  
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$submit}</p>
</div>

{$endform}
