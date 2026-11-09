// SILQ front-end script. Vanilla, deferred, no dependencies.
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

// Mobile contact bar: "Menu" opens the main navigation; hide the bar while typing so it never covers a form field.
(() => {
	const bar = document.querySelector('[data-mobile-bar]');
	if (!bar) return;
	const btn = bar.querySelector('[data-mobile-menu]');
	const toggle = document.querySelector('[data-nav-toggle]');
	if (btn && toggle) {
		btn.addEventListener('click', () => {
			toggle.click();
			btn.setAttribute('aria-expanded', toggle.getAttribute('aria-expanded'));
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}
	const typing = (e) => e.target.matches && e.target.matches('input:not([type=checkbox]):not([type=radio]):not([type=submit]), textarea, select');
	document.addEventListener('focusin', (e) => { if (typing(e)) bar.dataset.hidden = ''; });
	document.addEventListener('focusout', (e) => { if (typing(e)) delete bar.dataset.hidden; });
})();
