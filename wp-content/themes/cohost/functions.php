<?php

require_once get_stylesheet_directory() . '/prop_hub.php';

// ============================================================
// Onboarding Checklist — WordPress admin page version
// Uses $wpdb since onboarding_tasks lives in the same DB as WordPress
// Add this to functions.php in the cohost theme
// ============================================================

// --- Register the admin menu page ---
add_action('admin_menu', 'lbs_add_admin_pages');

function lbs_add_admin_pages() {
    add_menu_page(
        'Onboarding',
        'Onboarding',
        'manage_options',
        'lbs-onboarding',
        'lbs_render_onboarding_page',
        'dashicons-yes-alt',
        3
    );
}

// --- Helper: slugify for anchor ids ---
function lbs_slugify($text) {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// --- Helper: render a single task row ---
// Clicking anywhere on the row (except the checkbox itself) turns the
// label into an editable text field. Blurring the field saves it via AJAX.
function lbs_render_task($task, $nested = false) {
    $key = esc_attr($task['task_key']);
    $classes = 'task' . ($nested ? ' nested' : '');
    echo '<div class="' . $classes . '" data-key="' . $key . '">';
    echo '<input type="checkbox" id="' . $key . '" data-key="' . $key . '"'
        . ($task['is_checked'] ? ' checked' : '') . '>';
    echo '<span class="task-label-wrap">';
    echo '<span class="task-label' . ($task['is_checked'] ? ' checked' : '') . '" data-key="' . $key . '">'
        . esc_html($task['label']) . '</span>';
    echo '</span>';
    echo '</div>';
}

// --- Main page render callback ---
function lbs_render_onboarding_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

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
        document.querySelectorAll('.task-label-wrap').forEach(function (wrap) {
            wrap.addEventListener('click', function () {
                const labelSpan = wrap.querySelector('.task-label');
                if (!labelSpan) return; // already editing

                const taskKey = labelSpan.dataset.key;
                const currentText = labelSpan.textContent;
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
                    newLabel.textContent = newText;
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
                            newLabel.textContent = currentText; // revert on failure
                            alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        }
                    })
                    .catch(function () {
                        newLabel.textContent = currentText;
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
