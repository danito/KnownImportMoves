<?php

    if (empty(\Idno\Core\site()->session()->currentUser()->importmoves)) {
        $login_url = \Idno\Core\site()->config()->getDisplayURL() . 'importmoves/auth';
    } else {
        $login_url = \Idno\Core\site()->config()->getDisplayURL() . 'importmoves/deauth';
    }

?>
<div class="social">
    <a href="<?= $login_url ?>" class="connect moves <?php

        if (!empty(\Idno\Core\site()->session()->currentUser()->importmoves)) {
            echo 'connected';
        }

    ?>" target="_top">Twitter<?php

            if (!empty(\Idno\Core\site()->session()->currentUser()->importmoves)) {
                echo ' - connected!';
            }

        ?></a>
    <label class="control-label">Import activities from Moves and create a post on your Known.</label>
</div>
