<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::logout();
Session::start();
Flash::info('You have been signed out.');
Helpers::redirect('index.php');
