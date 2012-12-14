# Elao Error Notifier Bundle

## Installation

###Add the followings to your `composer.json` file

    "elao/error-notifier-bundle": "dev-master"

### Register the bundle

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new Elao\ErrorNotifierBundle\ElaoErrorNotifierBundle(),
            // ...
        );
    }

#Configuration

Add in your `config_prod.yml` file, you don't need this lines when you are in dev environment.

```yml
elao_error_notifier:
    from: from@example.com
    to: to@example.com
    handle404: true
    handlePHPErrors: true # catch fatal erros and email them
    handlePHPWarnings: true # catch warnings and email them
```
