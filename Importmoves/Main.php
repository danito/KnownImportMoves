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
            \Idno\Core\site()->addPageHandler('/moves/delete/([A-Za-z0-9]+)/?', '\IdnoPlugins\Importmoves\Pages\Delete');

            /** Template extensions */
            // Add menu items to account & administration screens
            \Idno\Core\site()->template()->extendTemplate('admin/menu/items', 'admin/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('onboarding/connect/networks', 'onboarding/connect/importmoves');
            \Idno\Core\site()->addPageHandler('importmoves/save', '\IdnoPlugins\Importmoves\Pages\Save');
            \Idno\Core\site()->template()->extendTemplate('shell/head', 'importmoves/shell/head');
        }

        /**
         * Returns the Api url with request token
         * https://dev.moves-app.com/docs/authentication#authorization
         * @return string $request_url
         */
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

        /**
         * Returns token details if valid or error
         * https://dev.moves-app.com/docs/authentication#validatetoken
         * @param string $access_token
         * @return type
         */
        function getTokenValidation($access_token) {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            return $importmovesApi->validate_token($access_token);
        }

        /**
         * Checks if token is valid and refreshs if not
         * https://dev.moves-app.com/docs/authentication#refreshtoken
         * @param string $refresh_token
         * @return boolean
         */
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

        /**
         * Returns Moves-App profile details 
         * https://dev.moves-app.com/docs/api_profile
         * @param string $access_token
         * @return array
         */
        function getProfile($access_token) {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $profile = $importmovesApi->get_profile($access_token);
            return $profile;
        }

        /**
         * 
         * @param string $access_token
         * @param string $start
         * @return type
         */
        function getDailySummary($access_token, $start) {
            return $this->getRange($access_token, $start, $start);
        }

        /**
         * Groups activies (walking + running..)
         * @param array $day
         * @return array
         */
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

        /**
         * Returns the body of the entity depending on the different moves.
         * @param array $day
         * @return string $description
         */
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

        /**
         * Gets the summary details of your moves between end and start date (Y-m-d)
         * https://dev.moves-app.com/docs/api_summaries
         * @param string $access_token
         * @param string $start
         * @param string $end
         * @return boolean | array
         */
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
