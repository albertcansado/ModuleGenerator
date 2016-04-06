<div class="pagecontainer">
    <script type="text/javascript">

        {literal}
    function parseTree(ul){
            var tags = [];
            ul.children("li").each(function(){
                    var subtree =	$(this).children("ul");
                    if(subtree.size() > 0)
                            tags.push([$(this).attr("id"), parseTree(subtree)]);
                    else
                            tags.push($(this).attr("id"));
            });
            return tags;
    }

    $(document).ready(function(){

    var ajax_url = $('form').attr('action')+"?";
    $('form input:hidden,form input:text,form input:submit:first').each(function(){
    ajax_url+="&"+$(this).attr('name')+"="+$(this).attr('value');
    })

    $('ul.sortable').nestedSortable({	
                                            disableNesting: 'no-nest',
                                                listType: 'ul',
                                            forcePlaceholderSize: true,
                                            handle: 'div',
                                            items: 'li',
                                            opacity: .6,
                                            placeholder: 'placeholder',
                                            tabSize: 25,
                                            tolerance: 'pointer',
                                            toleranceElement: '> div'
                                    });

     
    $(".reorder-pages form").submit(function(){
        var tree = $.toJSON(parseTree($('ul.sortable')));
        var ajax_res = false;
        $.ajax({
          type: 'POST',
          url:  ajax_url,
          data: {data: tree},
          cache: false,
          async: false,
          success: function(res) {
             ajax_res = true;
          }
        });
        return ajax_res;
    });//end save

    });//end ready
        {/literal}
    </script>
    <div class="reorder-pages">
        {$startform}
        <ul class="sortableList sortable">

            {if $itemcount > 0}
                {foreach from=$items item=entry name="categorylist"}

                    {assign var="hierarchycount" value="."|explode:$hierarchy}
                    {assign var="hierarchycurrentcount" value="."|explode:$entry->hierarchy_position}
                    {if $smarty.foreach.categorylist.first==false && $parent_id!=$entry->parent_id && $hierarchycount|@count <=  $hierarchycurrentcount|@count}
                        <ul>
                        {elseif $smarty.foreach.categorylist.first==false && $parent_id!=$entry->parent_id}
                            {math equation="x - y" x=$hierarchycount|@count y=$hierarchycurrentcount|@count assign="tmpdepth"}
                        {repeat string="</li></ul>" times=$tmpdepth}{else}
                        {if $smarty.foreach.categorylist.first==false }</li>{/if}
                    {/if}


                    <li id="category_{$entry->category_id}">
                        <div class="label"><span>&nbsp;</span>{$entry->category_id}:&nbsp;{$entry->category_name}<em>{$entry->hierarchy}</em></div>



                        {assign var="hierarchy" value=$entry->hierarchy_position}
                        {assign var="parent_id" value=$entry->parent_id}
                    {/foreach}
                {/if}
            </li>
        </ul>

        <div class="pagoeverflow">
            <input type="submit" name="{$actionid}submit" class="button save" value="{'submit'|lang}"/>
            <input type="submit" name="{$actionid}cancel" value="{'cancel'|lang}"/>
        </div>
        {$endform}
</div>
</div>
