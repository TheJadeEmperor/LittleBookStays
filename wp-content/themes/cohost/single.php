<?php
include('header.php');

function get_previous_post_date($format = 'm/d/Y') {
    $previous_post = get_previous_post();
    
    if ($previous_post) {
        // Get the previous post's date
        $previous_post_date = get_the_date($format, $previous_post->ID);
        return $previous_post_date;
    }

    // Return empty string if there is no previous post
    return '';
}

function get_next_post_date($format = 'm/d/Y') {
    $next_post = get_next_post();
    
    if ($next_post) {
        // Get the previous post's date
        $next_post_date = get_the_date($format, $next_post->ID);
        return $next_post_date;
    }

    // Return empty string if there is no previous post
    return '';
}

?>

<div id="main" class="alt">
<section id="one">
<div class="inner">
    
    <?php
    // Check if there are posts
    if ( have_posts() ) :
        // Loop through the posts
        while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <!-- Post Title -->
                <h1 class="post-title"><?php the_title(); ?></h1>

                <!-- Post Meta Information (e.g., Date, Author) -->
                <div class="post-meta" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5em 1.5em;">
                    <div>
                        <span>Posted on: <?php echo get_the_date(); ?></span>
                        <span> by <a href="#upsell"><?php the_author(); ?></a></span>
                    </div>

                    <!-- Previous / Next post (top) -->
                    <nav class="post-navigation-top" style="white-space: nowrap;">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        if ($prev_post) : ?>
                            <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" title="<?php echo esc_attr(get_the_title($prev_post)); ?>">&laquo; Previous Post</a>
                        <?php endif;
                        if ($prev_post && $next_post) : ?>
                            <span style="margin: 0 0.5em;">|</span>
                        <?php endif;
                        if ($next_post) : ?>
                            <a href="<?php echo esc_url(get_permalink($next_post)); ?>" title="<?php echo esc_attr(get_the_title($next_post)); ?>">Next Post &raquo;</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <br /><br />

                <!-- Featured Image -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <!-- Post Content -->
                <div class="post-content">
                    <?php the_content(); ?>
                </div>

                <!-- Post Tags -->
                <div class="post-tags">
                    <?php the_tags('<span>Tags: </span>', ', ', ''); ?>
                </div>
            </article>

        <?php endwhile; // End of the loop.
        endif;
        ?>

        <p>&nbsp;</p>
 
    <footer class="entry-footer">
        <nav class="post-navigation" style="display: flex; justify-content: space-between; padding: 1em 0; border-top: 1px solid #ddd;">
            <div class="prev-post" style="flex: 1; text-align: left;">
                <?php 
                previous_post_link('%link', '« Previous Post: %title ('.get_previous_post_date().')'); 
                ?>
            </div>
            <div class="next-post" style="flex: 1; text-align: right;">
                <?php 
                next_post_link('%link', 'Next Post: %title ('.get_next_post_date().') »' ); 
                ?>
            </div>
        </nav>


        <div class="all-posts-link" style="text-align: center; margin-top: 20px;">
            <a href="/blog" class="button">Back to All Posts</a>
 
            </a>
        </div>
    </footer>

        </div>
    </section>
</div>



<?php
include('footer.php');
?>