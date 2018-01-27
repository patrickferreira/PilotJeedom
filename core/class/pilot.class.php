<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class pilotCmd extends cmd {
      /*     * *************************Attributs****************************** */


      /*     * ***********************Methode static*************************** */


      /*     * *********************Methode d'instance************************* */

      /*
       *      * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
       *            public function dontRemoveCmd() {
       *                  return true;
       *                        }
       *                             */

      public function execute($_options = array()) {
        //message::add('pilot', 'woot');
        //log::add('pilot', 'info', 'woot -> ' . $this->getLogicalId() . '<>' . $this->getType());

        $eqLogic = $this->getEqLogic();
        $key = $eqLogic->getConfiguration('apikey');

        if ( $this->getType() == 'action' && $this->getLogicalId() == 'notification' ) {
            pilot::sendNotification($key, $_options['title'], $_options['message']);

        }
        if ( $this->getType() == 'action' && $this->getLogicalId() == 'turnon' ) {
            $infoCmd = pilotCmd::byId($this->getConfiguration('infoId'));
            $result = jeedom::evaluateExpression('1');
            $infoCmd->event($result);
        }
        if ( $this->getType() == 'action' && $this->getLogicalId() == 'turnoff' ) {
            $infoCmd = pilotCmd::byId($this->getConfiguration('infoId'));
            $result = jeedom::evaluateExpression('0');
            $infoCmd->event($result);
          }

      }
}

class pilot extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        $key = config::genKey(10);
        $this->setConfiguration('apikey', $key);
        $this->setIsEnable('1');
        $this->setConfiguration('user', '1');
    }

    public function postInsert() {
        $logicalId = config::genKey(32);
        $this->setLogicalId($logicalId);
        $pilotCmd = new pilotCmd();
        $pilotCmd->setName('Notification');
        $pilotCmd->setType('action');
        $pilotCmd->setLogicalId('notification');
        $pilotCmd->setSubType('message');
        $pilotCmd->setDisplay('generic_type', 'GENERIC_ACTION');
        $pilotCmd->setEqLogic_id($this->getId());

        $pilotCmd2 = new pilotCmd();
        $pilotCmd2->setName('State');
        $pilotCmd2->setType('info');
        $pilotCmd2->setLogicalId('state');
        $pilotCmd2->setSubType('binary');
        $pilotCmd2->setDisplay('generic_type', 'ENERGY_STATE');
        $pilotCmd2->setEqLogic_id($this->getId());

        $pilotCmd->save();
        $pilotCmd2->save();

        $pilotCmd3 = new pilotCmd();
        $pilotCmd3->setName('Turn Off');
        $pilotCmd3->setValue('');
        $pilotCmd3->setType('action');
        $pilotCmd3->setLogicalId('turnoff');
        $pilotCmd3->setSubType('other');
        $pilotCmd3->setConfiguration('infoId', $pilotCmd2->getId() );
        $pilotCmd3->setDisplay('generic_type', 'ENERGY_OFF');
        $pilotCmd3->setEqLogic_id($this->getId());        

        $pilotCmd4 = new pilotCmd();
        $pilotCmd4->setName('Turn On');
        $pilotCmd4->setValue('');
        $pilotCmd4->setType('action');
        $pilotCmd4->setLogicalId('turnon');
        $pilotCmd4->setSubType('other');
        $pilotCmd4->setConfiguration('infoId', $pilotCmd2->getId() );
        $pilotCmd4->setDisplay('generic_type', 'ENERGY_ON');
        $pilotCmd4->setEqLogic_id($this->getId());
        
        $pilotCmd3->save();
        $pilotCmd4->save();
        $this->save();

    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */

    public static function sendNotification($key, $title, $message){
        log::add('pilot', 'info', 'wootwoot');

        $escTitle = urlencode($title);
        $escMessage = urlencode($message);

        $url = 'https://api.pilot.patrickferreira.com/' . $key . '/' . $escTitle . '/' . $escMessage;

        log::add('pilot', 'info', 'url -> '.$url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);            
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec ($ch);
        curl_close($ch);

        log::add('pilot', 'debug', 'Résultat de la notification '.$server_output);

    }

    public function getQrCode() {

      $internalUrl = network::getNetworkAccess('internal');
      $externalUrl = network::getNetworkAccess('external');
      $userId = $this->getConfiguration('user');
      $user = user::byId($this->getConfiguration('user'));

      if ( $internalUrl == null ) {
        return 'noUrlInterne';
      }
      else if ( $externalUrl == null ) {
        return 'noUrlExterne';
      }
      else if ( $user == '') {
        return 'noUser';
      }
      
      $key = $this->getConfiguration('apikey');

      $params = array(
        'apikey' => $key,
        'internalUrl' => $internalUrl,
        'externalUrl' => $externalUrl
      );

      $servername = config::byKey('name', 'core');
      log::add('pilot', 'info', 'url re-> ' . $servername );



      if (is_object($user)) {
        $params['servername'] = $servername;
        $params['username'] = $user->getLogin();
        $params['hash'] = $user->getHash();
      }

      log::add('pilot', 'info', 'url ref-> ' . urlencode(json_encode($params)));

      $url = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=pilot://';
      
      
      return $url . urlencode(json_encode($params));
      

  }
}