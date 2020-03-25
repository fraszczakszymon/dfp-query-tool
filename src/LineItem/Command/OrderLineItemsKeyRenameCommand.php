<?php

namespace LineItem\Command;

use Inventory\Api\CustomTargetingService;
use Inventory\Api\LineItemService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderLineItemsKeyRenameCommand extends Command
{
	public function __construct($app, $name = null)
	{
		parent::__construct($name);
	}

	protected function configure()
	{
		$this->setName('order:key-values:rename-key')
			->setDescription('Renames key in all line items custom targeting in order')
			->addArgument('order-id', InputArgument::REQUIRED, 'Order ID')
			->addArgument('old-new-keys-map', InputArgument::REQUIRED, 'Old and new keys map (example: hb_pb:hb_pb_appnexus,hb_size:hb_size_appnexus');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$customTargetingService = new CustomTargetingService();
		$lineItemService = new LineItemService();

		$orderId = $input->getArgument('order-id');

		$keyIdsMap = [];

		$pairs = explode(',', $input->getArgument('old-new-keys-map'));
		foreach ($pairs as $pair) {
			list ($old, $new) = explode(':', $pair);

			$oldKeyName = substr(trim($old), 0, 20);
			$newKeyName = substr(trim($new), 0, 20);

			list($oldKeyId, $newKeyId) = $customTargetingService->getKeyIds([$oldKeyName, $newKeyName]);

			$keyIdsMap[$oldKeyId] = $newKeyId;
		}

		$lineItems = $lineItemService->getLineItemsInOrder($orderId);

		foreach ($lineItems as $i => $lineItem) {
			$lineItemService->renameKeyInLineItemTargeting($lineItem, $keyIdsMap);

			printf('.');

			if (($i+1) % 50 === 0) {
				printf(" (%s/%s)\n", $i+1, count($lineItems));
			}
		}
	}
}
