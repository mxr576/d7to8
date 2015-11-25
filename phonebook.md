Old-fashioned phonebook
=======================

Goals
-----

- Provide an index page at `/phonebook` which displays all the phonebook entries
  in a pagered table, where the user can sort the results by clicking on column
  headers. When there is no phonebook entry, a message should be displayed
  instead.
- Provide a form at `/phonebook/add` to be able to add new entries.
- Reuse the same form to edit an entry at `/phonebook/{phonebook]/edit`.
- Provide a CSRF-protected way to delete an entry without confirmation at
  `/phonebook/{phonebook]/delete`.

Drupal 7
--------

TODO

Drupal 8
--------

Some random notes, in order of appearance.

- [Schema API]
  (https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Database!database.api.php/group/schemaapi/8)
  has not changed too much. A `hook_schema()` implementation is needed in the
  `mymodule.install` file.
- Dependency injection is your friend. Just take the time to learn how to use
  it. It's being used for three things:
  - the connection to the database,
  - the date formatter service,
  - the CSRF token generator.
- [DbLogController::overview](https://api.drupal.org/api/drupal/core!modules!dblog!src!Controller!DbLogController.php/function/DbLogController%3A%3Aoverview/8)
  has an example for table sorting and pager selection in core.
  - Table sorting needs an additional `$query->orderByHeader($header)` line.
  - Pager selection needs an additional `$query->limit(25)` line.
- Most controller classes will have an `l()` method which can be used for
  creating a link. However, its second argument is a `\Drupal\Core\Url`, and the
  single mandatory argument of that is not a (local) URL, but a route name.
- Two change records about CSRF in D8:
  - [https://www.drupal.org/node/2068221]
    (drupal_get_private_key() and drupal_get_token()/drupal_valid_token() got replaced by 'private_key' and 'csrf_token' service)
  - [https://www.drupal.org/node/2133117]
    (CSRF tokens now integrated directly into the routing/access system)
- Use core modules' code as examples when you're interested in new topics. In
  this case, this was the way of finding how to use table sorting and the pager.
- Both page controllers and forms may return non-content/non-form answers like
  `403 Access denied` or a redirection.
- The named arguments in the routes are just that: named arguments. No automatic
  autoloading of any object happens on them. (Well, at least not on things that
  are not entities like this phonebook example.) Their value should be retrieved
  by their name, not by their place in the URL.
- Form validate and submit handler methods will have a
  `\Drupal\Core\Form\FormStateInterface` as their second argument (not an
  array). This object will have some useful methods like `setErrorByName()` and
  `setRedirect()`. First one takes two strings: the form element's name and the
  error message; second one takes a route name.
