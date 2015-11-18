### D8 Batch

#### tl;dr;
Drupal's [Batch API](https://api.drupal.org/api/drupal/core!includes!form.inc/group/batch/8)
did not change anything in Drupal 8, that is why this demo module also contains
other useful examples from the Drupal 8 new features.

#### About this module
This module is basically a demonstration of how can you import feed sources from
 a CSV file to the Drupal. The imported feed sources will be stored in nodes.

The *D8: Feed Source CT* sub-module holds the definition of the necessary custom
node type (Feed source) with its settings thanks to the new configuration
management system. When the sub-module uninstalled, then all Feed Source nodes
will be deleted too, because the sub-module uninstall will remove the node type
definition from the active configuration.

The imported feed item links will be stored in a link field and the
title of the generated nodes will be set to the feed source's original title
with the help of the [Guzzle](http://docs.guzzlephp.org/en/latest/) and the
[Crawler](http://symfony.com/doc/current/components/dom_crawler.html) libraries,
which are also part of the Drupal 8 (thanks for the Symfony).
