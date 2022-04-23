# Charter Boat Bookings
Contributors: megphillips91

Author: Meg Phillips

Author URI: https://msp-media.org/

Donate link: https://msp-media.org/product/support-open-source/

Stable tag: 2.0.01

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Charter Boat Bookings is a boat booking system for captains - Sunset Sails, Daysails, Sportfishing, Inshore Fishing


## Description
This is a plugin designed for WordPress. The goal of this plugin is to serve as a backend REST API for whatever blocks, admin, etc needs to be created in ReactJS to build the UX. 

After several years of this plugin being a WooCommerce extension, I realized that it really wasn't working for the bookings object to be so entangled with the payments. This was consistent feedback from all my customers, including my husband. Everyone wants more direct control over the bookings - to CRUD the bookings regardless of payment status. There were a couple other major things I did naively when I first wrote this plugin, and honestly then I neglected it for a few years. 

In the meantime, I came to really prefer writing in ReactJS. I appreciate that React is now a part of WordPress, and I intend to give myself the ability to take full advantage of that and offer a faster and better experience for charter captains...mainly my husband. And also, that old code was quite ugly with a security problem...and so it was suspended. 

## WooCommerce Integration
The goal is still paid bookings through WooCommerce, but less entangled. The idea is that the booking can be free, or require payments. That integration can be triggered through the Calendar Booking Block and the "add to cart" will just be a simple charter payment. 
- the integration of each date as a product variation was just overly complicated
- the concerns will be very separate and give captains more control over how and when to accept online payment reservations and final payments. 
- I think I can trigger woocommerce plugin install/activation wizard when the content creator chooses the calendar booking block so I'm going to experiment with all that before I even try to begin publishing this integration. 

With the new WooCommerce Store API and the **coming soon** new order object table I think its going to be far far less code to maintain and better overall for me to create something that I am much happier with that functions less 'buggy'. I don't really have much of a choice because my husband has shopped all the other booking systems and still wants this one even after I pleaded with him to check out other options. So, here we are. The whole thing sort of came to a suddenly critical problem when he had customers at the dock that he was not prepared for. Uh Oh!

At this point, the plan is to use this particular plugin as a sort of 'internal api' and transition slowly.

## A Gallery of plugins
When I originally wrote Charter Boat Bookings, I did not fully understand ReactJS. In fact, I was only just learning it. Now that I have a mnuch more mature understanding and a better appreciation for how much easier it is to maintain...I want to build a gallery of plugins which are all quite separate each other as to better enable myself to maintain all the parts / peices and retire the ones that can be replaced by other plugins maintained by other people (I hope at some point). So...it shall be a series of plugins including the following:

### WooCommerce Extension: Charter Payments
Depending on the user experience that eventually works itself into reality, I may be able to use an existing Woo extension but if not, I can pretty easily modify the Booking Blocks to "add to cart" in addition to "adding to bookings" and then I can consider what/if any integration between payment_status and booking_status needs to be considered. At the moment, most likely I figure just adding order_ids to the booking_meta table for every order associated with a particular booking. Although several of my charter boat captains are just wanting more like a customer statement view that shows bookings against payments and refunds more like a charter customer statement. 

### Charter Boat Bookings: A Core Bookings Rest API
Each of the blocks and the admin experience will live in separate plugins. Even if it seems insane, I want to keep them all separate so that other people can toy around with the customer facing shopping experience and the WP Admin experience that they like the best. Maybe at some point, this Booking Rest API will be useful to some other bookings plugins and I can get a little help maintaining the back end code.  combine efforts on the core 'bookings' plugin rest api. 

### Charter Booking Blocks: Booking Calendar
The goal is to publish the booking calendar block and a "global" booking calendar block like the one that I offered in Charter Boat Bokings Pro. The global block will pull all the charters onto the same availability calendar. The Remote Booking Calendar block will offer affiliate bookings for boats living on different URLs. 

### Charter Bookings Admin Experience
and a react app which can take all the settings, etc from the WP-Admin or maybe in a standable ReactJS PWA. I haven't decided yet on that one. Its honestly easier to do the standalone, and I think there is more long term potential if I do it that way. IDK.

## History

## Separation of concerns


