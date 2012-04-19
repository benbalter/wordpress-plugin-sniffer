<?php 
include( 'class.plugin-sniffer.php' );
$sniffer = new WP_Plugin_Sniffer();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>WordPress Plugin Sniffer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <style>
    	body { padding-top: 60px; }
    </style>

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    </head>

  <body>

    <div class="container">

      <div class="hero-unit">
      	<h1>WordPress Plugin Sniffer</h1>
      </div>
      
      <?php if ( !isset( $_GET['home'] ) ) { ?>
      <div class="row">
      	<div class="span8">
      		<form class="form-horizontal" method="GET">
      		    <legend>Options</legend>
      			<fieldset>
      				<div class="control-group">
      					<label class="control-label" for="home">Site Url <i class="icon-home"></i></label>
      					<div class="controls">
      						<input type="text" class="input-xlarge" id="home" name="home" placeholder="http://foo.com">
 						    <p class="help-block">The URL to the site's homepage</p>
      					</div>
      				 </div>
      				<div class="control-group">
      					<label class="control-label" for="count">Number of Checks <i class="icon-search"></i></label>
      					<div class="controls">
      						 <input type="text" class="input-small" id="count" name="count" value="<?php echo $sniffer->count; ?>">
 						    <p class="help-block">Number of plugins to check, sorted descending by most popular.</p>
      					</div>
      				 </div>
      				<div class="form-actions">
      					<button type="submit" class="btn btn-large btn-primary">Sniff Plugins</button>
      					<a href="./" class="btn"><i class="icon-repeat"></i> Reset</a>
      				 </div>
      			</fieldset>
      		</form>
      	</div>
      	<div class="span4">
      		<h3>Instructions</h3>
      		<p>Enter the url of a WordPress site to begin.</p>
      		<p>Optionally, enter the number of plugins to check.</p>
      		<p>The tool will check the site to see which, if any of those plugins are installed.</p>
      	</div>
      </div>
      <?php } else { $sniffer->sniff(); ?>
      
      <div class="row">
      	<div class="span4">
        	<h3>Results</h3>

       		<div class="well">
   	  			<p>Site: <code><?php echo $sniffer->home; ?></code></p>
      			<p><span class="badge badge-info"><?php echo $sniffer->count; ?></span> Plugins Checked </p>
      			<p><span class="badge badge-success"><?php echo $sniffer->num_found; ?></span> Plugins Found</p>
          		<a href="./" class="btn btn-small"><i class="icon-repeat"></i> Sniff Another Site</a>
  			</div>
      	</div>
      	<div class="span6 offset2">
      		<h3>Plugins</h3>
      		<ol>
      		<?php foreach ( $sniffer->plugins as $plugin ) {?> 
      			<li><?php echo $plugin; ?></li>
      		<?Php } ?>
      		</ol>
      	</div>
      </div>
      
      <?php } ?>


    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>