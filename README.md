README
======

About
-----

This repository is a skeleton project that shows how to integrate Trustap into
websites, using buy-and-sell websites as an example.

See the [live demo](https://demo.trustap.com) for a sample application that
uses this integration.

Flow
----

This Trustap demo is based on buy-and-sell listings.

A user can create a new listing on `sell.php`. Clicking "Use Trustap" will
populate the form with a Trustap listing ID that will be generated for the
Trustap user that is currently logged in in the browser. This ID should be
stored in a database with the listing. See the script at the bottom of
`sell.php` for details on how to generate a Trustap listing ID.

The `index.php` page shows all listings that were created. Any listings that
have a Trustap listing ID stored with them say that they have got Trustap
enabled. This allows websites to enable/disable Trustap for particular listings.

The `listing.php` page gives details for a particular listing. Any element can
be used as a "Safe Payment Button" using `setSafePaymentButton` (see example
code in `listing.php`. This will bring up a modal that can create a new Trustap
transaction between a second user and the user that created the initial listing.

Running
-------

Create `config.php` from `config.sample.php` and populate the database
connection values. Then copy `js/trustapi_config.sample.js` to
`js/trustapi_config.js` and populate the `clientId` value with your Trustap
client ID.

Run `bash scripts/docker_build.sh` to build the Docker image for this project,
and then run `bash scripts/serve_dev.sh` to run the image. Visit the
[MySQL seed script](http://localhost:8080/reseed_mysql.php) to initialise the
database defined in `config.php`. You should now be able to browse [the
demo](http://localhost:8080).
