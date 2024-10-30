=== Campi Moduli Italiani ===
Contributors: mociofiletto
Donate link: https://paypal.me/GiuseppeF77
Tags: Contact Form 7, WPForms, comuni italiani, codice fiscale, firma digitale
Requires at least: 5.9
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

Plugin to create useful fields for Italian sites, to be used in the forms produced with Contact Form 7 and WPForms.

== Description ==
This plugin creates form tags for Contact Form 7 and form fields for WPForms.

= Contact Form 7 =
4 form-tags (and corresponding mail-tags) are available in this version:
* [comune]: creates a series of select for the selection of an Italian municipality
* [cf]: creates a field for entering the Italian tax code of a natural person
* [stato]: creates the ability to select a country
* [formsign]: creates the possibility to digitally sign the e-mails sent with a private key attributed to each individual form

= WPForms =
2 fields types are available:
* Cascade selection of an Italian municipality (returning Istat's municipality code as value)
* A field to select a state (returning Istat's country code as value)

== Data used ==
At the time of activation, the plugin downloads the data it uses from the Istat and from the Italian Revenue Agency websites. This data can be updated from the administration console.
Downloading and entering data into the database takes several minutes: be patient during the activation phase.
The selection of the municipalities was created starting from the code of https://wordpress.org/plugins/regione-provincia-comune/

This plugin uses data made available by ISTAT and the Agenzia delle entrate (Italian revenue agency).
In particular, data made available at these URLs are acquired and stored:

* https://www.istat.it/it/archivio/6789
* https://www.istat.it/it/archivio/6747
* https://www1.agenziaentrate.gov.it/servizi/codici/ricerca/VisualizzaTabella.php?ArcName=00T4

The data published on the ISTAT website are covered by a Creative Commons license - Attribution (CC-by) (https://creativecommons.org/licenses/by/3.0/it/), as indicated here: https://www.istat.it/it/note-legali
The data taken from the website of the Agenzia delle entrate are in the public domain and constitute a public database made available to allow tax compliance and, more generally, to allow the identification of physical persons with the Italian public administrations, through the personal fiscal code.
The data are managed by the Ufficio Archivio of the Agenzia delle entrate.
By Italian law (art. 52 d.lgs. 82/2005) all data, that are not personal data, published by an Italian administration without an explicit license are open data (CC0).
This plugin uses the data taken from the website of the Agenzia delle entrate exclusively for the purpose of carrying out a formal regularity check of the pesonal tax code.
This plugin does not include any links on the external pages of the website on which it is used, neither to the Agenzia delle entrate's site nor to the ISTAT's website; in particular, no kind of direct link is made, nor of deep linking.

== How to use form tags in Contact Form 7 ==

[comune]
`[comune]` has a manager in the CF7 form creation area that allows you to set various options.
In particular, it is possible to set the "kind" attribute to "tutti" (all); "attuali" (current), "evidenza_cessati" (evidence ceased). In the first and third cases, in different ways, both the currently existing municipalities and those previously closed are proposed (useful, for example, to allow the selection of the municipality of birth). In the "attuali" mode, however, only the selection of the currently existing municipalities is allowed (useful to allow the selection of the Municipality of residence / domicile).
It is also possible to set the "comu_details" option, to show an icon after the select cascade that allows the display of a modal table with the statistical details of the territorial unit.
The value returned by the group is always the ISTAT code of the selected municipality. The corresponding mail-tag converts this value into the name of the municipality followed by the indication of the automotive code of the province.
From version 1.1.1 hidden fields are also populated with the strings corresponding to the denomination of the region, province and municipality selected, useful for being used in plugins that directly capture the data transmitted by the form (such as "Send PDF for Contact Form 7" )
The cascade of select can also be used outside of CF7, using the [comune] shortcode (options similar to those of the form tag for Contact Form 7).

Starting from version 2.2.0 there is a new filters' builder for the field [comune] useful for creating fields that allow the selection of a customizable list of municipalities.
Filters can be used both for CF7 tag, and for WPForms field, and for the shortcode 'comune'.
A short youtube video illustrates how to use filters and the filters' builder.

https://www.youtube.com/watch?v=seycOunfikk

[cf]
`[cf]` has a manager in the CF7 form creation area that allows you to set the various options.
In particular, it is possible to set various validation options allowing you to find the correspondence of the tax code with other fields of the form.
Specifically, it is possible to verify that the tax code corresponds with the foreign state of birth (selected by means of a select [stato]), the Italian municipality of birth (selected by means of a cascade of select [comune]), gender (indicating the name of a form field that returns "M" or "F"), the date of birth. If multiple fields are used to select the date of birth, one for the day, one for the month and one for the year, it is possible to find the correspondence of the tax code with these values.

[stato]
`[stato]` has a manager in the CF7 form creation area that allows you to set various options.
In particular, it is possible to set the selection of only the currently existing states ("only_current" option) and it is possible to set the "use_continent" option to have the select values divided by continent. The field always returns the ISTAT code of the foreign state (code 100 for Italy). The ISTAT code is the type of data expected by [cf], for the verification of the tax code.

[formsign]
`[formsign]` NOW (v. 2.2.1) has a manager in the CF7 form creation area.
To use it, simply insert the tag followed by the field name in your own form: for example [formsign firmadigitale]. This tag will create a hidden field in the form with attribute name = "firmadigitale" and no value.
To use the code, it is also necessary to insert the [firmadigitale] mail-tag in the email or emails that the form sends (it is recommended at the end of the email).
In this way, in the email body it will be written a two-lines sequence containing:
an md5 hash of the data transmitted with the module (not of the content of any attached files)
a digital signature of the hash.
If you use html email, you can style the output using a wp option named: "gcmi-forsign-css" with a css as value.
The signature is affixed by generating a pair of RSA keys, attributed to each form.
By checking the hash and the signature, it will be possible to verify that the emails have actually been sent by the form and that the data transmitted by the user correspond to what has been registered.
To facilitate data feedback, it is preferable to use "Flamingo" for archiving sent messages. In fact, in the Flamingo admin screen, a specific box is created that allows feedback of the hash and the digital signature entered in the email.
The system is useful in the event that through the form it is expected to receive applications for registration or applications etc... and avoids disputes regarding the data that the candidates claim to have sent and what is recorded by the system in Flamingo.

## Code

Want to check the code? [https://github.com/MocioF/campi-moduli-italiani](https://github.com/MocioF/campi-moduli-italiani)

== Installation ==

= Automatic installation =

1. Plugin admin panel and `add new` option.
2. Search in the text box `campi-moduli-italiani`.
3. Position yourself on the description of this plugin and select install.
4. Activate the plugin from the WordPress admin panel.
NOTE: activation takes several minutes, because the updated data tables are downloaded from the official sites (Istat and Agenzia delle entrate and then the data is imported into the database)

= Manual installation of ZIP files =

1. Download the .ZIP file from this screen.
2. Select add plugin option from the admin panel.
3. Select `upload` option at the top and select the file you downloaded.
4. Confirm installation and activation of plugins from the administration panel.
NOTE: activation takes several minutes, because the updated data tables are downloaded from the official sites (Istat and Agenzia delle entrate and then the data is imported into the database)

= Manual FTP installation =

1. Download the .ZIP file from this screen and unzip it.
2. FTP access to your folder on the web server.
3. Copy the whole `campi-moduli-italiani` folder to the `/wp-content/plugins/` directory
4. Activate the plugin from the WordPress admin panel.
NOTE: activation takes several minutes, because the updated data tables are downloaded from the official sites (Istat and Agenzia delle entrate and then the data is imported into the database)

== Frequently Asked Questions ==

= How to get default values from the context ? =
Since version 1.2, [comune], [stato] and [cf] support standard Contact Form 7 method to get values from the context.
More, all of them support predefined values in tag.
Look here for more informations: https://contactform7.com/getting-default-values-from-the-context/
[comune] uses javascript to be filled with default or context value.


= How do I report a bug? =
You can create an issue in our Github repo:
[https://github.com/MocioF/campi-moduli-italiani](https://github.com/MocioF/campi-moduli-italiani)

== Screenshots ==

1. Image of the [stato] and [comune] form tags in a form
2. Image of the form-tag [cf] in a form
3. Image of the "digital signature" block inserted at the bottom of an email using the form-tag [formsign]
4. Image of the hash code verification meta-box and digital signature in Flamingo
5. Image of the admin screen, from which it is possible to update the data

== Changelog ==
= 2.2.4 =
* Enhancement in activation requirement checks
* Fixed a validation's error string in module [cf]
* Fixed error due to missing CSV in archive deployed by data provider

= 2.2.3 =
* Fix error in the order of provinces when the module shows only actual municipalities

= 2.2.2 =
* Fix bug in cf validation against birth nation

= 2.2.1 =
* Fix js for comune [comune]
* Minor bugs fixed

= 2.2.0 =
* Added new filter builder for module [comune]

= 2.1.5 =
* Update comuni_attuali structure to new dataset
* Added a check on data format before parsing

= 2.1.4 =
* Update URL for cadastrial codes
* Updated istat.it pem chain

= 2.1.3 =
* Updated www1.agenziaentrate.gov.it pem chain
* Updated istat.it pem chain

= 2.1.2 =
* Updated agenziaentrate.gov.it pem chain
* Updated agenziaentrate's data url

= 2.1.1 =
* Updated PEM chains
* Added a wget fallback to download from Istat's website

= 2.1.0 =
* Changed method to get remote file update time on ISTAT website from HEAD to GET
* Fixed bug in wpforms allowing form submission without a full selection of a municipality (marked as compulsory)
* Secured comune's ajax with nonce
* Added use of WordPress Object Cache to db queries
* Fixed markup changes in form control for CF7 v.5.6
* Added default value in wpforms for "stato"; changed order of choices (now using denominations)
* Added multisite activation feature
* Switch to minified scripts and styles
* Default value in CF7 comune form-tag can be set by municipality's name
* Added default value in wpforms for "comune"

= 2.0.8 =
* Fixed bug in cf atts

= 2.0.7 =
* Updated to work in Contact Form 7 > 5.5
* Minor bugs fixed

= 2.0.6 =
* Fixed ssl issue in checking last update date of remote files on ISTAT website

= 2.0.5 =
* Added new istat.it ca cert for cUrl. Fixes: https://wordpress.org/support/topic/attivazione-vietata-forbidden/

= 2.0.3 =
* Minor bug fixes Fixes [#1](https://github.com/MocioF/campi-moduli-italiani/issues/1).

= 2.0.2 =
* Use the remote update date of comuni_attuali to set the remote update date of codici_catastali

= 2.0.1 =
* Minor bug fixes

= 2.0.0 =
* added a field to select a municipality to WPForms
* removed variable definition from global scope
* added use of options' groups in country selection

= 1.3.0 =
* first integration with wpforms

= 1.2.2 =
* modified table _comuni_variazioni (ISTAT changed the file's format)
* modified table _comuni_soppressi (ISTAT changed the file's format)
* updated jquery-ui-dialog.css to version used in WP 5.6
* added standard wpcf7's classes to [comune] (wpcf7-select), [stato] (wpcf7-select) and [cf] (wpcf7-text)
* changed behaviour of option "use_label_element" in [comune]: if not set, no strings will be shown before selects
* changed previous first elements used as labels in selects of [comune]
* added option to use a label in [stato] (Select a Country) 
* changed class name: gcmi_wrap to gcmi-wrap
* for [comune] it is now possible to set custom classes both for the span container and for the selects
*
* [comune] shortcode (not for CF7):
* changed class name: gcmi_comune to gcmi-comune
* added options "use_label_element"; default to true
* removed p and div tags

= 1.2.1 =
* Bug fix: fixed [stato] not replacing mail-tag with contry name

= 1.2.0 =
* Added support for default values from the context in [comune], [cf] and [stato]. Contact Form 7 standard sintax is used. Read: https://contactform7.com/getting-default-values-from-the-context/
* Minor bug fixes

= 1.1.3 =
* Minor bug fixes

= 1.1.2 =
* Fixed charset for https://www.istat.it/storage/codici-unita-amministrative/Elenco-comuni-italiani.csv (data set "comuni_attuali", table _gcmi_comuni_attuali). Please update the table from admin console if some names have characters mismatch
* Minor bug fix in class-gcmi-comune.php

= 1.1.1 =
* Added hidden fields that contain the name of the municipality, province and region selected to be used within plugins that create PDFs
* Set set_time_limit (360) in the activation routine
* Added readme.txt in English

= 1.1.0 =
* Modified email signature check: the form ID is determined directly from Flamingo data and is no longer entered in the body of the email
* Insert links to reviews and support page on the plugin page
* Modified "comuni attuali" database import routines, following modification in ISTAT files since June 2020
* Modified remote file update detection system

= 1.0.3 =
* Bug fix: error in hash calculation on modules/formsign/wpcf7-formsign-formtag.php

= 1.0.2 =
* Updates of some translation strings.
* Bug fix (addslashes before calculating verification hash)

= 1.0.1 =
* Updated the text domain to the slug assigned by wordpress.

= 1.0.0 =
* First release of the plugin.

== Upgrade Notice ==
= 2.0.0 =
Integrated with WPForms

= 1.1.0 =
ISTAT has changed the format of its database.
After this update it is necessary to update the table relating to the current municipalities [comuni_attuali].
It is also recommended to update the tables relating to the municipalities suppressed [comuni_soppressi] and to the variations [comuni_variazioni]

= 1.0.0 =
First installation

== Upgrade Notice ==

= 2.1.0 =
Security fixes and object cache implementation.