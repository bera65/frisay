<link rel="stylesheet" href="{$domain}modules/blog/assets/css/front.css?v={$smarty.now}">

<section class="blog-page">
	<div class="container py-4">
		<header class="blog-page__head mb-4">
			{if $blogCategory}
			<p class="small text-muted mb-1"><a href="{$domain}blog">Blog</a> / Kategori</p>
			<h1 class="h3 mb-1">{$blogCategory.name|escape}</h1>
			{if $blogCategory.description}
			<p class="text-muted mb-0">{$blogCategory.description|escape}</p>
			{/if}
			{else}
			<h1 class="h3 mb-1">Blog</h1>
			<p class="text-muted mb-0">Haberler, ipuçları ve mağaza yazıları</p>
			{/if}
		</header>

		{if $blogCategories|@count}
		<nav class="blog-cats mb-4" aria-label="Blog kategorileri">
			<a href="{$domain}blog" class="blog-cats__item{if !$blogCategory} is-active{/if}">Tümü</a>
			{foreach $blogCategories as $cat}
			<a href="{$cat.url|escape}" class="blog-cats__item{if $blogCategory && $blogCategory.id_blog_category == $cat.id_blog_category} is-active{/if}">{$cat.name|escape}</a>
			{/foreach}
		</nav>
		{/if}

		{if $blogPosts|@count}
		<div class="row g-4">
			{foreach $blogPosts as $post}
			<div class="col-md-6 col-lg-4">
				<article class="blog-card h-100">
					{if $post.cover_url}
					<a href="{$post.url|escape}" class="blog-card__media">
						<img src="{$post.cover_url|escape}" alt="{$post.title|escape}" loading="lazy">
					</a>
					{/if}
					<div class="blog-card__body">
						<p class="blog-card__meta small text-muted mb-1">
							{if $post.category_name}
							<a href="{$post.category_url|escape}">{$post.category_name|escape}</a> ·
							{/if}
							{$post.date_formatted|escape}
						</p>
						<h2 class="h6 mb-2"><a href="{$post.url|escape}">{$post.title|escape}</a></h2>
						<p class="small text-muted mb-3">{$post.excerpt|escape}</p>
						<a href="{$post.url|escape}" class="stretched-link small">Devamını oku</a>
					</div>
				</article>
			</div>
			{/foreach}
		</div>
		{else}
		<p class="text-muted">{if $blogCategory}Bu kategoride henüz yazı yok.{else}Henüz yayınlanmış yazı yok.{/if}</p>
		{/if}
	</div>
</section>
