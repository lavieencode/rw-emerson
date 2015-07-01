<?php

require 'vendor/autoload.php';
date_default_timezone_set ('America/Los_Angeles' );

// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
use Slim\Slim;

// $log = new Logger('name');
// $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Oh noes!');

//TWIG INSTANTIATION

$app = new Slim(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

//PAGE RENDERS

$app->get('/hello/:name', function ($name) {
	echo "Hello, $name!";
});

$app->get('/', function() use($app) {
	$app->render('about.twig');
})->name('home');

$app->get('/contact', function() use($app){
	$app->render('contact.twig');
})->name('contact');

$app->post('/contact', function() use($app){
	$name = $app->request->post('name');
	$email = $app->request->post('email');
	$msg = $app->request->post('msg');

	if(!empty($name) && !empty($email) && !empty($msg)){
		$cleanName = filter_var($name, FILTER_SANITIZE_STRING);
		$cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
		$cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
	} else {
		//message the user that there was a problem
		$app->redirect('contact');
	}

	//SWIFT MAIL ASSIGNMENTS

	$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
	$mailer = \Swift_Mailer::newInstance($transport);

	$message = \Swift_Message::newInstance();
	$message->setSubject('Email from your website');
	$message->setFrom(array(
				$cleanEmail => $cleanName
				));
	$message->setTo(array('sofrlowi@gmail.com'));
	$message->setBody($cleanMsg);

	$result = $mailer->send($message);

	if($result > 0) {
		//send message that says thank you
		$app->redirect('/treehousephp/rw-emerson'); //FIX LATER: used I static URL just to get this shit to work
	} else {
		//send a message to the user that the message failed to send
		//log that there was an error (monolog)
		$app->redirect('/contact');
	}
});

$app->run();

?>