<?php


namespace App\Services\Pos;


use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\Log;

class PosService
{
    /**
     * Search for POS products across 2 ES indexes (parts, inventory)
     *
     * @param $queryTerm
     * @param $dealerId
     * @param  array  $options includes `allowAll`
     * @return mixed
     * @throws \Exception
     */
    public function productSearch($queryTerm, $dealerId, $options = [])
    {
        // search across 2 indexes
        $search = Part::boolSearch()->join(Inventory::class);

        // if a query is specified
        if ($queryTerm) {
            $search->must('multi_match', [
                'query' => $queryTerm,
                'fuzziness' => 'AUTO',
                'fields' => ['title^1.3', 'sku^3', 'model', 'brand', 'manufacturer', 'type', 'category', 'alternative_part_number^2', 'description^0.5']
            ]);

        // if no query supplied but is allowed
        } else if (!$queryTerm && ($options['allowAll'] ?? false)) {
            $search->must('match_all', []);

        } else { // no query and allowAll is false
            throw new \Exception('Query is required');
        }

        $search
            // do not include non serialized inventory
            ->mustNot("match", [ 'non_serialized' => 0 ])
            // filter by dealer
            ->filter('term', [ 'dealer_id' => $dealerId ])
            // load relationed models
            ->load(['brand', 'manufacturer', 'type', 'category', 'images', 'bins']);


        // if size is specified use it, if not, then if there is a query, return 20, if not return 50
        if ($size = $options['size'] ?? 50) {
            $search->size($size);
        }

        return $search->execute()->models();
    }
}
