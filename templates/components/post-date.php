<?php if (get_the_date()) : ?>
    <div class="post-date">
        <span class="date-value">
            <?php 
            // Get time components
            $time = get_the_time('g:i a'); // 12-hour format with am/pm
            $date = get_the_date('F j, Y');
            
            // Display date and time
            echo "sys.time: " . $date . " @ " . $time;

            // Session info wrapped in a class for hiding on small screens
            echo '<span class="session-info hide-sm"> [session_' . substr(md5(get_the_ID() . get_the_title()), 0, 6) . ']</span>';
            ?>
        </span>
    </div>
<?php endif; ?>
