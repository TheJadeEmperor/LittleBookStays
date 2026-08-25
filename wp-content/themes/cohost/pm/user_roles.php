<?php
// ============================================================
// Custom User Roles — LBS
// Add this to functions.php in the cohost theme
//
// Levels:
//   Administrator (built-in) — full access, no changes needed
//   Property Manager   — same as admin on content/tools, but can't touch
//                      plugins, themes, or users
//   Power Dialer       — can view everything, but read-only (no posts),
//                      same plugin/theme/user restrictions as VA
// ============================================================

add_action('after_switch_theme', 'lbs_register_custom_roles');

function lbs_register_custom_roles() {

    // --- VA ==> Property Manager ---
    add_role('lbs_va', 'Property Manager', [
        'read'                    => true,
        'lbs_view_admin_pages'    => true, // custom cap — gates our internal tool pages (Onboarding, Prop Hub, etc.)

        // Full content editing, same as an Editor
        'edit_posts'              => true,
        'edit_others_posts'       => true,
        'edit_published_posts'    => true,
        'publish_posts'           => true,
        'delete_posts'            => true,
        'delete_others_posts'     => true,
        'delete_published_posts'  => true,
        'edit_pages'              => true,
        'edit_others_pages'       => true,
        'edit_published_pages'    => true,
        'publish_pages'           => true,
        'delete_pages'            => true, 
        'delete_published_pages'  => true,
        'upload_files'            => true,
        'moderate_comments'       => true,
        'manage_categories'       => true,
        'manage_links'            => true,
        'edit_theme_options'      => true, // widgets/menus — NOT full theme editing

        // Deliberately NOT granted:
        // install_plugins, activate_plugins, edit_plugins, delete_plugins, update_plugins
        // install_themes, switch_themes, edit_themes, delete_themes, update_themes
        // create_users, edit_users, delete_users, promote_users, list_users
    ]);

    // --- Salesperson ==> Power Dialer ---
    add_role('lbs_salesperson', 'Power Dialer', [
        'read'                  => true,
        'lbs_view_admin_pages'  => true, // can see all the same internal tool pages
        'upload_files'          => true,

        // No edit_posts / publish_posts / edit_pages etc. — can't create or
        // edit Posts or Pages at all.
        // Same plugin/theme/user restrictions as VA (nothing granted below).
    ]);
}

// --- Clean up on theme switch away ---

add_action('switch_theme', 'lbs_remove_custom_roles');

function lbs_remove_custom_roles() {
    remove_role('lbs_va');
    remove_role('lbs_salesperson');
}


// --- Make sure Administrators always have the shared "view admin pages"
// capability too, so page checks below include admins automatically ---
add_action('after_switch_theme', 'lbs_grant_admin_custom_caps');

function lbs_grant_admin_custom_caps() {
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('lbs_view_admin_pages');
    }
}