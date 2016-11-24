{assign var='tmpmod' value=$mod}
{literal}
    <style type="text/css">
        .scrollable {
            display: none;
            width: 90%;
        }

    </style>

    <script type="text/javascript">
    $(document).ready(function(){

        $('#fieldlist{/literal}{$section}{literal}').tableDnD();
      $('#fields{/literal}{$section}{literal}').submit(function(){
        var data = new Array;
        $('table#fieldlist{/literal}{$section}{literal} tr').each(function(i,tr){
          data.push($(tr).attr('id'));
        });
        data = data.join(',');
        $('#fserialdata{/literal}{$section}{literal}').val(data);
      });
    });

    </script>
{/literal}



{if $itemcount > 0}
    <table cellspacing="0" class="pagetable cms_sortable tablesorter" id="fieldlist{$section}">
        <thead>
            <tr>
                <th></th>
                <th>{$fieldtext}</th>
                <th>{$alias}</th>
                <th>{$typetext}</th>
                <th>{$requiredtext}</th>
                <th>{$editviewtext}</th>
                <th>{$hidenametext}</th>
                <th class="pageicon">&nbsp;</th>
                <th class="pageicon">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {if $items|@count >= 1}
            {foreach from=$items item=entry}
                {cycle values="row1,row2" assign='rowclass'}
                <tr id="{$entry->id}" class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
                    <td>{$entry->id}</td>
                    <td>{$entry->name}</td>
                    <td>{$entry->alias}</td>
                    <td>{$entry->type}</td>
                    <td>{$entry->required|truefalse}</td>
                    <td>{$entry->editview|truefalse}</td>
                    <td>{$entry->hidename|truefalse}</td>
                    <td>{$entry->editlink}</td>
                    <td>{$entry->deletelink}</td>
                </tr>
            {/foreach}
            {/if}
        </tbody>
    </table>

    <div id="fields{$section}">{$tmpmod->CreateFormStart($actionid, 'admin_movefielddef')}
        <input id="fserialdata{$section}" type="hidden" name="{$actionid}serialdata" value=""/>
        <input id="section" type="hidden" name="{$actionid}section" value="{$section}"/>
        <div class="pageoverflow">
            <p class="pagetext"></p>
            <p class="pageinput">
                <input class="save_fields{$section}" type="submit" name="{$actionid}submit" value="{$tmpmod->Lang('save_order')}"/>
            </p>
        </div>
        {$tmpmod->CreateFormEnd()}</div>

{/if}


<div class="pageoptions">{$addlink}</div>
