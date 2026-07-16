{foreach $hookMenuItems as $item}
<a href="{$item.url|escape}" class="menu-item {if $pageName == $item.slug}active{/if}"{if $item.target} target="{$item.target|escape}" rel="noopener"{/if}>
	<span class="menu-item__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><path d="M14 4h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2v-1"/><path d="M7 15h8"/></svg></span>
	<span class="menu-item__label">{$item.label|adminT|escape}</span>
	{if $item.badge > 0}<span class="nav-badge">{$item.badge}</span>{/if}
</a>
{/foreach}
