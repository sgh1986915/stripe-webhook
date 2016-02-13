<?php
  require('lib/Stripe.php');

  $cus_id = $_POST['cus_id'];
  $sub_id = $_POST['sub_id'];

  $stripeKey = "sk_test_9b496fHXP5VKKsecxIzFxMld";

  date_default_timezone_set('UTC');
  Stripe::setApiKey($stripeKey);

  //$invoiceID = 'in_16jRNHGoIOM2Af5cmztSyj57';
  $invoiceID = Stripe_Invoice::all(array("customer" => $cus_id))->data[0]->id;
  
  $invoice = Stripe_Invoice::retrieve($invoiceID);

  $customer = Stripe_Customer::retrieve($cus_id);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Customer Invoice">
    <meta name="author" content="5marks">

    <link rel="stylesheet" href="css/bootstrap-min.css">
    <style>
      .invoice-head td {
        padding: 0 8px;
      }
      .container {
      	padding-top:30px;
      }
      .invoice-body{
      	background-color:transparent;
      }
      .invoice-thank{
      	margin-top: 60px;
      	padding: 5px;
      }
      address{
      	margin-top:15px;
      }
    </style>
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  </head>

  <body>
    <div class="container">
    	<div class="row">
    		<div class="span4">
    			<img src="/img/5marks-logo.png" title="logo">
    			<address>
			        <strong><?php echo $customer->metadata->company; ?></strong>
		    	</address>
    		</div>
    		<div class="span4 well">
    			<table class="invoice-head">
    				<tbody>
    					<tr>
    						<td class="pull-right"><strong>Customer #</strong></td>
    						<td><?php echo $invoice->customer; ?></td>
    					</tr>
    					<tr>
    						<td class="pull-right"><strong>Invoice #</strong></td>
    						<td><?php echo $invoice->id; ?></td>
    					</tr>
              <?php
                if ($customer->metadata->vat != "" && $customer->metadata->vat != "-1") {
              ?>
              <tr>
                <td class="pull-right"><strong>VAT #</strong></td>
                <td><?php echo $customer->metadata->vat; ?></td>
              </tr>
              <?php
                }
              ?>
    					<tr>
    						<td class="pull-right"><strong>Date</strong></td>
    						<td><?php echo date('M j, Y', $invoice->date); ?></td>
    					</tr>
    					<tr>
    						<td class="pull-right"><strong>Period</strong></td>
    						<td><?php echo date('M j, Y', $invoice->period_start) .' to ' . date('M j, Y', $invoice->period_end); ?></td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    	<div class="row">
    		<div class="span8">
    			<h2>Invoice</h2>
    		</div>
    	</div>
    	<div class="row">
		  	<div class="span8 well invoice-body">
		  		<table class="table table-bordered">
					<thead>
						<tr>
							<th>Description</th>
							<th>Date</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
  				  <?php
    					$total = 0;
    					foreach ($invoice->lines->data as $subscription) {
    						echo '<tr>';
    						$amount = $subscription->amount / 100;
    						echo '<td>'.$subscription->plan->name.' ($'.number_format($subscription->plan->amount / 100,2).'/'.$subscription->plan->interval.')</td>';
    						echo '<td>' . date('M j, Y', $subscription->period->start) .' - ' . date('M j, Y', $subscription->period->end). '</td>';
    						echo '<td>$' . number_format($amount,2).'</td>';
    						$total += $amount;
    						echo '</tr>';
    					}

    					if (isset($invoice->discount)) {
    						echo '<tr>';
    						echo '<td>'.$invoice->discount->coupon->id.' ('.$invoice->discount->coupon->percent_off.'% off)</td>';
    						$discount = $total * ($invoice->discount->coupon->percent_off/100);
    						echo '<td>&nbsp;</td>';
    						echo '<td>-$'.number_format($discount,2).'</td>';
    						echo '</tr>';
    					}

              if (isset($invoice->tax_percent)) {
                echo '<tr>';
                $tax_amount = $invoice->tax / 100;
                echo '<td></td><td>Tax ('.$invoice->tax_percent.'%)</td>';
                echo '<td>$' . number_format($tax_amount, 2).'</td>';
                echo '</tr>';
              }
  				  ?>
						<tr>
							<td>&nbsp;</td>
							<td><strong>Total</strong></td>
							<td><strong>$<?php echo number_format(($invoice->total / 100), 2); ?></strong></td>
						</tr>
					</tbody>
				</table>
		  	</div>
  		</div>
  		<div class="row">
  			<div class="span6 offset1 well invoice-thank">
  				<h5 style="text-align:center;">Thank You!</h5>
  			</div>
  		</div>
  		<div class="row">
  	    	<div class="span3">
  		        <strong>Phone:</strong> <a href="tel:555-555-5555">555-555-5555</a>
  	    	</div>
  	    	<div class="span3">
  		        <strong>Email:</strong> <a href="mailto:<?php echo $customer->email; ?>"><?php echo $customer->email; ?></a>
  	    	</div>
  	    	<div class="span3">
  		        <strong>Website:</strong> <a href="http://brandbits.com">http://brandbits.com</a>
  	    	</div>
  		</div>
    </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	  <script>!window.jQuery && document.write(unescape('%3Cscript src="js/jquery/jquery-1.7.1.min.js"%3E%3C/script%3E'))</script>	
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
  </body>
</html>
