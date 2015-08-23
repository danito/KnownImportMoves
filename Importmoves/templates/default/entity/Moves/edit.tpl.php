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
$day = array();
$movesorder = array();
$totaldistance = $totalsteps = 0;
$tags = "#moves ";
foreach ($summary as $activity) {
    $a = $activity['activity'];
    $tags = $tags . "#" . $a . " ";
    $daytmp[$a]['distance'] = $activity['distance'];
    $daytmp[$a]['duration'] = $activity['duration'];
    $daytmp[$a]['steps'] = $activity['steps'];
    $movesorder[$a] = $activity['distance'];
}
arsort($movesorder, SORT_NUMERIC);
foreach ($movesorder as $key => $value) {
    $day[$key] = $daytmp[$key];
}
$data = $importmoves->construct_activity_group_array($moves[0]);
$dataset = $importmoves->construct_image($data['data2']);
$movesimage = $dataset['moveimage'];
$firstactivity = \Idno\Core\site()->session()->currentUser()->created;
$content = $importmoves->construct_content($day);
$diagram = $importmoves->construct_image_js($data['data2'], $yesterday);
$fulldate = date('j F Y', strtotime('yesterday'));
$titel = "My Moves for {$fulldate}";
$vars['object']->title = $titel;
$vars['object']->body = $content;
$vars['object']->data = json_encode($data);
$vars['object']->day = date('Ymd',strtotime('yesterday'));
$vars['object']->tags = $tags;
$vars['object']->created = "yesterday 9:00 am";



$yesterday = $fulldate;

?>
<form action="<?= $vars['object']->getURL() ?>" method="post">
    <input type="hidden" name="title" id="title" value="<?=htmlspecialchars($vars['object']->title)?>"  />
    <input type="hidden" name="body" id="body" value="<?=htmlspecialchars($vars['object']->body)?>"  />
    <input type="hidden" name="data" id="data" value='<?=($vars['object']->data)?>'  />
    <input type="hidden" name="tags" id="tags" value="<?=($vars['object']->tags)?>"  />
    <input type="hidden" name="created" id="created" value="<?=($vars['object']->created)?>"  />
    <input type="hidden" name="day" id="day" value="<?=($vars['object']->day)?>"  />
    <div class="row idno-entry">

        <div class="col-md-8 col-md-offset-2 edit-pane">
<?php if (empty($vars['object']->_id)) echo "<h2>Moves for $yesterday already published. Editing</h2>"; ?>
            
            <h2><?= $titel ?></h2>
            <p>
                <?php echo $diagram . "<p>" . $content . "<br/>" . $tags; ?>

            </p>
            <?= $this->draw('entity/tags/input'); ?>
            <?php if (empty($vars['object']->_id)) echo $this->drawSyndication('moves'); ?>
            <?php if (empty($vars['object']->_id)) { ?>
                <input type="hidden" name="forward-to" value="<?= \Idno\Core\site()->config()->getDisplayURL() . 'content/all/'; ?>" />
            <?php } ?>
            <p class="button-bar ">
                <?= \Idno\Core\site()->actions()->signForm('/moves/edit') ?>
                <input type="button" class="btn btn-cancel" value="Cancel" onclick="hideContentCreateForm();" />
                <input type="submit" class="btn btn-primary" value="Publish" />
                <?= $this->draw('content/access'); ?>
            </p>

        </div>

    </div>

</form>

<?= $this->draw('entity/edit/footer'); ?>