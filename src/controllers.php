<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

$app->get('/', function () use ($app) {
	$view = $app['twig']->render('index.html.twig', array());

	return new Response($view);
});

$app->post('/create', function () use ($app) {
	$name = $app['request']->get('name');
	$email = $app['request']->get('email');
	$identifier = $app['request']->get('identifier');

	$errors = $app['entryManager']->validateEntry($name, $email, $identifier, true);

	if (empty($errors) && $app['entryManager']->addEntry($name, $email, $identifier)) {
		$app['session']->getFlashBag()->add('success', sprintf('Successfully added entry with name \'%s\', email \'%s\' and GUID \'%s\'', $name, $email, $identifier));
	} else {
		$app['session']->getFlashBag()->add('error', 'Error: ' . implode(', ', $errors));
	}

	return new RedirectResponse('/' . $app['cfg']['general']['site_subdir']);
});

$app->get('/ajax/dsource/{type}', function ($type) use ($app) {
	$result = array();
	$dataSource = $app['dataSource'];

	if ($dataSource->hasType($type)) {
		$result = $dataSource->collect($type);
	}

	return new JsonResponse($result);
});

$app->get('/ajax/centry', function () use ($app) {
	$id = (int) $app['request']->get('id');
	$newState = (int) $app['request']->get('state');

	return new JsonResponse(array(
		'success' => $app['entryManager']->changeState($id, $newState)
	));
});

$app->post('/ajax/eentry', function () use ($app) {
	$id = (int) $app['request']->get('id');
	$field = $app['request']->get('col');
	$value = $app['request']->get('value');

	$success = false;
	$errors = null;

	$entry = $app['entryManager']->getEntryBy($id);

	if (!empty($entry) && $app['entryManager']->isValidColumn($field)) {
		$entry = $entry[0];
		$entry[$field] = $value;

		$validErrors = $app['entryManager']->validateEntry($entry['name'], $entry['email'], $entry['identifier']);

		if (empty($validErrors)) {
			$success = $app['entryManager']->editEntryField($id, $value, $field);
		} else {
			$errors = $validErrors;
		}
	}

	return new JsonResponse(array(
		'success' => $success,
		'errors' => $errors
	));
});

$app->get('/ajax/dentry', function () use ($app) {
	$id = (int) $app['request']->get('id');

	return new JsonResponse(array(
		'success' => $app['entryManager']->deleteEntry($id)
	));
});
