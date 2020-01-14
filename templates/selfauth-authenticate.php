<html dir="ltr" lang="en"><head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Login</title>

  <link nonce="<?= kirby()->nonce() ?>" rel="stylesheet" href="<?= kirby()->url('media') . '/panel/' . kirby()->versionHash() ?>/css/app.css">
</head>
<body>
<div class="k-panel">
	<main class="k-panel-view">
		<div class="k-view k-page-view">
			<header class="k-header" data-editable="true">
				<h1 data-size="huge" class="k-headline">
					<span>Authenticate</span>
				</h1>
			</header>
			<div data-gutter="large" class="k-grid k-sections">
				<div data-width="1/1" class="k-column">
					<section class="k-fields-section k-section k-section-name-main-fields">
						<form method="POST" autocomplete="off" novalidate="novalidate" class="k-form" validate="true">
							<fieldset class="k-fieldset">
								<div class="k-grid">
									
									<div data-width="1/1" class="k-column">
										<p data-size="large" class="" type="headline" width="1/1" style="word-wrap:break-word;">
											You are attempting to login with client <strong><?php echo htmlspecialchars($page->client_id()); ?></strong><?php if (strlen($page->scope()) > 0) : ?>, requesting the following scopes (uncheck any you do not wish to grant):<?php endif; ?>
										</p>
									</div>

									<?php if (strlen($page->scope()) > 0) : ?>

									<div data-width="1/1" class="k-column">
										<div class="k-checkboxes-field k-field k-field-name-categories">
											<header class="k-field-header">
												<label class="k-field-label">Scopes</label>
											</header>
											<div data-theme="field" data-type="checkboxes" class="k-input">
												<span class="k-input-element">
													<ul class="k-checkboxes-input" style="--columns:1;">

														<?php foreach (explode(' ', $page->scope()) as $n => $checkbox) : ?>
														<li>
															<label class="k-checkbox-input">
																<input id="scope_<?php echo $n; ?>" name="scopes[]" type="checkbox" class="k-checkbox-input-native" value="<?php echo htmlspecialchars($checkbox); ?>" checked>
																<span aria-hidden="true" class="k-checkbox-input-icon">
																	<svg width="12" height="10" viewBox="0 0 12 10" xmlns="http://www.w3.org/2000/svg"><path d="M1 5l3.3 3L11 1" stroke-width="2" fill="none" fill-rule="evenodd"></path></svg>
																</span>
																<span class="k-checkbox-input-label"><?php echo $checkbox; ?></span>
															</label>
														</li>
														<?php endforeach; ?>

													</ul>
												</span>
											</div>
										</div>
									</div>

									<?php endif; ?>

									<div data-width="1/1" class="k-column">
										<div class="k-field k-info-field" name="info" type="info" width="1/1">
											<p style="word-wrap:break-word;">
												Logging in as <strong><?php echo htmlspecialchars($page->user_url()); ?></strong>
											</p>
										</div>
									</div>

								</div>
							</fieldset>
							<input type="hidden" name="_csrf" value="<?php echo $page->csrf_code(); ?>" />
							<input type="hidden" name="password" id="password" value="password" />
							<button type="submit" class="" style="color:#efefef;background:#16171a;padding:10px;margin-top:2em;margin-bottom:2em;">
								<span>Authenticate</span>
							</button>

							<fieldset class="k-fieldset">
								<div class="k-grid">
									<div data-width="1/1" class="k-column">
										<div class="k-field k-info-field" name="info" type="info" width="1/1">
											<p style="word-wrap:break-word;font-size:80%;">
												<em>After login you will be redirected to <?php echo htmlspecialchars($page->redirect_uri()); ?></em>
											</p>
										</div>
									</div>
								</div>
							</fieldset>

						</form>
					</section>
				</div>
			</div>
		</div>


		<!--
		<div data-align="center" class="k-view">
			<form method="POST" action="">
				<h1>Authenticate</h1>
				<div>You are attempting to login with client <pre><?php echo htmlspecialchars($page->client_id()); ?></pre></div>
				<?php if (strlen($page->scope()) > 0) : ?>
				<div>It is requesting the following scopes, uncheck any you do not wish to grant:</div>
				<fieldset>
					<legend>Scopes</legend>
					<?php foreach (explode(' ', $page->scope()) as $n => $checkbox) : ?>
					<div>
						<input id="scope_<?php echo $n; ?>" type="checkbox" name="scopes[]" value="<?php echo htmlspecialchars($checkbox); ?>" checked>
						<label for="scope_<?php echo $n; ?>"><?php echo $checkbox; ?></label>
					</div>
					<?php endforeach; ?>
				</fieldset>
				<?php endif; ?>
				<div>After login you will be redirected to  <pre><?php echo htmlspecialchars($page->redirect_uri()); ?></pre></div>
				<div class="form-login">
					<input type="hidden" name="_csrf" value="<?php echo $page->csrf_code(); ?>" />
					<p class="form-line">
						Logging in as:<br />
						<span class="yellow"><?php echo htmlspecialchars($page->user_url()); ?></span>
					</p>
					<div class="form-line">
						<input type="hidden" name="password" id="password" value="password" />
						<input class="submit" type="submit" name="submit" value="Authenticate" />
					</div>
				</div>
			</form>
			-->

		</div>
	</main>
</div>

</body></html>
