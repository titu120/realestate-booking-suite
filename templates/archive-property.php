<?php
/**
 * Archive Property Template
 * 
 * @package RealEstate_Booking_Suite
 */

get_header();
?>

<div class="container" style="padding: 20px;">
    <h1>Properties Archive</h1>
    
    <?php if (have_posts()) : ?>
        <div class="properties-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <?php while (have_posts()) : the_post(); ?>
                <div class="property-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3><?php the_title(); ?></h3>
                    <p><strong>Price:</strong> $<?php echo get_post_meta(get_the_ID(), '_property_price', true); ?></p>
                    <p><strong>Bedrooms:</strong> <?php echo get_post_meta(get_the_ID(), '_property_bedrooms', true); ?></p>
                    <p><strong>Bathrooms:</strong> <?php echo get_post_meta(get_the_ID(), '_property_bathrooms', true); ?></p>
                    <a href="<?php the_permalink(); ?>" style="background: #007cba; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        the_posts_pagination(array(
            'prev_text' => 'Previous',
            'next_text' => 'Next',
        ));
        ?>
    <?php else : ?>
        <p>No properties found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
