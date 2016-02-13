<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment Form</title>
<link rel="stylesheet" href="css/bootstrap-min.css">
<link rel="stylesheet" href="css/bootstrap-formhelpers-min.css" media="screen">
<link rel="stylesheet" href="css/bootstrapValidator-min.css"/>
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" />
<link rel="stylesheet" href="css/bootstrap-side-notes.css" />
<style type="text/css">
.col-centered {
    display:inline-block;
    float:none;
    text-align:left;
    margin-right:-4px;
}
.row-centered {
  margin-left: 9px;
  margin-right: 9px;
}
</style>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="js/bootstrap-min.js"></script>
<script src="js/bootstrap-formhelpers-min.js"></script>
<script type="text/javascript" src="js/bootstrapValidator-min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('#payment-form').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        submitHandler: function(validator, form, submitButton) {
          var country_code = $('#countries').val();
          var country_name = $("#countries option:selected").text();
          $('#countryname').val(country_name);

          // createToken returns immediately - the supplied callback submits the form if there are no errors
          Stripe.card.createToken({
            number: $('.card-number').val(),
            cvc: $('.card-cvc').val(),
            exp_month: $('.card-expiry-month').val(),
            exp_year: $('.card-expiry-year').val(),
            name: $('.card-holder-name').val(),
            description: $('.company-name').val(),
            address_country: country_code
          }, stripeResponseHandler);
            return false; // submit from callback
        },
        fields: {
          cardholdername: {
              validators: {
                  notEmpty: {
                      message: 'The card holder name is required and can\'t be empty'
                  },
                  stringLength: {
                      min: 6,
                      max: 70,
                      message: 'The card holder name must be more than 6 and less than 70 characters long'
                  }
              }
          },
          cardnumber: {
            selector: '#cardnumber',
            validators: {
              notEmpty: {
                message: 'The credit card number is required and can\'t be empty'
              },
              creditCard: {
                message: 'The credit card number is invalid'
              },
            }
          },
          expMonth: {
            selector: '[data-stripe="exp-month"]',
            validators: {
              notEmpty: {
                  message: 'The expiration month is required'
              },
              digits: {
                  message: 'The expiration month can contain digits only'
              },
              callback: {
                  message: 'Expired',
                  callback: function(value, validator) {
                      value = parseInt(value, 10);
                      var year         = validator.getFieldElements('expYear').val(),
                          currentMonth = new Date().getMonth() + 1,
                          currentYear  = new Date().getFullYear();
                      if (value < 0 || value > 12) {
                          return false;
                      }
                      if (year == '') {
                          return true;
                      }
                      year = parseInt(year, 10);
                      if (year > currentYear || (year == currentYear && value > currentMonth)) {
                          validator.updateStatus('expYear', 'VALID');
                          return true;
                      } else {
                          return false;
                      }
                  }
              }
            }
          },
          expYear: {
              selector: '[data-stripe="exp-year"]',
              validators: {
                notEmpty: {
                    message: 'The expiration year is required'
                },
                digits: {
                    message: 'The expiration year can contain digits only'
                },
                callback: {
                  message: 'Expired',
                  callback: function(value, validator) {
                    value = parseInt(value, 10);
                    var month        = validator.getFieldElements('expMonth').val(),
                        currentMonth = new Date().getMonth() + 1,
                        currentYear  = new Date().getFullYear();
                    if (value < currentYear || value > currentYear + 100) {
                        return false;
                    }
                    if (month == '') {
                        return false;
                    }
                    month = parseInt(month, 10);
                    if (value > currentYear || (value == currentYear && month > currentMonth)) {
                        validator.updateStatus('expMonth', 'VALID');
                        return true;
                    } else {
                        return false;
                    }
                }
              }
            }
          },
          cvv: {
            selector: '#cvv',
            validators: {
              notEmpty: {
                message: 'The cvv is required and can\'t be empty'
              },
              cvv: {
                message: 'The value is not a valid CVV',
                creditCardField: 'cardnumber'
              }
            }
          }
        }
    });

    $('#countries').change(function() {
      var EUArray = ["AT", "BE", "BG", "HR", "CY", "CZ", "DK", "EE", "FI", "FR", "DE", "GR", "HU", "IS", "IE", "IT",
       "LV", "LT", "LU", "MT", "NL", "NO", "PL", "PT", "RO", "RU", "RS", "SK", "SI", "ES", "SE", "CH", "GB"];
      var v = $.inArray( $(this).val(), EUArray );
      
      if (v > 0) {
        $(".vat").val("");
        $(".vat-form").show();
      } else {
        $(".vat").val("-1");
        $(".vat-form").hide();
      }
    });
});
</script>

<script type="text/javascript">
  // this identifies your website in the createToken call below
  Stripe.setPublishableKey('pk_test_zrsYRtcuuF6UM70gtrsKJcpH');

  function stripeResponseHandler(status, response) {
    if (response.error) {
      // re-enable the submit button
      $('.submit-button').removeAttr("disabled");
      // show hidden div
      document.getElementById('a_x200').style.display = 'block';
          // show the errors on the form
          $(".payment-errors").html(response.error.message);
      } else {
          var form$ = $("#payment-form");
          // token contains id, last4, and card type
          var token = response['id'];
          // insert the token into the form so it gets submitted to the server
          form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
          // and submit
          form$.get(0).submit();
      }
  }

</script>

</head>
<body>
<form action="result.php" method="POST" id="payment-form" class="form-horizontal">
  <div class="row row-centered">
  <div class="col-md-4 col-md-offset-4">
  <div class="page-header">
    <h2 class="gdfg">Payment Form</h2>
  </div>

  <noscript>
  <div class="bs-callout bs-callout-danger">
    <h4>JavaScript is not enabled!</h4>
    <p>This payment form requires your browser to have JavaScript enabled. Please activate JavaScript and reload this page. Check <a href="http://enable-javascript.com" target="_blank">enable-javascript.com</a> for more informations.</p>
  </div>
  </noscript>

  <div class="alert alert-danger" id="a_x200" style="display: none;"> <strong>Error!</strong> <span class="payment-errors"></span> </div>
  <fieldset>
    <legend>Contact Information</legend>
    
    <!-- Company Name -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Company Name</label>
      <div class="col-sm-6">
        <input type="text" name="companyname" class="company-name form-control">
      </div>
    </div>

    <!-- VAT -->
    <div class="vat-form form-group" style="display: none;">
      <label class="col-sm-4 control-label" for="textinput">VAT</label>
      <div class="col-sm-6">
        <input type="text" name="vat" class="vat form-control" value="-1">
      </div>
    </div>
  </fieldset>
  
  <fieldset>
    <legend>Card Information</legend>
    
    <!-- Card Holder Name -->
    <div class="form-group">
      <label class="col-sm-4 control-label"  for="textinput">Card Holder's Name</label>
      <div class="col-sm-6">
        <input type="text" name="cardholdername" maxlength="70" placeholder="Card Holder Name" class="card-holder-name form-control">
      </div>
    </div>
    
    <!-- Card Number -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Card Number</label>
      <div class="col-sm-6">
        <input type="text" id="cardnumber" maxlength="19" placeholder="Card Number" class="card-number form-control">
      </div>
    </div>
    
    <!-- Expiry-->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Card Expiry Date</label>
      <div class="col-sm-6">
        <div class="form-inline">
          <select name="select2" data-stripe="exp-month" class="card-expiry-month stripe-sensitive required form-control">
            <option value="01" selected="selected">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
          <span> / </span>
          <select name="select2" data-stripe="exp-year" class="card-expiry-year stripe-sensitive required form-control">
          </select>
          <script type="text/javascript">
            var select = $(".card-expiry-year"),
            year = new Date().getFullYear();
 
            for (var i = 0; i < 12; i++) {
                select.append($("<option value='"+(i + year)+"' "+(i === 0 ? "selected" : "")+">"+(i + year)+"</option>"))
            }
        </script> 
        </div>
      </div>
    </div>
    
    <!-- CVV -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">CVV/CVV2</label>
      <div class="col-sm-3">
        <input type="text" id="cvv" placeholder="CVV" maxlength="4" class="card-cvc form-control">
      </div>
    </div>

    <!-- Plan -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Plan</label>
      <div class="col-sm-6">
        <div class="form-inline">
          <select name="plan" class="required form-control">
            <option value="starter" selected="selected">starter</option>
            <option value="basic">basic</option>
            <option value="business">business</option>
            <option value="enterprise">enterprise</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Submit -->
    <div class="control-group">
      <div class="controls">
        <center>
          <button class="btn btn-success" type="submit">Pay Now</button>
        </center>
      </div>
    </div>
  </fieldset>
</form>

</body>
</html>
