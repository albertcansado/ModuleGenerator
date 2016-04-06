{assign var="tmpmod" value=$mod}
{assign var="tmpactionid" value=$actionid}

{$startform}

<h3>{$mod->Lang('search')}</h3>
<div class="pageoverflow">
    <p class="pagetext">{$prompt_searchable}:</p>
    <p class="pageinput">{$input_searchable}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$prompt_search_date_end}:</p>
    <p class="pageinput">{$input_search_date_end}</p>
</div>
    <h3>{$tmpmod->Lang('searchable_fields')}</h3>
{foreach from=$search_custom_fields key='prompt' item='item'}
    <div class="pageoverflow">
        <p class="pagetext">{$prompt}:</p>
        <p class="pageinput">{$item}</p>
    </div>
{/foreach}
{if $searchable}
    <fieldset>
        <h3>{$mod->Lang('reindex')}</h3>
        <div class="pageoverflow">
            <p class="pageinput">
                <a href="{$searchable_link}">{$mod->Lang('start')}</a>
            </p>
        </div>
    </fieldset>
{/if}
<input type="hidden" name="{$tmpactionid}tab" value="optionsearchtab" />
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$submit}</p>
</div>

{$endform}
