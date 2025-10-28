<?php
/**
 * Test Single Property Template - SUPER SIMPLE
 */

get_header();
?>

<div style="padding: 20px; background: #f0f0f0;">
    <h1 style="color: red;">SINGLE PROPERTY PAGE WORKS!</h1>
    
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <div style="background: white; padding: 20px; border: 2px solid green; margin: 10px 0;">
                <h2><?php the_title(); ?></h2>
                <p><strong>ID:</strong> <?php echo get_the_ID(); ?></p>
                <p><strong>Price:</strong> $<?php echo get_post_meta(get_the_ID(), '_property_price', true); ?></p>
                <p><strong>Bedrooms:</strong> <?php echo get_post_meta(get_the_ID(), '_property_bedrooms', true); ?></p>
                <p><strong>Bathrooms:</strong> <?php echo get_post_meta(get_the_ID(), '_property_bathrooms', true); ?></p>
                
                <a href="<?php echo home_url('/property/'); ?>" style="background: blue; color: white; padding: 10px; text-decoration: none;">Back to Properties</a>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p>No property found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
