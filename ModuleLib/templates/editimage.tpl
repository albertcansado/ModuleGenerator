{assign var="module" value=$mod}




{$startform}

{$module->StartTabHeaders()}
{$module->SetTabHeader('image', $module->Lang('image'))}

{if isset($custom_fielddef)}
    {foreach from=$custom_fielddef item='field'}
        {if $field->type == 'tab'}
            {$module->SetTabHeader($field->alias, $field->label)}
        {/if}
    {/foreach}
{/if}
{$module->EndTabHeaders()}

{$module->StartTabContent()}
{$module->StartTab('category')}

{if isset($imagesrc) && $imagesrc}
    {if $image_size_admin_width && $image_size_admin_height}
        {cms_module module="CGSmartImage" src=$imagesrc alt='' filter_croptofit="`$image_size_admin_width`,`$image_size_admin_height`" style="margin:5px 0;"}
    {elseif $image_size_admin_width}
        {cms_module module='CGSmartImage' src=$imagesrc alt='' filter_resize="w,`$image_size_admin_width`" style="margin:5px 0;"}
    {elseif $image_size_admin_height}
        {cms_module module="CGSmartImage" src=$imagesrc alt='' filter_resize="h,`$image_size_admin_height`" style="margin:5px 0;"}
    {/if}
    {assign var='imagesize' value=$imagesrc|@getimagesize}
    <br />
    <strong>{$module->GetPreference('item_singular')}</strong>: {$item.title} <br />
    <strong>{$module->Lang('image_orig_size')}</strong>: {$imagesize.0}x{$imagesize.1}px
{/if}

{if isset($custom_fielddef)}
    {foreach from=$custom_fielddef item='field'}
        {if $field->type == 'tab'}
            {$module->EndTab()}
            {$module->StartTab($field->alias)}
        {elseif (!isset($item_id) && !$field->editview) || isset($item_id)}
            {if $field->field}
                <div class="pageoverflow">
                    <p class="pagetext">{$field->prompt}:</p>
                    <p class="pageinput">
                    {if !empty($field->help)}({$field->help})<br />{/if}
                    {$field->field}
                    {if isset($field->filename)}<br />
                    {capture assign="src"}{$field->file_location}/{$field->filename}{/capture}
                {capture assign="srcpath"}{$field->filepath_location}/{$field->filename}{/capture}
                {if $field->is_image}
                    {if $image_size_admin_width && $image_size_admin_height}
                        {cms_module module="CGSmartImage" src=$srcpath|replace:"`$root_url`":'' alt=$file->filename filter_croptofit="`$image_size_admin_width`,`$image_size_admin_height`" style="margin:5px 0;"}
                    {elseif $image_size_admin_width}
                        {cms_module module="CGSmartImage" src=$srcpath|replace:"`$root_url`":'' alt=$file->filename filter_resize="w,`$image_size_admin_width`" style="margin:5px 0;"}
                    {elseif $image_size_admin_height}
                        {cms_module module="CGSmartImage" src=$srcpath|replace:"`$root_url`":'' alt=$file->filename filter_resize="h,`$image_size_admin_height`" style="margin:5px 0;"}
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
{$module->EndTab()}

<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$submit}{$hidden}{$cancel}</p>
</div>
{$endform}

