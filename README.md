# KVM Syndication Toolbox for Organisations and Events

## Installation
To install this Plugin in your-wordpress-Seite.de/wp-admin/plugin-install.php download the zip-file from the latest release https://github.com/kartevonmorgen/wp-kvm-syndication-toolbox/releases and make sure, that the zip-files name is just "wp-kvm-syndication-toolbox.zip" without any v1.1 or -main Ending. 

## This plugin is constructed out of different modules:

1. The Base module contains helper classes and basic functionallity which is used by the different modules.
2. The Events interface module gives a common interface to different Calendar Plugins supported by Wordpress( The Events Manager, Events Calendar, All In One Events Calendar).
3. The Events feed importer module  imports events from other websites to this website.
4. The Organisation module makes it possible to register an Organisation.
5. The KVM interface module makes it possible to upload and download Organisations and Events to the Karte von Morgen.
6. With the User register module (if enabled), users can register themselves und their Organisation. So the Organisation Module must be enabled to use this Module.
7. With the Dashboard items module it is possible to create custom messages on the Dashboard. Here we can show users that have logged in, what they can do in the admin area. 

The Base module can be find directly in the root of the plugin. The other modules are find under the modules directory.

To get access to a module the following code can be used:

Use is_module_enabled(..) to get a specific module.

```php
$mc = WPModuleConfiguration::get_instance();
$isEiInterfaceEnabled = $mc->is_module_enabled('wp-events-interface');
```
Use get_module(..) to get a specific module.

```php
$mc = WPModuleConfiguration::get_instance();
$eiInterface = $mc->get_module('wp-events-interface');
```
Use get_root_module() to get the base module, this module is always enabled.

```php
$mc = WPModuleConfiguration::get_instance();
$root = $mc->get_root_module();
```

## Base Module

This Base Module contains the following functionallity:
* Makes a wrapper around the WP_HTTP class which is compatible with the PSR-7 Api. 
* Creates a class based Wrapper around the Wordpress Settings API
* Adding functionality to add Metaboxes with custom fields to Posttypes. 
* Adding an own MVC for creating additional Forms in Wordpress. 
* Has an API to find the Open Street Map coordinates for an address, with OSM Nominatim (https://nominatim.org/)
* Basic classes for Initiative and Locations.

The Base Module delivers the "general Settings Tab" and contains the following options:
* Maximal media upload settings: the maximum upload size for images, audio and videos for users can here be determined.
* Developer and Administrator options: Extra logging and actions can be enabled to do tests.
* OSM Nominatim settings: OSM Nominatim search coordinates for addresses and also complete addresses if it can. The URL of the OSM Nominatim Server is setted by default and can be changed. To Test the URL, you can do a test.


## Events Interface

WP Events Interface Module converts the common used Events Calendars in Wordpress to a Standard Interface, which can be used by other Modules, which want to deal with events.

On the "Events Interface" tab some default settings can be entered.
* Calendar plugin: All the available and activated Calendars (and supported by Events Interface) are listed here. You can select one which "Events Interface" will use. The following calendars are supported for now:
  * The Events Manager.
  * All in One Events Calendar (only loading, saving is not supported at the Moment).
  * The Events Calendar (only loading, saving is not supported at the Moment).
* Timerange to select in days: How many days (from now) into the future should be selected when we are loading events.
* Category (slug) to select: Filter on specific category by the slug name.
* Delete events permanently: If this option is enabled, the events that are deleted, will be deleted permanently instead that they will be put into the trash.
* Fill longitude und latitude automatically by OSM: Wenn a location is saved the GEO Coordinates will be retrieved by OSM Nominatim and will be filled into the location automatically.

The Events Interface can be used to load and save events into the selecte Event Calendar plugin.

In PHP the following Interface can be used to load and save events.

Loading:
```php
$mc = WPModuleConfiguration::get_instance();
$eiInterface = $mc->get_module('wp-events-interface');
$eiEvents = $eiInterface->get_events_by_cat();
```

Saving:
```php
$mc = WPModuleConfiguration::get_instance();
$eiInterface = $mc->get_module('wp-events-interface');
$eiInterface->save_event($eiEvent);
```


## Events feed importer

This Module loads events from a Feed into the currently selected Events Calendar in Wordpress.

This Module depends on the "Events Interface" module. Before this module can be activated the "Events Interface" module should first be activated.

### Supported Feeds:

#### ESS Feed 
ESS-Feeds are like RSS-Feeds, but especially made for events.

More Information about ESS can be found here: 

On GitHub: https://github.com/essfeed
On Youtube: https://www.youtube.com/watch?v=OGi0U3Eqs6E


#### iCal Feed 
ICal Feeds are also supported.

### Events feed importer settings

* General settings
  * Publish events directly: if events are imported they will normally get the state Pending. If this option is enabled, they will be published directly. 
  * Add link from source: if this option is enabled, in the description of the event an link to the source where this event is imported from, will be added.
  * Strip prefix from category: if events are imported from another website we also import the categories. We can set specific categories on the client website for this server with a prefix. Then on the Server we do not want to have this prefix, so setting it in this option will remove this prefix from the imported category.
  * Last message eventscron: every day the feeds that have the option "update daily" enabled, will be updated. The eventscron gives a status message of the updated feeds.
* Limits of imported events
  * Max. recurring events: repeating events can produce many events in the future, here we can set a maximum pro repeating event.
  * Max. period in Days: Maximum period in the future that will be used to import events.
  * Maximum events pro Feed: maximum number of events that will be imported pro Feed. If -1, then all events will be imported. This property can be useful for testing with feeds with a lot of events, that saves waiting time.

### Import event feeds

The Import event feeds part takes care of importing events by feeds into wordpress. An event can be assigend to a user. The user is then the owner of the feed and its imported events.

#### Feed details

The event feeds have some more settings pro feed:
* Feed URL: The Feed URL that need to be imported
* Feed URL type: Kind of Feed (ESS or ICal)
* Update daily: Import this Feed every day and update the events in the Feed
* Disable linkurl check: Sometimes the url of the feed does not match with the url of the events that are imported by this feed, if this behaviour is wanted, then set this option, so you do not get an error.
* Define the location by GEO Coordinates: Sometimes the event in the feed has already GEO Coordinates(Latitude, Longitude). Then we can search the address by this Coordinates. Set this option if you want this. Otherwise the coordinates are determined by the given address in the Feed.
* Feed location free text format type: In many feeds the location is given in a free text format. To extract the addressdetails out of it, we can use two methods. We can use the local method to find te addressdetails or we can ask OSM Nominatim to find out the addressdetails. (You can just test with it, which method works the best)
* Filtered tags: Only events with one of these tags will be imported You can give a comma seperated list for multiple tags
* Include tags: Include this tags for every event that are imported by this Feed. You can give a comma seperated list for multiple tags. This option make it possible to see from which feed an event is coming from if it is uploaded to the Karte von Morgen.

#### Feed informationen

The Event ids are the Ids of the Events of the Events Calendar used in Wordpress to import the feed.
In the "Update log" you find the logging of the last import of this Feed.

#### Update event feed

In the menu there is an action to update the event feed manually. If you click on this action the feed will be imported directly. Otherwise (if Update daily is on) the feed will be updated every day.

## Organisation

The organisation module makes it possible to create an organisation inclusiv name, addresse and a contact person.

### Organisation settings

* Allow multiple Organisations pro User: Normally a user registers himself and its organisation. In some cases you want to make it possible that a User can create multiple Organisation. (If this is option is enabled, for now it is not possible to create a organisation page with an eventslist for the organisation)
* Migrate User und Initiative to a Organisation: This option is for backwards compatibility. If you have already created initiative in the past after the upgrade they still exists but are saved under the old post type "initiative". If you enable this option, then the initiative are visible and we can migrate them to organisations. In the past the addressdetails and contact person where stored under the User. They will also be migrated to the Organisation.

### Organisations

Organisations can be edited here. First you can give the Organisation a description and if you want an excerpt. If the excerpt is filled, this text will be visible in the overview page of organisations and will be uploaded to the Karte von Morgen if this module is enabled.

There two kind of Organisationstypes:
* Initiative
* Company 

The addressdetails can be given and the Latitude and Longitude will be automatically filled if the Organsation will be uploaded to the Karte von Morgen (if this modul is enabled)

For the Karte von Morgen there is a field "Meldung" where you find the status from the last upload to the Karte von Morgen and the unique Karte von Morgen Id is shown, so you can find the Organisation back into the Karte von Morgen by id.

The contactperson can also be filled. If the user can register itself (if the module User register(Benuter registrieren) is enabled), then mostly the Organisation is already created and this data is already filled. 

If the Organisation is saved and published the it will automatically uploaded to the Karte von Morgen if this module is enabled.

## User register (Benutzer registrieren)

This module makes it possible for Users to register themselves und their Organisation. In the "User register items" part you can define which fields will be on the User register from.

The standard register form of Wordpress will be extended with this extra fields. Also it is possible to add information parts (for example: privacy policy or a manual).

### User register settings

* Create default userregister items: It is possible to create the default extra fields for the WP Registrationform. Then you have to switch this option OFF and save and then switch it ON and save. This will only work if there are not already items created. If you want to restore to the default items, then make sure that there are not already items in "User register items". You should first delete them.
* Email that will be send after register: This is a template for the email that will be send to the User after registration. There is already a default template defined, which can be changed.
* Path to Logo for the Loginpage: Above the Wordpress login and registration page appears normally the default Wordpress logo. Here you can give a path to a custom Logo from your Platform (for example)

### User register items

Here you can define the fields that will be added to the default Wordpress registration form. 

The first textarea gives the description of the field. If the field is a description then this textarea is used as description.

#### Form properties

* Fields from Model: It can be not assigned (Nicht zugewiesen an ein Feld, nur beschriebend) to a field from model, then it can be used as a describing field. Otherwise you can assign the field to one of the items from the model.
* Type: This can be a Field, then it must be assigned to an item from model. Or it can be a Description, then the text above will be outputed.
* Postion on form: this gives the position on the form an determines the order of the added form items.
* Backgroundcolor: You can give different fields different backgroundcolors. I used is to make visible which field are used internal and which fields are published on the website.

## Karte von Morgen

This modul enables uploading and downloading events and organisations to and from the Karte von Morgen (https://www.kartevonmorgen.org).

### Karte von Morgen (settings)

* URL: This is the URL to the OpenFairDB database. The Karte von Morgen is build on top of this database. Default it is setted to the Development database: https://dev.ofdb.io/v0. The productions database is: https://api.ofdb.io/v0/
  * For testing purpose use: https://dev.ofdb.io/v0
* Access token: This token you become from the Karte von Morgen after you have registered you organisation there (Write a mail to info@kartevonmorgen.org). With this token it is possible to upload and download events and organisations.
  * For testing purpuse use this token: 24vGG9gyYuVanmjJBJCRzZ51Qt5GBYGxkEGu1BxNQhQBgQ7
* Fixed tag: Gives uploaded events and entries a fixed tag so they all can be found by this tag. This tag is also used as the special organisation hashtag on which your organisation/platform will be registered by the Karte von Morgen
  * For testing purpuse use: test

## Dashboard items

If users login on Wordpress, then they come first to the Dashboard in the Administration-Area of Wordpress. With this module you are able to put custom messages on the Dashboard area, to help users how to use the platform.

### Creating Dashboard items 

In submenu of the plugin you find a menu entry "Dashboard items", there you can add new Items with a text and a position on the Dashboard. The postion "Normal" is on the leftside and the position "Right side" is on the right side".

First after some entries are created the old Dashboard will be removed.

## Newsletter interface

The Newsletter interface Module makes it possible to generate an Events List in the Newsletter. At the moment the Noptin Newsletter Plugin is only supported. In the future also the Mailpoet Newsletter Plugin should be supported. 

In the Noptin Newsletter use the [[events]] tag to import the event list automatically in the Newsletter, the category and number of days in future for this event list can be configured in the Settings of the module (KVM Syndication Toolbox -> Newsletter Interface). 

### Newsletter lists

As an addition to Noptin, different Lists of Subscribers are also supported and can be enabled in the Settings of the module (KVM Syndication Toolbox -> Newsletter Interface), then lists can be added under (KVM Syndication Toolbox -> Newsletter lists). Then they will be automatically added to the Noptin Plugin.

## Commons Booking Extensions

Gives Commons Booking the oppertunity to duplicate Timeframes for all the articles.

2 Dezember 2022, Sjoerd Takken
