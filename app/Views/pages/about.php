<?php
$title = $title ?? 'About Us';
$stats = is_array($stats ?? null) ? $stats : [];
$booksCount = (int)($stats['books'] ?? 0);
$membersCount = (int)($stats['members'] ?? 0);
$borrowsCount = (int)($stats['borrows'] ?? 0);
$reviewsCount = (int)($stats['reviews'] ?? 0);
View::includeLayout('header', ['title' => $title]);
?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="padding-top: var(--navbar-height);">
	<!-- Floating decorations -->
	<div class="floating-decorations" aria-hidden="true"></div>

	<section class="section">
		<div class="container">
			<div class="section-header">
				<h1 class="text-gradient" >About <?= e(APP_NAME) ?></h1>
			</div>
			<p class="text-muted about-lead">
				We build a digital-first library experience that helps readers discover, borrow, and now purchase books in one place.
			</p>
		</div>
	</section>

	<section class="section" style="padding-top:0;">
		<div class="container about-grid">
			<article class="glass-card about-block">
				<h3>Our Mission</h3>
				<p>Make reading accessible through a clean, fast, and inclusive library platform for every learner.</p>
			</article>
			<article class="glass-card about-block">
				<h3>Our Vision</h3>
				<p>Become the most trusted community-driven library platform for modern education and lifelong curiosity.</p>
			</article>
			<article class="glass-card about-block">
				<h3>Platform Focus</h3>
				<p>Reliable catalog access, secure user accounts, responsive interfaces, and meaningful recommendations.</p>
			</article>
		</div>
	</section>

	<section class="section" style="padding-top:0;">
		<div class="container">
			<div class="section-header">
				<h2 class="text-gradient">By the Numbers</h2>
			</div>
			<div class="kpi-grid">
				<div class="kpi-card glass-card">
					<div class="kpi-icon purple">📚</div>
					<div>
						<div class="kpi-value" data-count="<?= $booksCount ?>"><?= $booksCount ?></div>
						<div class="kpi-label">Books</div>
					</div>
				</div>
				<div class="kpi-card glass-card">
					<div class="kpi-icon cyan">👥</div>
					<div>
						<div class="kpi-value" data-count="<?= $membersCount ?>"><?= $membersCount ?></div>
						<div class="kpi-label">Members</div>
					</div>
				</div>
				<div class="kpi-card glass-card">
					<div class="kpi-icon amber">📖</div>
					<div>
						<div class="kpi-value" data-count="<?= $borrowsCount ?>"><?= $borrowsCount ?></div>
						<div class="kpi-label">Borrows</div>
					</div>
				</div>
				<div class="kpi-card glass-card">
					<div class="kpi-icon red">⭐</div>
					<div>
						<div class="kpi-value" data-count="<?= $reviewsCount ?>"><?= $reviewsCount ?></div>
						<div class="kpi-label">Reviews</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="section text-center" style="padding-top:0;">
		<div class="container container-md glass-card about-cta">
			<h2 class="text-gradient">Join the Reading Community</h2>
			<p class="text-muted">Create your account and get personalized recommendations, borrowing tools, and curated collections.</p>
			<div class="hero-actions" style="justify-content:center;">
				<?php if (!Auth::check()): ?>
					<a href="<?= url('register') ?>" class="btn btn-primary">Create Account</a>
				<?php endif; ?>
				<a href="<?= url('catalog') ?>" class="btn btn-secondary">Browse Catalog</a>
			</div>
		</div>
	</section>
</div>

<?php View::includeLayout('footer'); ?>

