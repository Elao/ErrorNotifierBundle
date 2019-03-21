# Elao ErrorNotifier Bundle

[![Latest Stable Version](https://poser.pugx.org/elao/error-notifier-bundle/v/stable)](https://packagist.org/packages/elao/error-notifier-bundle)
[![Total Downloads](https://poser.pugx.org/elao/error-notifier-bundle/downloads)](https://packagist.org/packages/elao/error-notifier-bundle)
[![Monthly Downloads](https://poser.pugx.org/elao/error-notifier-bundle/d/monthly)](https://packagist.org/packages/elao/error-notifier-bundle)
[![Latest Unstable Version](https://poser.pugx.org/elao/error-notifier-bundle/v/unstable)](https://packagist.org/packages/elao/error-notifier-bundle)
[![License](https://poser.pugx.org/elao/error-notifier-bundle/license)](https://packagist.org/packages/elao/error-notifier-bundle)

## What is it?

This bundle sends an email each time there is a 500 error on the production server. You can also be notified of 404 or PHP fatal errors.

The email contains a lot of information: see the screenshot at the end of the README.

## Installation

### Symfony >= 2.1

Add this in your `composer.json`

    "require": {
        "elao/error-notifier-bundle" : "dev-master"
    },

And run `php composer.phar update elao/error-notifier-bundle`

### Symfony 2.0.x

Add the followings lines to your `deps` file

    [ElaoErrorNotifierBundle]
        git=git://github.com/Elao/ErrorNotifierBundle.git
        target=bundles/Elao/ErrorNotifierBundle

and don't forget to register it in your autoloading `app/autoload.php`

```php
$loader->registerNamespaces(array(
    ...
    'Elao' => __DIR__.'/../vendor/bundles',
));
```

and finally run the vendors script:

```bash
$ php bin/vendors install
```

### Register the bundle in `app/AppKernel.php`

```php
public function registerBundles()
{
    return array(
        // ...
        new Elao\ErrorNotifierBundle\ElaoErrorNotifierBundle(),
    );
}
```

## Configuration

Add in your `config_prod.yml` file, you don't need error notifier when you are in dev environment.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    handle404: true # default :  false
    handleHTTPcodes: ~
    mailer: your.mailer.id # default : mailer
    handlePHPErrors: true # catch fatal erros and email them
    handlePHPWarnings: true # catch warnings and email them
    handleSilentErrors: false # don't catch error on method with an @
    filteredRequestParams: [password] # replace request contents of parameter "password" with stars
    ignoredClasses: ~
```

### How to setup another mailer for sending the error mail

The mailer option has been added to let the application send the error mail via local smtp instead of using the regular quota on 3rd mailer services.

For example, if you wish to use an custom mailer that send mails via your server mail transport, create this one in the `config.yml` of your project:

```yml
# app/config/config.yml
swiftmailer:
    default_mailer: default
    mailers:
        default:
            transport: smtp
            host: localhost
            username: mylogin
            password: mypassword
        notifier:
            transport: mail
```

And after just change the `mailer` key on your `config_prod.yml` :
```yml
# app/config/config_prod.yml
elao_error_notifier:
    mailer: swiftmailer.mailer.notifier
```

### How to ignore errors raised by given classes ?

Sometimes, you want the bundle not to send email for errors raised by a given class. You can now do it by adding the name of the class raising the error in the `ignored_class` key.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    ignoredClasses:
        - "Guzzle\\Http\\Exception\\ServerErrorResponseException"
        - ...
```

### How to handle other HTTP errors by given error code ?

If you want the bundle to send email for other HTTP errors than 500 and 404, you can now specify the list of error codes you want to handle.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    handleHTTPcodes:
        - 405
        - ...
```

### How to avoid sending many same messages for one error ?

If an error occurs on a website with a lot of active visitors you'll get spammed by the notifier for the same error.

In order to avoid getting spammed, use the `repeatTimeout` option.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    repeatTimeout: 3600
```

In this example, if an errors X occurs, and the same error X occurs again within 1 hour, you won't recieve a 2nd email.

## Twig Extension

There are also some extensions that you can use in your Twig templates (thanks to [Goutte](https://github.com/Goutte)).

Extends Twig with:

```twig
{{ "my string, whatever" | pre }}  --> wraps with <pre>
{{ myBigVar | yaml_dump | pre }} as {{ myBigVar | ydump }} or {{ myBigVar | dumpy }}
{{ myBigVar | var_dump | pre }}  as {{ myBigVar | dump }}

You may control the depth of recursion with a parameter, say foo = array('a'=>array('b'=>array('c','d')))

{{ foo | dumpy(0) }} --> 'array of 1'
{{ foo | dumpy(2) }} -->
                           a:
                             b: 'array of 2'
{{ foo | dumpy(3) }} -->
                           a:
                             b:
                               - c
                               - d
```

Default value is 1 (MAX_DEPTH const).

### How to ignore sending HTTP errors if request comes from any of given IPs?

If you want to ignore sending HTTP errors if the request comes from specific IPs, you can now specify the list of ignored IPs.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    ignoredIPs:
        - "178.63.45.100"
        - ...
```

### How to ignore sending HTTP errors if the user agent match a given pattern?

For some reasons you may need to ignore sending notifications if request comes from some user agents.
Often you will need to use this feature with annoying crawlers which uses artificial intelligence
to generate URLs which may not exist in your site.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    ignoredAgentsPattern: "(Googlebot|bingbot)"
```

### How to ignore sending HTTP errors if the URI match a given pattern?

For example if you want to ignore all not exist images errors you may do something like that.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    ignoredUrlsPattern: "\.(jpg|png|gif)"
```
### How to filter sensitive data in request variables?

Sometimes you don't want to receive passwords or other sensitive data via email. With `filteredRequestParams` you can specify request variable names which should be replaced with stars. This works for named forms, too (e.g. `myFormName[password]`).

```yml
# app/config/config_prod.yml
elao_error_notifier:
    filteredRequestParams:
      - "password"
      - "creditCardNo"
      - "_token"
```

## Screenshot

![Email ErrorNotifier Bundle](http://i49.tinypic.com/2wck36e.png "Email ErrorNotifier Bundle")

