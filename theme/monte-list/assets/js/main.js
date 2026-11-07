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
	});

	// ---------- 絞り込み ----------
	const dialog = document.querySelector('[data-filter-dialog]');
	const form = dialog?.querySelector('[data-filter-form]');

	const syncAllChips = () => {
		form.querySelectorAll('[data-chip-group]').forEach((group) => {
			const any = group.querySelector('input:checked');
			group.querySelector('[data-chip-all]')?.classList.toggle('is-active', !any);
		});
	};

	const cancel = () => {
		form.reset(); // 開く前（表示中の条件）の状態に戻す
		syncAllChips();
		dialog.close();
	};

	if (dialog && form) {
		syncAllChips();

		document.querySelector('[data-filter-open]')?.addEventListener('click', () => dialog.showModal());

		dialog.addEventListener('click', (e) => {
			if (e.target === dialog) return cancel(); // 背景クリック
			if (e.target.closest('[data-filter-cancel]')) return cancel();

			const all = e.target.closest('[data-chip-all]');
			if (all) {
				all.closest('[data-chip-group]').querySelectorAll('input').forEach((i) => (i.checked = false));
				syncAllChips();
				return;
			}

			const more = e.target.closest('[data-chip-more]');
			if (more) {
				const list = form.querySelector('[data-chip-more-list]');
				list.hidden = !list.hidden;
				more.setAttribute('aria-expanded', String(!list.hidden));
				return;
			}

			if (e.target.closest('[data-filter-clear]')) {
				form.querySelectorAll('input[type=checkbox]').forEach((i) => (i.checked = false));
				const videoAll = form.querySelector('input[name=video][value=""]');
				if (videoAll) videoAll.checked = true;
				syncAllChips();
			}
		});

		form.addEventListener('change', syncAllChips);

		// Esc で閉じたときもキャンセル扱い
		dialog.addEventListener('cancel', (e) => {
			e.preventDefault();
			cancel();
		});

		// 「すべて」（空の値）は URL に載せない
		form.addEventListener('submit', () => {
			form.querySelectorAll('input[name=video]').forEach((i) => {
				if (i.checked && !i.value) i.disabled = true;
			});
		});
	}

	// 別タブで保存した内容も反映する
	window.addEventListener('storage', (e) => {
		if (e.key === KEY) render();
	});

	render();
	if (savedList) loadSaved();
})();
