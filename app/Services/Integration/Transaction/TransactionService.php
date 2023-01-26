<?php

namespace App\Services\Integration\Transaction;

use App\Services\Integration\Transaction\Validation;
use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class TransactionService
 * @package App\Services\Integration\Transaction
 */
class TransactionService implements TransactionServiceInterface
{
    /**
     * @var TransactionExecuteQueueRepositoryInterface
     */
    private $transactionExecuteQueueRepository;

    /**
     * @var array
     */
    private $transactionErrors = [];

    public function __construct(TransactionExecuteQueueRepositoryInterface $transactionExecuteQueueRepository)
    {
        $this->transactionExecuteQueueRepository = $transactionExecuteQueueRepository;
    }

    /**
     * @param array $params
     * @return array
     * @throws BindingResolutionException
     */
    public function post(array $params): array
    {
        $transactionData = [
            'data' => $params['data'],
            'api' => $params['integration_name'],
            'without_prepare_data' => true
        ];

        $this->transactionExecuteQueueRepository->create($transactionData);

        $config = new \SimpleXMLElement($params['data'], LIBXML_NOCDATA);

        if (!$config || !isset($config->transactions) || !isset($config->transactions->transaction)) {
/*            return $this->_setResponseBody(array(
                'status'  => 'error',
                'message' => 'No transactions were found in request body.',
                'type'    => 'ContentException',
                'code'    => '201'
            ));*/
            print_r(1111111);exit();
        }

        $transactions = json_decode(json_encode($config->transactions), true);

        if (!isset($transactions['transaction'][0])) {
            $parsed[0] = $transactions['transaction'];
        } else {
            $parsed = $transactions['transaction'];
        }

        $i = 0;

        foreach ($parsed as $transaction) {
            if(empty($transaction['action'])) {
                $this->addTransactionError($i, 'No action supplied for this transaction.');
                continue;
            }

            if(empty($transaction['data'])) {
                $this->addTransactionError($i, 'No data supplied for this transaction.');
                continue;
            }

            if(!Validation::isValidAction($transaction['action'])) {
                $message = 'Invalid action "' . $transaction['action'] . '" supplied for this transaction.';
                $this->addTransactionError($i, $message);
                continue;
            }

            Validation::validateTransaction($transaction['action'], $transaction['data'], (string) $i, $this);
            $i++;
        }

        $errors = $this->getTransactionErrors();

        print_r($errors);exit();

        exit();
        return [];
    }

    /**
     * @param int $i
     * @param string|null $error
     * @return void
     */
    public function addTransactionError(int $i, string $error = null)
    {
        if(empty($i) && $i != '0') {
            return;
        }

        $this->transactionErrors[$i][] = $error;
    }

    /**
     * @return array
     */
    public function getTransactionErrors(): array
    {
        return $this->transactionErrors;
    }
}
