<?php

namespace App\Services\Integration\Transaction;

use App\Helpers\StringHelper;
use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;
use App\Services\Integration\Transaction\Adapter\Adapter;
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
     * @var Validation
     */
    private $validation;

    /**
     * @var Reference
     */
    private $reference;

    /**
     * @var string
     */
    private $integrationName;

    /**
     * @var array
     */
    private $transactionErrors = [];

    public function __construct(
        TransactionExecuteQueueRepositoryInterface $transactionExecuteQueueRepository,
        Validation $validation,
        Reference $reference
    ) {
        $this->transactionExecuteQueueRepository = $transactionExecuteQueueRepository;
        $this->validation = $validation;
        $this->reference = $reference;
    }

    /**
     * @param array $params
     * @return string
     * @throws BindingResolutionException|\DOMException
     */
    public function post(array $params): string
    {
        $this->integrationName = $params['integration_name'];

        if ($params['create_transaction_queue'] ?? false) {
            $transactionData = [
                'data' => $params['data'],
                'api' => $this->integrationName,
                'without_prepare_data' => true
            ];

            $this->transactionExecuteQueueRepository->create($transactionData);
        }

        $config = new \SimpleXMLElement($params['data'], LIBXML_NOCDATA);

        if (!$config || !isset($config->transactions) || !isset($config->transactions->transaction)) {
            $this->addTransactionError(0, 'No data supplied for this transaction.');
            return $this->getXml([]);
        }

        $transactions = json_decode(json_encode($config->transactions), true);

        if (!isset($transactions['transaction'][0])) {
            $parsed[0] = $transactions['transaction'];
        } else {
            $parsed = $transactions['transaction'];
        }

        $i = 0;

        $this->validation->setApiKey($this->integrationName);

        foreach ($parsed as $transaction) {
            if(empty($transaction['action'])) {
                $this->addTransactionError($i, 'No action supplied for this transaction.');
                continue;
            }

            if(empty($transaction['data'])) {
                $this->addTransactionError($i, 'No data supplied for this transaction.');
                continue;
            }

            if(!$this->validation->isValidAction($transaction['action'])) {
                $message = 'Invalid action "' . $transaction['action'] . '" supplied for this transaction.';
                $this->addTransactionError($i, $message);
                continue;
            }

            $this->validation->validateTransaction($transaction['action'], $transaction['data'], (string) $i, $this);
            $i++;
        }

        if(!count($this->getTransactionErrors())) {
            foreach ($parsed as $transaction) {
                $this->executeTransaction($transaction['action'], $transaction['data']);
            }
        }

        return $this->getXml($parsed);
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

    /**
     * @param string|null $method
     * @param array|null $data
     * @return void
     * @throws BindingResolutionException
     */
    protected function executeTransaction(?string $method = null, ?array $data = null)
    {
        if(empty($method) || empty($data)) {
            return;
        }

        $action = $this->reference->decodeAction($method, $this->integrationName);

        if(!$action) {
            return;
        }

        $adapter = $this->createAdapter($action);
        $method = $action['action'];

        return $adapter->$method($data);
    }

    /**
     * @param array $action
     * @return Adapter
     * @throws BindingResolutionException
     */
    protected function createAdapter(array $action): Adapter
    {
        $className = Adapter::ADAPTER_MAPPING['Adapter_' . ucwords($this->integrationName) . '_' . ucwords($action['entity_type'])];
        return app()->make($className);
    }

    /**
     * @return false|string
     * @throws \DOMException
     */
    private function getXml(array $data)
    {
        $xml = new \DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $responseElement = $xml->createElement('response');
        $this->toXml($this->prepareData($data), $responseElement, $xml);
        $xml->appendChild($responseElement);

        return $xml->saveXML();
    }

    /**
     * @param array $transactions
     * @return array
     */
    private function prepareData(array $transactions): array
    {
        $errors = $this->getTransactionErrors();

        if (count($errors)) {
            $status = 'error';
        } else {
            $status = 'success';
        }

        $body = array(
            'status'       => $status,
            'transactions' => array()
        );

        foreach ($transactions as $i => $transaction) {
            if(isset($errors[$i])) {
                $array = array(
                    'status'               => 'invalid',
                    'errors'               => array(),
                    'original_transaction' => $transaction
                );

                foreach ($errors[$i] as $error) {
                    $array['errors'][] = array('error' => $error);
                }
            } else {
                if(count($errors)) $status = 'valid';
                else $status = 'executed';

                $array = array(
                    'status'               => $status,
                    'original_transaction' => $transaction
                );
            }

            $body['transactions'][] = array(
                'transaction' => $array
            );
        }

        return $body;
    }

    /**
     * @param array $array
     * @param \DOMElement $xml
     * @param \DOMDocument $document
     * @return void
     * @throws \DOMException
     */
    private function toXml(array $array, \DOMElement $xml, \DOMDocument $document)
    {
        foreach ($array as $key => $value) {
            if(is_array($value)) {
                if((string) $key == '@attributes') {
                    foreach ($value as $attributeKey => $attributeValue) {
                        $attribute = $document->createAttribute($attributeKey);

                        $node = $document->createCDATASection($attributeValue);
                        $attribute->appendChild($node);

                        $xml->appendChild($attribute);
                    }
                } else {
                    if(!is_numeric($key)) {
                        $element = $document->createElement($key);
                        $this->toXml($value, $element, $document);
                        $xml->appendChild($element);
                    } else {
                        $this->toXml($value, $xml, $document);
                    }
                }

            } else {
                $element = $document->createElement($key);
                $element->appendChild($document->createCDATASection($value));
                $xml->appendChild($element);
            }
        }
    }
}
