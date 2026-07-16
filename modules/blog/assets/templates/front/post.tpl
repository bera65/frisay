<link rel="stylesheet" href="{$domain}modules/blog/assets/css/front.css?v={$smarty.now}">

<section class="blog-read">
	<div class="container py-4">
		<div class="row g-4">
			<div class="col-lg-8 col-xl-9">
				<article class="blog-box blog-read__article">
					{if $blogPost.cover_url}
					<div class="blog-read__cover">
						<img src="{$blogPost.cover_url|escape}" alt="{$blogPost.title|escape}">
					</div>
					{/if}

					<p class="blog-read__eyebrow">
						<a href="{$domain}blog">Blog</a>
						{if $blogPost.category_name && $blogPost.category_url}
						<span aria-hidden="true"> / </span>
						<a href="{$blogPost.category_url|escape}">{$blogPost.category_name|escape}</a>
						{/if}
						{if $blogPost.date_formatted}
						<span aria-hidden="true"> · </span>
						<span>{$blogPost.date_formatted|escape}</span>
						{/if}
					</p>

					<h1 class="blog-read__title">{$blogPost.title|escape}</h1>

					{if $blogPost.excerpt}
					<p class="blog-read__excerpt">{$blogPost.excerpt|escape}</p>
					{/if}

					<div class="blog-read__content blog-post__content">
						{$blogPost.content nofilter}
					</div>
				</article>
			</div>

			<aside class="col-lg-4 col-xl-3">
				<div class="blog-sidebar">
					<div class="blog-box blog-box--side">
						<h2 class="blog-sidebar__title">Blog Kategorileri</h2>
						{if $blogCategories|@count}
						<ul class="blog-sidebar__list">
							{foreach $blogCategories as $cat}
							<li>
								<a href="{$cat.url|escape}"{if $blogPost.id_blog_category|default:0 == $cat.id_blog_category} class="is-active"{/if}>{$cat.name|escape}</a>
							</li>
							{/foreach}
						</ul>
						{else}
						<p class="blog-sidebar__empty">Henüz kategori yok.</p>
						{/if}
						<p class="blog-sidebar__all">
							<a href="{$domain}blog">Tüm yazılar</a>
						</p>
					</div>

					<div class="blog-box blog-box--side">
						<h2 class="blog-sidebar__title">Son Konular</h2>
						{if $blogRecentPosts|@count}
						<ul class="blog-recent">
							{foreach $blogRecentPosts as $recent}
							<li>
								<a href="{$recent.url|escape}" class="blog-recent__link">
									{if $recent.cover_url}
									<span class="blog-recent__thumb">
										<img src="{$recent.cover_url|escape}" alt="">
									</span>
									{/if}
									<span class="blog-recent__body">
										<span class="blog-recent__title">{$recent.title|escape}</span>
										{if $recent.date_formatted}
										<span class="blog-recent__date">{$recent.date_formatted|escape}</span>
										{/if}
									</span>
								</a>
							</li>
							{/foreach}
						</ul>
						{else}
						<p class="blog-sidebar__empty">Henüz yazı yok.</p>
						{/if}
					</div>
				</div>
			</aside>
		</div>
	</div>
</section>
