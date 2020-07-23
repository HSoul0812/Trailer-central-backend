<?php

namespace App\Console\Commands\Inventory;

use App\Repositories\Inventory\AttributeRepositoryInterface;
use Illuminate\Console\Command;

/**
 * Class DeleteAttribute
 * @package App\Console\Commands\Inventory
 */
class DeleteAttribute extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:delete_attribute {attribute_id}";

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * DeleteAttribute constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        parent::__construct();

        $this->attributeRepository = $attributeRepository;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $attributeId = $this->argument('attribute_id');
        $this->attributeRepository->delete(['attribute_id' => $attributeId]);

        return true;
    }

}
