<?php

/**
 * Plugin administration
 */

namespace IdnoPlugins\Importmoves\Pages {

    /**
     * Default class to serve the homepage
     */
    class Save extends \Idno\Common\Page {

        function getContent() {
            $this->gatekeeper(); // Logged-in users only
            if ($importmoves = \Idno\Core\site()->plugins()->get('Importmoves')) {
                $yesterday = date('Y-m-d', strtotime('yesterday'));
                $yesterday = date('Y-m-d', strtotime('6 july 2015'));
                $moves = $importmoves->getDailySummary($access_token, $yesterday);
                $summary = $moves[0]['summary'];
                $day = array();
                $movesorder = array();
                $totaldistance = $totalsteps = 0;
                foreach ($summary as $activity) {
                    $a = $activity['activity'];
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
                $importmoves->saveMove();
            }
            $this->forward('/account/importmoves/');
        }

        function postContent() {
            $this->getContent();
        }

    }

}