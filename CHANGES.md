
The following changes have been made after the major upgrade from 5 plugins to 1 plugin:

Die following plugins are wrapped together to one plugin and are defined as modules.
* wp-libraries: This is the base module and is always enabled
* wp-events-interface
* wp-events-syndication-server: renamed to wp-events-feed-importer
* wp-kvm-interface:
* wp-initiative: renamed to wp-organisation and the user register part has become a seperated module "wp-user-register"

Added functionality:

* Dashboard items module: Admins can show their own custom Dashboard to logged in users.
* Events feed importer module, ICal
 * Better support for recurring events
 * Addresses can be direct resolved by OSM (Feed location free text format type)
 * Filtered tags: Only events with these tags will be imported
 * Include tags: Add this tags to events imported by this feed
* User register module: 
 * Address and contact data is moved to the organisation (initiative)
 * The fields of User Registration form can be setted up by admins 
 * The Email template send to registered users can be changed.
 * The Logo on the login and registration page can be changed.
* Organisation module: 
 * Initiative are renamed to Organisation 
 * Address and contact details are now on the Organisation and no longer on the user profile
 * In the Organisation settings you can enable a migration modus "Migrate User und Initiative to a Organisation". This show up the old Initiative and on the Initiative there is an action to migrate the Initiative and User to an Organisation.
 * It is possible to have multiple Organisations pro User (you have to enable the option: "Allow multiple Organisations pro User".
* Karte von Morgen
 * Adding support for custom links and image_url for Organisations
 * Send back fields that are not filled by the module, so that they are not erased.

29 March 2021, Sjoerd Takken

