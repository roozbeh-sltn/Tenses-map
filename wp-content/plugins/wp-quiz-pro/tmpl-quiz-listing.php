<div class="wp-quiz-listing">

	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
	<article <?php post_class( 'latestPost excerpt' ) ?>>

		<?php if ( has_post_thumbnail() ) : ?>
		<div class="featured-thumbnail">
			<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>" rel="nofollow" class="post-image post-image-left">
				<?php the_post_thumbnail() ?>
			</a>
		</div>
		<?php endif; ?>

		<header>

			<?php
				$quiz_type = get_post_meta( get_the_id(), 'quiz_type', true );
				$quiz_type = str_replace( '_quiz', '', $quiz_type );
				$quiz_type = 'fb' === $quiz_type ? 'Facebook' : $quiz_type;
			?>

			<h2 class="title front-view-title">
				<span class="thecategory"><?php echo ucwords( $quiz_type ) ?></span><a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>"><?php the_title() ?></a>
			</h2>

		</header>

	</article>
	<?php endwhile; ?>
</div>
