<?php

namespace App\Console\Commands;

use App\Repositories\CRM\Interactions\MessageRepositoryInterface;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "add:message";

    /**
     * @var MessageRepositoryInterface
     */
    private $message;

    /**
     * @param MessageRepositoryInterface $message
     */
    public function __construct(MessageRepositoryInterface $message)
    {
        parent::__construct();

        $this->message = $message;
    }

    public function handle()
    {
        $params = [
            'id' => 123456,
            'name' => 'Success!'
        ];

        $this->message->create($params);
    }
}
