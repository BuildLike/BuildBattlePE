<?php

namespace BuildBattle\commands;

use pocketmine\plugin\PluginBase;

use pocketmine\math\Vector3;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use BuildBattle\Main;

class BBCommand extends PluginBase {

  private $plugin;

  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
      case "bb":
        if(isset($args[0]) && $args[0] == "create") {
          if(isset($args[1])) {
            $this->plugin->getServer()->loadLevel($args[1]);
            $level = $this->plugin->getServer()->getLevelByName($args[1]);
            $sender->teleport(new Position($level->getSafeSpawn(), $level));
            $this->plugin->mode = 1;
            $sender->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->plugin->mode . " §dlower position.");
          }
        } else {
          $sender->sendMessage($this->plugin->prefix . " §cInvalid arguments.");
        }
      break;
    }
  }
}
