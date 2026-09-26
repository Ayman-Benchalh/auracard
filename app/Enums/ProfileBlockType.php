<?php

namespace App\Enums;

enum ProfileBlockType: string
{
    case HEADER = 'header';
    case BIO = 'bio';
    case CONTACT_ACTIONS = 'contact_actions';
    case SOCIAL_LINKS = 'social_links';
    case SKILLS = 'skills';
    case SERVICES = 'services';
    case PORTFOLIO = 'portfolio';
    case PROJECTS = 'projects';
    case PRODUCTS = 'products';
    case GALLERY = 'gallery';
    case DOCUMENTS = 'documents';
    case CERTIFICATIONS = 'certifications';
    case TESTIMONIALS = 'testimonials';
    case BUSINESS_HOURS = 'business_hours';
    case LOCATION = 'location';
    case BOOKING = 'booking';
    case LEAD_FORM = 'lead_form';
    case CUSTOM_CTA = 'custom_cta';
    case VCARD = 'vcard';

    public function label(): string
    {
        return match ($this) {
            self::HEADER => 'Header & Avatar',
            self::BIO => 'About & Bio',
            self::CONTACT_ACTIONS => 'Contact Actions',
            self::SOCIAL_LINKS => 'Social Networks',
            self::SKILLS => 'Skills & Expertise',
            self::SERVICES => 'Services Offered',
            self::PORTFOLIO => 'Portfolio Showcase',
            self::PROJECTS => 'Featured Projects',
            self::PRODUCTS => 'Featured Products',
            self::GALLERY => 'Photo Gallery',
            self::DOCUMENTS => 'Documents & Downloads',
            self::CERTIFICATIONS => 'Certifications & Badges',
            self::TESTIMONIALS => 'Client Testimonials',
            self::BUSINESS_HOURS => 'Opening Hours',
            self::LOCATION => 'Location & Map',
            self::BOOKING => 'Appointment Booking',
            self::LEAD_FORM => 'Inquiry Form',
            self::CUSTOM_CTA => 'Custom Call-To-Action',
            self::VCARD => 'Save Contact Card',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::HEADER => 'heroicon-o-user-circle',
            self::BIO => 'heroicon-o-document-text',
            self::CONTACT_ACTIONS => 'heroicon-o-phone',
            self::SOCIAL_LINKS => 'heroicon-o-share',
            self::SKILLS => 'heroicon-o-check-badge',
            self::SERVICES => 'heroicon-o-briefcase',
            self::PORTFOLIO => 'heroicon-o-photo',
            self::PROJECTS => 'heroicon-o-folder',
            self::PRODUCTS => 'heroicon-o-shopping-bag',
            self::GALLERY => 'heroicon-o-camera',
            self::DOCUMENTS => 'heroicon-o-arrow-down-tray',
            self::CERTIFICATIONS => 'heroicon-o-academic-cap',
            self::TESTIMONIALS => 'heroicon-o-chat-bubble-bottom-center-text',
            self::BUSINESS_HOURS => 'heroicon-o-clock',
            self::LOCATION => 'heroicon-o-map-pin',
            self::BOOKING => 'heroicon-o-calendar',
            self::LEAD_FORM => 'heroicon-o-envelope-open',
            self::CUSTOM_CTA => 'heroicon-o-sparkles',
            self::VCARD => 'heroicon-o-identification',
        };
    }
}
