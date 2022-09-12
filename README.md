# VerzameldwerkAkismetBundle

## Installation

1. Add the following to your `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:Verzameldwerk/365-vzw-sulu-akismet.git"
        }
    ]
}
```

2. Run the following command in your project's root directory:

```shell
composer require verzameldwerk/akismet-bundle:"dev-master"
```

3. Add the following lines to your `config/bundles.php` file:

```php
HandcraftedInTheAlps\Bundle\SuluResourceBundle\HandcraftedInTheAlpsSuluResourceBundle::class => ['all' => true],
Verzameldwerk\Bundle\AkismetBundle\VerzameldwerkAkismetBundle::class => ['all' => true],
```

4. Create a new `config/routes/verzameldwerk_akismet_admin.yaml` file with the following content:

```yaml
verzameldwerk_akismet_api:
    resource: "@VerzameldwerkAkismetBundle/config/routing_api.yml"
    type: rest
    prefix: /admin/api
```

5. Add the following dependency to your `assets/admin/package.json` file:

```json
{
    "dependencies": {
        "verzameldwerk-akismet-bundle": "file:node_modules/@sulu/vendor/verzameldwerk/akismet-bundle/assets/js"
    }
}
```

6. Add the following line to your `assets/admin/app.js` file:

```javascript
import 'verzameldwerk-akismet-bundle';
```

7. Add the following lines to your `assets/admin/webpack.config.js` file right before returning the `config` object:

```javascript
config.module.rules.unshift({
    test: /\.js$/,
    exclude: /node_modules[/\\](?!(verzameldwerk-akismet-bundle)[/\\])/,
    use: {
        loader: 'babel-loader',
        options: {
            cacheDirectory: true,
            cacheCompression: false,
        },
    },
});
```

8. Update your javascript build using `bin/console sulu:admin:update-build`.
When asked to overwrite your local version of "package.json", answer with no.

9. Update your database schema using `bin/console doctrine:schema:update --force`
or generate a migration using `bin/console doctrine:migrations:diff` if you are using the `DoctrineMigrationsBundle`.

## Configuration

By default, no additional configuration is necessary.

If you want the Akismet `comment-check` request to happen asynchronously, configure the Symfony Messenger component like in the following example:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        failure_transport: failed

        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
            failed: 'doctrine://default?queue_name=failed'
            sync: 'sync://'

        routing:
            Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\SynchronousMessageInterface: sync # this needs to be sync
            Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\AsynchronousMessageInterface: async # this can be either async or sync
```

When using an asynchronous transport, you should have a look at
https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker
to learn about how to consume the messages and setting it up in production.
