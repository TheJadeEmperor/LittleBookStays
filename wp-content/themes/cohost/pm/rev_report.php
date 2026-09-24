<?php

function lbs_render_rev_report() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    ?>

    <div class="wrap kc-revenue-report">
        <h1>Revenue Report</h1>
        <div class="kc-progress">Revenue report for presenting to clients</div>

        <nav class="kc-toc" id="toc">
            <div class="kc-toc-title">Overview</div>
            <div class="kc-toc-columns">
                <a href="#part-1">Part 1</a>
                <a href="#part-2">Part 2</a>
                <a href="#part-3">Part 3</a>
            </div>
        </nav>

        <div class="kc-report-content">
            <h2 class="kc-section" id="part-1">
                Revenue Report Part 1
                <a class="kc-back-to-toc" href="#toc">&#8593; TOC</a>
            </h2>
            <p>
                <iframe src="https://drive.google.com/file/d/1kXFV03LSmg8bJRR-jDkNRhMGjq-CYoVf/preview" width="640" height="480" allow="autoplay"></iframe>
            </p>

            <h2 class="kc-section" id="part-2">
                Revenue Report Part 2
                <a class="kc-back-to-toc" href="#toc">&#8593; TOC</a>
            </h2>
            <p>
                <iframe src="https://drive.google.com/file/d/1l5GBYxt10NnDMZtk2j1Rdhqxg0LL8521/preview" width="640" height="480" allow="autoplay"></iframe>
            </p>

            <div class="kc-subsection">Important</div>
            <p>
                Note: The error from the video is because a subscription was not purchased. If the same error occurs, ask your host to buy a subscription from this website. If there's another error, contact the host at Vantage STR.
            </p>

            <h2 class="kc-section" id="part-3">
                Revenue Report Part 3
                <a class="kc-back-to-toc" href="#toc">&#8593; TOC</a>
            </h2>
            <p>
                <iframe src="https://drive.google.com/file/d/1g8J5FyfBSyoA0L5nL98DYYcb3nJ3SzMe/preview" width="640" height="480" allow="autoplay"></iframe>
            </p>
        </div>
    </div>

    <?php
}

?>