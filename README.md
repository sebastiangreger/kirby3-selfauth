# Kirby3 Selfauth

A modified version of [Selfauth](https://github.com/inklings-io/selfauth) as a Kirby 3 plugin. Provides self-hosted authentication for the IndieAuth protocol ([read more](https://github.com/Inklings-io/selfauth/)).

Hacky. Use at own risk. Issues/PRs welcome.

Alternatively, you may set up IndieAuth using a third-party service, as described in this [Cookbook recipe](https://getkirby.com/docs/cookbook/integrations/indieauth).

## Adaptations

While the original Selfauth is based on using a dedicated password-based login, this version relies on the Kirby user login.

- The configuration routine (from setup.php) has been reduced to the essentials needed for setting up with Kirby: no setting of a password, no auto-creation of a config.php file
- Instead of the config.php file, configuration is stored in the config options at `site/config/config.php`
- When not logged in, only verification requests by other clients are processed; the later parts of the code (anything related to login/authentication) are only executed after logging in to Kirby
- Internally, the Kirby user ID is used instead of a password; this allows to tie the configuration to one user only (i.e. the user who runs the initial setup; other users on the same site can not use IndieAuth)
- No authentication password has to be entered when authenticating a client (since the user is already identified via Kirby); the password field in the form has been hidden and carries a dummy text, to minimize rewrite need of the application logic
- Minor changes to some error messages
- Removed some redundancies/checks for old PHP versions, as Kirby always runs on PHP 7

## Installation and setup

Download and copy this repository to `/site/plugins/kirby3-selfauth`.

Add `<?= selfauthEndpoint() ?>` to your template's HTML &lt;head&gt; (often located at `site/snippets/header.php` or similar).

Visit `https://your-domain.tld/auth-setup` in your browser, log in if not already logged in, and copy the output into your options array at `site/config/config.php`.

Go to https://indieauth.com/ and enter your website URL to the "Try it" field. Your browser will lead you through the authentication flow, and return debug info or a "You Successfully Authenticated!" page.

## Options

The plugin can be configured with optional settings in your `site/config/config.php`.

### Authentication endpoint

To change the URL of the authentication endpoint (default is `https://domain.tld/auth`), add the following setting and change the string as desired:

```php
'sgkirby.selfauth.endpoint' => 'auth',
```

The URL of the setup URL changes accordingly (it always is the value of `sgkirby.selfauth.endpoint` plus `-setup`).

## Credits

This is an adaptation of the brilliantly simplistic [Selfauth](https://github.com/inklings-io/selfauth), hence main credit goes to its [contributors](https://github.com/Inklings-io/selfauth/graphs/contributors). This does not include any bugs I may have introduced ...they are mine alone.

## License

Kirby 3 Selfauth is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Copyright © 2020 [Sebastian Greger](https://sebastiangreger.net)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
