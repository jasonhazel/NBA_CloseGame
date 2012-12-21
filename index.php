<?php
date_default_timezone_set('America/New_York');
$f3=require('lib/base.php');

$f3->set('DEBUG',2);
$f3->set('UI','ui/');

$f3->route('GET /', function() use ($f3) {

	$f3->set('output', 'whut?');
	$view = new View;
	echo $view->render('template.html');
});

$f3->route('GET /@date/@home/@away', function() use ($f3){
	$request 	= $f3->get('PARAMS');
	$date 		= date('Ymd',strtotime($request['date']));
	$home		= strtolower(str_replace(' ', '-', $request['home']));
	$away		= strtolower(str_replace(' ', '-', $request['away']));

	$api		= "http://erikberg.com/nba/boxscore/$date-$home-at-$away.json";
	$web 		= new Web;
	$response = $web->request($api);
	
	switch ($response['headers'][0])
	{
		case "HTTP/1.1 200 OK":
			$game_data = json_decode($response['body']);
			
			$home_score = 0;
			$away_score = 0;

			foreach ($game_data->home_period_scores as $score)
				$home_score += $score;

			foreach ($game_data->away_period_scores as $score)
				$away_score += $score;

			$within_threshold = (abs($home_score - $away_score <= 10) ? 'Yes' : 'No');
			$f3->set('output', $within_threshold);

		break;
		case "HTTP/1.1 404 Not Found":
			$f3->set('output', 'Invalid Request');
		break;
		default:
			$f3->set('output',$response['headers'][0]);
	}

	$view = new View;
	echo $view->render('template.html');
});

$f3->run();