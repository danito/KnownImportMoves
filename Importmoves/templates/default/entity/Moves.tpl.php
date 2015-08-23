<?php
$object = $vars['object'];
$importmoves = \Idno\Core\site()->plugins()->get('Importmoves');
if (\Idno\Core\site()->currentPage()->isPermalink()) {
    $rel = 'rel="in-reply-to"';
} else {
    $rel = '';
}
?>
<h2 class="p-name"><a href="<?= $object->getURL(); ?>"><?= htmlentities(strip_tags($object->getTitle()), ENT_QUOTES, 'UTF-8'); ?></a></h2>

<?php
$content = $object->getDescription();
$data = ($object->data);
$day = $object->day;
?>

<div id="moves-diagram-<?= $day ?>"></div>
<?= $this->autop($this->parseHashtags($this->parseURLs($vars['object']->body, $rel))) ?>
<script>
    var h = 200;
    var w = 500;
    var minimumBubbleSize = 10;
    var labelsWithinBubbles = true;
    var title = "";
    var dataset = <?= $data ?>;
    dataset = dataset.data2;
    var gapBetweenBubbles = 16;
    var xPadding = 20;
    var yPadding = 100;
    var scaling = 45;
    var steps = false;
    var distance = true;
    /* Sort the dataset to ensure the bubble are always ascending */
    dataset = dataset.sort(function (a, b) {
        return (b.distance - a.distance);
    });
    /* Scale the dataset */
    var factor = minimumBubbleSize / dataset[0].distance;
    //var l = dataset.length-1;
    //var factor = minimumBubbleSize / dataset[l].distance;

    dataset.forEach(function (d) {
        d.value = d.distance * factor;
    });
    /* Scaling */
    function getRadius(area) {
        return Math.sqrt(area / Math.PI);
    }
    function getLabelDivSideFromArea(area) {
        return Math.sqrt(Math.pow(2 * rScale(area), 2) / 2);
    }
    var rScale = function (input) {
        /* Magic number here is just to get a reasonable sized smallest bubble */
        return getRadius(input) * scaling;
    }
    /* For bubbles that are too big to centre their text, compute a better position */
    function getNewXPosition(leftBubble, rightBubble) {
    }
    function getNewYPosition(leftBubble, rightBubble) {
    }
    /* Create the chart */
    var svg = d3.select("div#moves-diagram-<?= $day ?>")
            .append("svg")
            .attr("width", w)
            .attr("height", h)
            .attr("class", "moves")
            .attr("viewBox", "0 0 " + w + " " + h)
    /* Adjust left hand side to add on the radius of the first bubble */
    xPaddingPlusRadius = xPadding + rScale(dataset[0].value);
    dataset[0].xPos = xPaddingPlusRadius;

    var node = svg.selectAll(".node")
            .data(dataset)
            .enter()
            .append("g")
            .attr("class", "node")
            //.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
            .on("mouseover", function (d) {
                d3.select(this).transition().ease("elastic")
                        .duration(1000)
                        .select("circle").attr("r", rScale(d.value) - 1)
                        ;
            })
            .on("mousemove", function (d, i)
            {
                //tooltipDivID.css({top:d.y+d3.mouse(this)[1],left:d.x+d3.mouse(this)[0]+50});
                //showToolTip("<ul><li>"+data[0][i]+"<li>"+data[1][i]+"</ul>",d.x+d3.mouse(this)[0]+10,d.y+d3.mouse(this)[1]-10,true);
                //console.log(d3.mouse(this));
            })
            .on("mouseout", function (d) {
                d3.select(this).transition().ease("elastic")
                        .duration(1000)
                        .attr("transform", "scale(1)")
                        .select("circle").attr("r", rScale(d.value))
                        ;
            })
            .on("mousedown", function (d) {
                if (rScale(d.value) > 30 && (d.group == "walking" || d.group == "running")) {
                    if (steps == false) {
                        d3.select(this)
                                .select(".label")
                                .html(function (d, i) {
                                    return "<p class=\'label\'>" + (d.steps) + " <br /><span class=\'label-small\'>Steps</span></p>";
                                });
                        d3.select(this).select("circle")
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 3)
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 1)
                                ;
                        steps = true;
                    } else {
                        d3.select(this)
                                .select(".label")
                                .html(function (d, i) {
                                    return "<p class=\'label\'>" + (d.distance / 1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>";
                                })
                        d3.select(this).select("circle")
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 3)
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 1)
                                ;
                        steps = false;
                    }
                }
                if (rScale(d.value) > 30 && (d.group == "transport" || d.group == "cycling")) {
                    if (distance == false) {
                        d3.select(this)
                                .select(".label")
                                .html(function (d, i) {
                                    return "<p class=\'label\'>" + (d.distance / 1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>";
                                });
                        d3.select(this).select("circle")
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 3)
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 1)
                                ;
                        distance = true;
                    } else {
                        d3.select(this)
                                .select(".label")
                                .html(function (d, i) {
                                    return "<p class=\'label\'>" + ("0" + Math.floor(d.duration / (60 * 60))).slice(-2) + ":" + ("0" + (Math.floor(d.duration / 60) % 60)).slice(-2) + "h</p>";
                                })
                        d3.select(this).select("circle")
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 3)
                                .transition().ease("elastic")
                                .duration(100)
                                .attr("r", rScale(d.value) - 1)
                                ;
                        distance = false;
                    }
                }
            });
    var accumulator = xPaddingPlusRadius;
    node.append("circle")
            .attr("cx", function (d, i) {
                if (i > 0) {
                    var previousRadius = rScale(dataset[i - 1].value);
                    var currentRadius = rScale(d.value);
                    // if bubble is to small to fit text
                    var ajustGap = 0;
                    if (currentRadius < 20) {
                        ajustGap = 10;
                    }
                    var increment = previousRadius + currentRadius + gapBetweenBubbles + ajustGap;
                    accumulator += increment;
                    d.xPos = accumulator;
                    return accumulator;
                } else {
                    return xPaddingPlusRadius;
                }
            })
            .attr("cy", function (d) {
                //return h - rScale(d.value) - yPadding;
                return h / 2;
            })
            .attr("r", function (d) {
                return rScale(d.value);
            })
            .attr("class", function (d) {
                return d.group;
            })
            ;
    /* Place text in the circles. Could try replacing this with foreignObject */
    node.append("foreignObject")
            .attr("x", function (d, i) {
                if (d.xPos > w) {
                    /* Do the different thing */
                    return d.xPos - ((getLabelDivSideFromArea(d.value) * 1.2) / 2);
                } else {
                    return d.xPos - ((getLabelDivSideFromArea(d.value) * 1.2) / 2);
                }
            })
            .attr("y", function (d, i) {
                if (labelsWithinBubbles) {
                    return h / 2 - ((getLabelDivSideFromArea(d.value) * 1.2) / 2);
                } else {
                    return h - yPadding + 20;
                }
            })
            .attr("width", function (d) {
                return getLabelDivSideFromArea(d.value) * 1.2;
            })
            .attr("height", function (d) {
                return getLabelDivSideFromArea(d.value) * 1.2;
            })
            .append("xhtml:body")
            .append("div")
            .attr("style", function (d) {
                return "width: " + getLabelDivSideFromArea(d.value) * 1.2 + "px; height: " + getLabelDivSideFromArea(d.value) * 1.2 + "px;";
            })
            .attr("class", "labelDiv")
            .attr("title", function (d) {
                return d.label + " " + (d.distance / 1000).toFixed(1) + " km";
            })
            .html(function (d, i) {
                if (rScale(d.value) > 20) {
                    return "<p class=\'label\'>" + (d.distance / 1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>";
                } else {
                    return "";
                }
            })
            ;
    node.append("text")
            .text(function (d) {
                return d.label;
            })
            .attr("y", function (d) {
                return h / 2 + rScale(d.value) + 10;
            })
            .attr("x", function (d, i) {
                return d.xPos;
            })
            .attr("font-size", 10)
            .attr("class", function (d) {
                return d.group;
            })
            .attr("text-anchor", "middle")
            ;
</script>

<?php
if (!empty($vars['object']->tags)) {
    ?>
    <p class="tag-row"><i class="fa fa-tag"></i> <?= $this->parseHashtags($vars['object']->tags) ?></p>
    <?php
}