<?php
include_once('settings.php');
include_once('lib.php');
include_once('headerpaint.php');

$stimuli = loadTxt($stimulusfile, 0);

$coords_json = file_get_contents('contour_coordinate_opencv.json');
$coords = json_decode($coords_json, true);

$userID = $_GET['userID'];
$p = $_GET['presentation'];
$progress = $_GET['perc'];

$outfile = "./subjects/$userID/$p.csv";
$perc = $_GET['perc'];
?>

<div id="header">
    <span style="font-size:14px;font-weight:bold;margin-left:10px;position:absolute;">id: <?php echo $userID; ?></span>
    <div style="float:right;margin-right:10px;">
        <a href="help.php" target="_blank">
            <img src="images/help-button.png" width="20" height="20" alt="help">
        </a>
    </div>

    <div id="progress-bar" class="all-rounded">
        <div id="progress-bar-percentage" class="all-rounded" style="width: <?php echo $perc;?>%">
            <span><?php echo $perc;?>%</span>
        </div>
    </div>
</div>

<div id="container">

    <!-- Drawing Area -->
    <div id="pbox" style="position:relative; width:900px; height:600px; margin:20px auto;">

        <div id="instruction-box" style="width:500px; margin:10px auto 5px auto; text-align:center; padding-top: 50px;">
            <h3 style="font-weight:bold;"><?php echo $tasklabel; ?></h3>
        </div>

        <div id="stimulus-box" style="width:500px; margin:10px auto 20px auto; text-align:center; font-size:24px;">
            <?php 
            if($type == 'paintimages') { 
                if(file_exists($stimuli[$p])):
                    $stimulusimage = $stimuli[$p];
                else:
                    $stimulusimage = './yuno.jpg';
                endif;
                echo '<img style="width:480px;margin-top:10px;border:2px solid black;" src="'.$stimulusimage.'">';
            } elseif($type == 'paintwords') { 
                echo $stimuli[$p];
            } 
            ?>
        </div>


        <!-- Gray Body Backgrounds and left/right instructions-->
        <div id="pbox1" style="position:absolute; top:10px; left:30px; width:175px; height:524px; background:url('images/dummyG_small.png') no-repeat; background-size:contain; z-index:0;"></div>
        <div id="pbox2" style="position:absolute; top:10px; right:30px; width:175px; height:524px; background:url('images/dummyG_small.png') no-repeat; background-size:contain; z-index:0;"></div>
        <div id="pbox1L"><?php echo $labels[0]; ?></div>
        <div id="pbox2L"><?php echo $labels[1]; ?></div>

        <!-- Canvas and SVG on top -->
        <canvas id="pbox-canvas" width="900" height="600" style="position:absolute; top:0; left:0; z-index:100; cursor:crosshair;"></canvas>
        <svg id="body-outline" style="position:absolute; top:0; left:0; width:900px; height:600px; pointer-events:none; z-index:101;"></svg>

    </div>
</div>

<!-- Footer Buttons -->
<div id="footer" style="margin-top:10px; width:900px; position:relative; height:50px;">

    <!-- Left button (Reset) -->
    <div style="position:absolute; left:0; top:50%; transform:translateY(-50%);">
        <form action="#">
            <input type="button" value="<?php echo $pagetexts['delete'];?>" style="color:black; cursor:pointer; background:#ddd; font-size:20px; padding:5px 15px; font-weight:bold; margin-left:20px;" onClick="window.location.reload();">
        </form>
    </div>

    <!-- Right button (Submit) -->
    <div style="position:absolute; right:0; top:50%; transform:translateY(-50%);">
        <form method="POST" action="getit.php" id="movenext">
            <input type="submit" value="<?php echo $pagetexts['forward'];?>" style="color:black; cursor:pointer; background:#ddd; font-size:20px; padding:5px 15px; font-weight:bold; margin-right:20px;">
        </form>
    </div>
</div>

<style>
#pbox1L, #pbox2L {
    position: absolute;
    bottom: 10px;  /* make sure they're near the bottom */
    width: 300px;  /* 🔥 make them WIDER */
    font-size: 16px;
    z-index: 2;
}
#pbox1L {
    left: 10px; /* move a little left */
    text-align: left;
}
#pbox2L {
    right: 10px; /* move a little right */
    text-align: right;
}
</style>

<script type="text/javascript">
// Variables
var outfile = "<?php echo $outfile; ?>";
var arrX = [];
var arrY = [];
var arrTime = [];
var isDrawing = false;
var bodyCoordsLeft = [];
var bodyCoordsRight = [];

// Load and transform body coordinates
fetch('contour_coordinate_opencv.json')
    .then(response => response.json())
    .then(data => {
        const width_orig = 418;
        const height_orig = 1224;
        const width_display = 184; // modified from 175
        const height_display = 547; // modified from 524
        const offset_x_left = 24; // modified from 30
        const offset_x_right = 900 - 175 - 36; //modified from 900 - 175 - 30
        const offset_y = 0; //modified from 10

        function transformPoint(pt, offsetX) {
            const [x, y] = pt;
            const scaled_x = (x / width_orig) * width_display + offsetX;
            const scaled_y = (y / height_orig) * height_display + offset_y;
            return [scaled_x, scaled_y];
        }

        bodyCoordsLeft = data.map(pt => transformPoint(pt, offset_x_left));
        bodyCoordsRight = data.map(pt => transformPoint(pt, offset_x_right));

        drawBodyOutline(bodyCoordsLeft, "red");
        drawBodyOutline(bodyCoordsRight, "blue");
    });

// Draw outlines
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

// Point-in-polygon
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

// Spray effect
function sprayEffect(x, y, color) {
    const ctx = document.getElementById("pbox-canvas").getContext("2d");
    ctx.globalAlpha = 0.3;
    for (let i = 0; i < 30; i++) {
        const angle = Math.random() * 2 * Math.PI;
        const radius = Math.random() * 5;
        const offsetX = Math.cos(angle) * radius;
        const offsetY = Math.sin(angle) * radius;

        ctx.beginPath();
        ctx.arc(x + offsetX, y + offsetY, 1.5, 0, 2 * Math.PI);
        ctx.fillStyle = color;
        ctx.fill();
    }
    ctx.globalAlpha = 1.0;
}

// Mouse handling
$("#pbox-canvas")
  .mousedown(function(e) {
      isDrawing = true;
  })
  .mouseup(function(e) {
      isDrawing = false;
  })
  .mousemove(function(e) {
      if (!isDrawing || bodyCoordsLeft.length === 0) return;

      var rect = this.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;

      let insideLeft = pointInPolygon([x, y], bodyCoordsLeft);
      let insideRight = pointInPolygon([x, y], bodyCoordsRight);

      if (insideLeft || insideRight) {
          let currColour = insideLeft ? '#FF000044' : '#0000FF44';
          sprayEffect(x, y, currColour);

          arrX.push(x);
          arrY.push(y);
          arrTime.push(e.timeStamp);
      }
  });

// Submit
$("#movenext").submit(function(event) {
    event.preventDefault(); // Always block normal form behavior

    if (arrX.length === 0 || arrY.length === 0) {
        alert("⚠️ You must draw on the body before submitting!");
        return; // Stop here — do not send anything to server
    }

    var $form = $(this);
    var url = $form.attr('action');

    $.post(url, {
        'arrX': arrX,
        'arrY': arrY,
        'arrTime': arrTime,
        'file': outfile
    }, function(data) {
        if (data == 1)
            window.location = "session.php?auto=1&userID=<?php echo $userID; ?>";
        else
            window.location = "error.html";
    });
});

</script>

<?php include('footer.php'); ?>
