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

3. Create a new `config/routes/verzameldwerk_akismet_admin.yaml` file with the following content:

```yaml
verzameldwerk_akismet_api:
    resource: "@VerzameldwerkAkismetBundle/config/routing_api.yml"
    type: rest
    prefix: /admin/api
```

4. Add the following dependency to your `assets/admin/package.json` file:

```json
{
    "dependencies": {
        "verzameldwerk-akismet-bundle": "file:node_modules/@sulu/vendor/verzameldwerk/akismet-bundle/assets/js"
    }
}
```

5. Add the following line to your `assets/admin/app.js` file:

```javascript
import 'verzameldwerk-akismet-bundle';
```

6. Add the following lines to your `assets/admin/webpack.config.js` file right before returning the `config` object:

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

7. Update your javascript build using `bin/console sulu:admin:update-build`.
When asked to overwrite your local version of "package.json", answer with no.

8. Update your database schema using `bin/console doctrine:schema:update --force`
or generate a migration using `bin/console doctrine:migrations:diff` if you are using the `DoctrineMigrationsBundle`.

10. Enable akimet in the role 'admin'

## Configuration
Put the following in config/packages/verzameldwerk_akismet.yaml to stop the spam:
```yaml
verzameldwerk_akismet:
    akismet_spam_strategy: no_email
```

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

:warning: If you end up with a message about ckeditor-duplicate-modules, you might need to add chkeditor to the exception at step 6, the javascript should look as followed
```javascript
config.module.rules.unshift({
        test: /\.js$/,
        exclude: /node_modules[/\\](?!(verzameldwerk-akismet-bundle|ckeditor5)[/\\])/,
        use: {
            loader: 'babel-loader',
            options: {
                cacheDirectory: true,
                cacheCompression: false,
            },
        },
    });
```
