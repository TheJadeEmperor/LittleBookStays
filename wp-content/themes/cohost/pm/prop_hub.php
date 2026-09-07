<?php
// ============================================================
// Property Hub — WordPress admin page
// LBS
// ============================================================

// --- DB connection for the pm_* tables ---
// Uses WordPress's own database connection info ($wpdb->dbhost/dbname/
// dbuser/dbpassword — the exact credentials wp-config.php already
// connects with) instead of a separate hardcoded connection, so this
// always points at whatever database WordPress itself is using.
function lbs_prop_hub_db() {
    static $pdo = null;
    if ($pdo === null) {
        global $wpdb;

        $pdo = new PDO(
            "mysql:host={$wpdb->dbhost};dbname={$wpdb->dbname};charset=utf8mb4",
            $wpdb->dbuser,
            $wpdb->dbpassword,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

// --- Register the admin menu page ---
add_action('admin_menu', 'lbs_add_prop_hub_page');

function lbs_add_prop_hub_page() {
    add_menu_page(
        'Property Hub',
        'Property Hub',
        'manage_options',
        'lbs-prop-hub',
        'lbs_render_prop_hub_page',
        'dashicons-admin-multisite',
        4
    );
}

// --- Status option definitions (value => [label, colors]) ---
function lbs_prop_status_options() {
    return [
        0 => ['label' => 'Cancelled',  'bg' => '#FBE4E4', 'fg' => '#C4554D', 'dot' => '#E03E3E'],
        1 => ['label' => 'Live',       'bg' => '#DDF3E4', 'fg' => '#0F7B6C', 'dot' => '#2EAE4E'],
        2 => ['label' => 'Onboarding', 'bg' => '#FDECC8', 'fg' => '#97701D', 'dot' => '#F5A623'],
    ];
}

// --- Table registry: pk column + column defs for every pm_* table. ---
// Single source of truth for the table headers, the sidebar panel fields,
// and the whitelist the generic field-save AJAX handler checks against.
function lbs_prop_hub_tables() {
    return [
        'pm_prop_hub' => [
            'pk'          => 'num',
            'columns'     => lbs_prop_hub_columns(),
            // Fields that only appear in the right-hand sidebar panel, not
            // as their own column in the main table.
            'panel_extra' => lbs_prop_hub_panel_extra_columns(),
        ],
        'pm_cleaners' => [
            'pk'      => 'id',
            'columns' => lbs_cleaners_columns(),
        ],
        'pm_contractors' => [
            'pk'      => 'id',
            'columns' => lbs_contractors_columns(),
        ],
    ];
}

// --- Panel-only fields: pm_prop_hub. Editable from the sidebar detail
// panel only — not shown as columns in the main properties table. ---
function lbs_prop_hub_panel_extra_columns() {
    return [
        ['key' => 'wifi_service',  'label' => 'WiFi Service',  'type' => 'text'],
        ['key' => 'wifi_username', 'label' => 'WiFi Username', 'type' => 'text'],
        ['key' => 'wifi_pw',       'label' => 'WiFi Password', 'type' => 'text'],
    ];
}

// --- Column definitions: pm_prop_hub, in display order ---
// type: 'num' | 'status' | 'title' | 'text' | 'link'
// 'key' is the db field this column reads from. The same key can appear
// more than once in the TABLE (e.g. Property is repeated at the end) —
// the sidebar panel dedupes automatically.
function lbs_prop_hub_columns() {
    return [
        ['key' => 'num',       'label' => '#',                'type' => 'num'],
        ['key' => 'status',    'label' => 'Status',           'type' => 'status'],
        ['key' => 'client',    'label' => 'Client',           'type' => 'text'],
        ['key' => 'name',      'label' => 'Property',         'type' => 'title'],
        ['key' => 'close',     'label' => 'Close CRM',        'type' => 'link', 'chip' => 'CRM'],
        ['key' => 'zip',       'label' => 'ZIP',              'type' => 'text'],
        ['key' => 'a_direct',  'label' => 'BNB Direct',       'type' => 'link', 'chip' => 'Listing'],
        ['key' => 'turno',     'label' => 'Turno',            'type' => 'link', 'chip' => 'Turno'],
        ['key' => 'gdrive',    'label' => 'Google Drive',     'type' => 'link', 'chip' => 'Folder'],
        ['key' => 'hosp',      'label' => 'Hospitable ID',    'type' => 'text'],
        ['key' => 'hosp_msg',  'label' => 'Messaging Rules',  'type' => 'link', 'chip' => 'Rules'],
        ['key' => 'a_direct',   'label' => 'Airbnb Listing ID','type' => 'text'],
        ['key' => 'v_list',    'label' => 'VRBO Listing',     'type' => 'link', 'chip' => 'Listing'],
        ['key' => 'v_ins',     'label' => 'VRBO Insurance',   'type' => 'link', 'chip' => 'Insurance'],
        ['key' => 'v_fees',    'label' => 'VRBO Fees',        'type' => 'link', 'chip' => 'Fees'],
        ['key' => 'v_live',    'label' => 'VRBO Live',        'type' => 'link', 'chip' => 'Live'],
        ['key' => 'pricelabs', 'label' => 'PriceLabs',        'type' => 'text'],
        ['key' => 'compset',   'label' => 'CompSet',          'type' => 'link', 'chip' => 'CompSet'],
        ['key' => 'hostco',    'label' => 'Host.co',          'type' => 'link', 'chip' => 'Store'],
        ['key' => 'name',      'label' => 'Property',         'type' => 'title'],
    ];
}

// --- Column definitions: pm_cleaners, in display order ---
function lbs_cleaners_columns() {
    return [
        ['key' => 'id',        'label' => 'ID',          'type' => 'num'],
        ['key' => 'name',      'label' => 'Cleaner',     'type' => 'title'],
        ['key' => 'manager',   'label' => 'Manager',     'type' => 'text'],
        ['key' => 'staff',     'label' => 'Staff',       'type' => 'text'],
        ['key' => 'scheduler', 'label' => 'Scheduler',   'type' => 'text'],
        ['key' => 'chat',      'label' => 'Chat',        'type' => 'text'],
        ['key' => 'close',     'label' => 'Close CRM',   'type' => 'link', 'chip' => 'CRM'],
        ['key' => 'address',    'label' => 'Address',   'type' => 'text', 'chip' => 'Folder'],
        ['key' => 'turnover',    'label' => 'Turnover',     'type' => 'text', 'chip' => 'Folder'],
        ['key' => 'phone',    'label' => 'Phone',       'type' => 'text', 'chip' => 'Folder'],
    ];
}

// --- Column definitions: pm_contractors, in display order ---
function lbs_contractors_columns() {
    return [
        ['key' => 'id',      'label' => 'ID',        'type' => 'num'],
        ['key' => 'name',    'label' => 'Contractor','type' => 'title'],
        ['key' => 'title',   'label' => 'Trade',     'type' => 'text'],
        ['key' => 'address', 'label' => 'Address',   'type' => 'text'],
        ['key' => 'close',   'label' => 'Close CRM', 'type' => 'link', 'chip' => 'CRM'],
        ['key' => 'phone',   'label' => 'Phone',     'type' => 'text'],
        ['key' => 'payment', 'label' => 'Payment',   'type' => 'text'],
        ['key' => 'note',    'label' => 'Note',      'type' => 'text'],
        ['key' => 'url',    'label' => 'URL',      'type' => 'text'], 
    ];
}

// --- Dedupe a column list down to one entry per field key (first wins).
// Used to build the sidebar panel's field list from a table's columns,
// since e.g. Property/name appears twice in the pm_prop_hub table. ---
function lbs_dedupe_columns($columns) {
    $seen = [];
    $out = [];
    foreach ($columns as $col) {
        if (isset($seen[$col['key']])) {
            continue;
        }
        $seen[$col['key']] = true;
        $out[] = $col;
    }
    return $out;
}

// --- Render a cell for num / title / text / link types (shared by all tables) ---
function lbs_render_generic_cell($row, $col) {
    $key = $col['key'];
    $value = isset($row[$key]) ? $row[$key] : null;
    $value = is_string($value) ? trim($value) : $value;

    if ($col['type'] === 'num') {
        echo '<td class="ph-cell ph-num" data-field="' . esc_attr($key) . '">' . esc_html($value) . '</td>';
        return;
    }

    if ($value === '' || $value === null) {
        echo '<td class="ph-cell ph-empty" data-field="' . esc_attr($key) . '" data-raw=""><span class="ph-dash">—</span></td>';
        return;
    }

    $rawAttr = ' data-raw="' . esc_attr($value) . '"';

    switch ($col['type']) {
        case 'title':
            echo '<td class="ph-cell ph-title" data-field="' . esc_attr($key) . '"' . $rawAttr . '><span class="ph-title-icon">▤</span>' . esc_html($value) . '</td>';
            break;

        case 'link':
            if (preg_match('#^https?://#i', $value)) {
                $chip = isset($col['chip']) ? $col['chip'] : 'Open';
                echo '<td class="ph-cell" data-field="' . esc_attr($key) . '"' . $rawAttr . '><a class="ph-chip" href="' . esc_url($value) . '" target="_blank" rel="noopener noreferrer">'
                    . esc_html($chip) . ' <span class="ph-chip-arrow">&#8599;</span></a></td>';
            } else {
                // Not a full URL (e.g. a bare folder ID) — show as plain text.
                echo '<td class="ph-cell ph-text" data-field="' . esc_attr($key) . '"' . $rawAttr . ' title="' . esc_attr($value) . '">' . esc_html($value) . '</td>';
            }
            break;

        case 'text':
        default:
            echo '<td class="ph-cell ph-text" data-field="' . esc_attr($key) . '"' . $rawAttr . ' title="' . esc_attr($value) . '">' . esc_html($value) . '</td>';
            break;
    }
}

// --- Render a cell for the pm_prop_hub table (adds the status dropdown) ---
function lbs_render_prop_cell($row, $col) {
    if ($col['type'] !== 'status') {
        lbs_render_generic_cell($row, $col);
        return;
    }

    $value = isset($row['status']) ? $row['status'] : null;
    $options = lbs_prop_status_options();
    $current = isset($options[(int) $value]) ? (int) $value : 0;
    $opt = $options[$current];

    echo '<td class="ph-cell ph-status-cell" data-field="status">';
    echo '<div class="ph-status-wrap" data-num="' . esc_attr($row['num']) . '">';
    echo '<button type="button" class="ph-pill ph-status-trigger" data-status="' . $current . '" '
        . 'style="background:' . esc_attr($opt['bg']) . ';color:' . esc_attr($opt['fg']) . ';">'
        . '<span class="ph-dot" style="background:' . esc_attr($opt['dot']) . ';"></span>'
        . esc_html($opt['label']) . '</button>';
    echo '<div class="ph-status-menu">';
    foreach ($options as $val => $o) {
        $selected = $val === $current ? ' ph-status-selected' : '';
        echo '<div class="ph-status-option' . $selected . '" data-status="' . $val . '" '
            . 'data-bg="' . esc_attr($o['bg']) . '" data-fg="' . esc_attr($o['fg']) . '" data-dot="' . esc_attr($o['dot']) . '">'
            . '<span class="ph-status-option-pill" style="background:' . esc_attr($o['bg']) . ';color:' . esc_attr($o['fg']) . ';">'
            . '<span class="ph-dot" style="background:' . esc_attr($o['dot']) . ';"></span>' . esc_html($o['label']) . '</span>'
            . '<span class="ph-status-check">&#10003;</span>'
            . '</div>';
    }
    echo '</div>'; // .ph-status-menu
    echo '</div>'; // .ph-status-wrap
    echo '</td>';
}

// --- Fetch all rows + columns for a pm_* table ---
function lbs_fetch_table_rows($sqlTable, $columns, $orderBy) {
    $selectFields = array_unique(array_column($columns, 'key'));
    if (!in_array($orderBy, $selectFields, true)) {
        $selectFields[] = $orderBy;
    }

    try {
        $pdo = lbs_prop_hub_db();
        $stmt = $pdo->query(
            'SELECT ' . implode(', ', $selectFields) . '
             FROM ' . $sqlTable . '
             ORDER BY ' . $orderBy
        );
        return ['rows' => $stmt->fetchAll(), 'error' => null];
    } catch (PDOException $e) {
        return ['rows' => [], 'error' => $e->getMessage()];
    }
}

// --- Render one Notion-style table section (heading + table + count).
// Rows get data-table / data-pk / data-pk-value so the row-click handler
// knows which record + table to open in the sidebar panel. ---
function lbs_render_table_section($heading, $sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback, $panelExtraColumns = []) {
    ?>
    <h2 class="ph-section-title"><?= esc_html($heading) ?></h2>
    <p class="ph-subtitle">Pulled live from <code><?= esc_html($sqlTable) ?></code>. Click a row to open it.</p>
    <?php
    lbs_render_table_body($sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback, $heading, $panelExtraColumns);
}

// --- Render just the table/empty-state/count (no heading) — used directly
// by Properties, which prints its own heading + view tabs above this.
// $panelExtraColumns (optional): panel-only fields (no visible <td>) that
// still need to travel with the row so the sidebar panel can read them —
// stashed as a JSON blob in data-panel-extra on the <tr>. ---
function lbs_render_table_body($sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback, $heading, $panelExtraColumns = []) {
    ?>
    <?php if ($dbError): ?>
        <div class="notice notice-error"><p>Could not load <?= esc_html($heading) ?>: <?= esc_html($dbError) ?></p></div>
    <?php elseif (empty($rows)): ?>
        <div class="ph-empty-state">No <?= esc_html(strtolower($heading)) ?> yet.</div>
    <?php else: ?>
        <div class="ph-table-shell">
            <div class="ph-table-scroll">
                <table class="ph-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <th class="ph-th"><?= esc_html($col['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $panelExtraAttr = '';
                            if (!empty($panelExtraColumns)) {
                                $extraData = [];
                                foreach ($panelExtraColumns as $ecol) {
                                    $v = isset($row[$ecol['key']]) ? $row[$ecol['key']] : '';
                                    $extraData[$ecol['key']] = is_string($v) ? trim($v) : $v;
                                }
                                $panelExtraAttr = ' data-panel-extra="' . esc_attr(wp_json_encode($extraData)) . '"';
                            }
                            ?>
                            <tr class="ph-row"
                                data-table="<?= esc_attr($sqlTable) ?>"
                                data-pk="<?= esc_attr($pkColumn) ?>"
                                data-pk-value="<?= esc_attr($row[$pkColumn]) ?>"
                                <?php if (isset($row['status'])): ?>data-status="<?= esc_attr($row['status']) ?>"<?php endif; ?><?= $panelExtraAttr ?>>
                                <?php foreach ($columns as $col): ?>
                                    <?php call_user_func($cellCallback, $row, $col); ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ph-count" data-count-for="<?= esc_attr($sqlTable) ?>"><?= count($rows) ?> <?= count($rows) === 1 ? 'row' : 'rows' ?></div>
    <?php endif;
}

// --- Main page render callback ---
function lbs_render_prop_hub_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $tables = lbs_prop_hub_tables();

    $propData = lbs_fetch_table_rows(
        'pm_prop_hub',
        array_merge($tables['pm_prop_hub']['columns'], $tables['pm_prop_hub']['panel_extra']),
        'num'
    );
    $cleanerData = lbs_fetch_table_rows('pm_cleaners', $tables['pm_cleaners']['columns'], 'id');
    $contractorData = lbs_fetch_table_rows('pm_contractors', $tables['pm_contractors']['columns'], 'id');

    $statusNonce = wp_create_nonce('lbs_prop_hub_status');
    $fieldNonce = wp_create_nonce('lbs_prop_hub_field');
    $linksNonce = wp_create_nonce('lbs_prop_hub_links');
    $ajaxUrl = admin_url('admin-ajax.php');

    // Lightweight id/name lists for the panel's "link a cleaner" /
    // "link a property" dropdown — built from data already fetched above,
    // no extra query needed.
    $allCleanersJs = array_map(function ($r) {
        return ['id' => (int) $r['id'], 'name' => $r['name']];
    }, $cleanerData['rows']);
    $allPropertiesJs = array_map(function ($r) {
        return ['id' => (int) $r['num'], 'name' => $r['name']];
    }, $propData['rows']);

    // Panel field config + status palette, handed to JS so the sidebar
    // panel (and the re-render after a save) share the exact same rules
    // as the PHP cell renderers above.
    $panelFields = [];
    $pkColumns = [];
    foreach ($tables as $tableName => $def) {
        $fields = lbs_dedupe_columns($def['columns']);
        if (!empty($def['panel_extra'])) {
            $fields = array_merge($fields, $def['panel_extra']);
        }
        $panelFields[$tableName] = $fields;
        $pkColumns[$tableName] = $def['pk'];
    }
    $statusOptionsJs = lbs_prop_status_options();
    ?>
    <div class="wrap ph-wrap">
        <h1>Property Hub</h1>

        <div class="ph-quicklinks">
            <a class="button" href="<?= esc_url(admin_url('admin.php?page=lbs-onboarding')) ?>">Onboarding checklist</a>
        </div>

        <div id="ph-properties-section">
            <h2 class="ph-section-title">Properties</h2>
            <p class="ph-subtitle">Pulled live from <code>pm_prop_hub</code>. Click a row to open it.</p>

            <div class="ph-tabs" id="ph-properties-tabs">
                <button type="button" class="ph-tab ph-tab-active" data-filter="active">Active</button>
                <button type="button" class="ph-tab" data-filter="cancelled">Cancelled</button>
            </div>

            <?php
            lbs_render_table_body('pm_prop_hub', 'num', $propData['rows'], $tables['pm_prop_hub']['columns'], $propData['error'], 'lbs_render_prop_cell', 'Properties', $tables['pm_prop_hub']['panel_extra']);
            ?>
        </div>

        <?php
        lbs_render_table_section('Cleaners', 'pm_cleaners', 'id', $cleanerData['rows'], $tables['pm_cleaners']['columns'], $cleanerData['error'], 'lbs_render_generic_cell');
        lbs_render_table_section('Contractors', 'pm_contractors', 'id', $contractorData['rows'], $tables['pm_contractors']['columns'], $contractorData['error'], 'lbs_render_generic_cell');
        ?>
    </div>

    <!-- Notion-style slide-in detail panel -->
    <div class="ph-panel-overlay" id="ph-panel-overlay"></div>
    <aside class="ph-panel" id="ph-panel">
        <div class="ph-panel-header">
            <span class="ph-panel-title" id="ph-panel-title">&nbsp;</span>
            <button type="button" class="ph-panel-close" id="ph-panel-close" aria-label="Close">&times;</button>
        </div>
        <div class="ph-panel-body" id="ph-panel-body"></div>
    </aside>

<style>
    .ph-relations-value {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .ph-relations-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .ph-relations-loading {
        color: #9B9A97;
        font-size: 13px;
        font-style: italic;
    }
    .ph-link-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F1F1EF;
        border-radius: 4px;
        padding: 3px 6px 3px 9px;
        font-size: 12px;
        color: #37352F;
    }
    .ph-link-chip-name {
        font-weight: 500;
    }
    .ph-link-chip-role {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 1px 5px;
        border-radius: 3px;
    }
    .ph-link-chip-role-main {
        background: #DDF3E4;
        color: #0F7B6C;
    }
    .ph-link-chip-role-backup {
        background: #FDECC8;
        color: #97701D;
    }
    .ph-link-chip-turnover {
        color: #9B9A97;
    }
    .ph-link-chip-remove {
        background: none;
        border: none;
        color: #9B9A97;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        padding: 0 2px;
        border-radius: 3px;
    }
    .ph-link-chip-remove:hover {
        background: #E3E2E0;
        color: #37352F;
    }
    .ph-relations-add {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .ph-relations-select {
        font-size: 12px;
        padding: 3px 6px;
        border: 1px solid #D9D9D6;
        border-radius: 4px;
        max-width: 220px;
    }
    .ph-relations-add-btn {
        font-size: 12px !important;
        padding: 2px 10px !important;
        height: auto !important;
        line-height: 1.6 !important;
    }
      </style>

    <script>
    (function () {
        const ajaxUrl = <?= json_encode($ajaxUrl) ?>;
        const statusNonce = <?= json_encode($statusNonce) ?>;
        const fieldNonce = <?= json_encode($fieldNonce) ?>;
        const linksNonce = <?= json_encode($linksNonce) ?>;
        const STATUS_OPTIONS = <?= json_encode($statusOptionsJs) ?>;
        const PANEL_FIELDS = <?= json_encode($panelFields) ?>;
        const PK_COLUMNS = <?= json_encode($pkColumns) ?>;
        const ALL_CLEANERS = <?= json_encode($allCleanersJs) ?>;
        const ALL_PROPERTIES = <?= json_encode($allPropertiesJs) ?>;

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

            // Panel-only fields (e.g. WiFi) have no <td> in the table — their
            // values travel with the row as a JSON blob instead.
            let panelExtra = {};
            if (tr.dataset.panelExtra) {
                try { panelExtra = JSON.parse(tr.dataset.panelExtra); } catch (e) { panelExtra = {}; }
            }

            panelBody.innerHTML = '';
            let titleText = '';

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

                panelBody.appendChild(buildPanelField(tr, table, pk, pkValue, col, rawValue));
            });

            panelTitle.textContent = titleText || '(untitled)';
            panel.dataset.table = table;
            panel.dataset.pkValue = String(pkValue);

            const relationsField = buildRelationsField(table, pkValue);
            if (relationsField) panelBody.appendChild(relationsField);

            document.querySelectorAll('.ph-row.ph-row-active').forEach(function (r) {
                r.classList.remove('ph-row-active');
            });
            tr.classList.add('ph-row-active');

            panel.classList.add('open');
            overlay.classList.add('open');
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
    </script>
    <?php

}

// --- AJAX handler for updating a property's status ---
add_action('wp_ajax_lbs_update_prop_status', 'lbs_update_prop_status');

function lbs_update_prop_status() {
    check_ajax_referer('lbs_prop_hub_status', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $num = isset($_POST['num']) ? (int) $_POST['num'] : 0;
    $status = isset($_POST['status']) ? (int) $_POST['status'] : null;
    $validStatuses = array_keys(lbs_prop_status_options());

    if ($num <= 0 || !in_array($status, $validStatuses, true)) {
        wp_send_json_error(['error' => 'Invalid property or status value'], 400);
    }

    $pdo = lbs_prop_hub_db();
    $stmt = $pdo->prepare('UPDATE pm_prop_hub SET status = :status WHERE num = :num');
    $stmt->execute([':status' => $status, ':num' => $num]);

    wp_send_json_success(['num' => $num, 'status' => $status]);
}

// --- AJAX handler for updating any editable text/link/title field on any
// pm_* table, used by the sidebar panel. Table + field are checked against
// lbs_prop_hub_tables() so nothing outside the known schema can be touched. ---
add_action('wp_ajax_lbs_update_row_field', 'lbs_update_row_field');

function lbs_update_row_field() {
    check_ajax_referer('lbs_prop_hub_field', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $table = isset($_POST['table']) ? sanitize_text_field($_POST['table']) : '';
    $pk = isset($_POST['pk']) ? sanitize_text_field($_POST['pk']) : '';
    $pkValue = isset($_POST['pk_value']) ? (int) $_POST['pk_value'] : 0;
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';

    $tables = lbs_prop_hub_tables();

    if (!isset($tables[$table]) || $pk !== $tables[$table]['pk'] || $pkValue <= 0) {
        wp_send_json_error(['error' => 'Invalid table or record'], 400);
    }

    $validFields = array_unique(array_column($tables[$table]['columns'], 'key'));
    if (!empty($tables[$table]['panel_extra'])) {
        $validFields = array_unique(array_merge($validFields, array_column($tables[$table]['panel_extra'], 'key')));
    }
    // Status has its own dedicated dropdown/handler; the pk column isn't editable here.
    if ($field === 'status' || $field === $pk || !in_array($field, $validFields, true)) {
        wp_send_json_error(['error' => 'Invalid field'], 400);
    }

    $pdo = lbs_prop_hub_db();
    // $table, $pk, $field are all validated against the whitelist above, not
    // raw user input, so it's safe to interpolate them into the identifier
    // positions here (PDO can't parameterize column/table names).
    $stmt = $pdo->prepare("UPDATE {$table} SET {$field} = :value WHERE {$pk} = :pk_value");
    $stmt->execute([':value' => $value, ':pk_value' => $pkValue]);

    wp_send_json_success(['table' => $table, 'field' => $field, 'value' => $value]);
}

// --- AJAX handler: fetch the cleaners linked to a property, or the
// properties linked to a cleaner, via the pm_props_cleaners junction table. ---
add_action('wp_ajax_lbs_get_row_links', 'lbs_get_row_links');

function lbs_get_row_links() {
    check_ajax_referer('lbs_prop_hub_links', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $table = isset($_POST['table']) ? sanitize_text_field($_POST['table']) : '';
    $pkValue = isset($_POST['pk_value']) ? (int) $_POST['pk_value'] : 0;

    if ($pkValue <= 0 || !in_array($table, ['pm_prop_hub', 'pm_cleaners'], true)) {
        wp_send_json_error(['error' => 'Invalid table or record'], 400);
    }

    $pdo = lbs_prop_hub_db();

    if ($table === 'pm_prop_hub') {
        // A property — return every cleaner linked to it.
        $stmt = $pdo->prepare(
            'SELECT pc.id, pc.role, pc.turnover, c.id AS other_id, c.name AS other_name
             FROM pm_props_cleaners pc
             JOIN pm_cleaners c ON c.id = pc.cleaner_id
             WHERE pc.prop_id = :pk_value
             ORDER BY pc.role, c.name'
        );
    } else {
        // A cleaner — return every property linked to it.
        $stmt = $pdo->prepare(
            'SELECT pc.id, pc.role, pc.turnover, p.num AS other_id, p.name AS other_name
             FROM pm_props_cleaners pc
             JOIN pm_prop_hub p ON p.num = pc.prop_id
             WHERE pc.cleaner_id = :pk_value
             ORDER BY pc.role, p.name'
        );
    }

    $stmt->execute([':pk_value' => $pkValue]);
    wp_send_json_success(['links' => $stmt->fetchAll()]);
}

// --- AJAX handler: link a cleaner to a property (insert into
// pm_props_cleaners). Defaults role to 'Main' and turnover to blank —
// both are editable later directly on the junction table if needed. ---
add_action('wp_ajax_lbs_add_prop_cleaner_link', 'lbs_add_prop_cleaner_link');

function lbs_add_prop_cleaner_link() {
    check_ajax_referer('lbs_prop_hub_links', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $propId = isset($_POST['prop_id']) ? (int) $_POST['prop_id'] : 0;
    $cleanerId = isset($_POST['cleaner_id']) ? (int) $_POST['cleaner_id'] : 0;
    $role = 'Main';

    if ($propId <= 0 || $cleanerId <= 0) {
        wp_send_json_error(['error' => 'Invalid property or cleaner'], 400);
    }

    $pdo = lbs_prop_hub_db();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO pm_props_cleaners (prop_id, cleaner_id, role, turnover) VALUES (:prop_id, :cleaner_id, :role, "")'
        );
        $stmt->execute([':prop_id' => $propId, ':cleaner_id' => $cleanerId, ':role' => $role]);
    } catch (PDOException $e) {
        // MySQL error code 1062 = duplicate entry, which the unique key
        // on (prop_id, cleaner_id) throws if this pair is already linked.
        if ((int) $e->errorInfo[1] === 1062) {
            wp_send_json_error(['error' => 'That link already exists'], 409);
        }
        wp_send_json_error(['error' => $e->getMessage()], 500);
    }

    wp_send_json_success([
        'id'         => (int) $pdo->lastInsertId(),
        'prop_id'    => $propId,
        'cleaner_id' => $cleanerId,
        'role'       => $role,
    ]);
}

// --- AJAX handler: unlink a cleaner from a property (delete the
// pm_props_cleaners row by its own id). ---
add_action('wp_ajax_lbs_remove_prop_cleaner_link', 'lbs_remove_prop_cleaner_link');

function lbs_remove_prop_cleaner_link() {
    check_ajax_referer('lbs_prop_hub_links', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $linkId = isset($_POST['link_id']) ? (int) $_POST['link_id'] : 0;

    if ($linkId <= 0) {
        wp_send_json_error(['error' => 'Invalid link'], 400);
    }

    $pdo = lbs_prop_hub_db();
    $stmt = $pdo->prepare('DELETE FROM pm_props_cleaners WHERE id = :id');
    $stmt->execute([':id' => $linkId]);

    wp_send_json_success(['id' => $linkId]);
}