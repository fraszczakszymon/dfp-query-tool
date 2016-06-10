<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$reportController = new \Report\Controller\ReportController($app);
$queryController = new \Report\Controller\QueryController($app);
$tableauController = new \Tableau\Controller\TableauController($app);

$app->post('/api/query', function(\Symfony\Component\HttpFoundation\Request $request) use ($app, $queryController) {
	return $app->json($queryController->post($request));
});

$app->get('/api/reports/{id}', function($id) use ($app, $reportController) {
	return $app->json($reportController->get($id));
});

$app->get('/tableau', function () use ($tableauController) {
	return $tableauController->renderWebConnector();
});

$app->run();