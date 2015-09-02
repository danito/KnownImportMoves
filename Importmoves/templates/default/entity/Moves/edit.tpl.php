<?= $this->draw('entity/edit/header'); ?>
<?php
$user_tokens = \Idno\Core\site()->session()->currentUser()->importmoves;
$access_token = $user_tokens['user_token'];
$refresh_token = $user_tokens['refresh_token'];
$importmoves = \Idno\Core\site()->plugins()->get('Importmoves');
$validation = $importmoves->getTokenValidation($access_token);
$strDate = 'yesterday';
$yesterday = date('Y-m-d', strtotime($strDate));
$moves = $importmoves->getDailySummary($access_token, $yesterday);
$summary = $moves[0]['summary'];
$totaldistance = $totalsteps = 0;

// construct tags from activities
$tags = "#moves ";
foreach ($summary as $activity) {
    $a = $activity['activity'];
    $tags = $tags . "#" . $a . " ";   
}

$data = $importmoves->construct_activity_group_array($moves[0]);
$dataset = json_encode($data['data2']);
$content = $importmoves->construct_content($data);
$fulldate = date('j F Y', strtotime('yesterday'));
$titel = "My Moves for {$fulldate}";

$vars['object']->title = $titel;
$vars['object']->body = $content;
$vars['object']->data = json_encode($data);
$day = $vars['object']->day = date('Ymd', strtotime('yesterday'));
$vars['object']->tags = $tags;
?>
<form action="<?= $vars['object']->getURL() ?>" method="post">
    <input type="hidden" name="title" id="title" value="<?= htmlspecialchars($vars['object']->title) ?>"  />
    <input type="hidden" name="body" id="body" value="<?= htmlspecialchars($vars['object']->body) ?>"  />
    <input type="hidden" name="data" id="data" value='<?= ($vars['object']->data) ?>'  />
    <input type="hidden" name="tags" id="tags" value="<?= ($vars['object']->tags) ?>"  />
    <input type="hidden" name="created" id="created" value="<?= ($vars['object']->created) ?>"  />
    <input type="hidden" name="day" id="day" value="<?= ($vars['object']->day) ?>"  />
    <div class="row idno-entry">

        <div class="col-md-8 col-md-offset-2 edit-pane">
            <?php
            if (!empty($vars['object']->_id)) {
                echo "<h3>Moves for {$fulldate} already published.</h3>";
            } else {
                echo "<h2>{$titel}</h2>";
            }
            ?>

            <div id="moves-diagram-<?= $day ?>"></div>
            <?= $this->autop($this->parseHashtags($this->parseURLs($vars['object']->body, $rel))) ?>
            <script>
                var h = 200;
                var w = 500;
                var minimumBubbleSize = 10;
                var labelsWithinBubbles = true;
                var title = "";
                var dataset = <?= $dataset ?>;
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
            ?>
            
            <?= $this->draw('entity/tags/input'); ?>
            <?php if (empty($vars['object']->_id)) echo $this->drawSyndication('article'); ?>
            <?php if (empty($vars['object']->_id)) { ?>
                <input type="hidden" name="forward-to" value="<?= \Idno\Core\site()->config()->getDisplayURL() . 'content/all/'; ?>" />
            <?php } ?>
            <p class="button-bar ">
                <?= \Idno\Core\site()->actions()->signForm('/moves/edit') ?>
                <input type="button" class="btn btn-cancel" value="Cancel" onclick="hideContentCreateForm();" />
                <?php if (empty($vars['object']->_id)) { ?>
                    <input type="submit" class="btn btn-primary" value="Publish" />
                <?php } ?>
                <?= $this->draw('content/access'); ?>
            </p>
        </div>

    </div>

</form>

<?= $this->draw('entity/edit/footer'); ?>