<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

use App\Repositories\GenericRepository;

interface SpecificIntegrationRepositoryInterface extends GenericRepository
{
    /**
     * Gets the specifics values for each config parameter.
     *
     * Given the follows integration settings:
     * [
     *      {
     *          "name": "dealer_id",
     *          "label": "Dealer ID",
     *          "description": "Your RacingJunk dealer ID.",
     *          "type": "text",
     *          "required": true,
     *          "value": "1004"
     *      },
     *      {
     *         "name": "package",
     *         "label": "Package",
     *         "description": "How many slots your RacingJunk account supports",
     *         "type": "select",
     *         "options": {
     *              "2": 2,
     *              "10": 10,
     *              "20": 20,
     *              "50": 50,
     *              "150": 150
     *         },
     *         "required": 1,
     *         "value": "50"
     *      }
     * ]
     * then it will return something like this:
     * {
     *  "package": 5
     * }
     *
     * @param array $params
     * @return array
     */
    public function get(array $params): array;
}
