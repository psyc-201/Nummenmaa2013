<?php
    include_once('settings.php');
    include_once('lib.php');
    include_once('headerpaint.php');
    
    $stimuli=loadTxt($stimulusfile,0);

    // added: read coordinates in the json file
    $coords_json = file_get_contents('contour_coordinate_opencv.json');
    $coords = json_decode($coords_json, true);
    
    $userID=$_GET['userID'];
    $p=$_GET['presentation'];
    $progress = $_GET['perc'];
    //middle part of the image
    
    // $p is the ID of the word to show
    $outfile="./subjects/$userID/$p.csv";
    $perc=$_GET['perc'];
    ?>


<div id ="header">
<span style="font-size:14px;font-weight:bold;margin-left:10px;position:absolute;">id: <?php echo $userID; ?></span>
<div style="float:right;margin-right:10px;"><a href="help.php" target="_blank"><img src="images/help-button.png" width="20" height="20" alt="help"></a></div>

<div id="progress-bar" class="all-rounded">
<div id="progress-bar-percentage" class="all-rounded" style="width: <?php echo $perc;?>%"><span><?php echo $perc;?>%</span>
</div>
</div>


</div>

<div id="container">

<div style="bottom:0px;left:0px;">
<span></span><span>&nbsp;</span>
</div>

<!---instructions in the middle-->
<div style="text-align:center;margin-top:50px;">
<div id = "pboxL"><h3 style="font-weight:bold;"><br>
<?php echo $tasklabel;?></h3> </div>

<?php if($type == 'paintimages'){
    if(file_exists($stimuli[$p])):
        $stimulusimage = $stimuli[$p];
    else:
        $stimulusimage = './yuno.jpg';
    endif;
    ?>
<img style="width:480px;margin-top:10px;border:2px solid black;" src="<?php echo $stimulusimage;?>">
</div>
<?php }elseif($type == 'paintwords'){?>
<div class="wordsbox"> 
<?php echo $stimuli[$p];?></div>
</div>
<?php }?>

<div id="pbox">
    <div id="pbox1">
    </div>
    <div id="pbox1L">
<?php echo $labels[0]; ?>
    </div>

    <div id="pbox2">
    </div>
    <div id="pbox2L">
<?php echo $labels[1]; ?>
    </div>
    </div>

<!-- added: Add SVG overlay for drawing the body outlines -->
<svg id="body-outline" style="position:absolute; top:0px; left:0px; width:900px; height:600px; pointer-events:none; z-index:101;"></svg>

<!--- navigation -->

</div>

<script type="text/javascript">
        var temp=document.getElementById("pbox");
        temp.onselectstart = function() { this.style.cursor='crosshair'; return false; }
</script>


<div id = "footer">

<form method="POST" action="getit.php" id="movenext">
<div style="float:right;margin-right:10px;margin-top:10px;">
<input type="submit" value="<?php echo $pagetexts['forward'];?>" style="color:#093;cursor:pointer;background:#ddd;font-size:20px;padding:1px;font-weight:bold;"></div></form>

<form action="#"><input type="button" style="color:#f00;cursor:pointer;background:#ddd;font-size:20px;padding:1px;font-weight:bold;margin-top:10px;margin-left:10px;" value=<?php echo $pagetexts['delete'];?>  onClick="history.go()"></form>

<script type="text/javascript" >

// $("span:first").text('ac '+spraycan);
var outfile="<?php echo $outfile; ?>";

// var xp=$("#pbox").offset().left;
// var yp=$("#pbox").offset().top;

// 🔵🔵🔵 [UPDATED] Dynamic offset tracking
var xp, yp;
function updateOffsets() {
    xp = $("#pbox").offset().left;
    yp = $("#pbox").offset().top;
}
updateOffsets();
$(window).resize(updateOffsets);

var arrX = new Array(0);
var arrY = new Array(0);
var arrTime = new Array(0);

var arrXD = new Array(0);
var arrYD = new Array(0);
var arrTimeD = new Array(0);

var arrMD = new Array(0);
var arrMU = new Array(0);

// added: Track mouse button down state, bodycoordinate list
var isDrawing = false;
var bodyCoordsLeft = [];
var bodyCoordsRight = [];

// 🔵 New: Load and transform the body polygons
fetch('contour_coordinate_opencv.json')
    .then(response => response.json())
    .then(data => {
        const width_orig = 418;
        const height_orig = 1224;
        const width_display = 175;  // #pbox1 and #pbox2 width
        const height_display = 524; // #pbox1 and #pbox2 height
        const offset_x_left = 30;   // pbox1 left margin
        const offset_x_right = 900 - 175 - 30; // pbox2 position
        const offset_y = 10;         // Top margin inside pbox

        function transformPoint(pt, offsetX) {
            const [x, y] = pt;
            const scaled_x = (x / width_orig) * width_display + offsetX;
            const scaled_y = (y / height_orig) * height_display + offset_y;
            return [scaled_x, scaled_y];
        }

        bodyCoordsLeft = data.map(pt => transformPoint(pt, offset_x_left));
        bodyCoordsRight = data.map(pt => transformPoint(pt, offset_x_right));

        // 🔵 Draw outlines after transformation
        drawBodyOutline(bodyCoordsLeft, "red");
        drawBodyOutline(bodyCoordsRight, "blue");
    });

// 🔵 New: Function to draw the body outlines
function drawBodyOutline(coords, color) {
    const svg = document.getElementById("body-outline");
    const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
    polygon.setAttribute("points", coords.map(p => p.join(",")).join(" "));
    polygon.setAttribute("fill", "none");
    polygon.setAttribute("stroke", color);
    polygon.setAttribute("stroke-width", "2");
    polygon.setAttribute("opacity", "0.5");
    svg.appendChild(polygon);
}

// added: Define point-in-polygon function
function pointInPolygon(point, vs) {
    var x = point[0], y = point[1];
    var inside = false;
    for (var i = 0, j = vs.length - 1; i < vs.length; j = i++) {
        var xi = vs[i][0], yi = vs[i][1];
        var xj = vs[j][0], yj = vs[j][1];

        var intersect = ((yi > y) != (yj > y)) &&
                        (x < (xj - xi) * (y - yi) / (yj - yi + 0.00001) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}


// modified: drawing behavior
$("#pbox")
  .mousedown(function(e) {
      isDrawing = true;
  })
  .mouseup(function(e) {
      isDrawing = false;
  })
  .mousemove(function(e) {
      if (!isDrawing || bodyCoordsLeft.length === 0) return;

      var x = e.offsetX;
      var y = e.offsetY;

      let insideLeft = pointInPolygon([x, y], bodyCoordsLeft);
      let insideRight = pointInPolygon([x, y], bodyCoordsRight);

      if (insideLeft || insideRight) {
          if (insideLeft)
              currColour = 'red';
          else if (insideRight)
              currColour = 'blue';

          drawCircle(x, y, currColour); // 🔵 manually draw!
          
          arrX.push(x);
          arrY.push(y);
          arrTime.push(e.timeStamp);
      }
  });



/* attach a submit handler to the form */
$("#movenext").submit(function(event) {
                      /* stop form from submitting normally */
                      event.preventDefault();
                      
                      /* get some values from elements on the page: */
                      var $form = $( this );
                      url = $form.attr( 'action' );
                      
                      /* Send the data using post and put the results in a div */
                      $.post( url, {'arrX': arrX, 'arrY': arrY, 'arrTime': arrTime,'arrXD': arrXD, 'arrYD': arrYD, 'arrTimeD': arrTimeD, 'arrMU': arrMU, 'arrMD': arrMD, 'file': outfile },
                             function(data) {
                             if(data==1)
                             window.location = "session.php?auto=1&userID=<?php echo $userID; ?>";
                             else
                             window.location = "error.html";
                             }
                             );
                      });

</script>

<?php
    include('footer.php');
    ?>
