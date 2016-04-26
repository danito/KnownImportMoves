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
                    $refresh_token = $user_tokens['user_refresh_token'];
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
                                            Ooops something went wrong (probably your token has expired). Please disconnect from Moves and retry again.
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
                        <p>
                            You can now import your daily Move summary.<br>
                            Create a new entity "Moves" and if you didn't already import your yesterday's moves, you can publish them.
                        </p>
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