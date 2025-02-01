<!-- Page Load Info Module -->
<div id="page-load-info" class="terminal-window collapsed">
    <header>
        <div class="button green"></div>
        <div class="button yellow"></div>
        <div id="close-stats" class="button red"></div>
        <div class="terminal-title">Sudo://Bash-CLI Command Line Interface</div>

    </header>
    <section class="terminal">
        <div id="load-stats" class="row">
            <div class="col-4">
                <p id="file-size">File Size: <span id="file-size-value" class="stat-value">Loading...</span></p>
            </div>
            <div class="col-4">
                <p id="dom-load-time">DOM Load Time: <span id="dom-load-time-value" class="stat-value">Loading...</span></p>
            </div>
            <div class="col-4">
                <p id="page-load-time">Page Load Time: <span id="page-load-time-value" class="stat-value">Loading...</span></p>
            </div>
        </div>
        <div id="events-log" class="col-12">
            <h4>Event Log</h4>
            <ul id="events-list" class="events-list"></ul>
        </div>
    </section>
</div>
<div id="toggle-stats">
    <svg width="24" height="24">
        <line x1="5" y1="12" x2="19" y2="12" style="stroke:white;stroke-width:2" />
    </svg>
</div>


<footer>
    <div class="container">
        <div class="footer-content">
            <a href="https://github.com/eboy79" target="_blank" class="github-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/img/github-mark.svg" alt="GitHub">
            </a>
            <a href="mailto:info@mnrdsgn.com" class="footer-email">info@mnrdsgn.com</a>
        </div>
    </div>
    <div class="footer-underline"></div>
    <?php wp_footer(); ?>
</footer>
</body>
</html>
