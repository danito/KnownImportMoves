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
                if (empty(\Idno\Core\site()->session()->currentUser()->twitter)) {
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
                        <?php
                }
            } else {
                    ?>
                        <?php
                }
            ?>
    </div>
</div>            