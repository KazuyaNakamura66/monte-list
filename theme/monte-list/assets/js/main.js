/**
 * ブックマーク（ブラウザに保存）、保存した活動ページ、絞り込みダイアログ。
 */
(() => {
	const KEY = 'monte-bookmarks';

	const load = () => {
		try {
			const ids = JSON.parse(localStorage.getItem(KEY) || '[]');
			return Array.isArray(ids) ? ids.map(String) : [];
		} catch {
			return [];
		}
	};

	const save = (ids) => {
		try {
			localStorage.setItem(KEY, JSON.stringify(ids));
		} catch {
			// 保存できない環境（プライベートモード等）では何もしない
		}
	};

	const render = () => {
		const ids = load();
		document.querySelectorAll('[data-bookmark]').forEach((btn) => {
			const on = ids.includes(btn.dataset.bookmark);
			btn.setAttribute('aria-pressed', String(on));
			btn.setAttribute('aria-label', on ? '保存を解除する' : '保存する');
		});
		document.querySelectorAll('[data-saved-count]').forEach((el) => {
			el.textContent = String(ids.length);
			el.hidden = ids.length === 0;
		});
	};

	// ---------- 保存した活動ページ ----------
	const savedList = document.querySelector('[data-saved-list]');
	const savedEmpty = document.querySelector('[data-saved-empty]');

	const toggleEmpty = () => {
		if (savedList && savedEmpty) {
			savedEmpty.hidden = savedList.children.length > 0;
		}
	};

	const loadSaved = async () => {
		const ids = load();
		if (!ids.length) {
			toggleEmpty();
			return;
		}
		try {
			const url = new URL(savedList.dataset.endpoint);
			url.searchParams.set('ids', ids.join(','));
			const res = await fetch(url);
			const data = await res.json();
			savedList.innerHTML = data.html || '';
			// 削除済みの記事は保存リストからも外す
			if (Array.isArray(data.ids)) {
				save(ids.filter((id) => data.ids.includes(id)));
			}
		} catch {
			savedList.innerHTML = '';
		}
		render();
		toggleEmpty();
	};

	// ---------- クリック ----------
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('[data-bookmark]');
		if (btn) {
			const id = btn.dataset.bookmark;
			const ids = load();
			const removing = ids.includes(id);
			save(removing ? ids.filter((x) => x !== id) : [...ids, id]);
			if (removing && savedList?.contains(btn)) {
				btn.closest('.card')?.remove();
				toggleEmpty();
			}
			render();
			return;
		}
		if (e.target.closest('[data-filter-open]')) {
			document.querySelector('[data-filter-dialog]')?.showModal();
		}
	});

	// 別タブで保存した内容も反映する
	window.addEventListener('storage', (e) => {
		if (e.key === KEY) render();
	});

	render();
	if (savedList) loadSaved();
})();
