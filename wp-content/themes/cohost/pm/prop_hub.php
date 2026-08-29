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
            'pk'      => 'num',
            'columns' => lbs_prop_hub_columns(),
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
        ['key' => 'zip',       'label' => 'ZIP',               'type' => 'text'],
        ['key' => 'shorturl',  'label' => 'Airbnb',           'type' => 'link', 'chip' => 'Listing'],
        ['key' => 'turno',     'label' => 'Turno',            'type' => 'link', 'chip' => 'Turno'],
        ['key' => 'gdrive',    'label' => 'Google Drive',     'type' => 'link', 'chip' => 'Folder'],
        ['key' => 'hosp',      'label' => 'Hospitable ID',    'type' => 'text'],
        ['key' => 'hosp_msg',  'label' => 'Messaging Rules',  'type' => 'link', 'chip' => 'Rules'],
        ['key' => 'a_listing', 'label' => 'Airbnb Listing ID','type' => 'text'],
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
        ['key' => 'photos',    'label' => 'Photos',      'type' => 'link', 'chip' => 'Folder'],
    ];
}

// --- Column definitions: pm_contractors, in display order ---
function lbs_contractors_columns() {
    return [
        ['key' => 'id',      'label' => 'ID',        'type' => 'num'],
        ['key' => 'name',    'label' => 'Contractor','type' => 'title'],
        ['key' => 'title',   'label' => 'Trade',     'type' => 'text'],
        ['key' => 'prop_id', 'label' => 'Property #','type' => 'num'],
        ['key' => 'phone',   'label' => 'Phone',     'type' => 'text'],
        ['key' => 'address', 'label' => 'Address',   'type' => 'text'],
        ['key' => 'payment', 'label' => 'Payment',   'type' => 'text'],
        ['key' => 'note',    'label' => 'Note',      'type' => 'text'],
        ['key' => 'close',   'label' => 'Close CRM', 'type' => 'link', 'chip' => 'CRM'],
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
function lbs_render_table_section($heading, $sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback) {
    ?>
    <h2 class="ph-section-title"><?= esc_html($heading) ?></h2>
    <p class="ph-subtitle">Pulled live from <code><?= esc_html($sqlTable) ?></code>. Click a row to open it.</p>
    <?php
    lbs_render_table_body($sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback, $heading);
}

// --- Render just the table/empty-state/count (no heading) — used directly
// by Properties, which prints its own heading + view tabs above this. ---
function lbs_render_table_body($sqlTable, $pkColumn, $rows, $columns, $dbError, $cellCallback, $heading) {
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
                            <tr class="ph-row"
                                data-table="<?= esc_attr($sqlTable) ?>"
                                data-pk="<?= esc_attr($pkColumn) ?>"
                                data-pk-value="<?= esc_attr($row[$pkColumn]) ?>"
                                <?php if (isset($row['status'])): ?>data-status="<?= esc_attr($row['status']) ?>"<?php endif; ?>>
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

    $propData = lbs_fetch_table_rows('pm_prop_hub', $tables['pm_prop_hub']['columns'], 'num');
    $cleanerData = lbs_fetch_table_rows('pm_cleaners', $tables['pm_cleaners']['columns'], 'id');
    $contractorData = lbs_fetch_table_rows('pm_contractors', $tables['pm_contractors']['columns'], 'id');

    $statusNonce = wp_create_nonce('lbs_prop_hub_status');
    $fieldNonce = wp_create_nonce('lbs_prop_hub_field');
    $ajaxUrl = admin_url('admin-ajax.php');

    // Panel field config + status palette, handed to JS so the sidebar
    // panel (and the re-render after a save) share the exact same rules
    // as the PHP cell renderers above.
    $panelFields = [];
    $pkColumns = [];
    foreach ($tables as $tableName => $def) {
        $panelFields[$tableName] = lbs_dedupe_columns($def['columns']);
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
                <button type="button" class="ph-tab ph-tab-active" data-filter="all">Default</button>
                <button type="button" class="ph-tab" data-filter="cancelled">Cancelled</button>
            </div>

            <?php
            lbs_render_table_body('pm_prop_hub', 'num', $propData['rows'], $tables['pm_prop_hub']['columns'], $propData['error'], 'lbs_render_prop_cell', 'Properties');
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
        .ph-wrap {
            font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            color: #37352F;
        }
        .ph-section-title {
            margin-top: 44px;
            margin-bottom: 2px;
            font-size: 19px;
        }
        .ph-wrap > .ph-section-title:first-of-type {
            margin-top: 30px;
        }
        .ph-subtitle {
            color: #9B9A97;
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 18px;
        }
        .ph-subtitle code {
            background: #F1F1EF;
            color: #37352F;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .ph-quicklinks {
            margin-bottom: 10px;
        }
        .ph-empty-state {
            color: #9B9A97;
            font-size: 14px;
            padding: 40px 0;
            text-align: center;
            border: 1px dashed #E9E9E7;
            border-radius: 8px;
            max-width: 900px;
        }

        /* Centered Notion-style database table */
        .ph-table-shell {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .ph-table-scroll {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            border: 1px solid #E9E9E7;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 15, 15, 0.04);
        }
        .ph-table {
            border-collapse: separate;
            border-spacing: 0;
            background: #FFFFFF;
            font-size: 13px;
        }
        .ph-th {
            position: sticky;
            top: 0;
            background: #FBFBFA;
            color: #9B9A97;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-align: left;
            padding: 9px 14px;
            border-bottom: 1px solid #E9E9E7;
            border-right: 1px solid #F1F1EF;
            white-space: nowrap;
        }
        .ph-th:last-child {
            border-right: none;
        }
        .ph-row {
            cursor: pointer;
        }
        .ph-row:hover .ph-cell {
            background: #F7F6F5;
        }
        .ph-row.ph-row-active .ph-cell {
            background: #EDF3FB;
        }
        .ph-cell {
            padding: 8px 14px;
            border-bottom: 1px solid #F1F1EF;
            border-right: 1px solid #F1F1EF;
            white-space: nowrap;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }
        .ph-status-cell {
            overflow: visible; /* let the dropdown escape the cell */
        }
        .ph-row:last-child .ph-cell {
            border-bottom: none;
        }
        .ph-cell:last-child {
            border-right: none;
        }
        .ph-num {
            color: #9B9A97;
            font-variant-numeric: tabular-nums;
            text-align: right;
            width: 36px;
        }
        .ph-title {
            font-weight: 500;
            color: #37352F;
        }
        .ph-title-icon {
            color: #A9A9A6;
            margin-right: 6px;
        }
        .ph-text {
            color: #37352F;
        }
        .ph-empty .ph-dash {
            color: #D8D8D5;
        }

        .ph-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 9px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .ph-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .ph-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 4px;
            background: #DDEBF1;
            color: #337EA9;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
        }
        .ph-chip:hover {
            background: #C7E0EA;
            color: #276583;
        }
        .ph-chip-arrow {
            font-size: 11px;
        }

        .ph-count {
            text-align: center;
            color: #9B9A97;
            font-size: 12px;
            margin-top: 10px;
        }

        /* View tabs (Properties: Default / Cancelled) */
        .ph-tabs {
            display: flex;
            gap: 4px;
            border-bottom: 1px solid #E9E9E7;
            margin-bottom: 16px;
        }
        .ph-tab {
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 8px 4px;
            margin-right: 18px;
            font-size: 13px;
            font-weight: 500;
            color: #9B9A97;
            cursor: pointer;
        }
        .ph-tab:hover {
            color: #37352F;
        }
        .ph-tab.ph-tab-active {
            color: #37352F;
            border-bottom-color: #37352F;
        }

        /* Status dropdown, Notion-style */
        .ph-status-wrap {
            position: relative;
            display: inline-block;
        }
        .ph-status-trigger {
            border: 1px solid transparent;
            cursor: pointer;
        }
        .ph-status-trigger:hover {
            border-color: rgba(0, 0, 0, 0.08);
        }
        .ph-status-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 100050;
            min-width: 160px;
            background: #FFFFFF;
            border: 1px solid #E9E9E7;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(15, 15, 15, 0.12), 0 0 0 1px rgba(15, 15, 15, 0.02);
            padding: 4px;
        }
        .ph-status-menu.open {
            display: block;
        }
        .ph-status-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 8px;
            border-radius: 5px;
            cursor: pointer;
        }
        .ph-status-option:hover {
            background: #F1F1EF;
        }
        .ph-status-option-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 9px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .ph-status-check {
            font-size: 11px;
            color: #37352F;
            visibility: hidden;
        }
        .ph-status-option.ph-status-selected .ph-status-check {
            visibility: visible;
        }

        /* ---- Slide-in detail panel ---- */
        .ph-panel-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 15, 15, 0.12);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
            z-index: 100060;
        }
        .ph-panel-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }
        .ph-panel {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 420px;
            max-width: 90vw;
            background: #FFFFFF;
            border-left: 1px solid #E9E9E7;
            box-shadow: -8px 0 24px rgba(15, 15, 15, 0.10);
            transform: translateX(100%);
            transition: transform 0.22s ease;
            z-index: 100070;
            display: flex;
            flex-direction: column;
            font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
        }
        .ph-panel.open {
            transform: translateX(0);
        }
        .ph-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid #F1F1EF;
            flex-shrink: 0;
        }
        .ph-panel-title {
            font-size: 16px;
            font-weight: 600;
            color: #37352F;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ph-panel-close {
            background: none;
            border: none;
            font-size: 20px;
            line-height: 1;
            color: #9B9A97;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .ph-panel-close:hover {
            background: #F1F1EF;
            color: #37352F;
        }
        .ph-panel-body {
            overflow-y: auto;
            padding: 8px 20px 30px;
            flex: 1;
        }
        .ph-panel-field {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #F7F6F5;
        }
        .ph-panel-label {
            width: 130px;
            flex-shrink: 0;
            font-size: 12px;
            color: #9B9A97;
            font-weight: 500;
            padding-top: 3px;
        }
        .ph-panel-value {
            flex: 1;
            min-width: 0;
            font-size: 14px;
            color: #37352F;
            cursor: text;
            min-height: 22px;
            word-break: break-word;
        }
        .ph-panel-value .ph-dash {
            color: #D8D8D5;
        }
        .ph-panel-readonly {
            color: #9B9A97;
            cursor: default;
        }
        .ph-panel-input {
            width: 100%;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #999;
            border-radius: 4px;
            padding: 3px 7px;
            box-sizing: border-box;
        }
        .ph-panel-value .ph-status-wrap {
            display: block;
        }
    </style>

    <script>
    (function () {
        const ajaxUrl = <?= json_encode($ajaxUrl) ?>;
        const statusNonce = <?= json_encode($statusNonce) ?>;
        const fieldNonce = <?= json_encode($fieldNonce) ?>;
        const STATUS_OPTIONS = <?= json_encode($statusOptionsJs) ?>;
        const PANEL_FIELDS = <?= json_encode($panelFields) ?>;
        const PK_COLUMNS = <?= json_encode($pkColumns) ?>;

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
                    tr.querySelectorAll('.ph-cell[data-field="' + col.key + '"]').forEach(function (td) {
                        td.dataset.raw = newValue;
                        td.classList.toggle('ph-empty', newValue === '');
                        td.title = newValue;
                        td.innerHTML = renderValueHtml(col, newValue);
                    });
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

        function openPanel(tr) {
            const table = tr.dataset.table;
            const pk = tr.dataset.pk;
            const pkValue = tr.dataset.pkValue;
            const fields = PANEL_FIELDS[table] || [];

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
                } else {
                    rawValue = td ? (td.dataset.raw !== undefined ? td.dataset.raw : td.textContent.trim()) : '';
                }

                if (col.type === 'title' && !titleText) titleText = rawValue;

                panelBody.appendChild(buildPanelField(tr, table, pk, pkValue, col, rawValue));
            });

            panelTitle.textContent = titleText || '(untitled)';
            panel.dataset.table = table;
            panel.dataset.pkValue = String(pkValue);

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

        // --- Properties view tabs: Default (all) / Cancelled (status 0) ---
        const propTabs = document.querySelectorAll('#ph-properties-tabs .ph-tab');
        const propSection = document.getElementById('ph-properties-section');
        const propCount = propSection ? propSection.querySelector('.ph-count[data-count-for="pm_prop_hub"]') : null;

        propTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                propTabs.forEach(function (t) { t.classList.remove('ph-tab-active'); });
                tab.classList.add('ph-tab-active');

                const filter = tab.dataset.filter;
                const rows = propSection.querySelectorAll('.ph-row');
                let visible = 0;

                rows.forEach(function (tr) {
                    const isCancelled = tr.dataset.status === '0';
                    const show = filter === 'all' ? true : isCancelled;
                    tr.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                if (propCount) {
                    propCount.textContent = visible + (visible === 1 ? ' row' : ' rows')
                        + (filter === 'cancelled' ? ' (cancelled)' : '');
                }
            });
        });
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