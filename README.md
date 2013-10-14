# Elao ErrorNotifier Bundle

[![knpbundles.com](http://knpbundles.com/Elao/ErrorNotifierBundle/badge)](http://knpbundles.com/Elao/ErrorNotifierBundle)

## What is it ?

This bundle sends an email each time there is a 500 error on the production server. You can also be notified of 404 or PHP fatal errors.

The email contains a lot of information : see the screenshot at the end of the README.

## Installation

#### If you are working with Symfony >= 2.1

Add this in your `composer.json`

    "require": {
        "elao/error-notifier-bundle" : "dev-master"
    },

And run `php composer.phar update elao/error-notifier-bundle`

#### If you are (still) working with Symfony 2.0.x

Add the followings lines to your `deps` file

    [ElaoErrorNotifierBundle]
        git=git://github.com/Elao/ErrorNotifierBundle.git
        target=bundles/Elao/ErrorNotifierBundle

and don't forget to register it in your autoloading `app/autoload.php`

    $loader->registerNamespaces(array(
        ...
        'Elao' => __DIR__.'/../vendor/bundles',
    ));

and finally run the vendors script:

```bash
$ php bin/vendors install
```

### Register the bundle `app/AppKernel.php`

    public function registerBundles()
    {
        return array(
            // ...
            new Elao\ErrorNotifierBundle\ElaoErrorNotifierBundle(),
        );
    }


## Configuration

Add in your `config_prod.yml` file, you don't need error notifier when you are in dev environment.

```yml
# app/config/config_prod.yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    handle404: true # default :  false
    mailer: your.mailer.id # default : mailer
    handlePHPErrors: true # catch fatal erros and email them
    handlePHPWarnings: true # catch warnings and email them
    ignored_classes: ~
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
    ignored_classes:
        - "Guzzle\Http\Exception\ServerErrorResponseException"
        - ...
```

## Twig Extension

There are also some extensions that you can use in your Twig templates (thanks to [Goutte](https://github.com/Goutte))

Extends Twig with

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

Default value is 1. (MAX_DEPTH const)


## Screenshot

![Email ErrorNotifier Bundle](http://i49.tinypic.com/2wck36e.png "Email ErrorNotifier Bundle")

