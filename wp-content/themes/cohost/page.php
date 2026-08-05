<?php
include('header.php');

global $post;
$post_slug = $post->post_name;
?>

    <?php 
    if ($post_slug == 'guests') //hard coded pages
        include('guests.php'); 
    else if ($post_slug == 'terms') 
        include('terms.php'); 
    else if ($post_slug == 'privacy') 
        include('privacy.php'); 
    else if ($post_slug == 'blog') 
        include('blog.php'); 
    else {
        if ( have_posts() ) : while ( have_posts() ) : the_post();  
            the_content(); 
        endwhile; endif;  
    } 
    ?>
  
<?php
include('footer.php');
?>