<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <?= $this->draw('account/menu') ?>
        <h1>import Moves</h1>

    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">

        <?php
        if (!empty(\Idno\Core\site()->config()->importmoves['moves_client_id']) && !empty(\Idno\Core\site()->config()->importmoves['moves_client_secret'])) {
            ?>
            <form action="<?= \Idno\Core\site()->config()->getDisplayURL() ?>account/importmoves/" class="form-horizontal" method="post">
                <?php
                if (empty(\Idno\Core\site()->session()->currentUser()->importmoves)) {
                    ?>
                    <div class="control-group">
                        <div class="controls-config">
                            <div class="row">
                                <div class="col-md-7">
                                    <p>
                                        Import your daily summary from Moves. </p>
                                    <p>
                                        You can post a graph with your moves to your Known site. 
                                    </p>


                                    <div class="social">
                                        <p>
                                            <a href="<?= $vars['oauth_url'] ?>" class="btn btn-primary">
                                                <i class="fa fa-globe"></i> Connect Moves</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <?php
                } else {
                    //check if token is still valid
                    $user_tokens = \Idno\Core\site()->session()->currentUser()->importmoves;
                    $access_token = $user_tokens['user_token'];
                    $refresh_token = $user_tokens['refresh_token'];
                    $importmoves = \Idno\Core\site()->plugins()->get('Importmoves');

                    $validation = $importmoves->getTokenValidation($access_token);
                    $refresh = false;
                    if ($validation === FALSE) {
                        $refresh = $importmoves->refreshToken($refresh_token);
                    } else {
                        $refresh = true;
                    }
                    if ($refresh == false) {
                        ?>
                        <div class="control-group">
                            <div class="controls-config">
                                <div class="row">
                                    <div class="col-md-7">
                                        <p>
                                            Ooops something went wrong. Please disconnect from Moves and retry again.
                                        </p>

                                        <div class="social">
                                            <p>
                                                <input type="hidden" name="remove" value="1" />
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-globe"></i> Disconnect Moves</button>
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="row">

                    </div>
                    <?php
                } else {
                    ?>
                    <div class="control-group">
                        <div class="controls-config">
                            <div class="row">
                                <div class="col-md-7">
                                    <p>
                                        You are connected to your Moves account.
                                    </p>

                                    <div class="social">
                                        <p>
                                            <input type="hidden" name="remove" value="1" />
                                            <button type="submit" class="btn btn-success"><i class="fa fa-globe"></i> Disconnect Moves</button>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                    <div class="row">
                        <?php
                        $strDate = '7 July 2015';
                        $yesterday = date('Y-m-d', strtotime($strDate));
                        $moves = $importmoves->getDailySummary($access_token, $yesterday);
                        $summary = $moves[0]['summary'];
                        $day = array();
                        $movesorder = array();
                        $totaldistance = $totalsteps = 0;
                        $tags = "#moves ";
                        foreach ($summary as $activity) {
                            $a = $activity['activity'];
                            $tags = $tags. "#".$a." ";
                            $daytmp[$a]['distance'] = $activity['distance'];
                            $daytmp[$a]['duration'] = $activity['duration'];
                            $daytmp[$a]['steps'] = $activity['steps'];
                            $movesorder[$a] = $activity['distance'];
                        }
                        arsort($movesorder, SORT_NUMERIC);
                        foreach ($movesorder as $key => $value) {
                            $day[$key] = $daytmp[$key];
                        }
                        $dir = \Idno\Core\site()->config()->getTempDir();
                        $data = $importmoves->construct_activity_group_array($moves[0]);
                        $dataset = $importmoves->construct_image($data['data2']);
                        $movesimage = $dataset['moveimage'];
                        $firstactivity = \Idno\Core\site()->session()->currentUser()->created;

                        
                        ?>
                        <div class="row idno-entry">
                            <hr>
                            <div style="font-size: 0.85em">
                                <smaller>Your Moves' activities since <?= date('j F Y', $firstactivity) ?> are ready to be published:</smaller>
                            </div>
                            <h2>My Moves for <?= date('j F Y', strtotime($strDate)) ?></h2>
                            
                            
                            <?php
                            $content = $importmoves->construct_content($day);
                            $diagram = $importmoves->construct_image_js($data['data2'], $yesterday);
                            echo $diagram . "<p>" . $content . "<br/>".$tags;
                            ?>
                            <form action="<?= \Idno\Core\site()->config()->getDisplayURL() ?>importmoves/save" class="form-horizontal" method="post">
                                <div class="social">
                                    <p>
                                        <input type="hidden" name="publish" value="1" />
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-globe"></i> Import</button>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            if (\Idno\Core\site()->session()->currentUser()->isAdmin()) {
                ?>
                <div class="control-group">
                    <div class="controls-config">
                        <div class="row">
                            <div class="col-md-7">
                                <p>
                                    Before you can begin connecting to Moves, you need to set it up.
                                </p>
                                <p>
                                    <a href="<?= \Idno\Core\site()->config()->getDisplayURL() ?>admin/importmoves/">Click here to begin
                                        Moves configuration.</a>
                                </p>
                                <?php
                            } else {
                                ?>
                                <p>
                                    The administrator has not finished setting up Twitter on this site.
                                    Please come back later.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
            }
            ?>
            <?php
        }
        ?>
    </div>
</div>            