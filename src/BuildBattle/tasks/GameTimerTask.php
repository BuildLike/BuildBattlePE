<?php

namespace BuildBattle\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\utils\Config;

use BuildBattle\Main;

class GameTimerTask extends PluginTask {

  private $buildzone;

  public function __construct(Main $plugin) {
    parent::__construct($plugin);
    $this->plugin = $plugin;
  }

  public function onRun($tick) {
    $config = new Config($this->plugin->getDataFolder() . "arenas.json", Config::JSON);
    $arenas = $config->get("arenas");
    if(!empty($arenas)) {
      foreach($arenas[0] as $arena => $data) {
        $gamearena = $this->plugin->getServer()->getLevelByName($arena);
        $levelplayers = $gamearena->getPlayers();
        $count = count($levelplayers);
        $waittime = $arenas[0][$arena]["waittime"];
        $gametime = $arenas[0][$arena]["gametime"];
        $status = $arenas[0][$arena]["status"];
        $vote = $arenas[0][$arena]["votetime"];
        if($status === "ingame") {
          if($count >= 2) {
            if($gametime > 0) {
              $gametime--;
              $arenas[0][$arena]["gametime"] = $gametime;
              $config->set("arenas", $arenas);
              $config->save();
            }
          } else {
            $gametime = 31;
            $arenas[0][$arena]["gametime"] = $gametime;
            $config->set("arenas", $arena);
            $config->save();
          }
        }

        foreach($levelplayers as $player) {
          if($count >= 2) {
            if($gametime > 0) {
              if($gametime % 60 == 0 && $gametime != 60) {
                $player->sendMessage($this->plugin->prefix . " §eBuilding ends in §c" . $gametime / 60 . " §eminutes.");
              } elseif($gametime == 60) {
                $player->sendMessage($this->plugin->prefix . " §eBuilding ends in §c" . $gametime / 60 . " §eminute.");
              } elseif($gametime == 30 or $gametime == 15 or $gametime == 10 or ($gametime > 1 && $gametime <= 5)) {
                $player->sendTip("§l§eBuilding ends in §c" . $gametime . " §eseconds.");
              } elseif($gametime == 1) {
                $player->sendTip("§l§eBuilding ends in §c" . $gametime . " §esecond.");
              }
            } else {
              $player->sendMessage($this->plugin->prefix . " §aVoting has started!");
              $arenas[0][$arena]["status"] = "voting";
              $config->set("arenas", $arenas);
              $config->save();

              if($status == "voting") {
                if(!(isset($this->buildzone))) {
                  $this->buildzone = 1;
                }

                $buildzone = $arenas[0][$arena]["buildzone" . $this->buildzone];

                $x = $buildzone["center"]["x"];
                $y = $buildzone["center"]["y"];
                $z = $buildzone["center"]["z"];

                if($votetimer == 5) {
                  $player->teleport(new Vector3($x, $y, $z));
                  if($this->buildzone <= $count) {
                    $this->buildzone++;
                  } else {
                    unset($this->buildzone);
                  }
                }

                if($votetimer > 0) {
                  $votetimer--;
                  $arenas[0][$arena]["votetimer"] = $votetimer;
                  $config->set("arenas", $arenas);
                  $config->save();
                } elseif($votetimer == 0) {
                  $player->sendMessage($this->plugin->prefix . " §8DEBUG");
                  $arenas[0][$arena]["votetimer"] = 5;
                  $config->set("arenas", $arenas);
                  $config->save();
                }
              }
            }
          }
        }
      }
    }
  }
}
