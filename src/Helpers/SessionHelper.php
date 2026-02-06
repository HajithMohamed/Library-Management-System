<?php

namespace App\Helpers;

class SessionHelper
{
    public static function addToWishlist($isbn)
    {
        if (!isset($_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'] = [];
        }

        if (!in_array($isbn, $_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'][] = $isbn;
            return true;
        }
        return false;
    }

    public static function removeFromWishlist($isbn)
    {
        if (isset($_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'] = array_diff($_SESSION['guest_wishlist'], [$isbn]);
            return true;
        }
        return false;
    }

    public static function getWishlist()
    {
        return $_SESSION['guest_wishlist'] ?? [];
    }

    public static function clearWishlist()
    {
        unset($_SESSION['guest_wishlist']);
    }
}
