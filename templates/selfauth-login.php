<html dir="ltr" lang="en"><head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Login</title>

  <link nonce="<?= kirby()->nonce() ?>" rel="stylesheet" href="<?= kirby()->url('media') . '/panel/' . kirby()->versionHash() ?>/css/app.css">
</head>
<body>
<div class="k-panel">
	<main class="k-panel-view">
		<div data-align="center" class="k-view k-login-view">
			<form class="k-login-form" method="post" action="<?= $page->action() ?>">
				<h1 class="k-offscreen">Login to Kirby</h1>
				<?php if ( $page->errormsg()!='' ) : ?>
				<div class="k-login-alert">
					<span><?= $page->errormsg()->html() ?></span>
					<span aria-hidden="true" class="k-icon k-icon-alert">
						<svg viewBox="0 0 16 16"><use xlink:href="#icon-alert"></use></svg>
					</span>
				</div>
				<?php endif; ?>
				<fieldset class="k-fieldset">
					<div class="k-grid">
						<div class="k-column">
							<div class="k-email-field k-field k-field-name-email">
								<header class="k-field-header">
									<label for="490" class="k-field-label">Email <abbr title="The field is required">*</abbr></label>
								</header>
								<div data-theme="field" data-type="email" class="k-input"><!---->
									<span class="k-input-element">
										<input autocomplete="email" autofocus="autofocus" id="490" name="email" placeholder="mail@example.com" required="required" spellcheck="true" type="email" class="k-text-input" value="<?= esc(get('email')) ?>">
									</span>
									<span class="k-input-icon">
										<span aria-hidden="true" class="k-icon k-icon-email">
											<svg viewBox="0 0 16 16"><use xlink:href="#icon-email"></use></svg>
										</span>
									</span>
								</div>
							</div>
						</div>
						<div class="k-column">
							<div class="k-password-field k-field k-field-name-pass">
								<header class="k-field-header">
									<label for="498" class="k-field-label">Password <abbr title="The field is required">*</abbr></label>
								</header>
								<div data-theme="field" data-type="password" class="k-input">
									<span class="k-input-element">
										<input autocomplete="current-pass" id="498" minlength="8" name="pass" required="required" spellcheck="true" type="password" class="k-text-input" value="<?= esc(get('pass')) ?>">
									</span>
									<span class="k-input-icon">
										<span aria-hidden="true" class="k-icon k-icon-key">
											<svg viewBox="0 0 16 16"><use xlink:href="#icon-key"></use></svg>
										</span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
				<div class="k-login-buttons">
					<span class="k-login-checkbox">
					</span>
					<input type="hidden" name="kirbylogin" value="true">
					<button type="submit" class="k-button k-login-button">
						<span aria-hidden="true" class="k-button-icon k-icon k-icon-check">
							<svg viewBox="0 0 16 16"><use xlink:href="#icon-check"></use></svg>
						</span>
						<span class="k-button-text">Login to Kirby</span>
					</button>
				</div>
			</form>
		</div>
	</main>
</div>

  <svg aria-hidden="true" class="k-icons" xmlns="http://www.w3.org/2000/svg" overflow="hidden">
  <defs>
    <symbol id="icon-alert" viewBox="0 0 16 16">
      <path d="M7 6h2v4H7V6zM9 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path>
      <path d="M15 16H1a1.001 1.001 0 0 1-.895-1.448l7-14c.34-.678 1.449-.678 1.789 0l7 14A1 1 0 0 1 15 16zM2.618 14h10.764L8 3.236 2.618 14z"></path>
    </symbol>
    <symbol id="icon-check" viewBox="0 0 16 16">
      <path d="M8 0C3.589 0 0 3.589 0 8s3.589 8 8 8 8-3.589 8-8-3.589-8-8-8zm0 14c-3.309 0-6-2.691-6-6s2.691-6 6-6 6 2.691 6 6-2.691 6-6 6z"></path>
      <path d="M7 11.414L3.586 8 5 6.586l2 2 4-4L12.414 6z"></path>
    </symbol>
    <symbol id="icon-email" viewBox="0 0 16 16">
      <path d="M15 1H1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zm-1 12H2V6.723l5.504 3.145a.998.998 0 0 0 .992 0L14 6.723V13zm0-8.58L8 7.849 2 4.42V3h12v1.42z"></path>
    </symbol>
    <symbol id="icon-key" viewBox="0 0 16 16">
      <path d="M12.7 0L6.5 6.3C6 6.1 5.5 6 5 6c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5c0-.5-.1-1.1-.3-1.6L11 8V6h2V4h2l1-1V0h-3.3zM4.5 12c-.8 0-1.5-.7-1.5-1.5S3.7 9 4.5 9 6 9.7 6 10.5 5.3 12 4.5 12z"></path>
    </symbol>
  </defs>
</svg>

</body></html>
