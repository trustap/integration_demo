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

Guide
-----

This section describes how to integrate Trustap into your own application using
the Trustap JS plugin.

Trustap is typically integrated with buy-and-sell style websites and generally
consists of the following 3 steps:

* Update your "Create Listing" page to add a Trustap listing ID to new listings
* Add a "Pay with Trustap" button to your "View Listing" page
* Add a page to allow users to view their Trustap transactions

### Configuration

Each of the steps in this tutorial involves importing the Trustap JS plugin and
configuring it before use.

#### Configuration Object

The Trustap JS plugin takes a single JS configuration object as a parameter. It
is recommended to store this object in a JS file for easy maintenance. A simple
version of such a file is included in
[trustap_config.sample.js](js/trustap_config.sample.js). The configuration
object should define the following fields:

<table>
    <tr>
        <td><code>production</code></td>
        <td>
            Using `false` for this value sets up the plugin for testing use.
            Trustap user accounts created and used in this mode are kept
            separate from production users, and payments can be made using test
            cards.
        </td>
    </tr>
    <tr>
        <td><code>clientId</code></td>
        <td>
            This value is provided by Trustap and is used to associate
            transactions with your application. It can be used to find out how
            users are interacting with your application using Trustap, and can
            also be used to learn how those transactions progress, even if the
            users continue the transaction through Trustap or another
            Trustap-enabled interface.
        </td>
    </tr>
    <tr>
        <td><code>redirectUri</code></td>
        <td>
            This should specify a page on your server that the user is
            redirected to when they log in to Trustap on your site. See
            "Redirect Page", below.
        </td>
    </tr>
    <tr>
        <td><code>silentRefreshUri</code></td>
        <td>
            This should specify a page on your server, which is used to refresh
            the user's Trustap session while they're active on your site, which
            gives a more comfortable user experience. See
            "Silent Refresh Page", below.
        </td>
    </tr>
</table>

#### Redirect Page

You must create a "redirect page" on your site. The user is redirected to this
page when they log in to Trustap on your site. This page should only load the
Trustap plugin and call the `trustap.signIn()` method - see [the sample
implementation](intg/trustapi_sign_in.php) for an example.

#### Silent Refresh Page

You must create a "silent refresh page" on your site. This page is invisibly
opened by the Trustap plugin in order to refresh the user's session while
they're active on your site. This page should only load the Trustap plugin and
call the `trustap.silentRefresh()` method - see [the sample
implementation](intg/trustapi_silent_refresh.php) for an example.

### Page Updates

This section details the updates that must be made to individual pages for the
Trustap integration.

#### Create Listing

Updating the "Create Listing" page is the most involved part of the integration,
and generally consists of the following steps:

1. Initialise the Trustap plugin
2. Specify a `<div>` element to contain the "Use Trustap" button
3. Update the backend to store the `trustap_listing_id` returned with the
   "Create Listing" form

See [one of the "Create Listing" sample pages](p2p/sell.php) for an example
setup.

##### Initialise the Trustap plugin

Import the Trustap plugin and initialise it. This will usually look something
like the following:

        <script src="https://static.trustap.com/js/plugin.js"></script>
        <script src="/js/trustapi_config.js"></script>
        <script>
            const trustApi = trustap.api(trustApiConf);
        </script>

`trustApi` can now be used throughout the rest of the page to invoke the
TrustAPI plugin.

##### Create "Use Trustap" button

Create a `<div>` with a unique ID:

    <div id="trustap"></div>

You must make sure that this `<div>` is inside a HTML form.

Set this to contain the "Use Trustap" button using the `trustApi` object created
above:

    <script>
        trustApi.p2p.singleUseListings.setUseTrustapWidget({containerId: 'trustap'});
    </script>

##### Update the backend

The "Use Trustap" widget adds a new `trustap_listing_id` field to the form that
it is located within. When the user submits the form, this `trustap_listing_id`
should be stored with the rest of the form values:

    INSERT INTO listings (name, descr, price, trustap_listing_id)
    VALUES (?, ?, ?, ?);

The `trustap_listing_id` is a string.

#### Start Transaction

The "View Listing" page should be updated to include the "safe payment" widget,
which will use the Trustap listing ID generated on the "Create Listing" page to
allow users to join previously created listings.

First, check if the opened listing has a `trustap_listing_id` associated with
it. If there is, then initialise the Trustap plugin as described in the "Create
Listing" section and create a `<div>` with a unique ID:

    <div id="pay-with-trustap"></div>

Set this to contain the "Pay With Trustap" button using the `trustApi` object
created above:

    <script>
        trustApi.p2p.singleUseListings.setSafePaymentWidget({
            containerId: 'pay-with-trustap',
            listingId: '<?php echo $trustap_listing_id; ?>',
            onJoinTransaction: function () {
                window.top.location.href = 'transactions.php';
            },
        });
    </script>

The `listingId` is the `trustap_listing_id` that was submitted when the listing
was created. `onJoinTransaction` is a callback that will be called when the user
joins the displayed listing. In general, this callback will simply redirect the
user to a page where they can view their Trustap transactions.

#### View Transactions

Users can view any of their transactions using the Trustap website. However, the
Trustap plugin also provides a widget that allows users to view their
transactions while staying in third party websites. To add this widget to a
page, initialise the Trustap plugin as described in the "Create Listing"
section, create a `<div>` that will contain the widget, and initialise the
widget using the `trustApi` object:

    <div id="txs"></div>
    <script src="https://static.trustap.com/js/plugin.js"></script>
    <script src="/js/trustapi_config.js"></script>
    <script>
        const trustApi = trustap.api(trustApiConf);
        trustApi.setTrustapWidget('txs');
    </script>
