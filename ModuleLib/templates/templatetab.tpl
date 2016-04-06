{if $itemcount > 0}
<h3>{$summarytemplates}</h3>
<table cellspacing="0" class="pagetable">
    <thead>
        <tr>
            <th>{$header_name}</th>
            <th class="pageicon">&nbsp;</th>
            <th class="pageicon">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$items.summary item=entry}
        <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
            <td>{$entry->link}</td>
            <td>{$entry->edit}</td>
            <td>{$entry->delete}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}

<div class="pageoptions">{$addsummarylink}</div>


{if $itemcount > 0}
<h3>{$detailtemplates}</h3>
<table cellspacing="0" class="pagetable">
    <thead>
        <tr>
            <th>{$header_name}</th>
            <th class="pageicon">&nbsp;</th>
            <th class="pageicon">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$items.detail item=entry}
        <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
            <td>{$entry->link}</td>
            <td>{$entry->edit}</td>
            <td>{$entry->delete}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}

<div class="pageoptions">{$adddetaillink}</div>