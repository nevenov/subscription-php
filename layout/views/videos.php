        <?php if ($canWatchVideos): ?>
        <div class="embed-responsive embed-responsive-16by9 mb-5">
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/eMR_IgXNs7E"
                allowfullscreen></iframe>
        </div>
        
        <?php else: ?>

        <p>In order to watch videos you must be logged in and have subscription valid. Go to see pricing and subscribe
            <a href="?action=pricing">subscribe</a></p>
        
        <?php endif; ?>