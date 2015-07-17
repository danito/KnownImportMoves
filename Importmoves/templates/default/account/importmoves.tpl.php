<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <?= $this->draw('account/menu') ?>
        <h1>Twitter</h1>

    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php
        if (!empty(\Idno\Core\site()->config()->importmoves['client_id']) && !empty(\Idno\Core\site()->config()->importmoves['client_secret'])) {
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
                                            <a href="<?= $vars['oauth_url'] ?>" class="connect tw">
                                                Connect Moves</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                            <input type="hidden" name="remove" class="form-control" value="<?= $account['username'] ?>"/>
                                            <button type="submit" class="connect moves connected">
                                                (Disconnect)
                                            </button>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
        
                <?php
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