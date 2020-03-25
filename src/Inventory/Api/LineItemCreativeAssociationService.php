<?php

namespace Inventory\Api;

use Google\AdsApi\AdManager\Util\v201911\StatementBuilder;
use Google\AdsApi\AdManager\v201911\LineItemCreativeAssociation;
use Google\AdsApi\AdManager\v201911\DeactivateLineItemCreativeAssociations;
use Google\AdsApi\AdManager\v201911\DeleteLineItemCreativeAssociations;


class LineItemCreativeAssociationService {
	private $customTargetingService;

	public function __construct() {
		$this->customTargetingService = new CustomTargetingService();
	}

	public function create( $creativeId, $lineItemId ) {

		if ( empty($creativeId) ) {
			return [
				'creativeSet' => false
			];
		}

		$response = [
			'success' => true,
			'creativeSet' => true,
			'creativeId' => $creativeId
		];

		$processedCreativeIds = $this->processCreativeId( $creativeId );
		try {
			if ( empty($lineItemId) ) {
				return $this->getIncorrectLineItemResult();
			} else {
				$lineItemCreativeAssociationService = AdManagerService::get(\Google\AdsApi\AdManager\v201911\LineItemCreativeAssociationService::class);
				$lineItemCreativeAssociations = [ ];

				foreach ( $processedCreativeIds as $extractedCreativeId ) {
					$lineItemCreativeAssociation = new LineItemCreativeAssociation();
					$lineItemCreativeAssociation->setCreativeId(trim( $extractedCreativeId ));
					$lineItemCreativeAssociation->setLineItemId($lineItemId);
					$lineItemCreativeAssociations[] = $lineItemCreativeAssociation;
				}

				$lica = null;
				for ($i = 0; $i < 10; $i++) {
					$lica = $lineItemCreativeAssociationService->createLineItemCreativeAssociations( $lineItemCreativeAssociations );

					if ($lica || isset($lica)) break;
					echo 'SOAP "createLineItemCreativeAssociations()" connection error - retrying (' . ($i + 1) . ")...\n";
				}

				if ( !$lica || !isset($lica) ) {
					$response['success'] = false;
					$response['message'] = 'line item - creative association not created';
				}
			}
		} catch ( \Exception $e ) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function getIncorrectLineItemResult() {
		return [
			'success' => false,
			'message' => 'Line item creation failed - unable to associate creative',
			'creativeSet' => false
		];
	}

	private function processCreativeId( $creativeId ) {
		return explode( ',', $creativeId );
	}

	public function deactivate( $creativeId, $lineItemId ) {
		$response = [
			'success' => true,
		];

		$statementBuilder = (new StatementBuilder())
			->where('lineItemId = :lineItemId and creativeId = :creativeId')
			->withBindVariableValue('lineItemId', $lineItemId)
			->withBindVariableValue('creativeId', $creativeId);

		try {
			$lineItemCreativeAssociationService = AdManagerService::get(\Google\AdsApi\AdManager\v201911\LineItemCreativeAssociationService::class);
			$action = new DeactivateLineItemCreativeAssociations();

			$result = $lineItemCreativeAssociationService
				->performLineItemCreativeAssociationAction(
					$action,
					$statementBuilder->toStatement()
				);

			if ($result !== null && $result->getNumChanges() > 0) {
				$response['success'] = true;
			} else {
				$response['message'] = 'Could not deactivate creative in line item ' . $lineItemId;
			}
		} catch ( \Exception $e ) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function delete( $creativeId, $lineItemId ) {
		$response = [
			'success' => true,
		];

		$statementBuilder = (new StatementBuilder())
			->where('lineItemId = :lineItemId and creativeId = :creativeId')
			->withBindVariableValue('lineItemId', $lineItemId)
			->withBindVariableValue('creativeId', $creativeId);

		try {
			$lineItemCreativeAssociationService = AdManagerService::get(\Google\AdsApi\AdManager\v201911\LineItemCreativeAssociationService::class);
			$action = new DeleteLineItemCreativeAssociations();

			$result = $lineItemCreativeAssociationService
				->performLineItemCreativeAssociationAction(
					$action,
					$statementBuilder->toStatement()
				);

			if ($result !== null && $result->getNumChanges() > 0) {
				$response['success'] = true;
			} else {
				$response['message'] = 'Could not delete creative in line item ' . $lineItemId;
			}
		} catch ( \Exception $e ) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function count( $lineItemId ) {
		$statementBuilder = (new StatementBuilder())
			->where('lineItemId = :lineItemId')
			->withBindVariableValue('lineItemId', $lineItemId);

		$lineItemCreativeAssociationService = AdManagerService::get(\Google\AdsApi\AdManager\v201911\LineItemCreativeAssociationService::class);

		$result = $lineItemCreativeAssociationService->getLineItemCreativeAssociationsByStatement($statementBuilder->toStatement());

		return count($result->getResults());
	}
}
