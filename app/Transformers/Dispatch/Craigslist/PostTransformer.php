<?php

namespace App\Transformers\Dispatch\Craigslist;

use App\Services\Dispatch\Craigslist\DTOs\ClappPost;
use League\Fractal\TransformerAbstract;

/**
 * Class PostTransformer
 * 
 * @package App\Transformers\Dispatch\Craigslist
 */
class PostTransformer extends TransformerAbstract
{
    /**
     * @param ClappPost $clapp
     * @return array
     */
    public function transform(ClappPost $clapp): array
    {
        // Initialize Params
        return [
            'inventory_id' => $clapp->queue->inventory_id,
            'session_id'   => $clapp->queue->session_id,
            'queue_id'     => $clapp->queue->queue_id,
            'profile_id'   => $clapp->queue->profile_id,
            'initUrl'      => $clapp->initUrl,
            'costs'        => $clapp->qData->costs,
            'type'         => $clapp->categoryType,
            'category'     => $clapp->qData->postCategory,
            'subarea'      => $clapp->subarea,
            'hood'         => $clapp->geographicArea,
            'market'       => $clapp->market,
            'images'       => $clapp->qData->images,
            'post'         => $this->byCategory($clapp, [
                'FromEMail'        => $clapp->fromEmail,
                'ConfirmEMail'     => $clapp->confirmEmail,
                'PostingTitle'     => $clapp->postingTitle,
                'Privacy'          => $clapp->privacy,
                'Ask'              => $clapp->ask,
                'show_phone_ok'    => $clapp->showPhoneOk,
                'contact_name'     => $clapp->qData->trimmedContactName(),
                'contact_phone'    => $clapp->contactPhone,
                'contact_phone_ok' => $clapp->contactPhoneOk,
                'contact_phone_extension' => $clapp->contactPhoneExt,
                'GeographicArea'   => $clapp->geographicArea,
                'xstreet0'         => $clapp->crossStreet1,
                'xstreet1'         => $clapp->crossStreet2,
                'city'             => $clapp->city,
                'region'           => $clapp->region,
                'postal'           => $clapp->postal(),
                'PostingBody'      => $clapp->qData->trimmedBody(),
                'language'         => $clapp->language,
                'condition'        => $clapp->condition()
            ])
        ];
    }

    /**
     * Get Category-Specific Post Details
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byCategory(ClappPost $clapp, array $post): array {
        // Trailers
        if($clapp->qData->postCategory == 205 || $clapp->qData->postCategory == 206) {
            return $this->byTrailers($clapp, $post);
        }
        // Vehicles
        elseif($clapp->qData->postCategory == 146 || $clapp->qData->postCategory == 145) {
            return $this->byVehicles($clapp, $post);
        }
        // RV's
        elseif($clapp->qData->postCategory == 124 || $clapp->qData->postCategory == 168) {
            return $this->byRvs($clapp, $post);
        }
        // Boats
        elseif($clapp->qData->postCategory == 119 || $clapp->qData->postCategory == 164) {
            return $this->byBoats($clapp, $post);
        }
        // Auto Parts
        elseif($clapp->qData->postCategory == 163) {
            return $this->byParts($clapp, $post);
        }
        // Everything else
        else {
            return $this->byOther($clapp, $post);
        }
    }

    /**
     * Get Trailer-Specific Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byTrailers(ClappPost $clapp, array $post): array {
        // Make
        $post['year_manufactured'] = $clapp->year;
        $post['sale_manufacturer'] = $clapp->qData->trimmedMake();
        $post['sale_model'] = $clapp->qData->trimmedModel();
        $post['sale_size'] = $clapp->qData->size;

        // Color
        $post['auto_paint'] = $clapp->color;

        // Return Trailer Post Details
        return $post;
    }

    /**
     * Get Vehicle-Specific Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byVehicles(ClappPost $clapp, array $post) {
        // Make
        $post['auto_year'] = $clapp->year;
        $post['auto_make_model'] = $clapp->makeModel();
        $post['auto_vin'] = $clapp->vin;
        $post['auto_miles'] = $clapp->miles;

        // Parts
        $post['auto_cylinders'] = '';
        $post['auto_drivetrain'] = '';
        $post['auto_fuel_type'] = '1';
        $post['auto_size'] = '';
        $post['auto_title_status'] = '1';
        $post['auto_transmission'] = '2';
        $post['auto_bodytype'] = '';

        // Color
        $post['auto_paint'] = $clapp->color;

        // Return Vehicle Post Details
        return $post;
    }

    /**
     * Get RV-Specific Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return type
     */
    private function byRvs(ClappPost $clapp, array $post): array {
        // Make
        $post['auto_year'] = $clapp->qData->year;
        $post['auto_make_model'] = $clapp->makeModel();
        $post['auto_vin'] = $clapp->qData->vin;

        // Condition
        $post['auto_miles'] = $clapp->miles;
        $post['sale_size'] = $clapp->qData->size;

        // Get Auto Data
        $post['auto_cylinders'] = '';
        $post['auto_drivetrain'] = '';
        $post['rv_type'] = $clapp->rvType();
        $post['auto_title_status'] = '';

        // Fuel Type
        $post['auto_fuel_type'] = $clapp->rvFuelType();
        $post['auto_transmission'] = $clapp->rvTransmission();

        // Color
        $post['auto_paint'] = $clapp->color;

        // Return RV Post Details
        return $post;
    }

    /**
     * Get Boat-Specific Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byBoats(ClappPost $clapp, array $post): array {
        // Make/Model/Year
        $post['year_manufactured'] = $clapp->year;
        $post['sale_manufacturer'] = $clapp->qData->trimmedMake();
        $post['sale_model'] = $clapp->qData->trimmedModel();

        // Boat Specifics
        $post['boat_length_overall'] = $clapp->boatLength();

        // Handle Boat Category
        $post['boat_propulsion_type'] = $clapp->boatPropulsion();

        // Return Boat Post Details
        return $post;
    }

    /**
     * Get Part-Specific Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byParts(ClappPost $clapp, array $post): array {
        // Make
        $post['sale_manufacturer'] = $clapp->qData->trimmedMake();
        $post['sale_model'] = '';

        // Get Condition
        $post['sale_size'] = $clapp->qData->size;

        // Return Parts Post Details
        return $post;
    }

    /**
     * Get Other Post Data
     * 
     * @param ClappPost $clapp
     * @param array $post
     * @return array
     */
    private function byOther(ClappPost $clapp, array $post): array {
        // Make/Model/Year
        $post['year_manufactured'] = $clapp->year;
        $post['sale_manufacturer'] = $clapp->qData->trimmedMake();
        $post['sale_model'] = $clapp->qData->trimmedModel();

        // Get Condition
        $post['sale_size'] = $clapp->qData->size;

        // Return Other Post Details
        return $post;
    }
}