{if $count > 0}
<ul>
{foreach from=$cats item=node}
{if $node.depth > $node.prevdepth}
{repeat string="<ul>" times=$node.depth-$node.prevdepth}
{elseif $node.depth < $node.prevdepth}
{repeat string="</li></ul>" times=$node.prevdepth-$node.depth}
</li>
{elseif $node.index > 0}</li>
{/if}
<li>
{if $node.count > 0}
	<a href="{$node.url}">{$node.category_name}</a> ({$node.count}){else}<span>{$node.category_name} (0)</span>{/if}
{/foreach}
{repeat string="</li></ul>" times=$node.depth-1}</li>
</ul>
{/if}