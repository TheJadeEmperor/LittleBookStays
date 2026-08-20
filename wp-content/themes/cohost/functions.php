<?php

require_once get_stylesheet_directory() . '/prop_hub.php';
require_once get_stylesheet_directory() . '/power_dialer.php';

// ============================================================
// Onboarding Checklist — WordPress admin page version
// Uses $wpdb since onboarding_tasks lives in the same DB as WordPress
// Add this to functions.php in the cohost theme
// ============================================================

// --- Register the admin menu page ---
add_action('admin_menu', 'lbs_add_admin_pages');

function lbs_add_admin_pages() {
    add_menu_page(
        'Onboarding',
        'Onboarding',
        'manage_options',
        'lbs-onboarding',
        'lbs_render_onboarding_page',
        'dashicons-yes-alt',
        3
    );
 
    add_menu_page(
        'Standard Operating Procedures', //page_title
        'SOP', //menu_title
        'manage_options',  //capability
        'lbs-sop', //menu_slug 
        'lbs_sop_page', //function 
        'dashicons-yes-alt', //icon_url
        4  //position 
    );

    add_submenu_page(  
        'lbs-sop', //parent_slug
        'SOP Bad Review', //page_title
        'Bad Review SOP', //menu_title
        'manage_options', //capability
        'bad-review', //menu_slug
        'sop_bad_review', //function
        1 //
    );

    add_submenu_page(  
        'lbs-sop', //parent_slug
        'Early C-in & Late C-out', //page_title
        'Late C-out', //menu_title
        'manage_options', //capability
        'late-c-out', //menu_slug
        'late_c_out', //function
        4 //
    );

    add_submenu_page(  
        'lbs-sop', //parent_slug
        'Owner Stay aka Owner Use', //page_title
        'Owner Stay', //menu_title
        'manage_options', //capability
        'owner-stay', //menu_slug
        'owner_stay', //function
        4 //
    );

    add_submenu_page(  
        'lbs-sop', //parent_slug
        'VRBO Extra Charge for Damage or Unauthorized Checkout', //page_title
        'VRBO Extra Charge', //menu_title
        'manage_options', //capability
        'vrbo-extra-charge', //menu_slug
        'vrbo_extra_charge', //function
        1 //
    );

    add_submenu_page(  
        'lbs-sop', //parent_slug
        'Reporting Goddam Guests', //page_title
        'Goddam Guests', //menu_title
        'manage_options', //capability
        'goddam-guests', //menu_slug
        'goddam_guests', //function
       1 //
    );


    add_submenu_page(  
        'lbs-sop', //parent_slug
        'RSVP Cancellation Refund Policies', //page_title
        'RSVP Cancellation ', //menu_title
        'manage_options', //capability
        'rsvp-cancel', //menu_slug
        'rsvp_cancel', //function
       1 //
    );


    add_submenu_page(  
        'lbs-sop', //parent_slug
        'Items Left Behind | Abandoned Items | Instr for Cleaners', //page_title
        'Items Left Behind', //menu_title
        'manage_options', //capability
        'items-left-behind', //menu_slug
        'items_left_behind', //function
        4 //
    );

    
    add_submenu_page(  
        'lbs-sop', //parent_slug
        'Sourcing Contractors', //page_title
        'Contractors', //menu_title
        'manage_options', //capability
        'contractors', //menu_slug
        'contractors', //function
        4 //
    );

     add_submenu_page(  
        'lbs-sop', //parent_slug
        'Maintenance SOP', //page_title
        'Maintenance SOP', //menu_title
        'manage_options', //capability
        'maint-sop', //menu_slug
        'maint_sop', //function
        4 //
    );
}


function lbs_sop_page () {
    ?>
    <h1>SOP</h1>
    <p>&nbsp;</p>

    <h2>Goddam Guests</h2>
    <p><a href="admin.php?page=goddam-guests">Reporting Goddam Guests</a></p>
    <p><a href="admin.php?page=lbs-sop">Bad Review SOP</a></p>
    <p><a href="admin.php?page=vrbo-extra-charge">VRBO Extra Charge Request</a></p>
    <p><a href="admin.php?page=rsvp-cancel">RSVP Cancellation Refund Policies</a></p>


    <h2>Items Left Behind</h2>
    <p><a href="admin.php?page=items-left-behind">Items Left Behind</a></p>

    <h2>Contractors</h2>
    <p><a href="admin.php?page=vrbo-extra-charge">Contractors</a></p>

    <h2>Contractors</h2>
    <p><a href="admin.php?page=contractors">Contractors</a></p>
    <p><a href="admin.php?page=maint-sop">Maintenance SOP</a></p>

    <h2>Owner Use or Owner Stay</h2>
    <p><a href="admin.php?page=owner-stay">Owner Use or Owner Stay</a></p>

    <?php
}

function rsvp_cancel () { 
    ?>

    <h1>Choose from 1 of these options</h1>


    <h2>Firm - stick to policy</h1>

    <p>Do not give a refund</p>

    <h2>Immediate Rebook Refund</h2>

    <p>Hi %guest_name% </p>
    <p>Thanks again for speaking with me earlier! As discussed, per our cancellation policy, bookings canceled within 6 days of check-in are non-refundable. However, we want to make sure you get value out of your trip!</p>

    <p>Here is what we can do for you:</p>

    <p>Immediate Rebook Refund: If you initiate the cancellation on your end now, we will immediately reopen the calendar. We will gladly issue a cash refund for any nights that get rebooked by another guest. </p>

    <h2>1 Year Credit</h2>


    <p>Hi  %guest_name% </p>
    Thanks again for speaking with me earlier! As discussed, per our cancellation policy, bookings canceled within 6 days of check-in are non-refundable. However, we want to make sure you get value out of your trip!

    <p>Here is what we can do for you:</p>

    <p>1-Year Stay Credit: If any of your nights do not rebook, we will convert that remaining balance into a 1-Year Credit! You can apply it toward a future stay at this home anytime within the next 12 months (subject to availability, equal nightly value, and excluding major holiday periods).*</p>


    <h2>Immediate Rebook Refund & 1 Year Credit</h2>


    <p>Hi  %guest_name% </p>
    <p>Thanks again for speaking with me earlier! As discussed, per our cancellation policy, bookings canceled within 6 days of check-in are non-refundable. However, we want to make sure you get value out of your trip!</p>

    <p>Here is what we can do for you:</p>

    <p>Immediate Rebook Refund: If you initiate the cancellation on your end now, we will immediately reopen the calendar. We will gladly issue a cash refund for any nights that get rebooked by another guest.</p>

    <p>1-Year Stay Credit: If any of your nights do not rebook, we will convert that remaining balance into a 1-Year Credit! You can apply it toward a future stay at this home anytime within the next 12 months (subject to availability, equal nightly value, and excluding major holiday periods).*</p>

    <p>Please submit the cancellation request on your end whenever you're ready so we can get those dates back live on the calendar!</p>


    <?php
}



function maint_sop () { 
    ?>

    <h1>Format for Contractors Appt &amp; Payment</h1>

    <h2>Schedule Appt</h2>

    <p><strong>Quick Note (BEFORE SENDING):</strong></p>
    <ul>
    <li>We can't assume they will always be available so typically this is something we send once availability is confirmed for the date</li>
    <li>A phone call to discuss and explain the situation is preferred when possible</li>
    </ul>

    <p><strong>TEMPLATE:</strong></p>
    <ol>
    <li>Date of requested service (day of the week and date)</li>
    <li>We are looking for a vacancy in the calendar. We do not want to disturb guests unless they specifically request an issue to be addressed during their stay.</li>
    <li>This service will typically be during a turnover</li>
    <li>Address of service location</li>
    <li>Range of time they would have access (this will typically be from check-out to check-in, same day)</li>
    <li>Check-In Instructions (Door code, lockbox combination, etc.)</li>
    <li>Numerical list of items that need to be addressed</li>
    </ol>

    <p><a href="https://app.close.com/activities/custom-activity/actitype_3oEjtZdr8UkkqfmtJBDdWi/save_bjqFnOtjRgQsI0Qm9AqG4fSPAGxCtCfOgK6oskcFcIS/share_5laUSCgAYlWHZwV4wUiSk5/">https://app.close.com/activities/custom-activity/actitype_3oEjtZdr8UkkqfmtJBDdWi/save_bjqFnOtjRgQsI0Qm9AqG4fSPAGxCtCfOgK6oskcFcIS/share_5laUSCgAYlWHZwV4wUiSk5/</a></p>

    <h3>EXAMPLE 1:</h3>
    <p><strong>Thursday (3/30)</strong></p>
    <p><em>1111 Lilac Way, Davenport, FL 33897</em></p>
    <p>Time frame: 10am - 4pm</p>
    <p>Door Code: 0000</p>
    <ol>
    <li>Fix the plexiglass issue? We can purchase new plexiglass to replace</li>
    <li>Need to replace slabs on the curtains (Can we buy the amount we need?)</li>
    <li>Change the remotes needed for fans or do whatever is needed to ensure remotes are working for fans and lights</li>
    <li>Check if the ping pong table is unstable and what would be the fix</li>
    </ol>

    <h3>EXAMPLE 2:</h3>
    <p><strong>Wednesday (3/29) or Thursday (3/30):</strong></p>
    <p><em>1111 St Davenport FL 33897</em></p>
    <p>Time Frame: Any time before 4pm (can go earlier if available, there is no guest there right now)</p>
    <p>Lock box combination: 0000.</p>
    <ol>
    <li>Dishwasher not working (Need to evaluate, hopefully it's a simple fix)</li>
    </ol>

    <h2>Payment SOP</h2>

    <p>If plumber takes CC</p>
    <ul>
    <li>If you have owner's CC
        <ul>
        <li>Give Owner's CC info to plumber</li>
        </ul>
    </li>
    <li>If not
        <ul>
        <li>Pay plumber from your CC then invoice the owner</li>
        </ul>
    </li>
    </ul>

    <p>If vendor takes venmo/paypal – add to owner's CC +3% fee</p>

    <?php
}


function contractors () { 
    ?>
 
    <h1>Format for Contractors Appt &amp; Payment</h1>

    <h2>Schedule Appt</h2>

    <p><strong>Quick Note (BEFORE SENDING):</strong></p>
    <ul>
    <li>We can't assume they will always be available so typically this is something we send once availability is confirmed for the date</li>
    <li>A phone call to discuss and explain the situation is preferred when possible</li>
    </ul>

    <p><strong>TEMPLATE:</strong></p>
    <ol>
    <li>Date of requested service (day of the week and date)</li>
    <li>We are looking for a vacancy in the calendar. We do not want to disturb guests unless they specifically request an issue to be addressed during their stay.</li>
    <li>This service will typically be during a turnover</li>
    <li>Address of service location</li>
    <li>Range of time they would have access (this will typically be from check-out to check-in, same day)</li>
    <li>Check-In Instructions (Door code, lockbox combination, etc.)</li>
    <li>Numerical list of items that need to be addressed</li>
    </ol>

    <p><a href="https://app.close.com/activities/custom-activity/actitype_3oEjtZdr8UkkqfmtJBDdWi/save_bjqFnOtjRgQsI0Qm9AqG4fSPAGxCtCfOgK6oskcFcIS/share_5laUSCgAYlWHZwV4wUiSk5/">https://app.close.com/activities/custom-activity/actitype_3oEjtZdr8UkkqfmtJBDdWi/save_bjqFnOtjRgQsI0Qm9AqG4fSPAGxCtCfOgK6oskcFcIS/share_5laUSCgAYlWHZwV4wUiSk5/</a></p>

    <h3>EXAMPLE 1:</h3>
    <p><strong>Thursday (3/30)</strong></p>
    <p><em>1111 Lilac Way, Davenport, FL 33897</em></p>
    <p>Time frame: 10am - 4pm</p>
    <p>Door Code: 0000</p>
    <ol>
    <li>Fix the plexiglass issue? We can purchase new plexiglass to replace</li>
    <li>Need to replace slabs on the curtains (Can we buy the amount we need?)</li>
    <li>Change the remotes needed for fans or do whatever is needed to ensure remotes are working for fans and lights</li>
    <li>Check if the ping pong table is unstable and what would be the fix</li>
    </ol>

    <h3>EXAMPLE 2:</h3>
    <p><strong>Wednesday (3/29) or Thursday (3/30):</strong></p>
    <p><em>1111 St Davenport FL 33897</em></p>
    <p>Time Frame: Any time before 4pm (can go earlier if available, there is no guest there right now)</p>
    <p>Lock box combination: 0000.</p>
    <ol>
    <li>Dishwasher not working (Need to evaluate, hopefully it's a simple fix)</li>
    </ol>

    <h2>Payment SOP</h2>

    <p>If plumber takes CC</p>
    <ul>
    <li>If you have owner's CC
        <ul>
        <li>Give Owner's CC info to plumber</li>
        </ul>
    </li>
    <li>If not
        <ul>
        <li>Pay plumber from your CC then invoice the owner</li>
        </ul>
    </li>
    </ul>

    <p>If vendor takes venmo/paypal – add to owner's CC +3% fee</p>
 
    <?php
}


function items_left_behind () {
?>
    
    <h2>Instructions for Cleaners</h2>
    <ol>
    <li>Throw out any perishable items like food or drinks. Leave unopened water in the fridge for next guests.</li>
    <li>Keep items that we can use for the property like candles, board games, etc. for future guests to use.</li>
    <li>For personal items (such as jewelry, shoes, clothes, or a kid's stuffed animal), save them in the storage closet in case the guest would like to retrieve them.</li>
    </ol>

    <h2>Guest Communication</h2>
    <ol>
    <li>If the guest does not reach out within a week, the cleaners can discard the personal belongings during the next turnover.</li>
    <li>For guests who have left the city:
        <ol type="a">
        <li>If the guest would like their items mailed to them, ask for their mailing address and request money for extra services through the Airbnb or Vrbo platform.</li>
        <li>Only send the items after the shipping fee has been paid for.</li>
        </ol>
    </li>
    <li>For guests who live nearby or have not yet left the city:
        <ol type="a">
        <li>If the guest can stop by and pick up their items, please coordinate with the cleaning team so that future guests are not disturbed. Give the guests a specific time and date to pick up their items.</li>
        </ol>
    </li>
    </ol>
    

    <?php
}


function vrbo_extra_charge () {
?>
    <h2>VRBO Extra Charge Request</h2>
    <p>You can send a request for an additional payment for any extra charges like late checkout, parking permits, and other incidentals. You can add up to five additional payment requests per booking.</p>
    <p>Requested additional payments are not automatically charged; the guest must pay the additional payment in order to receive the stay extension or incidentals you are charging extra for. Payment requests expire if the guest doesn't pay after three days.</p>
    <ol>
    <li>Log in to your account.</li>
    <li>Select the listing if you have multiple properties.</li>
    <li>Select <strong>Inbox</strong>.</li>
    <li>Select <strong>Filters</strong> to filter your conversations.
    <ul>
        <li>You can also enter the guest's name or reservation ID (Res ID) into the search bar.</li>
    </ul>
    </li>
    <li>Select the guest's name.</li>
    <li>Select <strong>Add extra charge</strong> from Payment schedule.</li>
    <li>Select <strong>Fee name</strong> from the drop-down menu. Enter the full amount, due date, and a brief message.</li>
    <li>Select <strong>Review</strong>.</li>
    <li>Select <strong>Send additional charge</strong>.</li>
    </ol>
    <p>Should you require any further assistance, you can reach out to us back through chat and phone support via this number<br>
    <strong>877-228-3145 or 877-202-4291</strong></p>

    <p><em>Scenario: guest did some damage, or missing items, or caused extra cleaning</em></p>

    <h3>Damage claim:</h3>
    <ul>
    <li>Go to Inbox on Vrbo and select that RSVP</li>
    <li>Select damage claim - it goes to an external insurance service called CSA</li>
    <li>Submit pictures &amp; receipts of claim</li>
    </ul>
    <ul>
    <li>Ask cleaner for pictures of damages</li>
    <li>Get receipt from cleaner for extra cleaning charge</li>
    <li>Get receipt from handyman</li>
    </ul>
    <p>After claim - wait 5 days for an email from Generali Global Assistance</p>

    <h2>Initiate your payment from Generali Global Assistance</h2>
    <ol>
    <li>Log in or create an account using this email address.</li>
    <li>Verify your identity.</li>
    <li>Select your preferred method of payment.</li>
    <li>Initiate your payment.</li>
    <li>Get your payment within 1 hours to 3 days.</li>
    </ol>


<?php
}


function goddam_guests () {
    ?>

    <h2>Dispute Refund / Reporting Hostile Guests</h2>
    <ul>
    <li>Report guest's profile on Airbnb - select scammer</li>
    <li>Then contact help on Airbnb - talk to a rep</li>
    </ul>

    <p><strong>Message to Rep:</strong></p>

    <p>Hello [Representative Name], thank you for your message and for respecting our time zone difference. We really appreciate it.</p>

    <p>Thanks for bringing this to our awareness about [Guest].</p>

    <p>We were happy to resolve directly with the guest. However, after some back and forth the guest got hostile and have made threats in which we felt uncomfortable. We want to mention that we've reported this guest and his account for multiple accounts of harassment before today. He has also contacted us at unreasonable hours in the night.</p>

    <p>We do not list or advertise an ___ as an amenity in our listing, therefore that information is irrelevant. The guest failed to mention damage.</p>

    <p>In addition, the guest has violated our house rules to notify of any damages immediately and Airbnb's Rebooking and Refund Policy - to notify within 72 hours. He made mention of these things post check out and we do not believe it warrants compensation. We hope you'll take our feedback into careful consideration. We have been hosts a long time and value the honesty and support of the Airbnb community.</p>

    <?php
}


function hire_photographer () {
    ?> 
        
    <h1>Sourcing Photographer</h1>

    <h2>Where to find leads</h2>
    <ol>
    <li>Instagram: search "#yourtownrealestate" "#yourtownrealestatephotographer"</li>
    <li>Google: "Your town vacation rental photographer"</li>
    <li>Facebook groups: search in vacation rental groups for recommended photographers</li>
    </ol>

    <h2>Questions to Ask</h2>
    <ol>
    <li>How long have you been doing real estate photography?</li>
    <li>How many Airbnb's have you photographed?</li>
    <li>In your opinion what's the biggest difference between traditional real estate and vacation rental photos?</li>
    <li>Do you do any staging?</li>
    <li>How many photos are generally included?</li>
    <li>What is your turnaround time?</li>
    <li>How far out are you booking?</li>
    <li>Can we reschedule if the lighting isn't good?</li>
    <li>Do you offer aerial photography?</li>
    </ol>

    <h2>Pricing Questions</h2>
    <ol>
    <li>What is your most common package?</li>
    <li>Is there any additional cost to shoot at sunset/twilight?</li>
    </ol>

    <h2>Example photos we like</h2>
    <p>We are looking for photographers who understand how to create a "WOW" factor for the cover photo, this means they will be photoshopping the sky, making sure all lights are on, picking great angles etc.</p>
    <p>The time of day they shoot is also super important!</p>

    <h2>Importance of Photography</h2>
    <p><strong>Before</strong></p>
    <img src="p_before.jpg" alt="Before">
    <p><strong>After</strong></p>
    <img src="p_after.jpg" alt="After">
    <p>Exterior should be shot at twilight to market correctly</p>

    <h2>Day of considerations</h2>
    <ol>
    <li>Make sure unit is 100% CLEANED</li>
    <li>We would like for photographer to stage units, blow up pool toys put out games etc
        <ol type="a">
        <li>Turn on fire pit / hot tub / exterior lighting including string lights / open outdoor umbrellas.</li>
        <li>Make sure trash or broken furniture is out of picture</li>
        </ol>
    </li>
    <li>Ensure the weather is ideal for lighting purposes otherwise we should reschedule</li>
    <li>Ensure they have proper access codes and instructions</li>
    </ol>

    <h2>List of Photos</h2>
    <ol>
    <li>Every bathroom - 3-4 photos of bathroom including, sink, toilet, bathtub/shower, etc.</li>
    <li>Bedroom - 3-4 of every room including, closet, attached bathrooms, TV, view, etc.</li>
    <li>Kitchen - 4-5 including all appliances, island area, breakfast nook, etc.</li>
    <li>Living Room</li>
    <li>Game Room (if applicable)</li>
    <li>Pool/hot tub</li>
    <li>Patio Area including furniture, grill, etc.</li>
    <li>Formal dining room - 3-4 of every room</li>
    <li>Balcony</li>
    </ol>

    <h1>Things to avoid</h1>
    <p>See photo's below</p>

    <?php
}


function owner_stay () {
    ?>

    <h2>Owner Use or Owner Stay</h2>
    <p>Client uses their own house for a vacation.</p>
    <ol>
    <li>Create booking in Hospitable calendar</li>
    <li>Client pays the turnover fees for the previous turn - 2 choices:
        <ol type="a">
        <li>We pay the cleaning fee &amp; invoice the client - easier option for client
            <ul>
            <li>Do NOT use PayPal - they take a chunk of fees. $400 results in $385, a big chunk is lost</li>
            <li>Make invoice from Merc Bank</li>
            </ul>
        </li>
        <li>Client pays the cleaner's invoice directly - easier option for us</li>
        </ol>
    </li>
    </ol>

<?php
}


function late_c_out () {
    ?>

   <h2>Early Check-In Requests</h2>

<h3>Preceded by Orphan Day</h3>
<p><em>Guest asks for early check-in and no one is currently staying the night before guest is checking in</em></p>

<p><strong>If they asked for the early check-in within 48 hours of their scheduled check-in:</strong></p>
<p><strong>Response:</strong> We should be able to get you in a bit early! We currently don't have someone staying the night before, so assuming it stays that way, a check in time of <em>whatever the requested time is, so long as it is after <strong>noon</strong></em> should work. That said, if someone books the night before you at the last minute, then we will have to give our cleaner some time to clean up the house for you 😊 Right now you're good to go for the early check-in, but we will let you know if anything changes!</p>
<p><strong>Action:</strong> Make a note in the "Notes" area on Hospitable/Guesty that the guest will be checking in at ____ o'clock.</p>

<p><strong>If they asked for the early check-in more than 48 hours of their scheduled check-in:</strong></p>
<p><strong>Response:</strong> (canned answer on Hospitable/Guesty)<br>
Hi, %guest first name%! Unfortunately, we cannot promise an early check-in. Our cleaning window is typically between 11:00 AM - 3:00 PM. We will have a better idea on the day of your arrival whether it's possible. If cleaning is completed early, we would be happy to update you immediately.</p>

<h3>Preceded by Booked Day</h3>
<p><strong>If they asked for the early check-in within 48 hours of their scheduled check-in:</strong></p>
<p><strong>Response:</strong> We should be able to get you in a bit early! We currently don't have someone staying the night before, so assuming it stays that way, a check in time of <em>whatever the requested time is, so long as it is after <strong>noon</strong></em> should work. That said, if someone books the night before you at the last minute, then we will have to give our cleaner some time to clean up the house for you 😊 Right now you're good to go for the early check-in, but we will let you know if anything changes!</p>
<p><strong>Action:</strong> Make a note in the "Notes" area on Hospitable/Guesty that the guest will be checking in at ____ o'clock.</p>

<h2>How to charge guest for late check outs</h2>
<ul>
  <li>30-60 min late ⇒ $50 late fee</li>
  <li>Every hour after the first hour ⇒ $50 per hour</li>
</ul>
<p>Do this within Airbnb resolution center<br>
Or make an extra request on VRBO</p>

    <?php
}


function sop_bad_review() {
?>
    <h1>Bad Review SOP</h1>

    <p>Send message to guest from Hospitable<br>
    <a href="https://my.hospitable.com/inbox/segments/default">https://my.hospitable.com/inbox/segments/default</a></p>

    <hr>

    <h2>If guest agrees to delete review:</h2>
    <p>Airbnb said guest can call their hotline &amp; get it removed:<br>
    Airbnb's hotline: <a href="tel:4158005959">415 800 5959</a></p>

    <hr>

    <h2>What we can do is reply to their review</h2>
    <p>Public reply to the review from Hospitable<br>
    <a href="https://my.hospitable.com/inbox/segments/default">https://my.hospitable.com/inbox/segments/default</a></p>

    <hr>

    <h2>Review Form</h2>
    <p>Use review form - can only try 2x per review</p>
    <p><a href="https://www.airbnb.com/resolution/review_dispute/intro">https://www.airbnb.com/resolution/review_dispute/intro</a><br>
    Request to remove a review you received - Airbnb</p>

    </body>
    </html>


    <?php
}



// --- Helper: slugify for anchor ids ---
function lbs_slugify($text) {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// --- Helper: turn any http(s) URLs inside a label into clickable links,
// truncating the displayed text (not the href) if it's over 50 chars ---
function lbs_linkify_label($text) {
    $parts = preg_split('/(https?:\/\/[^\s]+)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1) {
            // Odd indices are the captured URLs. Trim trailing punctuation
            // that's likely sentence punctuation, not part of the URL.
            $url = rtrim($part, '.,);:');
            $display = strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
            $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                . esc_html($display) . '</a>';
        } else {
            $html .= esc_html($part);
        }
    }
    return $html;
}

// --- Helper: render a single task row ---
// Clicking anywhere on the row (except the checkbox, or a link inside the
// label) turns the label into an editable text field. Blurring saves it.
function lbs_render_task($task, $nested = false) {
    $key = esc_attr($task['task_key']);
    $classes = 'task' . ($nested ? ' nested' : '');
    echo '<div class="' . $classes . '" data-key="' . $key . '">';
    echo '<input type="checkbox" id="' . $key . '" data-key="' . $key . '"'
        . ($task['is_checked'] ? ' checked' : '') . '>';
    echo '<span class="task-label-wrap">';
    echo '<span class="task-label' . ($task['is_checked'] ? ' checked' : '') . '" data-key="' . $key . '" data-raw="'
        . esc_attr($task['label']) . '">'
        . lbs_linkify_label($task['label']) . '</span>';
    echo '</span>';
    echo '</div>';
}

// --- Main page render callback ---
function lbs_render_onboarding_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    $allTasks = $wpdb->get_results(
        "SELECT task_key, parent_key, section, subsection, label, is_checked
         FROM onboarding_tasks
         ORDER BY sort_order",
        ARRAY_A
    );

    $topLevel = [];
    $childrenOf = [];
    foreach ($allTasks as $task) {
        if ($task['parent_key'] === null) {
            $topLevel[] = $task;
        } else {
            $childrenOf[$task['parent_key']][] = $task;
        }
    }

    // Build TOC
    $toc = [];
    $seenSections = [];
    $seenSubsections = [];
    foreach ($topLevel as $task) {
        $section = $task['section'];
        if ($section !== null && !isset($seenSections[$section])) {
            $seenSections[$section] = true;
            $toc[] = ['level' => 'section', 'label' => $section, 'id' => lbs_slugify($section)];
        }
        $subsection = $task['subsection'];
        if ($subsection !== null) {
            $subKey = $section . '|' . $subsection;
            if (!isset($seenSubsections[$subKey])) {
                $seenSubsections[$subKey] = true;
                $toc[] = [
                    'level' => 'subsection',
                    'label' => $subsection,
                    'id'    => lbs_slugify($section . '-' . $subsection),
                ];
            }
        }
    }

    $totalCount = count($allTasks);
    $checkedCount = 0;
    foreach ($allTasks as $t) {
        if ($t['is_checked']) $checkedCount++;
    }

    $toggleNonce = wp_create_nonce('lbs_onboarding_toggle');
    $labelNonce  = wp_create_nonce('lbs_onboarding_label');
    $ajaxUrl = admin_url('admin-ajax.php');
    ?>
    <div class="wrap">
        <h1>Onboarding Checklist</h1>
        <div class="kc-progress"><?= $checkedCount ?> / <?= $totalCount ?> steps complete</div>

        <?php if (!empty($toc)): ?>
        <nav class="kc-toc" id="toc">
            <div class="kc-toc-title">Table of Contents</div>
            <div class="kc-toc-columns">
                <?php foreach ($toc as $entry): ?>
                    <a class="<?= $entry['level'] === 'subsection' ? 'kc-toc-subsection' : '' ?>"
                       href="#<?= esc_attr($entry['id']) ?>">
                        <?= esc_html($entry['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php endif; ?>

        <div id="kc-task-list">
        <?php
        $currentSection = null;
        $currentSubsection = null;

        foreach ($topLevel as $task):
            if ($task['section'] !== $currentSection) {
                $currentSection = $task['section'];
                $currentSubsection = null;
                echo '<h2 class="kc-section" id="' . esc_attr(lbs_slugify($currentSection)) . '">'
                    . esc_html($currentSection)
                    . ' <a class="kc-back-to-toc" href="#toc">&#8593; TOC</a></h2>';
            }
            if ($task['subsection'] !== $currentSubsection) {
                $currentSubsection = $task['subsection'];
                if ($currentSubsection !== null) {
                    echo '<h3 class="kc-subsection" id="' . esc_attr(lbs_slugify($currentSection . '-' . $currentSubsection)) . '">'
                        . esc_html($currentSubsection) . '</h3>';
                }
            }

            lbs_render_task($task);

            if (!empty($childrenOf[$task['task_key']])) {
                foreach ($childrenOf[$task['task_key']] as $child) {
                    lbs_render_task($child, true);
                }
            }
        endforeach;
        ?>
        </div>
    </div>

    <style>
        .kc-progress { font-size: 13px; color: #777; margin-bottom: 8px; }
        .kc-toc { background: #f7f7f7; border: 1px solid #e5e5e5; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; max-width: 900px; }
        .kc-toc-title { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #888; margin-bottom: 8px; }
        .kc-toc-columns { column-count: 2; column-gap: 28px; }
        .kc-toc a { display: block; font-size: 14px; line-height: 1.7; color: #333; text-decoration: none; break-inside: avoid; }
        .kc-toc a:hover { text-decoration: underline; }
        .kc-toc a.kc-toc-subsection { margin-left: 18px; color: #666; font-size: 13px; }
        #kc-task-list { column-count: 2; column-gap: 36px; max-width: 1200px; }
        .kc-section { font-size: 19px; margin-top: 40px; margin-bottom: 4px; padding-bottom: 6px; border-bottom: 2px solid #333; break-inside: avoid; display: flex; align-items: baseline; gap: 8px; }
        .kc-subsection { font-size: 15px; margin-top: 18px; margin-bottom: 4px; color: #555; text-transform: uppercase; letter-spacing: .03em; break-inside: avoid; }
        .kc-back-to-toc { font-size: 12px; font-weight: normal; text-transform: none; color: #999; text-decoration: none; }
        .task { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; break-inside: avoid; }
        .task.nested { margin-left: 28px; border-bottom: 1px dashed #eee; }
        .task input[type="checkbox"] { width: 17px; height: 17px; cursor: pointer; flex-shrink: 0; }
        .task-label-wrap { flex: 1; min-height: 20px; cursor: text; }
        .task-label { cursor: text; }
        .task-label.checked { text-decoration: line-through; color: #999; }
        .task-label a { color: #2271b1; text-decoration: none; }
        .task-label a:hover { text-decoration: underline; }
        .task-label-input {
            width: 100%;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 2px 6px;
            box-sizing: border-box;
        }
    </style>

    <script>
    (function () {
        const ajaxUrl = <?= json_encode($ajaxUrl) ?>;
        const toggleNonce = <?= json_encode($toggleNonce) ?>;
        const labelNonce = <?= json_encode($labelNonce) ?>;

        // --- Checkbox toggle ---
        document.querySelectorAll('#kc-task-list input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const taskKey = this.dataset.key;
                const checked = this.checked;
                const labelSpan = document.querySelector('.task-label[data-key="' + taskKey + '"]');

                const body = new URLSearchParams();
                body.append('action', 'lbs_toggle_onboarding_task');
                body.append('nonce', toggleNonce);
                body.append('task_key', taskKey);
                body.append('checked', checked ? '1' : '0');

                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        labelSpan.classList.toggle('checked', checked);
                    } else {
                        checkbox.checked = !checked;
                        alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                    }
                })
                .catch(function () {
                    checkbox.checked = !checked;
                    alert('Network error - could not save change.');
                });
            });
        });

        // --- Inline label editing ---
        // Clicking the label, or the empty space next to it (.task-label-wrap),
        // swaps the text into an editable input. Blurring saves it.
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Mirrors lbs_linkify_label() in PHP: turns URLs into clickable
        // links and truncates the displayed text (not the href) over 50 chars.
        function linkifyLabel(text) {
            const parts = text.split(/(https?:\/\/[^\s]+)/i);
            return parts.map(function (part, i) {
                if (i % 2 === 1) {
                    const url = part.replace(/[.,);:]+$/, '');
                    const display = url.length > 50 ? url.slice(0, 47) + '...' : url;
                    return '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer">'
                        + escapeHtml(display) + '</a>';
                }
                return escapeHtml(part);
            }).join('');
        }

        document.querySelectorAll('.task-label-wrap').forEach(function (wrap) {
            wrap.addEventListener('click', function (e) {
                if (e.target.closest('a')) return; // let the link open normally

                const labelSpan = wrap.querySelector('.task-label');
                if (!labelSpan) return; // already editing

                const taskKey = labelSpan.dataset.key;
                const currentText = labelSpan.dataset.raw; // full, untruncated text
                const wasChecked = labelSpan.classList.contains('checked');

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'task-label-input';
                input.value = currentText;
                input.dataset.key = taskKey;

                wrap.replaceChild(input, labelSpan);
                input.focus();
                input.select();

                function saveAndRevert() {
                    const newText = input.value.trim();

                    const newLabel = document.createElement('span');
                    newLabel.className = 'task-label' + (wasChecked ? ' checked' : '');
                    newLabel.dataset.key = taskKey;
                    newLabel.dataset.raw = newText;
                    newLabel.innerHTML = linkifyLabel(newText);
                    wrap.replaceChild(newLabel, input);

                    if (newText === currentText) return; // nothing changed, skip the request

                    const body = new URLSearchParams();
                    body.append('action', 'lbs_update_onboarding_label');
                    body.append('nonce', labelNonce);
                    body.append('task_key', taskKey);
                    body.append('label', newText);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            newLabel.dataset.raw = currentText;
                            newLabel.innerHTML = linkifyLabel(currentText); // revert on failure
                            alert('Could not save: ' + (data.data && data.data.error ? data.data.error : 'unknown error'));
                        }
                    })
                    .catch(function () {
                        newLabel.dataset.raw = currentText;
                        newLabel.innerHTML = linkifyLabel(currentText);
                        alert('Network error - could not save change.');
                    });
                }

                input.addEventListener('blur', saveAndRevert);
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        input.blur();
                    } else if (e.key === 'Escape') {
                        input.value = currentText;
                        input.blur();
                    }
                });
            }, { once: false });
        });
    })();
    </script>
    <?php
}

// --- AJAX handler: toggle a checkbox ---
add_action('wp_ajax_lbs_toggle_onboarding_task', 'lbs_toggle_onboarding_task');

function lbs_toggle_onboarding_task() {
    check_ajax_referer('lbs_onboarding_toggle', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $taskKey = isset($_POST['task_key']) ? sanitize_text_field($_POST['task_key']) : '';
    $checked = isset($_POST['checked']) ? (int) (bool) $_POST['checked'] : null;

    if ($taskKey === '' || $checked === null) {
        wp_send_json_error(['error' => 'Missing task_key or checked value'], 400);
    }

    global $wpdb;
    $wpdb->update(
        'onboarding_tasks',
        ['is_checked' => $checked],
        ['task_key' => $taskKey],
        ['%d'],
        ['%s']
    );

    wp_send_json_success(['task_key' => $taskKey, 'checked' => (bool) $checked]);
}

// --- AJAX handler: save an edited label ---
add_action('wp_ajax_lbs_update_onboarding_label', 'lbs_update_onboarding_label');

function lbs_update_onboarding_label() {
    check_ajax_referer('lbs_onboarding_label', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Not allowed'], 403);
    }

    $taskKey = isset($_POST['task_key']) ? sanitize_text_field($_POST['task_key']) : '';
    $label   = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';

    if ($taskKey === '' || $label === '') {
        wp_send_json_error(['error' => 'Missing task_key or label'], 400);
    }

    global $wpdb;
    $wpdb->update(
        'onboarding_tasks',
        ['label' => $label],
        ['task_key' => $taskKey],
        ['%s'],
        ['%s']
    );

    wp_send_json_success(['task_key' => $taskKey, 'label' => $label]);
}