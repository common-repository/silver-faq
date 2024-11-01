<?php


get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php
				// Start the Loop.
				while ( have_posts() ) : the_post();
			?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
				<header class="entry-header">
					<?php echo the_title( '<h1 class="entry-title">', '</h1>' );?>
					<?php 
					$askedBy = get_post_meta( get_the_ID(), '_silver_faq_asked_by_name', true );
					if(!empty($askedBy)):
						echo '<time class="entry-date" datetime="'.get_the_date( 'c' ).'">'.__('Asked On', 'silver_faq-plugin'). ' : ' . get_the_date();
						echo '<span class="author">'.$askedBy.'</span>'; 
					endif;
					?>
				</header>
			</article>
			<div class="entry-meta">
				<?php echo get_post_meta( get_the_ID(), '_silver_faq_answer', true ) ?>
			</div>

					
			<?php 
				endwhile;
			?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
