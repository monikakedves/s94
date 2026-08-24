<?php
/*
Template Name: The Studio
*/
get_header(); 
?>

<div class="studio94-page-layout">
    <div class="studio94-page-main">
        <?php 
        while ( have_posts() ) : the_post();
            the_content();
        endwhile; 
        ?>
    </div>
</div>
<div class="page-body-container" style="display: none;">
    <div class="page-content">
    </div>
</div>

<?php get_footer(); ?>