=== LTL Freight Quotes – GlobalTranz Edition ===
Contributors: enituretechnology
 Tags: eniture,GlobalTranz,,LTL freight rates,LTL freight quotes, shipping estimates
Requires at least: 6.4
Tested up to: 6.6.2
Stable tag: 2.3.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Real-time LTL freight quotes from Cerasis. Fifteen day free trial.

== Description ==

GlobalTranz (http://globaltranz.com) is a third party logistics company that gives its customers access
to UPS and over 60 LTL freight carriers through a single account relationship. The plugin retrieves 
the LTL freight rates you negotiated globaltranz, takes action on them according to the plugin settings, and displays the 
result as shipping charges in your WooCommerce shopping cart. To establish a Cerasis account call 1.866.275.1407.

**Key Features**

* Three rating options: Cheapest, Cheapest Options and Average.
* Custom label results displayed in the shopping cart.
* Control the number of options displayed in the shopping cart.
* Display transit times with returned quotes.
* Restrict the carrier list to omit specific carriers.
* Product specific freight classes.
* Support for variable products.
* Option to determine a product's class by using the built in density calculator.
* Option to include residential delivery fees.
* Option to include fees for lift gate service at the destination address.
* Option to mark up quoted rates by a set dollar amount or percentage.

**Requirements**

* WooCommerce 6.4 or newer.
* A Globaltranz customer ID.
* Your username and password to globaltranz's online shipping system.
* Your Globaltranz web services authentication key.
* An API from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your Globaltranz Customer ID.
* Your username and password to Globaltranz's online shipping system.
* Your Globaltranz web services authentication key.

If you need assistance obtaining any of the above information, contact your local Cerasis office
or call the [Globaltranz](http://globaltranz.com) corporate headquarters at 1.866.275.1407.

A more extensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-globaltranz-ltl-freight/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "eniture ltl freight quotes", and click Install Now.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get a an API from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-globaltranz-ltl-freight/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your API keys and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the API key. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => Globaltranz Quotes. Use the *Connection* link to create a connection to your Globaltranz
account.

**4. Identify the carriers**
Go to WooCommerce => Settings => Globaltranz Quotes. Use the *Carriers* link to identify which carriers you want to include in the 
dataset used as input to arrive at the result that is displayed in your cart. Including all carriers is highly recommended.

**5. Select the plugin settings**
Go to WooCommerce => Settings => Globaltranz Quotes. Use the *Quote Settings* link to enter the required information and choose
the optional settings.

**6. Define warehouses and drop ship locations**
Go to WooCommerce => Settings => Globaltranz Quotes. Use the *Warehouses* link to enter your warehouses and drop ship locations.  You should define at least one warehouse, even if all of your products ship from drop ship locations. Products are quoted as shipping from the warehouse closest to the shopper unless they are assigned to a specific drop ship location. If you fail to define a warehouse and a product isn’t assigned to a drop ship location, the plugin will not return a quote for the product. Defining at least one warehouse ensures the plugin will always return a quote.

**7. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the Shipping Zones link. Add a US domestic shipping zone if one doesn’t already exist. Click the “+” sign to add a shipping method to the US domestic shipping zone and choose Globaltranz from the list.

**8. Configure your products**
Assign each of your products and product variations a weight, Shipping Class and freight classification. Products shipping LTL freight should have the Shipping Class set to “LTL Freight”. The Freight Classification should be chosen based upon how the product would be classified in the NMFC Freight Classification Directory. If you are unfamiliar with freight classes, contact the carrier and ask for assistance with properly identifying the freight classes for your  products.

== Frequently Asked Questions ==

= What happens when my shopping cart contains products that ship LTL and products that would normally ship FedEx or UPS? =

If the shopping cart contains one or more products tagged to ship LTL freight, all of the products in the shopping cart 
are assumed to ship LTL freight. To ensure the most accurate quote possible, make sure that every product has a weight 
and dimensions recorded.

= What happens if I forget to identify a freight classification for a product? =

In the absence of a freight class, the plugin will determine the freight classification using the density calculation method. 
To do so the products weight and dimensions must be recorded.

= Why was the invoice I received from Globaltranz more than what was quoted by the plugin? =

One of the shipment parameters (weight, dimensions, freight class) is different, or additional services (such as residential 
delivery, lift gate, delivery by appointment and others) were required. Compare the details of the invoice to the shipping 
settings on the products included in the shipment. Consider making changes as needed. Remember that the weight of the packaging 
materials,such as a pallet, is included by the carrier in the billable weight for the shipment.

= How do I find out what freight classification to use for my products? =

Contact your local Globaltranz office for assistance. You might also consider getting a subscription to ClassIT offered 
by the National Motor Freight Traffic Association (NMFTA). Visit them online at classit.nmfta.org.

= How do I get a Globaltranz account number? =

Globaltranz is a US national franchise organization. Check your phone book for local listings or call its corporate 
office at 1.866.275.1407 and ask how to contact the sales office serving your area.

= Where do I find my Globaltranz username and password? =

Usernames and passwords to Globaltranz’s online shipping system are issued by Globaltranz. Contact the Globaltranz office servicing your account to request them. If you don’t have a Globaltranz account, contact the Globaltranz corporate office at 1.866.275.1407.

= Where do I get my Cerasis authentication key? =

You can can request an authentication key by logging into Cerasis’s online shipping system (cerasis.com) and 
navigating to Services > Web Services. An authentication key will be emailed to you, usually within the hour.

= How do I get an API key for my plugin? =

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or 
purchased an API key outright. At the conclusion of the registration process an email will be sent to you that will include the 
API key. You can also login to eniture.com using the username and password you created during the registration process 
and retrieve the API key from the My API keys tab.

= How do I change my plugin API key from the trail version to one of the paid subscriptions? =

Login to eniture.com and navigate to the My API keys tab. There you will be able to manage the licensing of all of your 
Eniture Technology plugins.

= How do I install the plugin on another website? =

The plugin has a single site API key. To use it on another website you will need to purchase an additional API key. 
If you want to change the website with which the plugin is registered, login to eniture.com and navigate to the My API keys tab. 
There you will be able to change the domain name that is associated with the API key.

= Do I have to purchase a second API key for my staging or development site? =

No. Each API key allows you to identify one domain for your production environment and one domain for your staging or 
development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website? =

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > Globaltranz Quotes > Connections) 
then you have one or more of the following licensing issues:

1) You are using the API key on more than one domain. The API keys are for single sites. You will need to purchase an additional API key.
2) Your trial period has expired.
3) Your current API key has expired and we have been unable to process your form of payment to renew it. Login to eniture.com and go to the My API keys tab to resolve any of these issues.

== Screenshots ==

1. Carrier inclusion page
2. Quote settings page
3. Quotes displayed in cart

== Changelog ==

= 2.3.11 =
* Fix: Fixed empty response issue in GlobalTranz response

= 2.3.10 =
* Update: Introduced an error management feature.
* Update: Introduced a liftgate weight restriction rule.
* Update: Introduced backup rates.
* Fix: Corrected the order of the plugin tabs.
* Fix: Resolved issues with the calculation of live shipping rates in draft orders.

= 2.3.9 =
* Update: Updated connection tab according to wordpress requirements 

= 2.3.8 =
* Fix: Corrected the link for Unishippers Freight account

= 2.3.7 =
* Update: Added FedEx Freight Economy and FedEx Freight Priority in the carriers list

= 2.3.6 =
 * Fix: Fixed product level markup. 

= 2.3.5 =
* Fix: Added changes for liftgate in GTZ API

= 2.3.4 =
* Update: Introduced capability to suppress parcel rates once the weight threshold has been reached.
* Update: Compatibility with WordPress version 6.5.2
* Update: Compatibility with PHP version 8.2.0
* Fix:  Incorrect product variants displayed in the order widget.

= 2.3.3 =
* Fix: Resolved an error encountered during warehouse creation.

= 2.3.2 =
* Update: Introduced a field for the maximum weight per handling unit.
* Update: Updated the description text in the warehouse.

= 2.3.1 =
* Update: Changed required plan from standard to basic for delivery estimate options.

= 2.3.0 =
* Update: Display "Free Shipping" at checkout when handling fee in the quote settings is  -100% .
* Update: Introduced the Shipping Logs feature.
* Update:  Introduced “product level markup” and “origin level markup”.

= 2.2.9 =
* Update: Changed required plan from standard to basic for Limited Access Delivery

= 2.2.8 =
* Fix: Resolved conflict with Distance Based Shipping Calculator  

= 2.2.7 =
* Fix: Fixed order of options on quote settings tab. 

= 2.2.6 =
* Fix: Fixed required plan link Limited Access Delivery.  

= 2.2.5 =
* Update: Introduced Limited Access Delivery. 

= 2.2.4 =
* Update: Compatibility with High-Performance Order Storage(Woocommerce new order storage machanism)
* Update: Reformatted Instore pickup and local delivery response.

= 2.2.3 =
* Update: Fixed threshold issue with UPS Small Package 

= 2.2.2 =
* Update: Add programming to switch the Worldwide account to New/Old API. 

= 2.2.1 =
* Update: Added Worldwide Express migration programming. 

= 2.2.0 =
* Update: Introduced Worldwide Express LTL new API OAuth process with client ID and client secret.

= 2.1.9 =
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations.
* Fix: Fixed space character issue in city name.

= 2.1.8 =
* Fix: Fixed issue in calculation of weight threshold. 

= 2.1.7 =
* Update: Text changes in FreightDesk.Online coupon expiry notice

= 2.1.6 =
* Update: Added compatibility with "Address Type Disclosure" in Residential address detection 

= 2.1.5 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 2.1.4 =
* Fix: Fixed issue in product CSV export. 

= 2.1.3 =
* Update: By default mark all carriers checked. 

= 2.1.2 =
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID

= 2.1.1 =
* Update: Compatibility with WordPress version 6.0.

= 2.1.0 =
* Update: Introduced coupon code for freightdesk.online and validate-addresses.com.

= 2.0.5 =
* Update: Compatibility with PHP version 8.1.
* Update: Compatibility with WordPress version 5.9.

= 2.0.4 =
* Update: added option in quote settings to show/hide WooCommerce flat rate.

= 2.0.3 =
* Update: Isolate flat rate from GTZ api request. 

= 2.0.2 =
* Update: Show WooCommerce Shipping Options dropdown functionality.

= 2.0.1 =
* Update: Relocation of NMFC Number field along with freight class.
* Update: Added features, Multiple Pallet Packaging and data analysis.

= 2.0.0 =
* Update: Compatibility with PHP version 8.0.
* Update: Compatibility with WordPress version 5.8.
* Fix: Corrected product page URL in connection settings tab.

= 1.2.2 =
* Update: Introduced Handling weight unit for freightdesk.online.

= 1.2.1 =
* Update: Added feature "Weight threshold limit".
* Update: Added feature In-store pickup with terminal information.

= 1.2.0 =
* Update: Showing order widget  for GT option.
* Update: Carriers list added for GT.
* Update: Cuttoff Time.
* Update: CSV columns updated.
* Update: Virtual product at order widget.

= 1.1.6 =
* Update: Introduced new features : 
* Compatibility with WordPress 5.7,
* Order detail widget for draft orders,
* Improved order detail widget for Freightdesk.online,
* Compatibly with Shippable add-on,
* Compatibly with Account Details(ET) add-don(Capturing account number on checkout page).

= 1.1.1 =
* Update: Compatibility with Flat Rate addon

= 1.1.0 =
* Update: Compatibility with WooCommerce 5.6

= 1.0.1 =
* Update: Introduced product nesting feature. 

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

