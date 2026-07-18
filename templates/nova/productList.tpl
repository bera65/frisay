{assign var="listProducts" value=$products|default:[]}
{if $listProducts|@count == 0 && isset($product)}
	{assign var="listProducts" value=$product}
{/if}
{if $listProducts|@count > 0}
<div class="nova-hscroll" id="{$id|default:'novaProductList'|escape}">
	{foreach $listProducts as $p}
		{include file='./plugin/productCard.tpl' p=$p}
	{/foreach}
</div>
{/if}
