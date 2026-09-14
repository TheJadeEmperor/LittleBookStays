/**
 * Property Hub — Client-side interactivity
 * Enqueued via wp_enqueue_script with wp_localize_script data
 * Data passed as propHubData object
 */

(function () {
    // Data passed from PHP via wp_localize_script
    const ajaxUrl = propHubData.ajaxUrl;
    const statusNonce = propHubData.statusNonce;
    const fieldNonce = propHubData.fieldNonce;
    const linksNonce = propHubData.linksNonce;
    const STATUS_OPTIONS = propHubData.STATUS_OPTIONS;
    const PANEL_FIELDS = propHubData.PANEL_FIELDS;
    const PK_COLUMNS = propHubData.PK_COLUMNS;
    const ALL_CLEANERS = propHubData.ALL_CLEANERS;
    const ALL_PROPERTIES = propHubData.ALL_PROPERTIES;
    const PANEL_COLUMN_BREAKS = propHubData.PANEL_COLUMN_BREAKS;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // Mirrors lbs_render_generic_cell() in PHP — builds the inner
    // HTML for a cell/value given its column definition + raw value,
    // so the table cell and the panel field always render the same way.
    function renderValueHtml(col, value) {
        value = (value == null) ? '' : String(value).trim();

        if (col.type === 'num') {
            return escapeHtml(value);
        }
        if (value === '') {
            return '<span class="ph-dash">—</span>';
        }
        if (col.type === 'title') {
            return '<span class="ph-title-icon">▤</span>' + escapeHtml(value);
        }
        if (col.type === 'link') {
            if (/^https?:\/\//i.test(value)) {
                const chip = col.chip || 'Open';
                return '<a class="ph-chip" href="' + escapeHtml(value) + '" target="_blank" rel="noopener noreferrer">'
                    + escapeHtml(chip) + ' <span class="ph-chip-arrow">&#8599;</span></a>';
            }
            return escapeHtml(value);
        }
        return escapeHtml(value);
    }

    function buildStatusPillHtml(statusVal) {
        const opt = STATUS_OPTIONS[statusVal] || STATUS_OPTIONS[0];
        return '<span class="ph-dot" style="background:' + opt.dot + ';"></span>' + opt.label;
    }

    // --- Status dropdown: shared builder used by both the table cell
    // and the panel. Saves via the existing lbs_update_prop_status. ---
    function buildStatusDropdown(num, currentStatus, onSaved) {
        const wrap = document.createElement('div');
        wrap.className = 'ph-status-wrap';
        wrap.dataset.num = num;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ph-pill ph-status-trigger';
        btn.dataset.status = currentStatus;
        const opt = STATUS_OPTIONS[currentStatus] || STATUS_OPTIONS[0];
        btn.style.background = opt.bg;
        btn.style.color = opt.fg;
        btn.innerHTML = buildStatusPillHtml(currentStatus);

        const menu = document.createElement('div');
        menu.className = 'ph-status-menu';
        Object.keys(STATUS_OPTIONS).forEach(function (val) {
            const o = STATUS_OPTIONS[val];
            const row = document.createElement('div');
            row.className = 'ph-status-option' + (String(val) === String(currentStatus) ? ' ph-status-selected' : '');
            row.dataset.status = val;
            row.dataset.bg = o.bg;
            row.dataset.fg = o.fg;
            row.dataset.dot = o.dot;
            row.innerHTML = '<span class="ph-status-option-pill" style="background:' + o.bg + ';color:' + o.fg + ';">'
                + '<span class="ph-dot" style="background:' + o.dot + ';"></span>' + escapeHtml(o.label) + '</span>'
                + '<span class="ph-status-check">&#10003;</span>';
            row.addEventListener('click', function (e) {
                e.stopPropagation();
                const newStatus = row.dataset.status;
                const prevStatus = btn.dataset.status;
                if (newStatus === prevStatus) {
                    closeAllStatusMenus();
                    return;
                }

                const prevHtml = btn.innerHTML;
                const prevBg = btn.style.background;
                const prevFg = btn.style.color;

                btn.dataset.status = newStatus;
                btn.style.background = row.dataset.bg;
                btn.style.color = row.dataset.fg;
                btn.innerHTML = buildStatusPillHtml(newStatus);
                menu.querySelectorAll('.ph-status-option').forEach(function (o2) {
                    o2.classList.toggle('ph-status-selected', o2 === row);
                });
                closeAllStatusMenus();

                const body = new URLSearchParams();
                body.append('action', 'lbs_update_prop_status');
                body.append('nonce', statusNonce);
                body.append('num', num);
                body.append('status', newStatus);

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) {
                        btn.dataset.status = prevStatus;
                        btn.style.background = prevBg;
                        btn.style.color = prevFg;
                        btn.innerHTML = prevHtml;
                        alert('Could not update status: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        return;
                    }
                    if (typeof onSaved === 'function') onSaved(newStatus);
                })
                .catch(function () {
                    btn.dataset.status = prevStatus;
                    btn.style.background = prevBg;
                    btn.style.color = prevFg;
                    btn.innerHTML = prevHtml;
                    alert('Network error - could not save status.');
                });
            });
            menu.appendChild(row);
        });

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = menu.classList.contains('open');
            closeAllStatusMenus();
            if (!isOpen) menu.classList.add('open');
        });

        wrap.appendChild(btn);
        wrap.appendChild(menu);
        return wrap;
    }

    function closeAllStatusMenus() {
        document.querySelectorAll('.ph-status-menu.open').forEach(function (m) {
            m.classList.remove('open');
        });
    }

    // Wire up the status dropdowns rendered server-side in the tables.
    document.querySelectorAll('.ph-table .ph-status-wrap').forEach(function (wrap) {
        const btn = wrap.querySelector('.ph-status-trigger');
        const menu = wrap.querySelector('.ph-status-menu');
        const num = wrap.dataset.num;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = menu.classList.contains('open');
            closeAllStatusMenus();
            if (!isOpen) menu.classList.add('open');
        });

        menu.querySelectorAll('.ph-status-option').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                const newStatus = opt.dataset.status;
                const prevStatus = btn.dataset.status;
                if (newStatus === prevStatus) {
                    closeAllStatusMenus();
                    return;
                }

                const prevHtml = btn.innerHTML;
                const prevStyle = btn.getAttribute('style');

                btn.dataset.status = newStatus;
                btn.setAttribute('style', 'background:' + opt.dataset.bg + ';color:' + opt.dataset.fg + ';');
                btn.innerHTML = buildStatusPillHtml(newStatus);
                menu.querySelectorAll('.ph-status-option').forEach(function (o) {
                    o.classList.toggle('ph-status-selected', o === opt);
                });
                closeAllStatusMenus();

                const body = new URLSearchParams();
                body.append('action', 'lbs_update_prop_status');
                body.append('nonce', statusNonce);
                body.append('num', num);
                body.append('status', newStatus);

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) {
                        btn.dataset.status = prevStatus;
                        btn.setAttribute('style', prevStyle);
                        btn.innerHTML = prevHtml;
                        alert('Could not update status: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        return;
                    }
                    // Keep the panel's own status pill in sync if it's open on this same row.
                    if (panel.classList.contains('open') && panel.dataset.table === 'pm_prop_hub' && panel.dataset.pkValue === num) {
                        const panelStatusBtn = panelBody.querySelector('.ph-status-wrap .ph-status-trigger');
                        if (panelStatusBtn) {
                            panelStatusBtn.dataset.status = newStatus;
                            panelStatusBtn.style.background = opt.dataset.bg;
                            panelStatusBtn.style.color = opt.dataset.fg;
                            panelStatusBtn.innerHTML = buildStatusPillHtml(newStatus);
                        }
                    }
                })
                .catch(function () {
                    btn.dataset.status = prevStatus;
                    btn.setAttribute('style', prevStyle);
                    btn.innerHTML = prevHtml;
                    alert('Network error - could not save status.');
                });
            });
        });
    });

    document.addEventListener('click', closeAllStatusMenus);

    // --- Slide-in detail panel ---
    const overlay = document.getElementById('ph-panel-overlay');
    const panel = document.getElementById('ph-panel');
    const panelTitle = document.getElementById('ph-panel-title');
    const panelBody = document.getElementById('ph-panel-body');
    const panelClose = document.getElementById('ph-panel-close');

    function closePanel() {
        panel.classList.remove('open');
        overlay.classList.remove('open');
        document.body.classList.remove('ph-body-panel-open');
        document.querySelectorAll('.ph-row.ph-row-active').forEach(function (r) {
            r.classList.remove('ph-row-active');
        });
    }

    panelClose.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePanel();
    });

    // Save a text/link/title field via the generic field handler, then
    // sync every matching cell in the row (a key can appear twice,
    // e.g. Property) plus the panel's own value span.
    function saveField(tr, table, pk, pkValue, col, newValue, span, onDone) {
        const body = new URLSearchParams();
        body.append('action', 'lbs_update_row_field');
        body.append('nonce', fieldNonce);
        body.append('table', table);
        body.append('pk', pk);
        body.append('pk_value', pkValue);
        body.append('field', col.key);
        body.append('value', newValue);

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) {
                alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                if (onDone) onDone(false);
                return;
            }
            // Sync every cell for this field in the table row (handles duplicate columns).
            if (tr) {
                const matchingCells = tr.querySelectorAll('.ph-cell[data-field="' + col.key + '"]');
                if (matchingCells.length) {
                    matchingCells.forEach(function (td) {
                        td.dataset.raw = newValue;
                        td.classList.toggle('ph-empty', newValue === '');
                        td.title = newValue;
                        td.innerHTML = renderValueHtml(col, newValue);
                    });
                } else {
                    // Panel-only field (e.g. WiFi) — no <td> to update, so
                    // refresh the row's cached JSON instead.
                    let extra = {};
                    if (tr.dataset.panelExtra) {
                        try { extra = JSON.parse(tr.dataset.panelExtra); } catch (e) { extra = {}; }
                    }
                    extra[col.key] = newValue;
                    tr.dataset.panelExtra = JSON.stringify(extra);
                }
                // If this was the title field, refresh the panel header too.
                if (col.type === 'title' && panel.dataset.table === table && panel.dataset.pkValue === String(pkValue)) {
                    panelTitle.textContent = newValue || '(untitled)';
                }
            }
            if (onDone) onDone(true);
        })
        .catch(function () {
            alert('Network error - could not save change.');
            if (onDone) onDone(false);
        });
    }

    function buildPanelField(tr, table, pk, pkValue, col, rawValue) {
        const field = document.createElement('div');
        field.className = 'ph-panel-field';

        const label = document.createElement('div');
        label.className = 'ph-panel-label';
        label.textContent = col.label;
        field.appendChild(label);

        const valueWrap = document.createElement('div');
        valueWrap.className = 'ph-panel-value';

        if (col.key === pk) {
            valueWrap.classList.add('ph-panel-readonly');
            valueWrap.textContent = rawValue;
            field.appendChild(valueWrap);
            return field;
        }

        if (col.type === 'status') {
            const dropdown = buildStatusDropdown(pkValue, rawValue || 0, function (newStatus) {
                // keep table cell's dropdown in sync
                if (tr) {
                    const tableBtn = tr.querySelector('.ph-cell[data-field="status"] .ph-status-trigger');
                    if (tableBtn) {
                        const opt = STATUS_OPTIONS[newStatus];
                        tableBtn.dataset.status = newStatus;
                        tableBtn.style.background = opt.bg;
                        tableBtn.style.color = opt.fg;
                        tableBtn.innerHTML = buildStatusPillHtml(newStatus);
                    }
                }
            });
            valueWrap.appendChild(dropdown);
            field.appendChild(valueWrap);
            return field;
        }

        // Editable text / link / title field — click to edit, blur to save.
        const span = document.createElement('span');
        span.className = 'ph-panel-field-display';
        span.innerHTML = renderValueHtml(col, rawValue);
        valueWrap.appendChild(span);

        valueWrap.addEventListener('click', function (e) {
            if (e.target.closest('a')) return; // let links open normally
            if (valueWrap.querySelector('.ph-panel-input')) return; // already editing

                const currentSpan = valueWrap.querySelector('.ph-panel-field-display');
                const currentText = currentSpan ? currentSpan.dataset.raw || rawValue : rawValue;

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'ph-panel-input';
                input.value = rawValue;
                valueWrap.innerHTML = '';
                valueWrap.appendChild(input);
                input.focus();
                input.select();

                function finishEdit() {
                    const newValue = input.value.trim();
                    const newSpan = document.createElement('span');
                    newSpan.className = 'ph-panel-field-display';
                    newSpan.dataset.raw = newValue;
                    newSpan.innerHTML = renderValueHtml(col, newValue);
                    valueWrap.innerHTML = '';
                    valueWrap.appendChild(newSpan);

                    if (newValue === rawValue) return;
                    rawValue = newValue;

                    saveField(tr, table, pk, pkValue, col, newValue, newSpan, function (ok) {
                        if (!ok) {
                            newSpan.innerHTML = renderValueHtml(col, rawValue);
                        }
                    });
                }

                input.addEventListener('blur', finishEdit);
                input.addEventListener('keydown', function (e2) {
                    if (e2.key === 'Enter') {
                        e2.preventDefault();
                        input.blur();
                    } else if (e2.key === 'Escape') {
                        input.value = rawValue;
                        input.blur();
                    }
                });
            });

            field.appendChild(valueWrap);
            return field;
        }

        // --- Cleaners <-> Properties many-to-many link, via pm_props_cleaners.
        // Panel-only, per the earlier decision (table view stays uncluttered). ---
        const RELATION_CONFIG = {
            pm_prop_hub:  { label: 'Cleaners',   otherList: ALL_CLEANERS,   otherNoun: 'cleaner' },
            pm_cleaners:  { label: 'Properties', otherList: ALL_PROPERTIES, otherNoun: 'property' },
        };

        function buildRelationsField(table, pkValue) {
            const config = RELATION_CONFIG[table];
            if (!config) return null;

            const field = document.createElement('div');
            field.className = 'ph-panel-field';

            const label = document.createElement('div');
            label.className = 'ph-panel-label';
            label.textContent = config.label;
            field.appendChild(label);

            const valueWrap = document.createElement('div');
            valueWrap.className = 'ph-panel-value ph-relations-value';

            const chipsWrap = document.createElement('div');
            chipsWrap.className = 'ph-relations-chips';
            chipsWrap.innerHTML = '<span class="ph-relations-loading">Loading…</span>';
            valueWrap.appendChild(chipsWrap);

            const addRow = document.createElement('div');
            addRow.className = 'ph-relations-add';
            const select = document.createElement('select');
            select.className = 'ph-relations-select';
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'ph-relations-add-btn button';
            addBtn.textContent = 'Add';
            addRow.appendChild(select);
            addRow.appendChild(addBtn);
            valueWrap.appendChild(addRow);

            field.appendChild(valueWrap);

            let currentLinks = [];

            function renderSelectOptions() {
                const linkedIds = currentLinks.map(function (l) { return String(l.other_id); });
                const available = config.otherList.filter(function (item) {
                    return linkedIds.indexOf(String(item.id)) === -1;
                });
                select.innerHTML = '';
                if (!available.length) {
                    const opt = document.createElement('option');
                    opt.textContent = 'All ' + config.label.toLowerCase() + ' already linked';
                    opt.disabled = true;
                    select.appendChild(opt);
                    select.disabled = true;
                    addBtn.disabled = true;
                    return;
                }
                select.disabled = false;
                addBtn.disabled = false;
                available.forEach(function (item) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name || ('#' + item.id);
                    select.appendChild(opt);
                });
            }

            function renderChips() {
                if (!currentLinks.length) {
                    chipsWrap.innerHTML = '<span class="ph-dash">—</span>';
                    return;
                }
                chipsWrap.innerHTML = '';
                currentLinks.forEach(function (link) {
                    const chip = document.createElement('span');
                    chip.className = 'ph-link-chip';
                    const roleClass = String(link.role).toLowerCase() === 'backup'
                        ? 'ph-link-chip-role-backup' : 'ph-link-chip-role-main';

                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'ph-link-chip-name';
                    nameSpan.textContent = link.other_name;
                    chip.appendChild(nameSpan);

                    const roleSpan = document.createElement('span');
                    roleSpan.className = 'ph-link-chip-role ' + roleClass;
                    roleSpan.textContent = link.role;
                    chip.appendChild(roleSpan);

                    if (link.turnover) {
                        const turnoverSpan = document.createElement('span');
                        turnoverSpan.className = 'ph-link-chip-turnover';
                        turnoverSpan.textContent = link.turnover;
                        chip.appendChild(turnoverSpan);
                    }

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'ph-link-chip-remove';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.title = 'Remove this link';
                    removeBtn.addEventListener('click', function () {
                        if (!confirm('Remove this link?')) return;
                        removeBtn.disabled = true;

                        const body = new URLSearchParams();
                        body.append('action', 'lbs_remove_prop_cleaner_link');
                        body.append('nonce', linksNonce);
                        body.append('link_id', link.id);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: body.toString()
                        })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (!data.success) {
                                alert('Could not remove link: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                                removeBtn.disabled = false;
                                return;
                            }
                            currentLinks = currentLinks.filter(function (l) { return l.id !== link.id; });
                            renderChips();
                            renderSelectOptions();
                        })
                        .catch(function () {
                            alert('Network error - could not remove link.');
                            removeBtn.disabled = false;
                        });
                    });
                    chip.appendChild(removeBtn);

                    chipsWrap.appendChild(chip);
                });
            }

            addBtn.addEventListener('click', function () {
                const otherId = select.value;
                if (!otherId) return;
                addBtn.disabled = true;

                const body = new URLSearchParams();
                body.append('action', 'lbs_add_prop_cleaner_link');
                body.append('nonce', linksNonce);
                if (table === 'pm_prop_hub') {
                    body.append('prop_id', pkValue);
                    body.append('cleaner_id', otherId);
                } else {
                    body.append('prop_id', otherId);
                    body.append('cleaner_id', pkValue);
                }

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    addBtn.disabled = false;
                    if (!data.success) {
                        alert('Could not add link: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        return;
                    }
                    const otherItem = config.otherList.find(function (item) { return String(item.id) === String(otherId); });
                    currentLinks.push({
                        id: data.data.id,
                        role: data.data.role,
                        turnover: '',
                        other_id: otherId,
                        other_name: otherItem ? otherItem.name : ('#' + otherId)
                    });
                    renderChips();
                    renderSelectOptions();
                })
                .catch(function () {
                    addBtn.disabled = false;
                    alert('Network error - could not add link.');
                });
            });

            const body = new URLSearchParams();
            body.append('action', 'lbs_get_row_links');
            body.append('nonce', linksNonce);
            body.append('table', table);
            body.append('pk_value', pkValue);

            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                // The panel may have moved on to a different row by now.
                if (panel.dataset.table !== table || panel.dataset.pkValue !== String(pkValue)) return;

                if (!data.success) {
                    chipsWrap.innerHTML = '<span class="ph-dash">Could not load</span>';
                    return;
                }
                currentLinks = data.data.links || [];
                renderChips();
                renderSelectOptions();
            })
            .catch(function () {
                if (panel.dataset.table === table && panel.dataset.pkValue === String(pkValue)) {
                    chipsWrap.innerHTML = '<span class="ph-dash">Network error</span>';
                }
            });

            return field;
        }

        function openPanel(tr) {
            const table = tr.dataset.table;
            const pk = tr.dataset.pk;
            const pkValue = tr.dataset.pkValue;
            const fields = PANEL_FIELDS[table] || [];
            const breakKey = PANEL_COLUMN_BREAKS[table];

            // Panel-only fields (e.g. WiFi) have no <td> in the table — their
            // values travel with the row as a JSON blob instead.
            let panelExtra = {};
            if (tr.dataset.panelExtra) {
                try { panelExtra = JSON.parse(tr.dataset.panelExtra); } catch (e) { panelExtra = {}; }
            }

            panelBody.innerHTML = '';
            let titleText = '';

            // Tables with a configured break key (currently just Properties,
            // split after Hostco) get a two-column layout; everything else
            // keeps the original single column.
            let col1 = panelBody;
            let col2 = panelBody;
            if (breakKey) {
                const columnsWrap = document.createElement('div');
                columnsWrap.className = 'ph-panel-columns';
                col1 = document.createElement('div');
                col1.className = 'ph-panel-col';
                col2 = document.createElement('div');
                col2.className = 'ph-panel-col';
                columnsWrap.appendChild(col1);
                columnsWrap.appendChild(col2);
                panelBody.appendChild(columnsWrap);
            }

            let pastBreak = false;

            fields.forEach(function (col) {
                const td = tr.querySelector('.ph-cell[data-field="' + col.key + '"]');
                let rawValue;
                if (col.type === 'status') {
                    const trigger = td ? td.querySelector('.ph-status-trigger') : null;
                    rawValue = trigger ? trigger.dataset.status : '0';
                } else if (col.key === pk) {
                    rawValue = td ? td.textContent.trim() : pkValue;
                } else if (td) {
                    rawValue = td.dataset.raw !== undefined ? td.dataset.raw : td.textContent.trim();
                } else if (Object.prototype.hasOwnProperty.call(panelExtra, col.key)) {
                    rawValue = panelExtra[col.key];
                } else {
                    rawValue = '';
                }

                if (col.type === 'title' && !titleText) titleText = rawValue;

                const target = pastBreak ? col2 : col1;
                target.appendChild(buildPanelField(tr, table, pk, pkValue, col, rawValue));

                if (breakKey && col.key === breakKey) pastBreak = true;
            });

            panelTitle.textContent = titleText || '(untitled)';
            panel.dataset.table = table;
            panel.dataset.pkValue = String(pkValue);

            const relationsField = buildRelationsField(table, pkValue);
            if (relationsField) col2.appendChild(relationsField);

            document.querySelectorAll('.ph-row.ph-row-active').forEach(function (r) {
                r.classList.remove('ph-row-active');
            });
            tr.classList.add('ph-row-active');

            panel.classList.add('open');
            overlay.classList.add('open');
            document.body.classList.add('ph-body-panel-open');
        }

        document.querySelectorAll('.ph-row').forEach(function (tr) {
            tr.addEventListener('click', function (e) {
                if (e.target.closest('a, button, .ph-status-wrap')) return;
                openPanel(tr);
            });
        });

        // --- Properties view tabs: Active (status != 0) / Cancelled (status == 0) ---
        const propTabs = document.querySelectorAll('#ph-properties-tabs .ph-tab');
        const propSection = document.getElementById('ph-properties-section');
        const propCount = propSection ? propSection.querySelector('.ph-count[data-count-for="pm_prop_hub"]') : null;

        function applyPropFilter(filter) {
            const rows = propSection.querySelectorAll('.ph-row');
            let visible = 0;

            rows.forEach(function (tr) {
                const isCancelled = tr.dataset.status === '0';
                const show = filter === 'active' ? !isCancelled : isCancelled;
                tr.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (propCount) {
                propCount.textContent = visible + (visible === 1 ? ' row' : ' rows')
                    + (filter === 'cancelled' ? ' (cancelled)' : '');
            }
        }

        propTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                propTabs.forEach(function (t) { t.classList.remove('ph-tab-active'); });
                tab.classList.add('ph-tab-active');
                applyPropFilter(tab.dataset.filter);
            });
        });

        // Apply the default tab's filter immediately on load, since the PHP
        // renders all rows unfiltered.
        if (propSection) {
            const activeTab = propSection.querySelector('.ph-tab.ph-tab-active');
            applyPropFilter(activeTab ? activeTab.dataset.filter : 'active');
        }
    })();
