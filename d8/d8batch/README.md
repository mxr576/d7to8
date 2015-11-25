### D8 Batch

#### tl;dr;
Drupal's [Batch API](https://api.drupal.org/api/drupal/core!includes!form.inc/group/batch/8)
did not change anything in Drupal 8, that is why this demo module also contains
other useful examples from the Drupal 8 new features.

This module is basically a demonstration of how can you import feed sources from
a CSV file to the Drupal. The imported feed sources will be stored in nodes.

The *D8: Feed Source CT* sub-module holds the definition of the necessary custom
node type (Feed source) with its settings thanks to the new configuration
management system. When the sub-module uninstalled, then all Feed Source nodes
will have to be deleted too, because the sub-module uninstall will
remove the node type definition from the active configuration.

The imported feed item links will be stored in a link field and the
title of the generated nodes will be set to the feed source's original title
with the help of the [Guzzle](http://docs.guzzlephp.org/en/latest/) and the
[Crawler](http://symfony.com/doc/current/components/dom_crawler.html) libraries,
which are also part of Drupal 8 (thanks to Symfony).

#### About these modules

**d8feedsourcect.module**

This module only holds the definition of the Feed Source content type. This
module ensures that all of its shipped configuration will be removed from the
active configuration, when the module is uninstalled. To do so
each configuration files contains the following lines:

```yml
dependencies:
  enforced:
    module:
      - d8feedsourcect
```
A little explanation is probably necessary.
Because we added the d8feedsourcect module to its shipped configuration files
as a dependency to itself, the CMI will be enforced to remove all the
provided configurations by the module from the active configuration,
when the module is uninstalled. (You can see the same solution in the core's
forum.module [here](http://cgit.drupalcode.org/drupal/tree/core/modules/forum/config/install/node.type.forum.yml).)
However this could cause an other weird issue in Drupal 8. By default, when we
try to remove a content type on the D8's admin UI which is in use by least one
content, then the system does not allow us to remove the content type while any
content is using it. So when we use the previously described solution, then the
Drupal is enforced to remove the content type regardlessly if any content
is using it. Because of that, all associated contents of the removed
content type will be left intact in the system and that is the problem. From
this point, when someone is trying to open a content which belongs to the
removed content type then the response will be a long error message.
To resolve this issue, I've added a batch operation to the module's
hook_uninstall() implementation which removes all associated contents of the
Feed Source CT, when the module is uninstalled.

**d8batch.module**

Since Batch API has not changed at all in the Drupal 8, the real value of
this module could be found elsewhere, for example in the
`_d8batch_batch_operation()` function. This function contains the demonstration of
how to use [the new http client](https://www.drupal.org/node/1862446), called
Guzzle in Drupal 8 for doing GET requests. You can also see here an example for
creating nodes programmatically in Drupal 8, where you do not need to write
miles-long lines to set a field's value, like this: `$node->field_foo[LANGUAGE_NONE][0]['value']`.
In Drupal 8 this works the same as in Drupal 7 with [Entity Metadata Wrappers](https://www.drupal.org/node/1021556).
Moreover, as the example demonstrates, you can set all field values in one place
with `Node::create()`, when you create a new node.

This module also contains an example for how to use Symfony's Crawler
to do some XPATH queries without using PHP's [DOMXpath](http://php.net/manual/en/class.domxpath.php)
or [SimpleXML](http://php.net/manual/en/book.simplexml.php) classes.
