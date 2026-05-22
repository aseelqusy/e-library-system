<?php
$title = $title ?? 'Contact Us';
View::includeLayout('header', ['title' => $title]);
?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>

    <section class="section">
        <div class="container contact-layout">
            <div class="contact-info glass-card">
                <h1 class="text-gradient">Contact Us</h1>
                <p class="text-muted">Have a question, suggestion, or issue? Send a message and we will respond as soon as possible.</p>
                <div class="contact-meta">
                    <div>
                        <h4>Email</h4>
                        <a href="mailto:support@luminara-library.com">support@luminara-library.com</a>
                    </div>
                    <div>
                        <h4>Location</h4>
                        <p>Library Services, Knowledge District</p>
                    </div>
                    <div>
                        <h4>Follow</h4>
                        <p>
                            <a href="#" aria-label="Twitter">Twitter</a> ·
                            <a href="#" aria-label="Instagram">Instagram</a> ·
                            <a href="#" aria-label="LinkedIn">LinkedIn</a>
                        </p>
                    </div>
                </div>
            </div>

				<div class="contact-form-wrapper">
					<form method="POST" action="<?= url('contact') ?>" data-validate>
						<?= Csrf::field() ?>
						<div class="form-group">
							<label class="form-label" for="contact-name">Name</label>
							<input id="contact-name" name="name" class="form-control" required data-rules="required|min:2" data-label="Name" placeholder="Your full name">
						</div>
						<div class="form-group">
							<label class="form-label" for="contact-email">Email</label>
							<input id="contact-email" name="email" type="email" class="form-control" required data-rules="required|email" data-label="Email" placeholder="you@example.com">
						</div>
						<div class="form-group">
							<label class="form-label" for="contact-subject">Subject</label>
							<input id="contact-subject" name="subject" class="form-control" required data-rules="required|min:3" data-label="Subject" placeholder="How can we help?">
						</div>
						<div class="form-group">
							<label class="form-label" for="contact-message">Message</label>
							<textarea id="contact-message" name="message" class="form-control" rows="5" required data-rules="required|min:10" data-label="Message" placeholder="Write your message here..."></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Send Message</button>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>

<?php View::includeLayout('footer'); ?>

