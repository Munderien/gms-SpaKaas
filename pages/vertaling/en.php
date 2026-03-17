<?php
/**
 * English Language File
 * Language: English (en)
 * Used for session-based language management
 */

return [
    // ==================== HOME PAGE ====================
    // Welcome section
    'welcome' => 'Welcome',
    'welcome_back' => 'Welcome back',
    'welcome_guest' => 'Welcome to SpaKaas',
    'welcome_subtitle' => 'Enjoy pure relaxation and luxury',
    'welcome_intro' => 'We\'re glad you\'re at our Luxe Spa Resort. Here you can completely escape from daily life and pamper yourself with the best treatments.',
    
    // Features
    'premium_treatments' => 'Premium Treatments',
    'premium_treatments_desc' => 'Choose from our exclusive wellness offerings',
    'easy_booking' => 'Easy Booking',
    'easy_booking_desc' => 'Reserve your favorite treatment directly',
    'special_offers' => 'Special Offers',
    'special_offers_desc' => 'Exclusive deals for our members',
    'vip_status' => 'VIP Status',
    'vip_status_desc' => 'Enjoy extra benefits and privileges',
    
    // Statistics
    'availability_24_7' => '24/7',
    'availability_label' => 'Availability',
    'great_reviews' => 'Amazing',
    'reviews_label' => 'Reviews',
    
    // Reviews section
    'recent_reviews' => 'Recent Reviews',
    'create_review' => 'Write Review',
    'login_to_review' => 'Log in to write a review',
    'rating' => 'Rating',
    'out_of_5' => '/5',
    'edit' => 'Edit',
    
    // Database/Error messages
    'db_connection_failed' => 'Database connection failed',
    'guest' => 'Guest',
    'login_link' => 'Log in',
    
    // ==================== NAVIGATION ====================
    'nav_home' => 'Home',
    'nav_bookings' => 'Bookings',
    'nav_profile' => 'Profile',
    'nav_about' => 'About Us',
    'nav_reviews' => 'Reviews',
    'nav_agenda' => 'Agenda',
    'nav_logout' => 'Logout',
    'nav_login' => 'Login',
    'nav_employee' => 'Employee Page',
    
    // ==================== LODGES PAGE ====================
    'lodges_title' => 'Our Lodges',
    'lodges_description' => 'Description',
    'lodges_capacity' => 'Capacity',
    'lodges_price' => 'Price',
    'lodges_persons' => 'people',
    'lodges_book_appointment' => 'Make Appointment',
    'lodges_currency' => '€',
    
    // ==================== LODGE PDP (Product Detail Page) ====================
    'lodge_pdp_invalid' => 'Invalid lodge type.',
    'lodge_pdp_not_found' => 'Lodge type not found.',
    'lodge_pdp_capacity_label' => 'Capacity:',
    'lodge_pdp_capacity_unit' => 'people',
    'lodge_pdp_id_label' => 'Lodge Type ID:',
    'lodge_pdp_book_btn' => 'Make Appointment',
    'lodge_pdp_favorite_btn' => 'Favorite',
    'lodge_pdp_unfavorite_btn' => 'Unfavorite',
    
    // ==================== MAKE APPOINTMENT PAGE ====================
    'appointment_title' => 'Add New Appointment',
    'appointment_start_time' => 'Start Time:',
    'appointment_end_time' => 'End Time:',
    'appointment_description' => 'Description:',
    'appointment_select_user' => 'Select User:',
    'appointment_select_lodgetype' => 'Select Lodge Type:',
    'appointment_select_lodgetype_placeholder' => '-- Select Lodge Type --',
    'appointment_lodge_info_title' => 'Selected Lodge Information',
    'appointment_lodge_info_name' => 'Name:',
    'appointment_lodge_info_capacity' => 'Capacity:',
    'appointment_lodge_info_price' => 'Price:',
    'appointment_lodge_info_description' => 'Description:',
    'appointment_select_users' => '-- Select --',
    'appointment_number_of_people' => 'Number of People:',
    'appointment_submit_btn' => 'Add',
    'appointment_connection_failed' => 'Connection failed:',
    'appointment_not_logged_in' => 'You are not logged in.',
    
    // Appointment error messages
    'appointment_error_no_user' => 'Please select a user.',
    'appointment_error_no_lodgetype' => 'Please select a lodge type.',
    'appointment_error_invalid_people_count' => 'Number of people must be greater than 0.',
    'appointment_error_invalid_capacity' => 'Invalid lodge type or capacity not found.',
    'appointment_error_capacity_exceeded' => 'You cannot book more than :capacity people for this lodge type.',
    'appointment_error_past_date' => 'Date cannot be in the past.',
    'appointment_error_invalid_time' => 'End time must be after start time.',
    'appointment_error_no_availability' => 'No available lodge found for this lodge type in this period.',
    'appointment_error_db' => 'Error adding appointment:',
    
    // Appointment success messages
    'appointment_success_title' => 'Booking Confirmation',
    'appointment_success_message' => 'Dear :name,

Your appointment has been successfully created.

Details:
- Start Time: :startTime
- End Time: :endTime
- Description: :description
- Number of People: :numberOfPeople

We look forward to seeing you on the scheduled date and wish you a wonderful time!',
    
    // ==================== PROFILE/EDIT USER PAGE ====================
    'edit_user_title' => 'Update Information',
    'edit_user_email' => 'Email',
    'edit_user_email_required' => 'Must be a valid email',
    'edit_user_password' => 'Password',
    'edit_user_password_placeholder' => 'Leave blank to not change',
    'edit_user_password_empty_note' => 'Leave blank if you do not want to change the password',
    'edit_user_password_invalid' => 'Password does not meet the requirements.',
    'edit_user_name' => 'Name',
    'edit_user_address' => 'Address',
    'edit_user_postcode' => 'Postal Code',
    'edit_user_postcode_format' => 'Format: NNNN AA (e.g. 1234 AB)',
    'edit_user_city' => 'City',
    'edit_user_phone' => 'Phone',
    'edit_user_phone_note' => 'Minimum 9 digits',
    'edit_user_two_factor' => 'Two-Factor Authentication',
    'edit_user_two_factor_no' => 'No',
    'edit_user_two_factor_yes' => 'Yes',
    'edit_user_save' => 'Save',
    'edit_user_errors_found' => 'Errors Found',
    'edit_user_required_field' => 'Required field',
    
    // Password requirements
    'password_requirement_length' => 'At least 8 characters',
    'password_requirement_uppercase' => 'At least 1 uppercase letter',
    'password_requirement_lowercase' => 'At least 1 lowercase letter',
    'password_requirement_number' => 'At least 1 number',
    'password_requirement_special' => 'At least 1 special character (!@#$%^&*)',
    
    // Validation messages
    'validation_required_fields' => 'All required fields must be filled in (marked with *)',
    'validation_invalid_email' => 'Please enter a valid email address',
    'validation_invalid_postcode' => 'Postal code must be in the format NNNN AA (e.g. 1234 AB)',
    'validation_invalid_phone' => 'Phone number must contain at least 9 digits',
    'validation_invalid_password' => 'Password does not meet all requirements',
    
    // Profile Picture Section
    'profile_picture_title' => 'Profile Picture',
    'profile_picture_current' => 'Current Profile Picture',
    'profile_picture_none' => 'No profile picture uploaded',
    'profile_picture_select' => 'Select a photo',
    'profile_picture_formats' => 'Allowed formats: JPG, PNG, GIF (Max 5MB)',
    'profile_picture_preview_new' => 'Preview of new photo',
    'profile_picture_no_selected' => 'No photo selected',
    'profile_picture_change' => 'Change',
    'profile_picture_delete' => 'Delete',
    'profile_picture_save' => 'Save',
    'profile_picture_cancel' => 'Cancel',
    'profile_picture_delete_confirm' => 'Are you sure you want to delete your profile picture?',
    'profile_picture_error_required' => 'Please select a photo',
    'profile_picture_error_too_large' => 'File is too large. Maximum 5MB is allowed.',
    'profile_picture_error_invalid_type' => 'Invalid file type. Use JPG, PNG or GIF.',
    
    // ==================== ABOUT US PAGE ====================
    'about_title' => 'About SpaKaas Luxe Spa Resort',
    'about_subtitle' => 'An oasis of rest and rejuvenation in the heart of the Netherlands, since 2010',
    'about_story_heading' => '✨ Our Story',
    'about_story_p1' => 'SpaKaas Luxe Spa Resort was founded in 2010 with a simple yet powerful goal: to create a sacred place where guests can completely escape from daily stress and rediscover themselves. Located in the beautiful Achterhoek region, our resort enjoys a unique location surrounded by natural beauty and serenity.',
    'about_story_p2' => 'What started as a small spa with just 5 lodges has grown into a leading wellness resort with over 25 luxury lodges. Our success is based on our unwavering commitment to quality, craftsmanship, and guest service.',
    'about_story_p3' => 'We treat over 15,000 guests per year, with the majority returning regularly. This is the best proof that we fulfill our mission well: providing an exceptional spa experience that is transformative and restorative.',
    'about_stat_years' => 'Years of Experience',
    'about_stat_lodges' => 'Lodges',
    'about_stat_guests' => 'Annual Guests',
    'about_stat_rating' => 'Average Rating',
    
    'about_values_heading' => '💎 Our Values',
    'about_values_intro' => 'At SpaKaas Luxe Spa Resort, we are guided by four core values that form the heart of our business:',
    'about_value_nature' => 'Naturalness',
    'about_value_nature_desc' => 'We believe in the power of natural ingredients and traditional wellness practices. All our treatments use organic and environmentally friendly products.',
    'about_value_care' => 'Care',
    'about_value_care_desc' => 'Every guest is unique and deserves personal attention. Our team takes time to understand your needs and create a customized experience.',
    'about_value_excellence' => 'Excellence',
    'about_value_excellence_desc' => 'We strive for perfection in everything we do. From our therapists to our facilities, we maintain the highest standards.',
    'about_value_sustainability' => 'Sustainability',
    'about_value_sustainability_desc' => 'We are committed to the environment. Our resort operates 100% on renewable energy and maintains a zero-waste policy.',
    
    'about_team_heading' => '👥 Our Team',
    'about_team_intro' => 'Our team consists of over 50 qualified professionals, each passionate about their field and dedicated to guest service.',
    'about_team_director' => 'Director & Founder',
    'about_team_director_bio' => 'With over 20 years of experience in the hospitality industry, Oskar led the vision of SpaKaas from dream to reality.',
    'about_team_wellness' => 'Wellness Advisor',
    'about_team_wellness_bio' => 'With a Ph.D. in health sciences, Jesse ensures that all our treatments are scientifically sound.',
    'about_team_therapist' => 'Head Therapist',
    'about_team_therapist_bio' => 'Marijn trains and guides our team of 35 therapists, ensuring consistently high quality and innovation.',
    'about_team_experience' => 'Guest Experience Manager',
    'about_team_experience_bio' => 'Mert ensures that every guest has an unforgettable experience, from booking guidance to follow-up care.',
    
    'about_facilities_heading' => '🏛️ Our Facilities',
    'about_facilities_intro' => 'SpaKaas features state-of-the-art facilities designed for maximum comfort and relaxation:',
    'about_facility_lodges' => 'Luxury Lodges',
    'about_facility_lodges_desc' => 'Each equipped with massage beds, heating systems, and aroma diffusers',
    'about_facility_pool' => 'Spa Pool',
    'about_facility_pool_desc' => 'Heated 500m² pool with water currents and hydrotherapy jets',
    'about_facility_sauna' => 'Sauna Complex',
    'about_facility_sauna_desc' => 'Four different saunas including Finnish, infrared, and steam saunas',
    'about_facility_yoga' => 'Yoga & Meditation Studio',
    'about_facility_yoga_desc' => 'Serene space with bamboo interior and natural light',
    'about_facility_lounge' => 'Wellness Lounge',
    'about_facility_lounge_desc' => 'Relaxation area with fireplace and natural crystals',
    'about_facility_restaurant' => 'Organic Restaurant',
    'about_facility_restaurant_desc' => 'Farm-to-table restaurant with healthy, organic menus',
    'about_facility_boutique' => 'Retail Boutique',
    'about_facility_boutique_desc' => 'Selection of premium wellness products and souvenirs',
    'about_facility_parking' => 'Parking Lot',
    'about_facility_parking_desc' => 'Free parking for 200+ vehicles',
    
    'about_sustainability_heading' => '🌱 Our Sustainability Commitment',
    'about_sustainability_subheading' => 'Environmental Consciousness in Action',
    'about_sustainability_intro' => 'SpaKaas is proud of our efforts to provide a sustainable spa experience. In 2018, we transitioned to 100% renewable energy, and in 2021, we achieved carbon neutrality by capturing and converting our operational emissions into green initiatives.',
    'about_sustainability_solar' => 'Solar Panels',
    'about_sustainability_solar_desc' => '500kW solar panel capacity on our roof',
    'about_sustainability_waste' => 'Zero Waste',
    'about_sustainability_waste_desc' => '95% of our waste is recycled or composted',
    'about_sustainability_water' => 'Water Saving',
    'about_sustainability_water_desc' => 'Advanced recycling systems reduce water usage by 40%',
    'about_sustainability_reforestation' => 'Reforestation',
    'about_sustainability_reforestation_desc' => 'For every booking reservation, we plant two trees',
    'about_sustainability_biodiversity' => 'Biodiversity',
    'about_sustainability_biodiversity_desc' => 'Our property houses 15 bee hives and wildflower meadows',
    
    'about_contact_heading' => '📞 Get in Touch',
    'about_contact_intro' => 'We\'d love to hear from you! Send us your questions, comments, or booking requests.',
    'about_contact_location' => 'Location',
    'about_contact_location_address' => 'Superweg 420, 6769 CP Urk, Netherlands',
    'about_contact_phone' => 'Phone Number',
    'about_contact_phone_number' => '+31 (0)611 365 315',
    'about_contact_email' => 'Email',
    'about_contact_email_address' => 'SpaKaasBV@gmail.com',
    'about_contact_hours' => 'Opening Hours',
    'about_contact_hours_value' => 'Always Open',
    
    // ==================== REVIEW PAGE ====================
    'review_page_title' => 'Reviews',
    'review_form_title_new' => 'Write Review',
    'review_form_title_edit' => 'Edit Review',
    'review_form_rating' => 'Rating (1-5)',
    'review_form_message' => 'Message',
    'review_form_illustration' => 'Illustration (optional)',
    'review_form_submit' => 'Submit Review',
    'review_form_update' => 'Update Review',
    'review_form_delete' => 'Delete Review',
    'review_form_delete_confirm' => 'Delete this review?',
    
    'review_login_prompt' => 'Want to write a review?',
    'review_login_required' => 'You must be logged in to write a review.',
    'review_login_here' => 'Log in here',
    'review_create_account' => 'create an account',
    
    'review_success_new' => 'Review submitted successfully!',
    'review_success_update' => 'Review updated successfully!',
    'review_success_delete' => 'Review deleted successfully.',
    'review_error_rating' => 'Rating must be between 1 and 5.',
    'review_error_message_empty' => 'Message is required.',
    'review_error_upload' => 'Error uploading file.',
    'review_error_file_size' => 'Image is larger than 5MB.',
    'review_error_file_type' => 'Only JPG, PNG or GIF allowed.',
    'review_error_not_found' => 'Review not found or not yours to edit.',
    'review_error_not_found_delete' => 'Review not found or not yours to delete.',
    'review_error_db' => 'Database connection failed',
    
    'review_heading_recent' => 'Recent Reviews',
    'review_rating_label' => 'Rating',
    'review_edit_link' => 'Edit',

        // ==================== MY APPOINTMENTS PAGE ====================
    'my_appointments_title' => 'My Appointments',
    'my_appointments_subtitle' => 'View all your appointments and open details',
    'my_appointments_no_appointments' => 'You don\'t have any appointments yet.',
    'my_appointments_make_appointment' => 'Make an appointment',
    'my_appointments_lodge_type' => 'Lodge Type',
    'my_appointments_lodge_number' => 'Lodge Number:',
    'my_appointments_date' => 'Date:',
    'my_appointments_time' => 'Time:',
    'my_appointments_people' => 'Number of People:',
    'my_appointments_open_planner' => 'Open in planner',
    
    // Appointment status translations
    'appointment_status_gepland' => 'Scheduled',
    'appointment_status_bezig' => 'In Progress',
    'appointment_status_voltooid' => 'Completed',
    'appointment_status_geannuleerd' => 'Cancelled',
    'appointment_status_bevestigd' => 'Confirmed',
    'appointment_status_verplaatst' => 'Rescheduled',
    'appointment_status_niet_verschenen' => 'No Show',
    'appointment_status_in_afwachting' => 'Pending',
    'appointment_status_unknown' => 'Unknown',
];
?>