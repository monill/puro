<?php
/**
 * Main Layout Template
 * Use this template for all pages
 */

// Variables that can be passed to this template:
// $title - Page title
// $description - Meta description
// $body_class - Additional CSS classes for body
// $extra_css - Array of extra CSS files
// $extra_js_head - Array of extra JS files for head
// $extra_js - Array of extra JS files for bottom
// $show_header - Show/hide header (default: true)
// $show_footer - Show/hide footer (default: true)
// $container_class - Additional CSS classes for main container
// $page_class - Additional CSS classes for page content

// Default values
$show_header = $show_header ?? true;
$show_footer = $show_footer ?? true;
$container_class = $container_class ?? '';
$page_class = $page_class ?? '';

// Include header
if ($show_header) {
    include template_path('layout/header');
}

// Page content
if (!empty($page_class)): ?>
<div class="page-content <?= $page_class ?>">
<?php endif; ?>

<!-- Page content goes here -->
<?= $content ?? '' ?>

<?php if (!empty($page_class)): ?>
</div>
<?php endif; ?>

<!-- Include footer -->
<?php if ($show_footer): ?>
    <?php include template_path('layout/footer'); ?>
<?php endif; ?>
