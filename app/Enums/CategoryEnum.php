<?php

namespace App\Enums;

enum CategoryEnum: string
{
    case JEWELRY = 'jewelry';
    case WATCHES = 'watches';
    case BAGS = 'bags';
    case ACCESSORIES = 'accessories';
    case ELECTRONICS = 'electronics';
    case FASHION = 'fashion';
    case HOME_GARDEN = 'home_garden';
    case SPORTS_OUTDOORS = 'sports_outdoors';
    case BOOKS = 'books';
    case BEAUTY_PERSONAL_CARE = 'beauty_personal_care';
    case AUTOMOTIVE = 'automotive';
    case HEALTH_WELLNESS = 'health_wellness';

    /**
     * Get the label for the enum value
     */
    public function label(): string
    {
        return match ($this) {
            self::JEWELRY => 'Jewelry',
            self::WATCHES => 'Watches',
            self::BAGS => 'Bags',
            self::ACCESSORIES => 'Accessories',
            self::ELECTRONICS => 'Electronics',
            self::FASHION => 'Fashion',
            self::HOME_GARDEN => 'Home & Garden',
            self::SPORTS_OUTDOORS => 'Sports & Outdoors',
            self::BOOKS => 'Books',
            self::BEAUTY_PERSONAL_CARE => 'Beauty & Personal Care',
            self::AUTOMOTIVE => 'Automotive',
            self::HEALTH_WELLNESS => 'Health & Wellness',
        };
    }

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum labels as an array
     */
    public static function labels(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
    }

    /**
     * Get all enum values with their labels
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }
}
