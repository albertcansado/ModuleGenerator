{if $itemcount > 0}
<table cellspacing="0" class="pagetable">
    <thead>
        <tr>
            <th>{$title}</th>
            <th>{$alias}</th>
            <th class="pageicon">&nbsp;</th>
            <th class="pageicon">&nbsp;</th>
            <th class="pageicon">&nbsp;</th>
            <th class="pageicon">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$items item=entry}
        <tr class="{$entry.rowclass}" onmouseover="this.className='{$entry.rowclass}hover';" onmouseout="this.className='{$entry.rowclass}';">
            <td>{$entry.link}</td>
            <td>{$entry.alias}</td>
            <td>{$entry.uplink}</td>
            <td>{$entry.downlink}</td>
            <td>{$entry.editlink}</td>
            <td>{$entry.delete}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}

<div class="pageoptions">{$addlink}</div>
