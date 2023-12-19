=== CF7 Google Sheets Connector ===
Contributors: westerndeal
Donate link: https://www.paypal.me/WesternDeal
Author URL: https://www.gsheetconnector.com/
Tags: cf7, contact form 7, Contact Form 7 Integrations, contact forms, Google Sheets, Google Sheets Integrations, Google, Sheets
Requires at least: 3.6
Tested up to: 6.4.1
Stable tag: 5.0.6

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send your Contact Form 7 data directly to your Google Sheets spreadsheet.

== Description ==

CF7 Google Sheet Connector is an addon plugin, A bridge between your [WordPress](https://wordpress.org/) based [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) forms and [Google Sheets](https://www.google.com/sheets/about/). ** 🚀 A Most Popular WordPress Plugin.**

When a visitor submits his/her data on your website via a Contact Form 7 form, upon form submission, such data are also sent to Google Sheets.

[Homepage](https://www.gsheetconnector.com/) | [Documentation](https://www.gsheetconnector.com/docs) | [Support](https://www.gsheetconnector.com/support) | [Demo](https://cf7demo.gsheetconnector.com/) | [Premium Version](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?wp-repo)

= 📝 CF7 ➜ ✍️Google Sheet=
Get rid of making mistakes while adding the sheet settings or adding the headers ( Mail Tags ) to the sheet column. We have Launched the [Googlesheet Connector PRO version](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?wp-repo) with more automated features.

= ✨[PRO Features](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?wp-repo)✨ =
➜ Custom Google API Integration Settings
➜ Allowing to Create a New Sheet from Plugin Settings
➜ Custom Ordering Feature / Manage Fields to Display in Sheet using Enable-Disable / Edit the Fields/ Headers Name to display in Google Sheet.
➜ Using all the [Special Mail Tags](https://contactform7.com/special-mail-tags/) Fields in Headers
➜ Syncronize Existing Entries for WPForms PRO users
➜ Freeze Header Settings
➜ Header Color and Row Odd/Even Colors.
Refer to the features and benefits page for more detailed information on the features of the [WPForms Google Sheet PRO Addon Plugin](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?wp-repo)


= ⚡️ Check Live Demo =
[Demo URL: CF7 Google Sheet](https://cf7demo.gsheetconnector.com)

[Google Sheet URL to Check submitted Data](https://docs.google.com/spreadsheets/d/1ooBdX0cgtk155ww9MmdMTw8kDavIy5J1m76VwSrcTSs/)

= ⚡️ How to Use this Plugin =

* **Step: 1 - [In Google Sheets](https://sheets.google.com/)** 
➜ Log into your Google Account and visit Google Sheets.  
➜ Create a new Sheet and name it.  
➜ Rename or keep default name of the tab on which you want to capture the data. 
➜ Copy Sheet Name, Sheet ID, Tab Name and Tab ID (Refer Screenshots)

* **Step: 2 - In WordPress Admin** 
➜ Create or Edit the Contact Form 7 form from which you want to capture the data. Set up the form as usual in the Form and Mail etc tabs. Thereafter, go to the new "Google Sheets" tab.  
➜ On the "Google Sheets" tab, copy-paste the Google Sheets sheet name and tab name into respective positions, and hit "Save".

* **Step: 3 - Arranging Columns in Sheet**
➜ In the Google sheets tab, provide column names in row 1. The first column should be "date". For each further column, copy paste mail tags from the Contact Form 7 form (e.g. "your-name", "your-email", "your-subject", "your-message", etc).  
➜ Test your form submit and verify that the data shows up in your Google Sheet.

= 🔥 Videos to help you get started with CF7 Google Sheets Connector =

🚀How to Install, Authenticate and Integrate Contact Form with your Google Sheet.

[youtube https://www.youtube.com/watch?v=E_dVAQHyBlw]


= Important Notes = 

➜ You must pay very careful attention to your naming. This plugin will have unpredictable results if names and spellings do not match between your Google Sheets and form settings.

👉 [Get CF7 GoogleSheetConnector PRO today](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?wp-repo)

== Installation ==

1. Upload `cf7-google-sheets-connector` to the `/wp-content/plugins/` directory, OR `Site Admin > Plugins > New > Search > CF7 Google Sheets Connector > Install`.  
2. Activate the plugin through the 'Plugins' screen in WordPress.  
3. Use the `Admin Panel > Contact form 7 > Google Sheets` screen to connect to `Google Sheets` by entering the Access Code. You can get the Access Code by clicking the "Get Code" button. 
Enjoy!

== Screenshots ==

1. Google Sheet Integration without authentication  
2. Permission page if user is already logged-in to there account. 
3. Permission popup-1 after logged-in to your account.
4. Permission popup-2 after logged-in to your account.
5. After successful integration - Displays "Currently Active".
6. Google Sheet settings page with input box Sheet Name, Sheet Id, Tab Name, Tab Id.
7. Get Sheet and Tab Id from the URL.
8. Google Sheet headers with Special Mail Tags.

== Frequently Asked Questions ==

= Why isn't the data send to spreadsheet? CF7 Submit is just Spinning. = 
Sometimes it can take a while of spinning before it goes through. But if the entries never show up in your Sheet then one of these things might be the reason:

1. Wrong access code ( Check debug log )
2. Wrong Sheet name or tab name
3. Wrong Column name mapping ( keep in mind that not to use capital letters, number as initial and special characters like underscores, double or single code, space etc. You can only use small letters and hyphen. )

Please double-check those items and hopefully getting them right will fix the issue.

= How do I get the Google Access Code required in step 3 of Installation? =

* On the `Admin Panel > Contact form 7 > Google Sheets` screen, click the "Get Code" button.
* In a popup Google will ask you to authorize the plugin to connect to your Google Sheets. Authorize it - you may have to log in to your Google account if you aren't already logged in. 
* On the next screen, you should receive the Access Code. Copy it. 
* Now you can paste this code back on the `Admin Panel > Contact form 7 > Google Sheets` screen. 

== Changelog ==

= 5.0.6 = (05-10-2023)
* Fixed : Resolved debugging view, open and close link systematically.
* Added : For user without Google Drive and Google Sheets permissions displayed error message.

= 5.0.5 = (03-10-2023)
* Added: Get Code button has replaced with the Sign in with Google button.
* Added: For user without Google Drive and Google Sheets permissions authentication shows alert with message.
* Added: Redesigned System Status and Error Log for improved functionality and user experience.
* Fixed : Vulnerabilities issues.

= 5.0.4 = (24-06-2023)
* Added: few more minor UI changes.

= 5.0.3 = (04-05-2023)
* Added: minor UI changes.

= 5.0.2 = (27-04-2023)
* Fixed : Vulnerabilities issues. 

= 5.0.1 = (23-02-2023)
* Added : Remove access permission from google account while deactivating authentication.
* Fixed : Make it compatible with plugin Smart Grid-Layout Design for Contact Form 7.


= 5.0.0 = (05-08-2022)
* Updated: API Library to latest version 2.12.6 and Guzzle Library to version 7.4.3
* Migrated: OAuth OOB to alternative method as getting deprecated from 3rd October 2022.

= 4.9.2 = (19-01-2022)
* Fixed: \'Line Break\' on textarea issue resolved.

= 4.9.1 =
* Fixed: Undefined index issue.

= 4.9 =
* Fixed issue with incorrect or expired auth code
* Fixed deactivation issue while adding incorrect and expired auth code.
* Fixed displaying of error while setting Contact Form initially.

= 4.8 =
* Fixed vulnerability issues.
* Added 'Upgrade to PRO' links.
* Added Google sheet link in settings.
* Updated Google API version to 2.10.1 and Guzzle Library to 7.3.0
* Did few UI changes.

= 4.7 =
* New: Displayed authenticated email id at the integration page.
* Fixed: Data not getting saved under exact column names.
* Fixed: composer functions to avoid clashing with other plugins.

= 4.6 =
* Updated API library to avoid conflicts with "Facebook for WordPress" plugin and others.

= 4.5 =
* Fixed saving of incorrect file name to the Google Sheet.

= 4.4 =
* Fixed the special mail tag issue due to WordPress Contact Form 7 5.2.2 

= 4.3 =
* Fixed the special mail tag issue due to WordPress Contact Form 7 5.2.1 

= 4.2 =
* Allow user to deactivate authentication.
* Fixed - conflicts error.

= 4.1 =
* Fixed displaying of single quote sign in front of numeric and date values.

= 4.0 =
* Upgrade Google APIs Client Library to version v4.
* CF7 input field names and header name can have capital letters, underscore, number and space. 
* Fixed addition of backslash in front of apostrophes and quotation marks

= 3.0 =
* Update API Library.
* Allowed user to permanently close Google Sheet Connector Pro notice.

= 2.9 =
* Hide Google Sheet menu and settings as per user role contact form 7 edit capabilities.

= 2.8 =
* Fixed - Displaying of Google Sheet Connector notice to be dismissible.

= 2.7 =
* Done UI changes.
* Fixed - Not to delete authentication data when upgrade to Pro Version.
* Changed few classes and functions name to not get conflict when upgrade to pro Version

= 2.6 =
* Done UI changes at Google Sheet Tab under Contact Form settings.

= 2.5 =
* Removed Limitation

= 2.4 =
* Fixed - Connections of Contact Forms with Google Sheet.
* Added limitation to connect first 5 Contact Forms to Google Sheet.

= 2.3.1 =
* Fixed images not being displayed.

= 2.3 =
* Fixed integration issues.
* Fixed the functionality issues of limitations as per the last update.

= 2.2 =
* Done few UI changes and solved few bugs.
* Added a limitation for contact form to be connected with the Google Sheet.

= 2.1 =
* Added Google Sheet Connector dashboard widget for quick access to the contact form connected with Google Sheet.
* Added option to clear logs.
* Fixed - Multisite plugin activation issue.

= 2.0 ( 11/06/2018 ) =
* Fixed - Bypassing of CF7 built-in data validation.

= 1.9 =
* Fixed - Not fetching contact form data to google sheet.

= 1.8 =
* Fixed the hijacking( loading issue front-end form ) of page after submit actions.
* Integrated New Special Mail Tags ( including flamingo serial number ) with Spread Sheet without (_) underscores(Refer screenshot).

= 1.7 (26/08/2017) =
* Integrated Special Mail Tags with Spread Sheet without (_)underscores(Refer screenshot).
* Fixed Date Format as per wordpress standards.

= 1.6 =
* Updated Google Spread Sheet Library
* Changed classes name for PHP Google Auth library.
* Delete all data on uninstallation of plugin.

= 1.5 =
* Fixed more class names due to conflict with other plugins.
* Fixed issue to send hidden fields via dynamic text extension.
* Added settings link on activation of plugin.

= 1.4 =
* Fixed 500 Internal Server Error if sheet name or tab name is not set.

= 1.3 =
* Added .pot file for easier translation.

= 1.2 =
* Updated plugin description etc.

= 1.1 =
* Fixed date format and display issues related to non-English dates. 
* Fixed the class name due to conflict with other plugins.

= 1.0 =
* First public release
* Integrated Contact form 7 with Google sheets.
