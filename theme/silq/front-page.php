<?php
/**
 * Homepage — hard-coded for speed. Latest news is dynamic.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();

$featured = array(
	array(
		'slug'  => 'company-formation',
		'title' => 'Company Formation',
		'text'  => 'Choosing the right structure, registering with the DBD and staying compliant after day one.',
		'img'   => 1509,
	),
	array(
		'slug'  => 'foreign-investment-market-entry',
		'title' => 'Foreign Investment & BOI',
		'text'  => 'BOI promotion, the Foreign Business Act and the options open to foreign owners.',
		'img'   => 1110,
	),
	array(
		'slug'  => 'real-estate',
		'title' => 'Real Estate & Property',
		'text'  => 'Buying, leasing and holding property, and what foreigners can and cannot own.',
		'img'   => 930,
	),
	array(
		'slug'  => 'visas-work-permits',
		'title' => 'Visas & Work Permits',
		'text'  => 'Business visas, work permits, LTR and DTV, for you, your team and your family.',
		'img'   => 1474,
	),
	array(
		'slug'  => 'dispute-resolution',
		'title' => 'Dispute Resolution',
		'text'  => 'Negotiation, mediation and the Thai courts, when a deal or relationship goes wrong.',
		'img'   => 849,
	),
);

$values = array(
	array( 'S', 'Service', 'Delivering exceptional legal support with a client-first approach, ensuring every case is handled with dedication and care.' ),
	array( 'I', 'Integrity', 'Upholding the highest ethical standards in every aspect of legal practice, fostering trust and reliability.' ),
	array( 'L', 'Leadership', 'Guiding clients through complex legal challenges with expertise, confidence, and innovative solutions.' ),
	array( 'Q', 'Quality', 'Providing top-tier legal representation and outcomes, driven by precision, excellence, and attention to detail.' ),
);

$insights = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
$founder = silq_partners()[0];
$contact = silq_contact();
?>

<section class="hero">
	<?php
	echo silq_img(
		SILQ_HERO_ID,
		'full',
		array(
			'class'         => 'hero__image',
			'alt'           => '',
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'sizes'         => '100vw',
		)
	); // phpcs:ignore
	?>
	<div class="wrap hero__content">
		<p class="eyebrow eyebrow--light">Real Advice, Real Results</p>
		<h1 class="hero__title">Clear legal advice for doing business and living in Thailand</h1>
		<p class="hero__lead">SILQ is a team of Thai and international lawyers. We help business owners, investors and families set up, buy, build and resolve problems in Thailand, and we explain each step in plain English or Thai.</p>
		<p class="hero__actions">
			<a class="button button--light" href="<?php echo esc_url( home_url( '/our-services/' ) ); ?>">How we can help <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a>
			<a class="link-light" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Speak to a lawyer</a>
		</p>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<header class="section-head">
			<p class="eyebrow">Services</p>
			<h2>Where clients most often need us</h2>
			<p class="section-head__lead">Most questions we hear fall into one of these five areas. Each page explains how the law works, what to watch for, and how we can help.</p>
		</header>

		<ul class="feature-grid">
			<?php foreach ( $featured as $i => $f ) : ?>
				<li class="feature-card<?php echo 0 === $i ? ' feature-card--wide' : ''; ?>">
					<a href="<?php echo esc_url( silq_practice_url( $f['slug'] ) ); ?>">
						<?php echo silq_img( $f['img'], 'large', array( 'alt' => '', 'class' => 'feature-card__image', 'sizes' => '(min-width: 64rem) 40vw, 100vw' ) ); // phpcs:ignore ?>
						<span class="feature-card__body">
							<span class="feature-card__title"><?php echo esc_html( $f['title'] ); ?></span>
							<span class="feature-card__text"><?php echo esc_html( $f['text'] ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section section--mist">
	<div class="wrap">
		<header class="section-head section-head--row">
			<div>
				<p class="eyebrow">All services</p>
				<h2>Help with the whole journey, from the first idea to the day-to-day</h2>
			</div>
			<a class="text-link" href="<?php echo esc_url( home_url( '/our-services/' ) ); ?>">See all services <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a>
		</header>
		<div class="practice-index">
			<?php foreach ( silq_practices() as $group => $items ) : ?>
				<div class="practice-index__group">
					<h3 class="practice-index__heading"><?php echo esc_html( $group ); ?></h3>
					<ul>
						<?php foreach ( $items as $p ) : ?>
							<li><a href="<?php echo esc_url( silq_practice_url( $p['slug'] ) ); ?>"><?php echo esc_html( $p['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<header class="section-head">
			<p class="eyebrow">Our values</p>
			<h2>SILQ is best described through an acronym</h2>
		</header>
		<ul class="values">
			<?php foreach ( $values as list( $letter, $word, $text ) ) : ?>
				<li class="value">
					<span class="value__letter" aria-hidden="true"><?php echo esc_html( $letter ); ?></span>
					<span class="value__word"><?php echo esc_html( $word ); ?></span>
					<p class="value__text"><?php echo esc_html( $text ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php if ( $insights->have_posts() ) : ?>
<section class="section section--mist">
	<div class="wrap">
		<header class="section-head section-head--row">
			<div>
				<p class="eyebrow">News &amp; updates</p>
				<h2>What is changing in Thai law</h2>
			</div>
			<a class="text-link" href="<?php echo esc_url( home_url( '/news-and-updates/' ) ); ?>">All news &amp; updates <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a>
		</header>
		<div class="insight-grid">
			<?php
			$n = 0;
			while ( $insights->have_posts() ) :
				$insights->the_post();
				get_template_part( 'template-parts/card', null, array( 'lead' => 0 === $n ) );
				$n++;
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section">
	<div class="wrap founder">
		<?php echo silq_img( $founder['img'], 'large', array( 'alt' => $founder['name'], 'class' => 'founder__image', 'sizes' => '(min-width: 52rem) 38vw, 100vw' ) ); // phpcs:ignore ?>
		<div>
			<p class="eyebrow">Our founder</p>
			<h2><?php echo esc_html( $founder['name'] ); ?></h2>
			<p class="founder__role"><?php echo esc_html( $founder['role'] ); ?></p>
			<p>Penny founded SILQ to give business owners and investors straightforward advice on Thai law. Her practice covers dispute resolution, corporate governance, foreign investment structures and regulatory compliance, with a particular focus on nominee structures.</p>
			<p><a class="text-link" href="<?php echo esc_url( home_url( $founder['url'] ) ); ?>">Read Penny's profile <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a>
			&nbsp;&nbsp;<a class="text-link" href="<?php echo esc_url( home_url( '/our-team/' ) ); ?>">Meet the team <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a></p>
		</div>
	</div>
</section>

<section class="section section--mist">
	<div class="wrap contact-band">
		<div>
			<h2>Talk to us</h2>
			<p>Not sure where to start? Tell us what you are planning or what has happened. We will explain your options in plain language and reply within one business day.</p>
			<div class="offices">
				<?php foreach ( $contact['offices'] as $o ) : ?>
					<div>
						<h3><?php echo esc_html( $o['name'] ); ?></h3>
						<p><?php echo esc_html( $o['address'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div>
			<?php silq_contact_list( 'contact-band__list' ); ?>
			<p><a class="button" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact us <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a></p>
		</div>
	</div>
</section>

<?php
get_footer();
