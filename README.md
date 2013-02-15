# Elao ErrorNotifier Bundle

[![knpbundles.com](http://knpbundles.com/Elao/ErrorNotifierBundle/badge)](http://knpbundles.com/Elao/ErrorNotifierBundle)

## What is it ?

This bundle sends an email each time there is a 500 error on the production server. (It also can send an email for 404).

The email contains a lot of informations :

![Email ErrorNotifier Bundle](http://i48.tinypic.com/2en7xv4.png "Email ErrorNotifier Bundle")


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

Add in your `config_prod.yml` file, you don't need this lines when you are in dev environment.

```yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    handle404: true # default :  false
    mailer: your.mailer.id # default : mailer
```


The mailer option has been added to let the application send the error mail via local smtp instead of using the regular quota on 3rd mailer services.
