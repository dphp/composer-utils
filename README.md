composer-utils
==============
Some utility scripts for Composer by dPHP.


Notes
-----
Composer does not support custom scripts/events yet (see [https://github.com/composer/composer/issues/2059](https://github.com/composer/composer/issues/2059)). You may need to assign an event to a composer-utils' script in order to execute it. 

For example:

```
{
    "scripts": {
        "post-status-cmd": "\\Dphp\\ComposerUtils\\EclipsePdt::createPdt3Project"
    }
}
```


HISTORY
-------
* v0.1.0 - 2013-11-05: first version


Usage
-----
Require dphp/composer-utils in your `composer.json`:

```
{
    "require-dev": {
        "dphp/composer": "version_number"
    }
}
```

Generate Eclipse PDT Project
----------------------------
Script: `\Dphp\ComposerUtils\EclipsePdt::createPdt3Project`

It will generate an Eclipse PDT v3.0 project structure.


COPYRIGHT
---------
Copyright (c) Thanh Ba Nguyen. See [COPYRIGHT.md](COPYRIGHT.md) and [LICENSE.md](LICENSE.md) for more information.
