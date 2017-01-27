<?php

namespace BuildBattle\vents;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\level\Level;

use pocketmine\utils\Config;

use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;

use BuildBattle\Main;

class CreateBuildZones extends PluginBase implements Listener {

  public $arena = [];
  public $buildzone;

  private $plugin;

  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }

  public function onInteract(PlayerInteractEvent $event) {
    $config = new Config($this->plugin->getDataFolder() . "config.json", Config::JSON);
    $player = $event->getPlayer();
    $block = $event->getBlock();
    $x = $block->getX();
    $y = $block->getY();
    $z = $block->getZ();
    $current_arena = $player->getLevel()->getFolderName();
    if($this->plugin->mode > 0) {
      if($this->plugin->mode < 25) {
        if(!isset($this->buildzone)) {
          $this->buildzone = 1;
        }
        $first = [1, 4, 7, 10, 13, 16, 19, 22];
        $second = [2, 5, 8, 11, 14, 17, 20, 23];

        if(in_array($this->plugin->mode, $first)) {
          $lowpos = array("x1" => $x, "y1" => $y, "z1" => $z);
          if($this->plugin->mode == 1) {
            $this->arena[$current_arena]["players"] = array();
            $this->arena[$current_arena]["status"] = "";
            $this->arena[$current_arena]["waitroom"] = "";
            $this->arena[$current_arena]["waitlobby"] = "";
          }
          if($this->plugin->mode < 25) {
            $this->arena[$current_arena]["buildzone" . $this->buildzone] = array();
            $this->arena[$current_arena]["buildzone" . $this->buildzone]["builder"] = "";
            $this->arena[$current_arena]["buildzone" . $this->buildzone]["center"] = "";
          }
          array_push($this->arena[$current_arena]["buildzone" . $this->buildzone], $lowpos);
          $this->plugin->mode++;
        } elseif(in_array($this->plugin->mode, $second)) {
          $uppos = array("x2" => $x, "y2" => $y, "z2" => $z);
          array_push($this->arena[$current_arena]["buildzone" . $this->buildzone], $uppos);
          $this->plugin->mode++;
          $player->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->buildzone . " §dcenter");
        } elseif($this->plugin->mode % 3 == 0) { //if the remainder is 0 it can be divided by 3
          $center = array("x" => $x, "y" => $y, "z" => $z);
          $this->arena[$current_arena]["buildzone" . $this->buildzone]["center"] = $center;
          $this->plugin->mode++;
          $this->buildzone++;
          if($this->buildzone < 9) {
            $player->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->buildzone);
          }
        }
      } elseif($this->plugin->mode == 25) {
        unset($this->buildzone);
        if(!($this->arenaExists($current_arena))) {
          $this->plugin->mode++;
          $player->sendMessage($this->plugin->prefix . " §aBuild Zones registered. Type the world name of the wait lobby for " . $current_arena);
        } else {
          $player->sendMessage($this->plugin->prefix . " §4ERROR: §cARENA EXISTS");
        }
      } elseif($this->plugin->mode == (25 + 1)) {
        $this->plugin->currentArena = $current_arena;
        $this->plugin->getServer()->loadLevel($this->plugin->currentLobby);
        $lobby = $this->plugin->getServer()->getLevelByName($this->plugin->currentLobby);
        $player->teleport($lobby->getSafeSpawn(), 0, 0);
        $waitroom = array("x" => $x, "y" => $y, "z" => $z);
        $this->arena[$this->plugin->currentArena]["waitroom"] = $waitroom;
        $this->arena[$this->plugin->currentArena]["waitlobby"] = $this->plugin->currentLobby;
        array_push($this->plugin->arenas, $this->arena);
        $config->set("arenas", $this->plugin->arenas);
        $config->save();
        $this->plugin->mode++;
        $player->sendMessage($this->plugin->prefix . " §aWait room registered. Tap a sign to register it for this arena");
      }
    }
  }

  public function onChat(PlayerChatEvent $event) {
    $player = $event->getPlayer();
    if($this->plugin->mode == (25 + 1)) {
      $this->plugin->currentLobby = $event->getMessage();
      $player->sendMessage($this->plugin->prefix . " §9Tap to set waitroom coordinates");
    }
  }

  public function arenaExists(string $arena) {
    $config = new Config($this->plugin->getDataFolder() . "config.json", Config::JSON);
    if(!(empty($config->get("arenas")[0][$arena]))) {
      return true;
    } else {
      return false;
    }
  }
}
