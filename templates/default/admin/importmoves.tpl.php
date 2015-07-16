<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <?= $this->draw('admin/menu') ?>
        <h1>import Moves configuration</h1>

    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form action="<?= \Idno\Core\site()->config()->getDisplayURL() ?>admin/importmoves/" class="form-horizontal" method="post">
            <div class="control-group">
                <div class="controls-config">
                    <p>
                        To import your Moves summary, we need the client ID, the client Secret and the moves api url.
                    </p>
                    <p>
                        You find this when creatin a new App on the <a href="http://dev.moves-app.com" target="_blank">http://dev.moves-app.com</a> page.
                    </p>

                </div>
            </div>

            <div class="controls-group">
                <p>
                    Fill in the details below:
                </p>
                <label class="control-label" for="moves_client_id">Moves Client Id</label>

                <input type="text" id="moves_client_id" placeholder="client_id" class="form-control" name="moves_client_id" value="<?= htmlspecialchars(\Idno\Core\site()->config()->importmoves['moves_client_id']) ?>" >

                <label class="control-label" for="moves_client_secret">Moves Client secret</label>

                <input type="text" id="moves_client_secret" placeholder="client_secret" class="form-control" name="moves_client_secret" value="<?= htmlspecialchars(\Idno\Core\site()->config()->importmoves['moves_client_secret']) ?>" >

            </div>     	            
            <div class="controls-group">


            </div>  

            <div>
                <div class="controls-save">
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </div>
            <?= \Idno\Core\site()->actions()->signForm('/admin/twitter/') ?>
        </form>
    </div>
</div>
