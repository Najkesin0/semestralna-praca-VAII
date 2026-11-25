/* =====================================================================
   SmartRecipe Hub – klientske správanie (bez externých knižníc okrem Bootstrapu)
   ===================================================================== */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initFlash();
    initConfirmForms();
    initAutosubmit();
    initFavorites();
    initDetailTools();
    initServingsStepper();
    initPlanPicker();
    initShoppingList();
    initRecipeForm();
});

/* ---------------------------------------------------------------- pomocné */

function showToast(message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.remove('show'), 2400);
}

/** 0.5 → „0,5“, 200 → „200“ */
function formatAmount(value) {
    const rounded = Math.round(value * 100) / 100;
    return String(rounded).replace('.', ',');
}

/** Slovenské skloňovanie podľa počtu */
function plural(n, one, few, many) {
    if (n === 1) return one;
    return n >= 2 && n <= 4 ? few : many;
}

function storageGet(key, fallback) {
    try {
        const raw = localStorage.getItem(key);
        return raw ? JSON.parse(raw) : fallback;
    } catch (e) {
        return fallback;
    }
}

function storageSet(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        /* úložisko nie je dostupné – zmeny platia len do obnovenia stránky */
    }
}

/* ---------------------------------------------------------------- flash správy */

function initFlash() {
    document.querySelectorAll('[data-flash]').forEach(flash => {
        flash.querySelector('[data-flash-close]')?.addEventListener('click', () => flash.remove());
        setTimeout(() => flash.remove(), 7000);
    });
}

/* ---------------------------------------------------------------- potvrdenie pred mazaním */

function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}

/* ---------------------------------------------------------------- filtre: odoslanie pri zmene */

function initAutosubmit() {
    document.querySelectorAll('form[data-autosubmit]').forEach(form => {
        // form.elements obsahuje aj polia mimo formulára pripojené cez atribút form="…"
        [...form.elements].forEach(field => {
            if (field.matches('select, input[type="checkbox"], input[type="radio"]')) {
                field.addEventListener('change', () => form.requestSubmit ? form.requestSubmit() : form.submit());
            }
        });
    });
}

/* ---------------------------------------------------------------- obľúbené (AJAX) */

function initFavorites() {
    document.querySelectorAll('form[data-favorite-form]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('button');
            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();
                if (!data.ok) throw new Error(data.error || 'Chyba');
                updateFavoriteButton(form, data.favorite);
                showToast(data.favorite ? 'Uložené medzi obľúbené' : 'Odobrané z obľúbených');

                // Na stránke Obľúbené kartu po odobratí skryjeme
                if (!data.favorite && document.querySelector('a.active[href*="c=favorite"]')) {
                    const card = form.closest('[data-recipe-card]');
                    if (card) {
                        card.classList.add('is-removing');
                        setTimeout(() => card.remove(), 260);
                    }
                }
            } catch (e) {
                form.submit(); // záložné riešenie – klasické odoslanie formulára
            } finally {
                button.disabled = false;
            }
        });
    });
}

function updateFavoriteButton(form, isFavorite) {
    const button = form.querySelector('button');
    const icon = button.querySelector('.bi');
    button.classList.toggle('is-active', isFavorite);
    button.setAttribute('aria-pressed', String(isFavorite));
    icon.className = 'bi ' + (isFavorite ? 'bi-heart-fill' : 'bi-heart');
    button.classList.remove('pop');
    void button.offsetWidth; // reštart animácie
    button.classList.add('pop');

    const labels = form.dataset.favoriteLabels?.split('|');
    const label = button.querySelector('[data-label]');
    if (labels && label) {
        label.textContent = isFavorite ? labels[1] : labels[0];
    } else {
        button.setAttribute('aria-label', isFavorite ? 'Odobrať z obľúbených' : 'Pridať do obľúbených');
    }
}

/* ---------------------------------------------------------------- detail: tlač a zdieľanie */

function initDetailTools() {
    document.querySelectorAll('[data-print]').forEach(btn => btn.addEventListener('click', () => window.print()));

    document.querySelectorAll('[data-share]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const data = {title: btn.dataset.share, url: window.location.href};
            if (navigator.share) {
                try {
                    await navigator.share(data);
                } catch (e) { /* používateľ zdieľanie zrušil */ }
                return;
            }
            try {
                await navigator.clipboard.writeText(data.url);
                showToast('Odkaz na recept je skopírovaný');
            } catch (e) {
                window.prompt('Skopíruj odkaz na recept:', data.url);
            }
        });
    });
}

/* ---------------------------------------------------------------- detail: prepočet porcií */

function initServingsStepper() {
    const box = document.querySelector('[data-ingredients]');
    if (!box) return;

    const base = parseInt(box.dataset.baseServings, 10) || 1;
    let servings = base;
    const label = box.querySelector('[data-servings-label]');
    const amounts = box.querySelectorAll('[data-amount]');
    const planInput = document.querySelector('[data-servings-input]');

    box.querySelectorAll('[data-step]').forEach(btn => {
        btn.addEventListener('click', () => {
            servings = Math.min(20, Math.max(1, servings + parseInt(btn.dataset.step, 10)));
            label.textContent = servings + ' ' + plural(servings, 'porcia', 'porcie', 'porcií');
            amounts.forEach(el => {
                const value = parseFloat(el.dataset.amount) * servings / base;
                el.textContent = formatAmount(value) + ' ' + el.dataset.unit;
            });
            if (planInput) planInput.value = servings; // predvyplní počet porcií v okne „Pridať do plánu“
        });
    });
}

/* ---------------------------------------------------------------- Môj plán: výber receptu */

function initPlanPicker() {
    const modal = document.getElementById('planPicker');
    if (!modal) return;

    const form = modal.querySelector('[data-plan-picker]');
    const dateSelect = form.querySelector('select[name="date"]');
    const search = form.querySelector('[data-picker-search]');
    const items = [...form.querySelectorAll('.picker-item')];
    const empty = form.querySelector('.picker-empty');
    let filter = 'all';

    // Predvyplnenie dňa a jedla podľa políčka, na ktoré používateľ klikol
    modal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        if (trigger.dataset.date && [...dateSelect.options].some(o => o.value === trigger.dataset.date)) {
            dateSelect.value = trigger.dataset.date;
        }
        const meal = form.querySelector(`input[name="meal_type"][value="${trigger.dataset.meal}"]`);
        if (meal) meal.checked = true;
    });
    modal.addEventListener('shown.bs.modal', () => search.focus());

    const apply = () => {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        items.forEach(item => {
            const matchesText = !q || item.dataset.title.includes(q);
            const matchesFilter = filter === 'all'
                || (filter === 'fav' && item.dataset.fav === '1')
                || (filter === 'mine' && item.dataset.mine === '1');
            item.hidden = !(matchesText && matchesFilter);
            if (!item.hidden) visible++;
        });
        empty.hidden = visible > 0;
    };

    search.addEventListener('input', apply);
    form.querySelectorAll('[data-picker-filter]').forEach(chip => {
        chip.addEventListener('click', () => {
            form.querySelectorAll('[data-picker-filter]').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            filter = chip.dataset.pickerFilter;
            apply();
        });
    });

    form.addEventListener('submit', event => {
        if (!form.querySelector('input[name="recipe_id"]:checked')) {
            event.preventDefault();
            showToast('Vyber recept, ktorý chceš pridať');
        }
    });
}

/* ---------------------------------------------------------------- Môj plán: nákupný zoznam */

function initShoppingList() {
    const box = document.querySelector('[data-shopping]');
    if (!box) return;

    const storageKey = 'smartrecipe-shopping-' + box.dataset.shopping;
    const state = storageGet(storageKey, {checked: {}, custom: []});
    const customGroup = box.querySelector('[data-custom-group]');
    const customList = box.querySelector('[data-custom-list]');

    const save = () => storageSet(storageKey, state);

    const allChecks = () => [...box.querySelectorAll('[data-shop-item]')];

    const updateProgress = () => {
        const checks = allChecks();
        const done = checks.filter(c => c.checked).length;
        const total = checks.length;
        const percent = total ? Math.round(done / total * 100) : 0;
        box.querySelector('[data-shop-done]').textContent = done;
        box.querySelector('[data-shop-total]').textContent = total;
        box.querySelector('[data-shop-percent]').textContent = percent + ' %';
        box.querySelector('[data-shop-bar]').style.width = percent + '%';
    };

    const bindCheck = input => {
        input.checked = !!state.checked[input.dataset.shopItem];
        input.addEventListener('change', () => {
            if (input.checked) {
                state.checked[input.dataset.shopItem] = true;
            } else {
                delete state.checked[input.dataset.shopItem];
            }
            save();
            updateProgress();
        });
    };

    const renderCustom = () => {
        customList.innerHTML = '';
        state.custom.forEach((name, index) => {
            const li = document.createElement('li');
            const label = document.createElement('label');
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.dataset.shopItem = 'custom|' + name;
            const span = document.createElement('span');
            span.className = 'shop-name';
            span.textContent = name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'shop-custom-remove';
            remove.setAttribute('aria-label', 'Odstrániť ' + name);
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.addEventListener('click', event => {
                event.preventDefault();
                state.custom.splice(index, 1);
                delete state.checked['custom|' + name];
                save();
                renderCustom();
            });
            label.append(input, span, remove);
            li.append(label);
            customList.append(li);
            bindCheck(input);
        });
        customGroup.hidden = state.custom.length === 0;
        box.querySelector('[data-custom-count]').textContent =
            state.custom.length + ' ' + plural(state.custom.length, 'položka', 'položky', 'položiek');
        updateProgress();
    };

    box.querySelectorAll('.shop-group:not([data-custom-group]) [data-shop-item]').forEach(bindCheck);
    renderCustom();

    // Pridanie vlastnej položky
    const addForm = box.querySelector('[data-shop-add]');
    const addInput = addForm.querySelector('input');
    const addToggle = addForm.querySelector('[data-shop-add-toggle]');
    addToggle.addEventListener('click', () => {
        if (addInput.hidden) {
            addInput.hidden = false;
            addInput.focus();
            return;
        }
        addForm.requestSubmit();
    });
    addForm.addEventListener('submit', event => {
        event.preventDefault();
        const name = addInput.value.trim();
        if (name && !state.custom.includes(name)) {
            state.custom.push(name);
            save();
            renderCustom();
            showToast('Položka pridaná do zoznamu');
        }
        addInput.value = '';
        addInput.focus();
    });

    // Označiť / zrušiť všetko
    box.querySelectorAll('[data-shop-all]').forEach(btn => {
        btn.addEventListener('click', () => {
            const value = btn.dataset.shopAll === '1';
            allChecks().forEach(input => {
                input.checked = value;
                if (value) state.checked[input.dataset.shopItem] = true;
            });
            if (!value) state.checked = {};
            save();
            updateProgress();
        });
    });

    // Export do textového súboru
    box.querySelector('[data-shop-export]').addEventListener('click', () => {
        const lines = ['Nákupný zoznam – ' + box.dataset.weekLabel, ''];
        box.querySelectorAll('.shop-group').forEach(group => {
            if (group.hidden) return;
            lines.push(group.querySelector('summary span').textContent.trim().toUpperCase());
            group.querySelectorAll('li').forEach(li => {
                const checked = li.querySelector('input').checked;
                const name = li.querySelector('.shop-name').textContent.trim();
                const amount = li.querySelector('.shop-amount')?.textContent.trim() ?? '';
                lines.push(`${checked ? '[x]' : '[ ]'} ${name}${amount ? ' – ' + amount : ''}`);
            });
            lines.push('');
        });
        const blob = new Blob([lines.join('\n')], {type: 'text/plain;charset=utf-8'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'nakupny-zoznam-' + box.dataset.shopping.split('-').slice(1).join('-') + '.txt';
        document.body.append(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(link.href), 1000);
    });

    // Tlač len nákupného zoznamu
    box.querySelector('[data-shop-print]').addEventListener('click', () => {
        box.querySelectorAll('details').forEach(d => d.open = true);
        document.body.classList.add('print-shopping');
        window.print();
    });
    window.addEventListener('afterprint', () => document.body.classList.remove('print-shopping'));
}

/* ---------------------------------------------------------------- formulár receptu */

function initRecipeForm() {
    const form = document.querySelector('[data-recipe-form]');
    if (!form) return;

    // Pridávanie / odoberanie riadkov ingrediencií a krokov
    const renumberSteps = () => {
        form.querySelectorAll('[data-dyn-list="steps"] [data-step-number]').forEach((el, i) => el.textContent = i + 1);
    };
    let counter = 1000;

    form.querySelectorAll('[data-dyn-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.dynAdd;
            const template = document.getElementById('tpl-' + type);
            const list = form.querySelector(`[data-dyn-list="${type}"]`);
            const html = template.innerHTML.replaceAll('__i__', String(counter++));
            list.insertAdjacentHTML('beforeend', html);
            renumberSteps();
            list.lastElementChild.querySelector('input, textarea')?.focus();
        });
    });

    form.addEventListener('click', event => {
        const remove = event.target.closest('[data-dyn-remove]');
        if (!remove) return;
        const row = remove.closest('[data-dyn-row]');
        const list = row.parentElement;
        if (list.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach(el => el.value = '');
        }
        renumberSteps();
    });

    // Náhľad vybranej fotky
    const fileInput = form.querySelector('input[type="file"][name="image"]');
    const preview = form.querySelector('[data-image-preview]');
    fileInput?.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            showToast('Fotka môže mať najviac 5 MB');
            fileInput.value = '';
            return;
        }
        const img = document.createElement('img');
        img.alt = 'Náhľad fotky';
        img.src = URL.createObjectURL(file);
        preview.replaceChildren(img);
    });

    // Vegánske ⇒ aj vegetariánske
    const vegan = form.querySelector('input[name="is_vegan"]');
    const vegetarian = form.querySelector('input[name="is_vegetarian"]');
    vegan?.addEventListener('change', () => {
        if (vegan.checked) vegetarian.checked = true;
    });

    // Jednoduchá kontrola povinných polí pred odoslaním (server kontroluje znova)
    form.addEventListener('submit', event => {
        const missing = [...form.querySelectorAll('[required]')].filter(el => !el.value.trim());
        if (missing.length) {
            event.preventDefault();
            missing.forEach(el => el.classList.add('is-invalid'));
            missing[0].focus();
            showToast('Vyplň povinné polia označené hviezdičkou');
        }
    });
    form.querySelectorAll('[required]').forEach(el => el.addEventListener('input', () => el.classList.remove('is-invalid')));
}
