<?php


namespace App\Services\Pos;


use App\Domains\Parts\Actions\GetCriteriaToSearchPartInEsAction;
use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\Log;
use App\Repositories\Pos\QuoteRepository;
use App\Models\Pos\Quote;
use Illuminate\Support\Facades\Auth;

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
            $searchCriteria = resolve(GetCriteriaToSearchPartInEsAction::class)->execute($queryTerm);

            $search->should(...$searchCriteria);
        // if no query supplied but is allowed
        } else if (!$queryTerm && ($options['allowAll'] ?? false)) {
            $search->must('match_all', []);
            $search->sort('bins_total_qty', 'DESC');
            $search->sort('_score', 'DESC') ;

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

    /**
     * @param array $params
     * 
     * @return Quote
     */
    public function createQuote($params)
    {
        return Quote::create([
            'dealer_id' => Auth::user()->dealer_id,
            'quote_details' => $params['quote_details']
        ]);
    }
}
