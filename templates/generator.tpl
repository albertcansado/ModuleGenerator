{if $itemcount > 0}
    <table cellspacing="0" class="pagetable cms_sortable tablesorter">
        <thead>
            <tr>
                <th>{$mod->Lang('id')}</th>
                <th>{$mod->Lang('name')}</th>
                <th>{$mod->Lang('attach')}</th>
                <th>{$mod->Lang('createdate')}</th>
                <th>{$mod->Lang('modifieddate')}</th>

                <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
                <th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$items item=entry}
                {cycle values="row1,row2" assign='rowclass'}
                {*<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">*}
                <tr>
                    <td>{$entry->id}</td>
                    <td>
                {if isset($entry->modulelink) && $entry->modulelink}{$entry->modulelink}{else}<strong>{$entry->module_name}</strong>{/if}
            </td>

                    <td>{$entry->attachmodules}</td>
                    <td>{$entry->create_date|cms_date_format}</td>
                    <td>{$entry->modified_date|cms_date_format}</td>
                    <td>{$entry->editlink}</td>
                    <td>{$entry->regeneratelink}</td>
                    <td>{if isset($entry->modulelink) && $entry->modulelink}{else}{$entry->deletelink}{/if}</td>
                    <td>
                        {if isset($entry->installlink)}
                            {$entry->installlink}
                        {elseif isset($entry->uninstalllink)}
                            {$entry->uninstalllink}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/if} 
