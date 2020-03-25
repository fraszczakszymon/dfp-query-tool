<?php

namespace LineItem\Command;

use Inventory\Api\CustomTargetingService;
use Inventory\Api\LineItemService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindLineItemByKeyCommand extends Command
{
	public function __construct($app, $name = null)
	{
		parent::__construct($name);
	}

	protected function configure()
	{
		$this->setName('line-items:find-by-key')
			->setDescription('Find line items by used keys in the targeting')
			->addArgument('keys', InputArgument::REQUIRED, 'Keys (separated with comma');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$customTargetingService = new CustomTargetingService();
		$lineItemService = new LineItemService();

		$keys = explode(',', $input->getArgument('keys'));

		$keyIds = $customTargetingService->getKeyIds($keys);
		$map = array_combine($keyIds, $keys);

		$lineItems = $lineItemService->findLineItemIdsByKeys($keyIds);

		foreach ($lineItems as $lineItem) {
			printf(" - name: %s, key: %s, order: %s, line item: %s\n", $lineItem['name'], $map[$lineItem['key']], $lineItem['order_id'], $lineItem['line_item_id']);
		}
	}
}

