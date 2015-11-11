Custom access control to a page
===============================

Goals
-----

- Provide a page at `/user/$uid/identity` which displays the user's name and
  email.
- Every user should be able to access only his/her own page.
- Provide a tab at `/user/$uid` that's displayed only on the user's own profile.

Prerequisites
-------------

If you're a newcomer to Drupal 8, you should learn how to create [A "Hello
World" Custom Page Module](https://www.drupal.org/node/2464195).

Drupal 7
--------

Adding a custom access callback to a D7 router item consists of two steps.

- Add an `access callback` line (and an `access arguments` line as well, if
  needed) to the [`hook_menu()` implementation]
  (https://github.com/boobaa/d7to8/blob/master/d7/d7access/d7access.module#L17-L18).
- Provide the [function]
  (https://github.com/boobaa/d7to8/blob/master/d7/d7access/d7access.module#L28-L34)
  that compares the user ID coming from the URL with the currently logged in
  user's ID (coming from the global `$user` variable).

As Drupal 7's caching system is not that sophisticated as the one in Drupal 8,
we don't and can't provide caching-related information.

Drupal 8
--------

Adding a custom access callback to a D8 router item consists of quite some
steps.

- Provide a [class]
  (https://github.com/boobaa/d7to8/blob/master/d8/d8access/src/Access/OwnDataAccessCheck.php)
  that implements [`\Drupal\Core\Routing\Access\AccessInterface`]
  (https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Routing!Access!AccessInterface.php/interface/AccessInterface/8).
  This class should have the [`access()` function]
  (https://github.com/boobaa/d7to8/blob/master/d8/d8access/src/Access/OwnDataAccessCheck.php#L20-L35)
  which determines access based on the current user's ID and the raw integer
  coming from the matched router.
- Inform the system about this class in [`d8access.services.yml`]
  (https://github.com/boobaa/d7to8/blob/master/d8/d8access/d8access.services.yml),
  which provides an ID for the access checking mechanism
  (`_d8access_own_data_access_check` in this case).
- Use this ID in `d8access.routing.yml` as a [requirement]
  (https://github.com/boobaa/d7to8/blob/master/d8/d8access/d8access.routing.yml#L6-L7).

In addition to the access checking decision, the same function provides the
information to Drupal 8's caching system that the pages governed by this access
checking mechanism should only be [cached per user]
(https://github.com/boobaa/d7to8/blob/master/d8/d8access/src/Access/OwnDataAccessCheck.php#L33).
Later on, the controller that builds the render array informs the caching system
that the information provided by it [should not be cached]
(https://github.com/boobaa/d7to8/blob/master/d8/d8access/src/Controller/D8AccessDisplayController.php#L27-L30)
at all. (In our particular case it's not 100% required, but we're including this
to shed light to this corner of the caching system as well.)

References
----------

To understand why and how this routing and access checking works, check these
resources:

- [D7 to D8 upgrade tutorial: Convert hook_menu() and hook_menu_alter() to
  Drupal 8 APIs](https://www.drupal.org/node/2118147) is the first step, but it
  lacks some sophisticated cases like ours.
- [Access checking on routes](https://www.drupal.org/node/2122195) is the one
  that describes custom access checking, but if you're unfamiliar with the
  "normal" routing, you should check this third one, too:
- [Using parameters in routes](https://www.drupal.org/node/2310425) tells you
  why and how can you use different types of information as parameters to the
  `access()` function (and page controllers and form builders, too). It even
  describes what to do if you want to have two objects of the same type as
  parameters.
