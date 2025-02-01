
<?php if (get_the_date()) : ?>
    <div class="post-date">
        <span class="date-value">
            <?php 
            // Get time components
            $time = get_the_time('g:i a'); // 12-hour format with am/pm
            $date = get_the_date('F j, Y');
            
            // Create a friendly but techy format
            echo "sys.time: " . $date . " @ " . $time;
            // Optional: Add a pseudo-random session ID for extra tech feel
            echo " [session_" . substr(md5(get_the_ID() . get_the_title()), 0, 6) . "]";
            ?>
        </span>
    </div>
<?php endif; ?>