<?php
require_once get_stylesheet_directory() . '/pm/generate_statement.php';
require_once get_stylesheet_directory() . '/pm/prop_hub.php';
require_once get_stylesheet_directory() . '/pm/power_dialer.php';
require_once get_stylesheet_directory() . '/pm/user_roles.php';

// --- Hide the "WordPress X.X is available! Please update now." admin nag ---
// Note: this only hides the visual notice, it does not stop WP from
// checking for or installing updates. The site is still on the old version.
add_action( 'admin_head', 'lbs_hide_update_nag' );
function lbs_hide_update_nag() {
    echo '<style>.update-nag{display:none !important;}</style>';
}


// --- Register settings page ---
add_action('admin_menu', 'lbs_add_settings_page');

function lbs_add_settings_page() {
    add_options_page(
        'LBS Settings',
        'LBS Settings',
        'manage_options',
        'lbs-settings',
        'lbs_render_settings_page'
    );
}

// --- Register the option fields ---
add_action('admin_init', 'lbs_register_settings');

function lbs_register_settings() {
    register_setting('lbs_settings_group', 'lbs_phone', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('lbs_settings_group', 'lbs_calendly', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('lbs_settings_group', 'lbs_email', ['sanitize_callback' => 'sanitize_email']);
}

// --- Render the page ---
function lbs_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>LBS Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('lbs_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="lbs_phone">Phone</label></th>
                    <td><input type="text" id="lbs_phone" name="lbs_phone"
                        value="<?= esc_attr(get_option('lbs_phone')) ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="lbs_email">Email</label></th>
                    <td><input type="email" id="lbs_email" name="lbs_email"
                        value="<?= esc_attr(get_option('lbs_email')) ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="lbs_calendly">Calendly URL</label></th>
                    <td><input type="text" id="lbs_calendly" name="lbs_calendly"
                        value="<?= esc_attr(get_option('lbs_calendly')) ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}



// ============================================================
// Onboarding Checklist — WordPress admin page version
// Uses $wpdb since onboarding_tasks lives in the same DB as WordPress
// Add this to functions.php in the cohost theme
// ============================================================
//How would you use a custom settings table for variables, for example, a phone number that I can put in the admin page and I can use it anywhere? 



// --- Register the admin menu page ---
add_action('admin_menu', 'lbs_add_admin_pages');

function lbs_add_admin_pages() {
    add_menu_page(
        'Onboarding',
        'Onboarding',
        'lbs_view_admin_pages',
        'lbs-onboarding',
        'lbs_render_onboarding_page',
        'dashicons-yes-alt',
        3
    );
 
    add_menu_page(
        'Standard Operating Procedures', //page_title
        'SOP', //menu_title
        'lbs_view_admin_pages',  //capability
        'lbs-sop', //menu_slug 
        'lbs_sop_page', //function 
        'dashicons-yes-alt', //icon_url
        4  //position 
    );
 

}





function lbs_sop_page () {
    ?>

    <h1>All SOP</h1>
    <p>See our notion pages for Standard Operating Procedures to deal with common scenarios</p>
    <p>&nbsp;</p>


    <p>Notion - <a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/SOP-3c6e540782c18002afc7cd4f1910206c">ALL SOP</a></p>

    <p>Notion - <a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/Goddam-Guests-3c6e540782c1806283f0d932570c0cd1">Goddam Guests</a></p>

    <p>Notion - <a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/Disasters-Unhappy-Guest-2f1e540782c180418a8dec9a9faeff13">Unhappy Guests</a></p>

    <p>Notion - <a target="_BLANK" href="https://habitual-airbus-6d2.notion.site/General-SOP-3c6e540782c1807e8574fa0cf8b66d92">General SOP</a></p>

    <hr> <p>&nbsp;</p>

    <h1>Owner Statements</h1>

    <p><a target="_BLANK" href="https://drive.google.com/drive/folders/16iPNF0a_vttToYR5YfBk5WB40BwZDsGk?usp=sharing">https://drive.google.com/drive/folders/16iPNF0a_vttToYR5YfBk5WB40BwZDsGk?usp=sharing</a></p>

    

    <h2><strong>Instructions - do once a month</strong></h2>
    <ol>
    <li><p>For supplies, look at invoices for the month &amp; add their totals into the supplies section</p></li>
   
    <li><p>For cohost &amp; owner payouts, go to Hospitable calendar and see the RSVP Fee</p>
         
        <p>Cleaning fee is also listed on calendar - keep it the same as always unless if your host tells you otherwises<p>
        <p>Only add VRBO payments & others platforms - not Airbnb<p>
        <p>Put the RSVP Date, RSVP Name &amp; stay fee into the XLS and it will calculate the cohost payout &amp; owner payout<br>
        <a  target="_BLANK"  href="https://docs.google.com/spreadsheets/d/1Q3B047KCA122xPQ3P4bs79O35UESc-Bs/edit?gid=1316093940#gid=1316093940">https://docs.google.com/spreadsheets/d/1Q3B047KCA122xPQ3P4bs79O35UESc-Bs/edit?gid=1316093940#gid=1316093940</a></p>
        <p>Ignore the adjustment column</p>
         
    </li>
     <li><p>For insurance claims, look at the insurance claims for the month</p></li>
      <li><p>IF there are any guest refunds, it will show in Hospitable</p></li>
    </ol>
    


    <?php
}
 
 
 
 


// --- Helper: slugify for anchor ids ---
function lbs_slugify($text) {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// --- Helper: turn any http(s) URLs inside a label into clickable links,
// truncating the displayed text (not the href) if it's over 50 chars ---
function lbs_linkify_label($text) {
    $parts = preg_split('/(https?:\/\/[^\s]+)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1) {
            // Odd indices are the captured URLs. Trim trailing punctuation
            // that's likely sentence punctuation, not part of the URL.
            $url = rtrim($part, '.,);:');
            $display = strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
            $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                . esc_html($display) . '</a>';
        } else {
            $html .= esc_html($part);
        }
    }
    return $html;
}

// --- Helper: render a single task row ---
// Clicking anywhere on the row (except the checkbox, or a link inside the
// label) turns the label into an editable text field. Blurring saves it.
function lbs_render_task($task, $nested = false) {
    $key = esc_attr($task['task_key']);
    $classes = 'task' . ($nested ? ' nested' : '');
    echo '<div class="' . $classes . '" data-key="' . $key . '">';
    echo '<input type="checkbox" id="' . $key . '" data-key="' . $key . '"'
        . ($task['is_checked'] ? ' checked' : '') . '>';
    echo '<span class="task-label-wrap">';
    echo '<span class="task-label' . ($task['is_checked'] ? ' checked' : '') . '" data-key="' . $key . '" data-raw="'
        . esc_attr($task['label']) . '">'
        . lbs_linkify_label($task['label']) . '</span>';
    echo '</span>';
    echo '</div>';
}

// --- Main page render callback ---
function lbs_render_onboarding_page() {
    
    global $wpdb;

    $allTasks = $wpdb->get_results(
        "SELECT task_key, parent_key, section, subsection, label, is_checked
         FROM onboarding_tasks
         ORDER BY sort_order",
        ARRAY_A
    );

    $topLevel = [];
    $childrenOf = [];
    foreach ($allTasks as $task) {
        if ($task['parent_key'] === null) {
            $topLevel[] = $task;
        } else {
            $childrenOf[$task['parent_key']][] = $task;
        }
    }

    // Build TOC
    $toc = [];
    $seenSections = [];
    $seenSubsections = [];
    foreach ($topLevel as $task) {
        $section = $task['section'];
        if ($section !== null && !isset($seenSections[$section])) {
            $seenSections[$section] = true;
            $toc[] = ['level' => 'section', 'label' => $section, 'id' => lbs_slugify($section)];
        }
        $subsection = $task['subsection'];
        if ($subsection !== null) {
            $subKey = $section . '|' . $subsection;
            if (!isset($seenSubsections[$subKey])) {
                $seenSubsections[$subKey] = true;
                $toc[] = [
                    'level' => 'subsection',
                    'label' => $subsection,
                    'id'    => lbs_slugify($section . '-' . $subsection),
                ];
            }
        }
    }

    $totalCount = count($allTasks);
    $checkedCount = 0;
    foreach ($allTasks as $t) {
        if ($t['is_checked']) $checkedCount++;
    }

    $toggleNonce = wp_create_nonce('lbs_onboarding_toggle');
    $labelNonce  = wp_create_nonce('lbs_onboarding_label');
    $ajaxUrl = admin_url('admin-ajax.php');
    ?>
    <div class="wrap">
        <h1>Onboarding Checklist</h1>
        <div class="kc-progress"><?= $checkedCount ?> / <?= $totalCount ?> steps complete</div>

        <?php if (!empty($toc)): ?>
        <nav class="kc-toc" id="toc">
            <div class="kc-toc-title">Table of Contents</div>
            <div class="kc-toc-columns">
                <?php foreach ($toc as $entry): ?>
                    <a class="<?= $entry['level'] === 'subsection' ? 'kc-toc-subsection' : '' ?>"
                       href="#<?= esc_attr($entry['id']) ?>">
                        <?= esc_html($entry['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php endif; ?>

        <div id="kc-task-list">
        <?php
        $currentSection = null;
        $currentSubsection = null;

        foreach ($topLevel as $task):
            if ($task['section'] !== $currentSection) {
                $currentSection = $task['section'];
                $currentSubsection = null;
                echo '<h2 class="kc-section" id="' . esc_attr(lbs_slugify($currentSection)) . '">'
                    . esc_html($currentSection)
                    . ' <a class="kc-back-to-toc" href="#toc">&#8593; TOC</a></h2>';
            }
            if ($task['subsection'] !== $currentSubsection) {
                $currentSubsection = $task['subsection'];
                if ($currentSubsection !== null) {
                    echo '<h3 class="kc-subsection" id="' . esc_attr(lbs_slugify($currentSection . '-' . $currentSubsection)) . '">'
                        . esc_html($currentSubsection) . '</h3>';
                }
            }

            lbs_render_task($task);

            if (!empty($childrenOf[$task['task_key']])) {
                foreach ($childrenOf[$task['task_key']] as $child) {
                    lbs_render_task($child, true);
                }
            }
        endforeach;
        ?>
        </div>
    </div>

    <style>
        .kc-progress { font-size: 13px; color: #777; margin-bottom: 8px; }
        .kc-toc { background: #f7f7f7; border: 1px solid #e5e5e5; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; max-width: 900px; }
        .kc-toc-title { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #888; margin-bottom: 8px; }
        .kc-toc-columns { column-count: 2; column-gap: 28px; }
        .kc-toc a { display: block; font-size: 14px; line-height: 1.7; color: #333; text-decoration: none; break-inside: avoid; }
        .kc-toc a:hover { text-decoration: underline; }
        .kc-toc a.kc-toc-subsection { margin-left: 18px; color: #666; font-size: 13px; }
        #kc-task-list { column-count: 2; column-gap: 36px; max-width: 1200px; }
        .kc-section { font-size: 19px; margin-top: 40px; margin-bottom: 4px; padding-bottom: 6px; border-bottom: 2px solid #333; break-inside: avoid; display: flex; align-items: baseline; gap: 8px; }
        .kc-subsection { font-size: 15px; margin-top: 18px; margin-bottom: 4px; color: #555; text-transform: uppercase; letter-spacing: .03em; break-inside: avoid; }
        .kc-back-to-toc { font-size: 12px; font-weight: normal; text-transform: none; color: #999; text-decoration: none; }
        .task { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; break-inside: avoid; }
        .task.nested { margin-left: 28px; border-bottom: 1px dashed #eee; }
        .task input[type="checkbox"] { width: 17px; height: 17px; cursor: pointer; flex-shrink: 0; }
        .task-label-wrap { flex: 1; min-height: 20px; cursor: text; }
        .task-label { cursor: text; }
        .task-label.checked { text-decoration: line-through; color: #999; }
        .task-label a { color: #2271b1; text-decoration: none; }
        .task-label a:hover { text-decoration: underline; }
        .task-label-input {
            width: 100%;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 2px 6px;
            box-sizing: border-box;
        }
    </style>

    <script>
    (function () {
        const ajaxUrl = <?= json_encode($ajaxUrl) ?>;
        const toggleNonce = <?= json_encode($toggleNonce) ?>;
        const labelNonce = <?= json_encode($labelNonce) ?>;

        // --- Checkbox toggle ---
        document.querySelectorAll('#kc-task-list input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const taskKey = this.dataset.key;
                const checked = this.checked;
                const labelSpan = document.querySelector('.task-label[data-key="' + taskKey + '"]');

                const body = new URLSearchParams();
                body.append('action', 'lbs_toggle_onboarding_task');
                body.append('nonce', toggleNonce);
                body.append('task_key', taskKey);
                body.append('checked', checked ? '1' : '0');

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        labelSpan.classList.toggle('checked', checked);
                    } else {
                        checkbox.checked = !checked;
                        alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                    }
                })
                .catch(function () {
                    checkbox.checked = !checked;
                    alert('Network error - could not save change.');
                });
            });
        });

        // --- Inline label editing ---
        // Clicking the label, or the empty space next to it (.task-label-wrap),
        // swaps the text into an editable input. Blurring saves it.
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Mirrors lbs_linkify_label() in PHP: turns URLs into clickable
        // links and truncates the displayed text (not the href) over 50 chars.
        function linkifyLabel(text) {
            const parts = text.split(/(https?:\/\/[^\s]+)/i);
            return parts.map(function (part, i) {
                if (i % 2 === 1) {
                    const url = part.replace(/[.,);:]+$/, '');
                    const display = url.length > 50 ? url.slice(0, 47) + '...' : url;
                    return '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer">'
                        + escapeHtml(display) + '</a>';
                }
                return escapeHtml(part);
            }).join('');
        }

        document.querySelectorAll('.task-label-wrap').forEach(function (wrap) {
            wrap.addEventListener('click', function (e) {
                if (e.target.closest('a')) return; // let the link open normally

                const labelSpan = wrap.querySelector('.task-label');
                if (!labelSpan) return; // already editing

                const taskKey = labelSpan.dataset.key;
                const currentText = labelSpan.dataset.raw; // full, untruncated text
                const wasChecked = labelSpan.classList.contains('checked');

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'task-label-input';
                input.value = currentText;
                input.dataset.key = taskKey;

                wrap.replaceChild(input, labelSpan);
                input.focus();
                input.select();

                function saveAndRevert() {
                    const newText = input.value.trim();

                    const newLabel = document.createElement('span');
                    newLabel.className = 'task-label' + (wasChecked ? ' checked' : '');
                    newLabel.dataset.key = taskKey;
                    newLabel.dataset.raw = newText;
                    newLabel.innerHTML = linkifyLabel(newText);
                    wrap.replaceChild(newLabel, input);

                    if (newText === currentText) return; // nothing changed, skip the request

                    const body = new URLSearchParams();
                    body.append('action', 'lbs_update_onboarding_label');
                    body.append('nonce', labelNonce);
                    body.append('task_key', taskKey);
                    body.append('label', newText);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            newLabel.dataset.raw = currentText;
                            newLabel.innerHTML = linkifyLabel(currentText); // revert on failure
                            alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        }
                    })
                    .catch(function () {
                        newLabel.dataset.raw = currentText;
                        newLabel.innerHTML = linkifyLabel(currentText);
                        alert('Network error - could not save change.');
                    });
                }

                input.addEventListener('blur', saveAndRevert);
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        input.blur();
                    } else if (e.key === 'Escape') {
                        input.value = currentText;
                        input.blur();
                    }
                });
            }, { once: false });
        });
    })();
    </script>
    <?php
}

// --- AJAX handler: toggle a checkbox ---
add_action('wp_ajax_lbs_toggle_onboarding_task', 'lbs_toggle_onboarding_task');

function lbs_toggle_onboarding_task() {
    check_ajax_referer('lbs_onboarding_toggle', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $taskKey = isset($_POST['task_key']) ? sanitize_text_field($_POST['task_key']) : '';
    $checked = isset($_POST['checked']) ? (int) (bool) $_POST['checked'] : null;

    if ($taskKey === '' || $checked === null) {
        wp_send_json_error(['error' => 'Missing task_key or checked value'], 400);
    }

    global $wpdb;
    $wpdb->update(
        'onboarding_tasks',
        ['is_checked' => $checked],
        ['task_key' => $taskKey],
        ['%d'],
        ['%s']
    );

    wp_send_json_success(['task_key' => $taskKey, 'checked' => (bool) $checked]);
}

// --- AJAX handler: save an edited label ---
add_action('wp_ajax_lbs_update_onboarding_label', 'lbs_update_onboarding_label');

function lbs_update_onboarding_label() {
    check_ajax_referer('lbs_onboarding_label', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $taskKey = isset($_POST['task_key']) ? sanitize_text_field($_POST['task_key']) : '';
    $label   = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';

    if ($taskKey === '' || $label === '') {
        wp_send_json_error(['error' => 'Missing task_key or label'], 400);
    }

    global $wpdb;
    $wpdb->update(
        'onboarding_tasks',
        ['label' => $label],
        ['task_key' => $taskKey],
        ['%s'],
        ['%s']
    );

    wp_send_json_success(['task_key' => $taskKey, 'label' => $label]);
}