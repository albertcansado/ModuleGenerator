<div class="filter">                    
    {$formstart}
    <fieldset>
           {if isset($custom_fielddef)}
        {foreach from=$custom_fielddef item='field'}
            {if $field->field}
                <div class="pageoverflow">
                    <p class="pagetext">{$field->prompt}:</p>
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
    </fieldset>
    {$input_submit}
    {$formend}
       
</div>

{if isset($results)}
{$results}
{/if}