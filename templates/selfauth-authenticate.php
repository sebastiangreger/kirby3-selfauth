<html dir="ltr" lang="en"><head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Login</title>

  <link nonce="<?= kirby()->nonce() ?>" rel="stylesheet" href="<?= kirby()->url('media') . '/panel/' . kirby()->versionHash() ?>/css/app.css">
</head>
<body>
<div class="k-panel">
	<main class="k-panel-view">
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
			
		</div>
	</main>
</div>

</body></html>
