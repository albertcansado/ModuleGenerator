{foreach from=$items item=item}
<h2>{$item->title}</h2>
<p>{$item->item_date|cms_date_format}</p>
{if $item->category_name}<p>{$item->category_name}<p>{/if}
{if !empty($item->fields)}
    <div class="item-properties">
    {foreach from=$item->fields item=fielddef}
        {if $fielddef.type == 'upload_file' || $fielddef.type == 'select_file'}
            {$fielddef.name}: <a href="{$item->file_location}/{$fielddef.value}">{$fielddef.value}</a><br />
        {else}
            {$fielddef.name}: {$fielddef.value}<br />
        {/if}
    {/foreach}
    </div>
{/if}
<a href="{$item->link}">{$item->link}</a>
{/foreach}

{if isset($pagecount) && $pagecount gt 1}
<p>
        {if $prevurl}<a href="{$prevurl}" class="prev">«</a>&nbsp;{/if}
        <em>{$pagenumber} / {$pagecount}</em>
        {if $nexturl}<a href="{$nexturl}" class="next">»</a>&nbsp;{/if}
    </p>
{/if}