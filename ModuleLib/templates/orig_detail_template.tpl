<div class="item">
    {*values*}
    <h2 class="item-title">{$item->title}</h2>
    <div class="item-category">Category: {$item->category_name}</div>
    <p>
        Date: {$item->item_date}
    </p>
    <p>
        Date end: {$item->item_date_end}
    </p>
    {*custom fields*}
    {if !empty($item->fielddefs)}
        <div class="item-properties">
            {foreach from=$item->fielddefs item=fielddef}
                {if $fielddef.type == 'upload_file' || $fielddef.type == 'select_file'}
                    {$fielddef.name}: <a href="{$item->file_location}/{$fielddef.value}">{$fielddef.value}</a><br />
                    {*CGSmartImage src=$item->file_location|cat:'/':$fielddef.value quality=90 filter_resize='w,500' alt=$item->title*}
                {elseif $fielddef.value|is_array}
                    {$fielddef.name}: {$fielddef.value|print_r}<br />
                {else}
                    {$fielddef.name}: {$fielddef.value}<br />
                {/if}
            {/foreach}
        </div>
    {/if}

    {*gallery*}
    {if $item->images|@count}
        <ul>
            {foreach from=$item->images item='image'}
                <li>
                    <img src="{$image.file_location}/{$image.filename}" alt="" />
                    {*CGSmartImage src=$image['file_location']|cat:'/':$image['filename'] quality=90 filter_resize='w,500' alt=''*}
                </li>
            {/foreach}
        </ul>
    {/if}
</div><!-- item -->
