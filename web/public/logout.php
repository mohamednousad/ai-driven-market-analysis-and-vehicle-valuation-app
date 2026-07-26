<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::logout();
session_start();
flash('success', 'You have been logged out.');
redirect('/index.php');
