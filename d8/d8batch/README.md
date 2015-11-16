### D8 Batch

#### tl;dr;
Drupal's [Batch API](https://api.drupal.org/api/drupal/core!includes!form.inc/group/batch/8) did not change anything in Drupal 8, that is why this demo module also contains other useful examples from the Drupal 8 new features.

#### About this module
This module basically a demonstration of how can you import feed sources from a CSV file to the Drupal. The imported feed sources will be stored in nodes.

The module also holds the definition of the necessary custom node type (Feed source) and its link field (field_feed_source_link) thanks to the new configuration management system.

The imported feed item links will be stored in the mentioned link field and the title of the generated nodes will be set to the feed source's original title with the help of the [Guzzle](http://docs.guzzlephp.org/en/latest/) and the [Crawler](http://symfony.com/doc/current/components/dom_crawler.html) libraries, which also part of the Drupal 8 (thanks to the Symfony).
