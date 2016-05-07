<div class="pagecontainer">
    <h3>{$mod->Lang('bulkcategory_title')}</h3>
    {*$mod->Lang('KeyValue')*}
    {$formstart}
        <div class="hidden">
            {foreach from=$multiselect item='val'}
                <input type="hidden" name="{$actionid}multiselect[]" value="{$val}" />
            {/foreach}
            {$input_bulkaction}
            {$input_action}
        </div>

        <div class="pageoverflow">
            <p class="pagetext">{$mod->Lang('items')}:</p>
            <p class="pageinput">
                <ul>
                    {foreach from=$items item='item'}
                        <li>{$item.title}</li>
                    {/foreach}
                </ul>
            </p>
        </div>

        <div class="pageoverflow">
            <p class="pagetext">{$prompt_category}:</p>
            <p class="pageinput">{$input_category}</p>
        </div>

        <div class="pageoverflow">
            <p class="pagetext">&nbsp;</p>
            <p class="pageinput">{$submit}{$cancel}</p>
        </div>
    {$formend}
</div>
