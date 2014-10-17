<?php

$project = elgg_extract('entity', $vars);

$time_created = $project->getLatestRelease()->time_created;

$year = 60 * 60 * 24 * 365;
$seconds_ago = time() - $time_created;
$years_ago = (int) floor($seconds_ago / $year);

if ($years_ago > 1) {
	$warning = elgg_echo('plugins:project:outdated_warning', array($years_ago));

	$help = elgg_echo('plugins:project:help');

	$messages = array();

	// Link to the comments section
	$messages[] = elgg_view('output/url', array(
		'href' => $project->getURL() . '#comments',
		'text' => elgg_echo('plugins:project:ask'),
	));

	// Link to the code repository
	if ($project->repo) {
		$messages[] = elgg_view('output/url', array(
			'href' => $project->repo,
			'text' => elgg_echo('plugins:project:collaborate'),
		));
	}

	// Link to form for requesting project ownership
	$messages[] = elgg_view('output/url', array(
		'href' => "plugins/{$project->guid}/request",
		'text' => elgg_echo('plugins:project:request_ownership'),
	));

	$suggestions = '';
	foreach ($messages as $message) {
		$suggestions .= "<li>$message</li>";
	}

	echo <<<HTML
<div class="elgg-box-error elgg-output mbl">
	<p>$warning</p>
	<p>$help</p>
	<ul>$suggestions</ul>
</div>
HTML;
}
