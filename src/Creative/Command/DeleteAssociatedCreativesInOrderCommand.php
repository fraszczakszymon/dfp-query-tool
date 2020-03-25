<?php

namespace Creative\Command;

use Inventory\Api\LineItemCreativeAssociationService;
use Inventory\Api\LineItemService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAssociatedCreativesInOrderCommand extends Command
{
	public function __construct($app, $name = null)
	{
		parent::__construct($name);
	}

	protected function configure()
	{
		$this->setName('creatives:delete')
			->setDescription('Deletes associated creatives in all line items in order')
			->addArgument(
				'creative-id',
				InputArgument::REQUIRED,
				'Creative ID'
			)->addArgument(
				'order-id',
				InputArgument::REQUIRED,
				'Order ID'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$creativeId = $input->getArgument('creative-id');
		$orderId = $input->getArgument('order-id');

		$lineItemService = new LineItemService();
		$associationService = new LineItemCreativeAssociationService();

		$lineItems = $lineItemService->getLineItemsInOrder($orderId);
		$count = count($lineItems);

		printf("Updating %s line items\n", $count);
		foreach ($lineItems as $i => $lineItem) {
			$result = $associationService->delete($creativeId, $lineItem->getId());
			if ($result['success']) {
				printf("  - Line item %s updated (%s/%s)\n", $lineItem->getId(), $i + 1, $count);
			} else {
				printf("  - %s (%s/%s)\n", $result['message'], $i + 1, $count);
			}
		}
	}
}
