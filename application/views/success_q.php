<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>2018 School Questionnaire</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="http://www.study-au.com/ktform_is/track.js"></script>
</head>
<body onload="accessDetect();">
    
    <?php
    /*if (isset($_POST)){
        echo '<pre>';
        var_dump($_POST);
        echo '</pre>';
    }*/
    ?>

<div id="container">
	<h1>Submit success!</h1>
    
    <?php
    //var_dump($consequcen)
    ?>
    <p>
	<h1>Thank you for your coopeartion and please send below promotion items by mail.</h1>
	<ul>
		<li>Application form</li>
		<li>Price List(Special Promotion if any)</li>
		<li>Credit Card Payment Authorisation Form</li>
		<li>Course details and any other information</li>
		<li>Sample Timetable</li>
		<li>School Image Photo(at least five pictures)</li>
		<li>School logo</li>
	</ul>
        <!--<img src="./passport.png" />-->
    </p>
</div>

</body>
</html>