<!DOCTYPE html>
<html lang="en">
    <head>
        <title>jQuery.Gantt</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=IE8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo '<link href="'.RMO_FOLDER.'/css/style.css" type="text/css" rel="stylesheet">';?>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            body {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 13px;
                padding: 0 0 50px 0;
            }
            h1 {
                margin: 40px 0 20px 0;
            }
            h2 {
                font-size: 1.5em;
                padding-bottom: 3px;
                border-bottom: 1px solid #DDD;
                margin-top: 50px;
                margin-bottom: 25px;
            }
            table th:first-child {
                width: 150px;
            }
        </style>
    </head>
    <body>

       <div class="gantt"></div>

    <?php echo '<script src="'.RMO_FOLDER.'/js/jquery.min.js"></script>';?>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
	<?php echo '<script src="'.RMO_FOLDER.'/js/jquery.fn.gantt.js"></script>';?>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
    <script>
        $(function() {
            "use strict";
            $(".gantt").gantt({
				source:
				<?php 
				$view='user';
				if(isset($_GET['view']))
					$view = $_GET['view'];
				
				$guser = null;
				if(isset($_GET['user']))
					$guser = $_GET['user'];
				if($guser != null)
					 echo '"rmo?data=1&view='.$view.'&user='.$guser.'"';
				else
					echo '"rmo?data=1&view='.$view.'"';
				?>,
                navigate: "scroll",
                scale: "days",
                maxScale: "weeks",
                minScale: "days",
                itemsPerPage: 10,
                scrollToToday: false,
                useCookie: false,
                onItemClick: function(data) {
                    //alert("Item clicked - show some details");
                },
                onAddClick: function(dt, rowId) {
                    //alert("Empty space clicked - add an item!");
                },
                onRender: function() {
                   // if (window.console && typeof console.log === "function") {
                   //     console.log("chart rendered");
                   // }
                }
            });

            /*$(".gantt").popover({
                selector: ".bar",
                title: function _getItemText() {
                    return this.textContent;
                },
                content: "Here's some useful information.",
                trigger: "hover",
                placement: "auto right"
            });*/

            //prettyPrint();

        });
    </script>

    </body>
</html>
