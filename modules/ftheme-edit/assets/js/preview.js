(function () {
	'use strict';

	if (!document.documentElement.classList.contains('ftheme-customize-active') &&
		!window.location.search.match(/ftheme_customize=/)) {
		return;
	}

	document.documentElement.classList.add('ftheme-customize-active');

	var badge = document.createElement('div');
	badge.className = 'ftheme-customize-badge';
	badge.textContent = 'Canlı düzenleme — metne tıklayın';
	document.body.appendChild(badge);

	var cookieBanner = document.getElementById('cookieBanner');
	if (cookieBanner) {
		cookieBanner.style.display = 'block';
	}

	var siteDomain = '';
	var colorAliases = {};

	function resolveImageUrl(path) {
		if (!path) {
			return 'https://placehold.co/960x360/e2e8f0/64748b?text=Banner';
		}

		if (/^https?:\/\//i.test(path)) {
			return path;
		}

		var base = (siteDomain || '').replace(/\/?$/, '/');

		if (!base) {
			base = window.location.origin + window.location.pathname.replace(/[^/]*$/, '');
		}

		if (path.indexOf('img/') === 0) {
			return base + path;
		}

		return base + 'img/' + path.replace(/^\/+/, '');
	}

	function postParent(message) {
		if (!window.parent || window.parent === window) {
			return;
		}

		window.parent.postMessage(Object.assign({ source: 'ftheme-preview' }, message), '*');
	}

	function escapeAttr(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function getHomeRoot() {
		return document.querySelector('section.page');
	}

	function buildRenderUnits(blocks) {
		var units = [];
		var bannerBuffer = [];

		(blocks || []).filter(function (block) {
			return block.enabled;
		}).forEach(function (block) {
			if (block.type === 'banner') {
				bannerBuffer.push(block);
				return;
			}

			if (bannerBuffer.length) {
				units.push({ type: 'banner_row', banners: bannerBuffer.slice() });
				bannerBuffer = [];
			}

			units.push({ type: 'block', block: block });
		});

		if (bannerBuffer.length) {
			units.push({ type: 'banner_row', banners: bannerBuffer.slice() });
		}

		return units;
	}

	function findSectionForBlock(block) {
		if (!block) {
			return null;
		}

		var el = document.querySelector('[data-ftheme-block="' + block.id + '"]');

		if (el) {
			return el.closest('section') || el;
		}

		if (block.type === 'categories') {
			return document.querySelector('[data-ftheme-block^="' + block.id + '-"]');
		}

		return null;
	}

	function insertAfter(referenceNode, newNode) {
		if (!referenceNode || !referenceNode.parentNode) {
			getHomeRoot().appendChild(newNode);
			return;
		}

		referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
	}

	function insertBlockAtOrder(newSection, block, enabled) {
		var root = getHomeRoot();
		if (!root) {
			return;
		}

		var idx = enabled.findIndex(function (item) {
			return item.id === block.id;
		});
		var anchor = null;

		for (var i = idx - 1; i >= 0; i--) {
			anchor = findSectionForBlock(enabled[i]);
			if (anchor) {
				break;
			}
		}

		if (anchor) {
			insertAfter(anchor, newSection);
			return;
		}

		var first = root.querySelector('section, .fy-container');
		if (first) {
			root.insertBefore(newSection, first);
		} else {
			root.appendChild(newSection);
		}
	}

	function attachBlockToolbar(section) {
		if (!section || section.querySelector('.ftheme-block-toolbar')) {
			return;
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'ftheme-block-toolbar';

		var addBtn = document.createElement('button');
		addBtn.type = 'button';
		addBtn.className = 'ftheme-add-block-btn';
		addBtn.textContent = '+ Bölüm ekle';
		toolbar.appendChild(addBtn);
		section.appendChild(toolbar);
	}

	function updateHtmlSection(section, block) {
		var titleWrap = section.querySelector('.fy-section__head h2');
		var contentWrap = section.querySelector('.ftheme-custom-html');

		if (block.title) {
			if (!titleWrap) {
				var head = section.querySelector('.fy-section__head') || document.createElement('div');
				head.className = 'fy-section__head';
				titleWrap = document.createElement('h2');
				head.appendChild(titleWrap);
				section.querySelector('.fy-container').insertBefore(head, section.querySelector('.fy-container').firstChild);
			}
			titleWrap.textContent = block.title;
		} else if (titleWrap) {
			titleWrap.parentNode.remove();
		}

		if (contentWrap) {
			contentWrap.innerHTML = block.content || '';
		}
	}

	function createHtmlSection(block) {
		var section = document.createElement('section');
		section.className = 'fy-section fy-reveal ftheme-block-wrap';
		section.setAttribute('data-ftheme-block', block.id);
		section.setAttribute('data-ftheme-draft', '1');

		var html = '<div class="fy-container">';
		if (block.title) {
			html += '<div class="fy-section__head"><h2>' + escapeAttr(block.title) + '</h2></div>';
		}
		html += '<div class="ftheme-custom-html">' + (block.content || '') + '</div></div>';
		section.innerHTML = html;
		return section;
	}

	function createBannerRowSection(banners) {
		var section = document.createElement('section');
		section.className = 'fy-section fy-reveal ftheme-block-wrap ftheme-banner-row-section';
		section.setAttribute('data-ftheme-banner-row', '1');
		section.setAttribute('data-ftheme-draft', '1');

		var row = document.createElement('div');
		row.className = 'fy-container';
		var flex = document.createElement('div');
		flex.className = 'ftheme-banner-row';

		banners.forEach(function (banner) {
			var width = parseInt(banner.width, 10) || 100;
			var item = document.createElement('div');
			item.className = 'ftheme-banner-item';
			item.style.flex = '0 0 ' + width + '%';
			item.style.maxWidth = width + '%';
			item.setAttribute('data-ftheme-block', banner.id);

			var image = resolveImageUrl(banner.image || '');
			var label = escapeAttr(banner.label || 'Banner');
			var link = (banner.link || '').trim();

			if (link) {
				item.innerHTML = '<a href="' + escapeAttr(link) + '" class="ftheme-banner-card"><img src="' + escapeAttr(image) + '" alt="' + label + '"></a>';
			} else {
				item.innerHTML = '<div class="ftheme-banner-card"><img src="' + escapeAttr(image) + '" alt="' + label + '"></div>';
			}

			flex.appendChild(item);
		});

		row.appendChild(flex);
		section.appendChild(row);
		return section;
	}

	function syncBlocks(blocks) {
		var enabled = (blocks || []).filter(function (block) {
			return block.enabled;
		});

		enabled.forEach(function (block) {
			if (block.type !== 'html') {
				return;
			}

			var section = document.querySelector('section[data-ftheme-block="' + block.id + '"]');
			if (section) {
				updateHtmlSection(section, block);
				return;
			}

			section = createHtmlSection(block);
			insertBlockAtOrder(section, block, enabled);
			attachBlockToolbar(section);
		});

		document.querySelectorAll('.ftheme-banner-row-section').forEach(function (node) {
			node.remove();
		});

		buildRenderUnits(enabled).forEach(function (unit) {
			if (unit.type !== 'banner_row') {
				return;
			}

			var section = createBannerRowSection(unit.banners);
			insertBlockAtOrder(section, unit.banners[0], enabled);
			attachBlockToolbar(section);
		});

		document.querySelectorAll('[data-ftheme-block]').forEach(function (blockEl) {
			attachBlockToolbar(blockEl.closest('section') || blockEl);
		});
	}

	function applyColors(colors) {
		if (!colors) {
			return;
		}

		var root = document.documentElement;
		Object.keys(colors).forEach(function (key) {
			root.style.setProperty('--' + key, colors[key]);
		});

		Object.keys(colorAliases).forEach(function (key) {
			root.style.setProperty('--' + key, colorAliases[key]);
		});
	}

	function applyCustomCss(css) {
		var el = document.getElementById('ftheme-live-custom-css');
		if (!el) {
			el = document.createElement('style');
			el.id = 'ftheme-live-custom-css';
			document.head.appendChild(el);
		}
		el.textContent = css || '';
	}

	function applyCustomJs(js) {
		var el = document.getElementById('ftheme-live-custom-js');
		if (el) {
			el.remove();
		}

		if (!js || !String(js).trim()) {
			return;
		}

		el = document.createElement('script');
		el.id = 'ftheme-live-custom-js';
		el.textContent = js;
		document.body.appendChild(el);
	}

	function highlightBlock(blockId) {
		document.querySelectorAll('[data-ftheme-block].ftheme-block-highlight').forEach(function (node) {
			node.classList.remove('ftheme-block-highlight');
		});

		if (!blockId) {
			return;
		}

		var el = document.querySelector('[data-ftheme-block="' + blockId + '"]');
		if (el) {
			el.classList.add('ftheme-block-highlight');
			el.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	function updateRegion(region, value) {
		var el = document.querySelector('[data-ftheme="' + region + '"]');
		if (!el) {
			return;
		}

		el.textContent = value;
	}

	function highlightRegion(region) {
		document.querySelectorAll('[data-ftheme].ftheme-highlight').forEach(function (node) {
			node.classList.remove('ftheme-highlight');
		});

		var el = document.querySelector('[data-ftheme="' + region + '"]');
		if (el) {
			el.classList.add('ftheme-highlight');
			el.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	document.addEventListener('click', function (event) {
		var target = event.target;

		if (!(target instanceof Element)) {
			return;
		}

		var regionNode = target.closest('[data-ftheme]');
		if (regionNode) {
			event.preventDefault();
			event.stopPropagation();
			postParent({ type: 'selectRegion', region: regionNode.getAttribute('data-ftheme') });
			return;
		}

		var blockNode = target.closest('[data-ftheme-block]');
		if (blockNode && target.closest('.ftheme-add-block-btn')) {
			event.preventDefault();
			postParent({ type: 'addBlockHere', blockId: blockNode.getAttribute('data-ftheme-block') });
			return;
		}

		if (blockNode && (blockNode.closest('.ftheme-custom-html') || blockNode.closest('.ftheme-banner-item'))) {
			event.preventDefault();
			event.stopPropagation();
			postParent({ type: 'selectCustomBlock', blockId: blockNode.getAttribute('data-ftheme-block') });
		}
	}, true);

	document.querySelectorAll('[data-ftheme-block]').forEach(function (block) {
		attachBlockToolbar(block.closest('section') || block);
	});

	window.addEventListener('message', function (event) {
		var data = event.data || {};
		if (data.source !== 'ftheme-customizer') {
			return;
		}

		if (data.type === 'init') {
			siteDomain = data.domain || siteDomain;
			if (data.colorAliases) {
				colorAliases = data.colorAliases;
			}
			applyColors(data.colors);
			applyCustomCss(data.customCss);
			applyCustomJs(data.customJs);
			if (data.blocks) {
				syncBlocks(data.blocks);
			}
		}

		if (data.type === 'updateColors') {
			applyColors(data.colors);

			if (data.font) {
				document.documentElement.style.setProperty('--font-family', "'" + data.font + "', 'Segoe UI', sans-serif");
				document.documentElement.style.setProperty('--theme-font-family', "'" + data.font + "', 'Segoe UI', sans-serif");
			}
		}

		if (data.type === 'updateCustomCss') {
			applyCustomCss(data.css);
		}

		if (data.type === 'updateSetting') {
			updateRegion(data.region, data.value);
		}

		if (data.type === 'highlight') {
			highlightRegion(data.region);
		}

		if (data.type === 'highlightBlock') {
			highlightBlock(data.blockId);
		}

		if (data.type === 'syncBlocks') {
			if (data.domain) {
				siteDomain = data.domain;
			}
			syncBlocks(data.blocks);
		}
	});
})();
