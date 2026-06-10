/**
 * Zoeksuggesties (combobox) voor inputs met [data-suggest].
 *
 * - Debounce: pas een request als de gebruiker ~300ms stopt met typen.
 * - AbortController: een nieuwe aanslag annuleert het vorige request.
 * - Toegankelijk: role=combobox/listbox, pijltjes + Enter + Escape.
 * - Thema via data-suggest-theme="dark" (hero) of default licht.
 */

type Suggestion = {
    type: 'stad' | 'kot';
    label: string;
    detail: string;
    url: string;
};

const DEBOUNCE_MS = 300;
const MIN_CHARS = 2;

export function initSearchSuggest(): void {
    document.querySelectorAll<HTMLInputElement>('input[data-suggest]').forEach(setup);
}

function setup(input: HTMLInputElement): void {
    const endpoint = input.dataset.suggestUrl;
    if (!endpoint) return;

    const wrapper = input.closest<HTMLElement>('[data-suggest-anchor]') ?? input.parentElement;
    if (!wrapper) return;
    wrapper.classList.add('kk-suggest-anchor');

    const listId = `kk-suggest-${Math.random().toString(36).slice(2, 8)}`;
    const list = document.createElement('ul');
    list.id = listId;
    list.className = 'kk-suggest';
    if (input.dataset.suggestTheme === 'dark') list.classList.add('kk-suggest--dark');
    list.setAttribute('role', 'listbox');
    list.hidden = true;
    wrapper.appendChild(list);

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', listId);
    input.setAttribute('aria-autocomplete', 'list');
    input.autocomplete = 'off';

    let items: Suggestion[] = [];
    let active = -1;
    let timer = 0;
    let controller: AbortController | null = null;

    const close = (): void => {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };

    const render = (): void => {
        if (!items.length) {
            close();
            return;
        }

        list.innerHTML = '';
        items.forEach((item, i) => {
            const li = document.createElement('li');
            li.id = `${listId}-${i}`;
            li.setAttribute('role', 'option');
            li.className = 'kk-suggest-item';
            li.innerHTML = `
                <span class="kk-suggest-type">${item.type}</span>
                <span class="kk-suggest-label"></span>
                <span class="kk-suggest-detail"></span>`;
            (li.querySelector('.kk-suggest-label') as HTMLElement).textContent = item.label;
            (li.querySelector('.kk-suggest-detail') as HTMLElement).textContent = item.detail;
            // mousedown i.p.v. click: gaat vóór blur, anders sluit de lijst te vroeg
            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                window.location.assign(item.url);
            });
            list.appendChild(li);
        });
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const highlight = (index: number): void => {
        active = index;
        list.querySelectorAll('.kk-suggest-item').forEach((el, i) => {
            el.classList.toggle('is-active', i === active);
        });
        if (active >= 0) input.setAttribute('aria-activedescendant', `${listId}-${active}`);
        else input.removeAttribute('aria-activedescendant');
    };

    const fetchSuggestions = async (q: string): Promise<void> => {
        controller?.abort();
        controller = new AbortController();
        try {
            const res = await fetch(`${endpoint}?q=${encodeURIComponent(q)}`, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return close();
            const data = (await res.json()) as { suggestions: Suggestion[] };
            items = data.suggestions ?? [];
            highlight(-1);
            render();
        } catch (e) {
            if ((e as Error).name !== 'AbortError') close();
        }
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < MIN_CHARS) {
            controller?.abort();
            close();
            return;
        }
        timer = window.setTimeout(() => void fetchSuggestions(q), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', (e) => {
        if (list.hidden) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlight(Math.min(active + 1, items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlight(Math.max(active - 1, -1));
        } else if (e.key === 'Enter' && active >= 0) {
            e.preventDefault();
            window.location.assign(items[active].url);
        } else if (e.key === 'Escape') {
            close();
        }
    });

    input.addEventListener('blur', close);
}
