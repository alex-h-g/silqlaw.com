// FOSR front-end script. Vanilla, deferred, no dependencies.
(() => {
	const toggle = document.querySelector('[data-nav-toggle]');
	const nav = document.querySelector('[data-nav]');
	if (toggle && nav) {
		toggle.addEventListener('click', () => {
			const open = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', String(!open));
			nav.toggleAttribute('data-open', !open);
		});
	}
})();

// Chip filters: buttons [data-filter] inside [data-filters=X] show/hide items in [data-filter-list=X] by data-types.
(() => {
	document.querySelectorAll('[data-filters]').forEach((group) => {
		const list = document.querySelector(`[data-filter-list="${group.dataset.filters}"]`);
		if (!list) return;
		group.addEventListener('click', (e) => {
			const btn = e.target.closest('[data-filter]');
			if (!btn) return;
			group.querySelectorAll('[data-filter]').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
			const f = btn.dataset.filter;
			list.querySelectorAll('[data-types]').forEach((item) => {
				item.hidden = f !== '*' && !item.dataset.types.split(' ').includes(f);
			});
		});
	});
})();
