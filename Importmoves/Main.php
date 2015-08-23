<?php

namespace IdnoPlugins\Importmoves {

    class Main extends \Idno\Common\Plugin {

        function registerPages() {

            \Idno\Core\site()->addPageHandler('importmoves/auth', '\IdnoPlugins\Importmoves\Pages\Auth');
            \Idno\Core\site()->addPageHandler('importmoves/deauth', '\IdnoPlugins\Importmoves\Pages\Deauth');
            \Idno\Core\site()->addPageHandler('importmoves/callback', '\IdnoPlugins\Importmoves\Pages\Callback');
            \Idno\Core\site()->addPageHandler('admin/importmoves', '\IdnoPlugins\Importmoves\Pages\Admin');
            \Idno\Core\site()->addPageHandler('account/importmoves', '\IdnoPlugins\Importmoves\Pages\Account');
            \Idno\Core\site()->addPageHandler('moves/edit/?', '\IdnoPlugins\Importmoves\Pages\edit');
            \Idno\Core\site()->addPageHandler('moves/edit/([A-Za-z0-9]+)/?', '\IdnoPlugins\Importmoves\Pages\edit');
            /** Template extensions */
            // Add menu items to account & administration screens
            \Idno\Core\site()->template()->extendTemplate('admin/menu/items', 'admin/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('onboarding/connect/networks', 'onboarding/connect/importmoves');
            \Idno\Core\site()->addPageHandler('importmoves/save', '\IdnoPlugins\Importmoves\Pages\Save');
            \Idno\Core\site()->template()->extendTemplate('shell/head', 'importmoves/shell/head');
        }

        function getReqUrl() {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $request_url = $importmovesApi->requestURL();
            return $request_url;
        }

        function connect($access_token = FALSE) {
            require_once(dirname(__FILE__) . '/external/PHPMoves.php');
            error_log("ext: " . dirname(__FILE__) . '/external/PHPMoves.php');
            if (!empty(\Idno\Core\site()->config()->importmoves)) {
                $params = array(
                    'moves_client_id' => \Idno\Core\site()->config()->importmoves['moves_client_id'],
                    'moves_client_secret' => \Idno\Core\site()->config()->importmoves['moves_client_secret'],
                    'moves_redirect_uri' => \Idno\Core\site()->config()->importmoves['moves_redirect_url']
                );
                $client_id = \Idno\Core\site()->config()->importmoves['moves_client_id'];
                $client_secret = \Idno\Core\site()->config()->importmoves['moves_client_secret'];
                $redirect_url = \Idno\Core\site()->config()->importmoves['moves_redirect_url'];

                $phpmoves = new \PHPMoves\Moves($client_id, $client_secret, $redirect_url);

                return $phpmoves;
            }
            return false;
        }

        function getTokenValidation($access_token) {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            return $importmovesApi->validate_token($access_token);
        }

        function refreshToken($refresh_token) {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $tokens = $importmovesApi->refresh($refresh_token);
            if (!empty($tokens)) {
                $user = \Idno\Core\site()->session()->currentUser();
                $user->$importmoves[$profile['userId']] = array(
                    user_token => $tokens['access_token'],
                    user_refresh_token => $tokens['refresh_token']
                );
                $user->save();
                return true;
            } else {
                return false;
            }
        }

        function getProfile($access_token) {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $profile = $importmovesApi->get_profile($access_token);
            return $profile;
        }

        function getDailySummary($access_token, $start) {
            return $this->getRange($access_token, $start, $start);
        }

        function construct_activity_group_array(array $day) {
            $groups = array(
                "cycling" => array("label" => "cycling"), // 
                "running" => array("label" => ("running")), // 
                "walking" => array("label" => ("walking")), // 
                "transport" => array("label" => ("transport")));
            if (isset($day['summary'])) {
                $data = array();
                $graph_data = array();
                foreach ($groups as $group => $label) {
                    // filter all activity with group
                    // sum it up
                    foreach ($day['summary'] as $activityData) {
                        if ($activityData['group'] == $group) {
                            unset($activityData['activity']);
                            unset($activityData['group']);
                            $data[$group]['group'] = $group;
                            $data[$group]['label'] = $label['label'];
                            foreach ($activityData as $activityDataKey => $activityDataValue) {
                                $data[$group][$activityDataKey] = $data[$group][$activityDataKey] + $activityDataValue; // summieren pro key?
                            }
                        }
                    }
                }
                //parent::log(json_encode($data));
                foreach ($data as $group) {
                    $graph_data[] = $group;
                }
                $data['data1'] = $data;
                $data['data2'] = $graph_data;
                return $data;
            } else {
                return array();
            }
        }

       function construct_image_js($dataset, $day = '') {
            if ($day == ''){
                $day = date('Ymd',"yesterday");
            }
            $day = str_replace("-", "", $day);
            $post_meta_dataset_distance = array();
            foreach ($dataset as $activityDataKey => $activityDataValue) {
                    $postMetaKey = $activity . '_' . $activityDataKey;
                    if ($activityDataKey == "distance") {
                        $post_meta_dataset_distance[] = array("label" => $activity, "circle_label" => number_format( (intval($activityDataValue)/1000), 1, ',', '.') . ' km', "value" => $activityDataValue);
                    }
                }
           return $moves_diagram = '
    <div id="moves-diagram-'.$day.'"></div>
    <script>
    var h = 200;
    var w = 500;
    var minimumBubbleSize = 10;
    var labelsWithinBubbles = true;
    var title = "";
    var dataset = '.json_encode($dataset).';
    var gapBetweenBubbles = 16;
    var xPadding = 20;
    var yPadding = 100;
    var scaling = 45;
    var steps = false;
    var distance = true;
    
    /* Sort the dataset to ensure the bubble are always ascending */
    dataset = dataset.sort(function (a, b) { return (b.distance - a.distance);});
    /* Scale the dataset */
    var factor = minimumBubbleSize / dataset[0].distance;
    //var l = dataset.length-1;
    //var factor = minimumBubbleSize / dataset[l].distance;
    
    dataset.forEach(function(d) { d.value = d.distance * factor; });
    /* Scaling */
    function getRadius(area) {
        return Math.sqrt(area / Math.PI);
    }
    function getLabelDivSideFromArea(area) {
        return Math.sqrt(Math.pow(2 * rScale(area), 2) / 2);
    }
    var rScale = function(input) {
        /* Magic number here is just to get a reasonable sized smallest bubble */
        return getRadius(input) * scaling;
    }
    /* For bubbles that are too big to centre their text, compute a better position */
    function getNewXPosition(leftBubble, rightBubble) {
    }
    function getNewYPosition(leftBubble, rightBubble) {
    }
    /* Create the chart */
    var svg = d3.select("div#moves-diagram-'.$day.'")
    .append("svg")
    .attr("width", w)
    .attr("height", h)
    .attr("class", "moves")
    .attr("viewBox", "0 0 "+ w + " " + h)
    /* Adjust left hand side to add on the radius of the first bubble */
    xPaddingPlusRadius = xPadding + rScale(dataset[0].value);
    dataset[0].xPos = xPaddingPlusRadius;
	
	var node = svg.selectAll(".node")
    .data(dataset)
    .enter()
    .append("g")
    .attr("class", "node")
    //.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
    .on("mouseover", function(d) {
    	d3.select(this).transition().ease("elastic")
	        .duration(1000)
	        .select("circle").attr("r", rScale(d.value)-1)
	        ;
    })
    .on("mousemove", function(d,i)
    {
        //tooltipDivID.css({top:d.y+d3.mouse(this)[1],left:d.x+d3.mouse(this)[0]+50});
        //showToolTip("<ul><li>"+data[0][i]+"<li>"+data[1][i]+"</ul>",d.x+d3.mouse(this)[0]+10,d.y+d3.mouse(this)[1]-10,true);
        //console.log(d3.mouse(this));
    })    
    .on("mouseout", function(d) {
    	d3.select(this).transition().ease("elastic")
	        .duration(1000)
	        .attr("transform", "scale(1)")
	        .select("circle").attr("r", rScale(d.value))
	        ;
    })    
    .on("mousedown", function(d) {
        if (rScale(d.value) > 30 && (d.group == "walking" || d.group == "running")) { 
			if (steps == false) {
	    		d3.select(this)
		        .select(".label")
			    .html(function(d, i) { 
		    	    return "<p class=\'label\'>" + (d.steps) + " <br /><span class=\'label-small\'>'.("Steps").'</span></p>"; 
	    		});
				d3.select(this).select("circle")
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-3)
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-1)
	    		;
		    	steps = true;
		    } else {
    			d3.select(this)
	    	    .select(".label")
		    	.html(function(d, i) { 
			        return "<p class=\'label\'>" + (d.distance/1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>"; 
    			})
				d3.select(this).select("circle")
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-3)
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-1)
	    		;
	    		steps = false;
		    }
	    }
        if (rScale(d.value) > 30 && (d.group == "transport" || d.group == "cycling")) { 
			if (distance == false) {
	    		d3.select(this)
		        .select(".label")
			    .html(function(d, i) { 
		    	    return "<p class=\'label\'>" + (d.distance/1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>"; 
	    		});
				d3.select(this).select("circle")
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-3)
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-1)
	    		;
		    	distance = true;
		    } else {
    			d3.select(this)
	    	    .select(".label")
		    	.html(function(d, i) { 
			        return "<p class=\'label\'>" + ("0" + Math.floor(d.duration/(60*60))).slice(-2) + ":" +  ("0" + (Math.floor(d.duration/60)%60)).slice(-2) + " '.("h").'</p>"; 
    			})
				d3.select(this).select("circle")
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-3)
				.transition().ease("elastic")
		        .duration(100)
		        .attr("r", rScale(d.value)-1)
	    		;
	    		distance = false;
		    }
		}
    });
    var accumulator = xPaddingPlusRadius;
    node.append("circle")
    .attr("cx", function(d, i) {
        if (i > 0) {
            var previousRadius = rScale(dataset[i-1].value);
            var currentRadius = rScale(d.value);
            // if bubble is to small to fit text
            var ajustGap = 0;
            if (currentRadius < 20){
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
    .attr("cy", function(d) {
        //return h - rScale(d.value) - yPadding;
        return h / 2;
    })
    .attr("r", function(d) {
        return rScale(d.value);
    })
    .attr("class", function(d) {
        return d.group;
    })
    ;
    /* Place text in the circles. Could try replacing this with foreignObject */
    node.append("foreignObject")
    .attr("x", function(d, i) {
        if (d.xPos > w) {
            /* Do the different thing */
            return d.xPos - ((getLabelDivSideFromArea(d.value)*1.2)/2);
        } else {
            return d.xPos - ((getLabelDivSideFromArea(d.value)*1.2)/2);
        }
    })
    .attr("y", function(d, i) {
        if (labelsWithinBubbles) {
                return h /2  - ((getLabelDivSideFromArea(d.value)*1.2)/2);
        } else {
            return h - yPadding + 20;
        }
    })
    .attr("width", function(d) { return getLabelDivSideFromArea(d.value)*1.2; })
    .attr("height", function(d) { return getLabelDivSideFromArea(d.value)*1.2; })
    .append("xhtml:body")
    .append("div")
    .attr("style", function(d) { return "width: " + getLabelDivSideFromArea(d.value)*1.2 + "px; height: " + getLabelDivSideFromArea(d.value)*1.2 + "px;"; })
    .attr("class", "labelDiv")
    .attr("title", function(d) {
        return d.label + " " + (d.distance/1000).toFixed(1) + " km";
    })
    .html(function(d, i) { 
        if (rScale(d.value) > 20) { 
        return "<p class=\'label\'>" + (d.distance/1000).toFixed(1) + " <br /><span class=\'label-small\'>km</span></p>"; 
        } else { return ""; }
    })
    ;
	node.append("text")
    .text(function(d){
    	return d.label;
    })
    .attr("y", function(d) {
        return h/2 + rScale(d.value)+10;
    })
    .attr("x", function(d,i){
    	return d.xPos;
    })
    .attr("font-size",10)
    .attr("class", function(d) {
        return d.group;
    })
    .attr("text-anchor","middle")
    ;
    </script>
';
        }

        function construct_image($dataset) {
            $returndata = array();
            usort($dataset, function($b, $a) {
                return $a['distance'] - $b['distance'];
            });

            $minimumBubbleSize = 200;
            $labelsWithinBubbles = true;
            $title = "";

            $gapBetweenBubbles = 15;
            $xPadding = 20;
            $yPadding = 100;
            $scaling = 45;
            $steps = false;
            $w = 0;
            $factor = $minimumBubbleSize / $dataset[0]["distance"];
            $aera = pi() * (100 ** 2);
            foreach ($dataset as $key => $value) {
                $dataset[$key]["factor"] = round(($dataset[$key]["distance"] * $factor * 20), 0);
                $dataset[$key]["percent"] = 100 / ($dataset[0]["distance"] / $dataset[$key]["distance"]);
            }
            $w = 205 * (count($dataset));
            $returndata['width'] = $w;
            $h = 206;
            $im = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($im, 255, 255, 255);
            $grey = imagecolorallocate($im, 128, 128, 128);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagecolortransparent($im, $black);
            $color['circle'] = imagecolorallocate($im, 225, 152, 53);
            $color['walking'] = imagecolorallocate($im, 0, 213, 90);
            $color['cycling'] = imagecolorallocate($im, 0, 205, 236);
            $color['transport'] = imagecolorallocate($im, 132, 132, 132);
            $color['running'] = imagecolorallocate($im, 246, 96, 244);
            $font = (__DIR__ . '/arial.ttf');
            foreach ($dataset as $key => $value) {
                $label = $dataset[$key]['label'];
                $factor = $dataset[$key]['factor'];
                $distance = number_format((intval($dataset[$key]['distance']) / 1000), 1, ',', '.') . " km";
                if (!empty($dataset[$key]['steps'])) {
                    $steps = $dataset[$key]['steps'] . " steps";
                }
                $r = sqrt(($aera * ($dataset[$key]['percent'] / 100)) / pi());
                if ($r <= 10) {
                    $r = 45;
                    $distance = "";
                }
                $r += $r;
                $cy = 203 - $r / 2;
                $cx = 100 + (5 + $key * 200);
                imagefilledellipse($im, $cx, $cy, $r, $r, $color[$label]);
                $maxwidth = sqrt(($r ** 2) / 2);
                $tb = imagettfbbox(40, 0, $font, $distance);
                $scale = $maxwidth / $tb[4];
                $fontSize = $scale * 40;
                if ($fontSize > 10) {
                    $tb = imagettfbbox($fontSize, 0, $font, $distance);
                    $dataset[$key]['tb'] = $tb;
                    $tx = $cx - ($tb[4] / 2);
                    $ty = $cy - ($tb[5] / 2);
                    imagettftext($im, $fontSize, 0, $tx, $ty, $white, $font, $distance);
                }
            }
            $dir = \Idno\Core\site()->config()->getTempDir();
            $filename = $dir . 'importmoves.png';
            imagepng($im, $filename);
            imagedestroy($im);
            $returndata['dataset'] = $dataset;
            if (file_exists($filename)) {
                $type = pathinfo($filename, PATHINFO_EXTENSION);
                $data = file_get_contents($filename);
                $dataUri = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $returndata['moveimage'] = $dataUri;
                return $returndata;
            }
            return array();
        }

        function construct_content($day) {
            $distance = 0;
            $transport_distance = 0;
            $description = "I ";
            $description_transport = "";
            if (isset($day['walking']['steps']) && $day['walking']['steps'] >= 500) {
                $distance = intval($day['walking']['distance']);
                $description .= sprintf("walked %s steps", number_format(intval($day['walking']['steps']), 0, ',', '.'));
            } else {
                $description .= ("didn’t walk much");
            }
            if (isset($day['running']['distance']) && $day['running']['distance'] >= 500) {
                $distance = $distance + intval($day['running']['distance']);
                $description .= sprintf(" and ran for %s kilometers", number_format((intval($day['running']['distance']) / 1000), 1, ',', '.'));
            } else {
                $description .= "";
            }
            if (isset($day['cycling']['distance']) && $day['cycling']['distance'] >= 1000) {
                $distance = $distance + intval($day['cycling']['distance']);
                $description .= sprintf(" and rode bicycle for %s kilometers", number_format((intval($day['cycling']['distance']) / 1000), 1, ',', '.'));
            } else {
                $description .= "";
            }
            if (isset($day['transport']['distance']) && $day['transport']['distance'] >= 1000) {
                $transport_distance = intval($summary['distance']);
                // 
                $description .= sprintf((" and used transport for %s kilometers"), number_format((intval($day['transport']['distance']) / 1000), 1, ',', '.'));
            } else {
                $description .= "";
                $description_transport = "";
            }
            $description .= '.';
            if ($distance <= 500 && $description_transport != "") {
                $description = sprintf(("I hardly moved, but i %s."), $description_transport);
            } elseif ($distance <= 500 && $description_transport == "") {
                $description = ("I hardly moved");
            }

            if ($distance == 0 && $transport_distance == 0) {
                $description = ("I hardly moved");
            }
            return $description;
        }

        function getRange($access_token, $start, $end) {
            $endpoint = '/user/summary/daily';
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $summary = $importmovesApi->get_range($access_token, $endpoint, $start, $end);
            if ($summary) {
                return $summary;
            } else
                return false;
        }

    }

}
