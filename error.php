<?php

  	require 'lib/Stripe.php';
  
	Stripe::setApiKey("sk_test_9b496fHXP5VKKsecxIzFxMld"); 


    // Retrieve the request's body and parse it as JSON
	$body = @file_get_contents("php://input");
	$event_json = json_decode($body);

	$event_id = $event_json->id;
	$event = Stripe_Event::retrieve($event_id);

	//when a failed charge attempt
	if ($event->type == 'charge.failed') {
		email_charge_failed($event->data->object);
	}

	//when an invoice attempts to be paid, and the payment fails
	if ($event->type == 'invoice.payment_failed') {
		email_invoice_payment_failed($event->data->object);
	}

	//when payment is attempted on an order, and the payment fails
	if ($event->type == 'order.payment_failed') {
		email_order_payment_failed($event->data->object);
	}

	//when Stripe attempts to send a transfer and that transfer fails.
	if ($event->type == 'transfer.failed') {
		email_transfer_failed($event->data->object);
	}

	function email_charge_failed($charge) {
		$customer = Stripe_Customer::retrieve($charge->customer);
		
		$subject = 'Your payment was failed charge';
		$headers = 'From: "Brandbits Support" <support@brandbits.com>';
		mail($customer->email, $subject, message_body(), $headers);
	}

	function email_invoice_payment_failed($invoice) {
		$customer = Stripe_Customer::retrieve($invoice->customer);
		
		$subject = 'Your invoice payment was failed';
		$headers = 'From: "Brandbits Support" <support@brandbits.com>';
		mail($customer->email, $subject, message_body(), $headers);
	}

	function email_order_payment_failed($order) {
		$customer = Stripe_Customer::retrieve($order->customer);
		
		$subject = 'Your order payment was failed';
		$headers = 'From: "Brandbits Support" <support@brandbits.com>';
		mail($customer->email, $subject, message_body(), $headers);
	}

	function email_transfer_failed($transfer) {
		$customer = Stripe_Customer::retrieve($transfer->customer);
		
		$subject = 'Your transfer was failed';
		$headers = 'From: "Brandbits Support" <support@brandbits.com>';
		mail($customer->email, $subject, message_body(), $headers);
	}

	function message_body() {
		return "Brandbits Service Message";
	}
?>