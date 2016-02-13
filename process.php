<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Result</title>
<link rel="stylesheet" href="css/bootstrap-min.css">
<link rel="stylesheet" href="css/bootstrap-formhelpers-min.css" media="screen">
<link rel="stylesheet" href="css/bootstrapValidator-min.css"/>
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" />
<link rel="stylesheet" href="css/bootstrap-side-notes.css" />

<?php
  require 'lib/Stripe.php';
  $success = '';
  $error = '';
 
  if ($_POST) {
    
    Stripe::setApiKey("sk_test_9b496fHXP5VKKsecxIzFxMld");

    try {
      $cus_id = $_POST['cus_id'];
      $sub_id = $_POST['sub_id'];

      $action = $_GET['action'];

      if ($action == 1) {
        $cu = Stripe_Customer::retrieve($cus_id);
        $cu->subscriptions->retrieve($sub_id)->cancel();

        $success = '<div class="alert alert-success">
              <strong>Success!</strong> Your subscription was cancelled.
        </div>';
      }

      if ($action == 2) {
        $plan = $_POST['plan'];

        $cu = Stripe_Customer::retrieve($cus_id);
        $subscription = $cu->subscriptions->retrieve($sub_id);
        $subscription->plan = $plan;
        $subscription->save();

        $success = '<div class="alert alert-success">
              <strong>Success!</strong> Your subscription was updated.
        </div>';
      }      

      
    }
    catch (Exception $e) {
      $error = '<div class="alert alert-danger">
        <strong>Error!</strong> '.$e->getMessage().'
        </div>';
    }
  }
?>

</head>
<body>
  <?= $success ?>
  <?= $error ?>
</body>
</html>
