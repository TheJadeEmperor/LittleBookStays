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
        ['key' => 'door_lock',      'label' => 'Door lock notes',         'type' => 'title'],
        ['key' => 'door_lock_brand',      'label' => 'Door Lock Brand',         'type' => 'title'],
        ['key' => 'door_default_code',      'label' => 'Default Code',         'type' => 'title'],
        ['key' => 'door_lock_user',      'label' => 'SmartLock user',         'type' => 'title'],
        ['key' => 'door_lock_pw',      'label' => 'SmartLock PW',         'type' => 'title'],
        ['key' => 'lockbox',      'label' => 'Lockbox',         'type' => 'title'],
        ['key' => 'trash',      'label' => 'Trash',         'type' => 'title'],
        ['key' => 'auth_amt',      'label' => 'Auth Amt',         'type' => 'title'],
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

    // Enqueue the JavaScript file with localized data
    wp_enqueue_script(
        'lbs-prop-hub',
        get_stylesheet_directory_uri() . '/pm/lib/prop_hub.js',
        [],
        wp_get_theme()->get('Version'),
        false // Load in <head>, not footer
    );

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

    // Panel layout: for tables with a lot of fields, split into two
    // columns after the given field key (everything after it, including
    // panel-only extras and the relations field, flows into column 2).
    $panelColumnBreaks = [
        'pm_prop_hub' => 'hostco',
    ];

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

    // Localize script data for the JavaScript
    wp_localize_script(
        'lbs-prop-hub',
        'propHubData',
        [
            'ajaxUrl' => $ajaxUrl,
            'statusNonce' => $statusNonce,
            'fieldNonce' => $fieldNonce,
            'linksNonce' => $linksNonce,
            'STATUS_OPTIONS' => lbs_prop_status_options(),
            'PANEL_FIELDS' => $panelFields,
            'PK_COLUMNS' => $pkColumns,
            'ALL_CLEANERS' => $allCleanersJs,
            'ALL_PROPERTIES' => $allPropertiesJs,
            'PANEL_COLUMN_BREAKS' => $panelColumnBreaks,
        ]
    );
    ?>
    <div class="wrap ph-wrap">
        <h1>Property Hub</h1>

        <div class="ph-quicklinks">
            <a class="button" href="<?= esc_url(admin_url('admin.php?page=lbs-onboarding')) ?>">Onboarding checklist</a>
        </div>

        <div id="ph-properties-section">
            <h2 class="ph-section-title">Listings</h2>
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