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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<?php
  if ($_POST) {

    $isTax = false;

    if (isset($_POST['vat']) && $_POST['vat'] != "") {
      $vat = $_POST['vat'];
      $country_code = $_POST['countries'];

      if ($vat == "-1") {
        $isTax = false;
      } else {
        if ($country_code == "NL") {
          $isTax = true;
        } else {
          //Validate VAT Number
          require_once('vatValidation.class.php');
          $vatValidation = new vatValidation( array('debug' => false));

          try {
            if($vatValidation->check($country_code, $vat)) {
              $isTax = false;
            } else {
              $isTax = true;
            }
          }
          catch (Exception $e) {
            $isTax = true;
          }
        }
      }
    }

    if (isset($_POST['vat']) && $_POST['vat'] == "") {
      $isTax = true;
    }

    //Create Subscription
    require 'lib/Stripe.php';
    $success = '';
    $error = '';
  
    Stripe::setApiKey("sk_test_9b496fHXP5VKKsecxIzFxMld");

    try {
      if (!isset($_POST['stripeToken']))
        throw new Exception("The Stripe Token was not generated correctly");

        $tax = $isTax ? "21" : NULL;

        $customer = Stripe_Customer::create(
          array(
            "source" => $_POST['stripeToken'],
            "plan" => $_POST['plan'],
            "email" => "scottsgh915@gmail.com",
            "tax_percent" => $tax,
            "metadata" => array(
              "company" => $_POST['companyname'],
              "vat" => $_POST['vat']
            )
          )
        );
        
        $cus_id = $customer->id;
        $sub_id = $customer->subscriptions->all()->data[0]->id;

        $success = '<div class="alert alert-success">
                <strong>Success!</strong> Your payment was successful.
        </div>';
    }
    catch (Exception $e) {
      $error = '<div class="alert alert-danger">
        <strong>Error!</strong> '.$e->getMessage().'
        </div>';
    }
  }
?>

<script type="text/javascript">

  function cancelSubscription() {
    var form$ = $("#process-form");

    form$.attr("action", "process.php?action=1");
    
    form$.get(0).submit();
  }

  function updateSubscription() {
    var form$ = $("#process-form");

    form$.attr("action", "process.php?action=2");
    
    form$.get(0).submit();
  }

  function showInvoice() {
    var form$ = $("#process-form");

    form$.attr("action", "invoice.php");
    
    form$.get(0).submit();
  }

</script>

</head>
<body>
  <form action="process.php" method="POST" id="process-form" class="form-horizontal">
    <input type="hidden" name="cus_id" value="<?=$cus_id?>">
    <input type="hidden" name="sub_id" value="<?=$sub_id?>">

    <div class="row row-centered">
      <div class="col-md-4 col-md-offset-4">
        <div class="page-header">
          <h2 class="gdfg">Result</h2>
        </div>
        <?= $success ?>
        <?= $error ?>

        <div class="form-group">
          <label class="col-sm-2 control-label">Company</label>
          <div class="col-sm-10">
            <p class="form-control-static"><?=$_POST['companyname']?></p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Country</label>
          <div class="col-sm-10">
            <p class="form-control-static"><?=$_POST['countryname']?></p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Token</label>
          <div class="col-sm-10">
            <p class="form-control-static"><?=$_POST['stripeToken']?></p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Package</label>
          <div class="col-sm-10">
            <p class="form-control-static"><?=$_POST['plan']?></p>
          </div>
        </div>

        <?php
          if (!isset($_POST['vat']) || $_POST['vat']!="-1") {
        ?>
        <div class="form-group">
          <label class="col-sm-2 control-label">VAT</label>
          <div class="col-sm-10">
            <p class="form-control-static"><?=$_POST['vat']?></p>
          </div>
        </div>
        <?php
          }
        ?>

        <fieldset>
          <legend>Cancel Subscription</legend>
          <button class="btn btn-primary" type="button" onClick="cancelSubscription()">Cancel</button>
        </fieldset>

        <fieldset>
          <legend>Upgrade/Downgrade Subscription</legend>
          <select name="plan" class="required form-control">
            <option value="starter" selected="selected">starter</option>
            <option value="basic">basic</option>
            <option value="business">business</option>
            <option value="enterprise">enterprise</option>
          </select>
          <button class="btn btn-success" type="button" onClick="updateSubscription()">Update</button>
        </fieldset>

        <fieldset>
          <legend>Show Invoice</legend>
          <button class="btn btn-info" type="button" onClick="showInvoice()">Invoice</button>
        </fieldset>

      </div>
    </div>
  </form>
</body>
</html>
