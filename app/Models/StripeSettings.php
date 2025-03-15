<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeSettings extends Model
{
    public $timestamps = false;
    
    /*
        TODO:
        - Setup create / update / delete events for Product model
        - Setup update event for Settings model
        - When a product is created, create a StripeSettings entry
        - Upon StripeSettings create / update, check if product is in Stripe
            - If not, create a StripeProduct entry
            - If yes, update the StripeProduct entry
        - When a product is deleted, delete the StripeSettings entry
        - When a StripeSettings entry is deleted, delete the related Stripe product
    */
}
