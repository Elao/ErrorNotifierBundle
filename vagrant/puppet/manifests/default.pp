Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }

class system-update {
    exec { 'apt-get update':
        command => 'apt-get update',
    }
}

class dev-packages {

    include gcc
    include wget

    $devPackages = [ "vim", "curl", "git", "locate", "automake", "autoconf" ]
    package { $devPackages:
        ensure => "installed",
        require => Exec['apt-get update'],
    }
}

class php-setup {

    $php = [
        "php5-fpm",
        "php5-cli",
        "php5-dev",
        "php5-curl",
        "php5-xdebug",
        "php5-intl"
    ]

    package { "python-software-properties":
        ensure => present,
    }
    
    exec { 'add-apt-repository ppa:ondrej/php5-5.6':
        command => '/usr/bin/add-apt-repository ppa:ondrej/php5-5.6',
        require => Package["python-software-properties"],
    }

    exec { 'apt-get update for ondrej/php5-5.6':
        command => '/usr/bin/apt-get update',
        before => Package[$php],
        require => Exec['add-apt-repository ppa:ondrej/php5-5.6'],
    }

    package { $php:
        notify => Service['php5-fpm'],
        ensure => latest,
    }

    /**
    file { '/etc/php5/mods-available/xdebug.ini':
        owner  => root,
        group  => root,
        ensure => file,
        mode   => 644,
        source => '/vagrant/vagrant/files/php/mods-available/xdebug.ini',
        require => Package[$php],
    }

    file { '/etc/php5/cli/php.ini':
        owner  => root,
        group  => root,
        ensure => file,
        mode   => 644,
        source => '/vagrant/vagrant/files/php/cli/php.ini',
        require => Package[$php],
    }

    file { '/etc/php5/fpm/php.ini':
        notify => Service["php5-fpm"],
        owner  => root,
        group  => root,
        ensure => file,
        mode   => 644,
        source => '/vagrant/vagrant/files/php/fpm/php.ini',
        require => Package[$php],
    }

    file { '/etc/php5/fpm/php-fpm.conf':
        notify => Service["php5-fpm"],
        owner  => root,
        group  => root,
        ensure => file,
        mode   => 644,
        source => '/vagrant/vagrant/files/php/fpm/php-fpm.conf',
        require => Package[$php],
    }

    file { '/etc/php5/fpm/pool.d/www.conf':
        notify => Service["php5-fpm"],
        owner  => root,
        group  => root,
        ensure => file,
        mode   => 644,
        source => '/vagrant/vagrant/files/php/fpm/pool.d/www.conf',
        require => Package[$php],
    }
    */

    service { "php5-fpm":
        ensure => running,
        require => Package["php5-fpm"],
    }

    exec { 'fpm restart':
        command => 'sudo service php5-fpm restart',
        require => [Package['php5-fpm']],
    }
}

class composer {
    exec { 'install composer php dependency management':
        command => 'curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin && mv /usr/bin/composer.phar /usr/bin/composer',
        creates => '/usr/bin/composer',
        require => [Package['php5-cli'], Package['curl']],
    }

    exec { 'composer self update':
        environment => ['COMPOSER_HOME="/usr/bin/.composer"'],
        command => 'sudo composer self-update',
        require => [Package['php5-cli'], Package['curl'], Exec['install composer php dependency management']],
    }
}

class { 'apt':
    update => {
        frequency => 'always',
    },
}

Exec["apt-get update"] -> Package <| |>

include system-update
include dev-packages
include php-setup
include composer

