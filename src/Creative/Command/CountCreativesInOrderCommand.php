<?php

namespace Creative\Command;

use Inventory\Api\LineItemCreativeAssociationService;
use Inventory\Api\LineItemService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CountCreativesInOrderCommand extends Command
{
	public function __construct($app, $name = null)
	{
		parent::__construct($name);
	}

	protected function configure()
	{
		$this->setName('creatives:count')
			->setDescription('Count creatives in all line items in order')
			->addArgument(
				'order-id',
				InputArgument::REQUIRED,
				'Order ID'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$orderId = $input->getArgument('order-id');

		$lineItemService = new LineItemService();
		$associationService = new LineItemCreativeAssociationService();

		$lineItems = $lineItemService->getLineItemsInOrder($orderId);
		$count = count($lineItems);
		$allCreatives = 0;

		printf("Checking %s line items\n", $count);
		foreach ($lineItems as $i => $lineItem) {
			$creatives = $associationService->count($lineItem->getId());
			$allCreatives += $creatives;
			printf("  - Line item %s creatives: %s\n", $lineItem->getId(), $creatives);
		}

		printf("Number of all creatives: %s\n", $allCreatives);
	}
}
