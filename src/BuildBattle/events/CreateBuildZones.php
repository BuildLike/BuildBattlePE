<?php

namespace BuildBattle\events;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\level\Level;

use pocketmine\utils\Config;

use pocketmine\math\Vector3;

use BuildBattle\Main;

class CreateBuildZones extends PluginBase implements Listener {

  private $arena = [];
  private $buildzone = 1;

  private $plugin;

  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }

  public function onInteract(PlayerInteractEvent $event) {
    $config = new Config($this->plugin->getDataFolder() . "arenas.json", Config::JSON);
    $player = $event->getPlayer();
    $block = $event->getBlock();
    $x = $block->getX();
    $y = $block->getY();
    $z = $block->getZ();
    $currentArena = $player->getLevel()->getFolderName();
    if($this->plugin->mode > 0) {
      if($this->plugin->mode < 25) {
        // TODO find a better method for this
        $first = [1, 4, 7, 10, 13, 16, 19, 22];
        $second = [2, 5, 8, 11, 14, 17, 20, 23];
        if(in_array($this->plugin->mode, $first)) {
          $lowpos = [
            "x1" => $x,
            "y1" => $y,
            "z1" => $z
          ];
          if($this->plugin->mode == 1) {
            $this->arena[$currentArena]["players"] = [];
            $this->arena[$currentArena]["status"] = "";
            $this->arena[$currentArena]["waitroom"] = "";
            $this->arena[$currentArena]["waitlobby"] = "";
          }
          if($this->plugin->mode < 25) {
            $this->arena[$currentArena]["buildzone" . $this->buildzone] = [];
            $this->arena[$currentArena]["buildzone" . $this->buildzone]["builder"] = "";
            $this->arena[$currentArena]["buildzone" . $this->buildzone]["center"] = "";
          }
          array_push($this->arena[$currentArena]["buildzone" . $this->buildzone], $lowpos);
          $this->plugin->mode++;
          $player->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->buildzone . " upper position.");
        } elseif(in_array($this->plugin->mode, $second)) {
          $uppos = [
            "x2" => $x,
            "y2" => $y,
            "z2" => $z
          ];
          array_push($this->arena[$currentArena]["buildzone" . $this->buildzone], $uppos);
          $this->plugin->mode++;
          $player->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->buildzone . " §dcenter.");
        } elseif($this->plugin->mode % 3 == 0) {
          $center = [
            "x" => $x,
            "y" => $y,
            "z" => $z
          ];
          $this->arena[$currentArena]["buildzone" . $this->buildzone]["center"] = $center;
          $this->plugin->mode++;
          $this->buildzone++;
          if($this->buildzone < 9) { //bug fix
            $player->sendMessage($this->plugin->prefix . " §dTap to set Build Zone §5" . $this->buildzone . " lower position.");
          }
        }
      } elseif($this->plugin->mode == 25) {
        unset($this->buildzone);
        if(!$this->arenaExists($currentArena)) {
          $this->plugin->mode++;
          $player->sendMessage($this->plugin->prefix . " §aBuild Zones registered. Type the world name of the wait lobby for " . $currentArena . ".");
        } else {
          $player->sendMessage($this->plugin->prefix . " §cArena already exists!");
        }
      } elseif($this->plugin->mode == 26) {
        $this->plugin->currentArena = $currentArena;
        $this->plugin->getServer()->loadLevel($this->plugin->currentLobby);
        $lobby = $this->plugin->getServer()->getLevelByName($this->plugin->currentLobby);
        $player->teleport($lobby->getSafeSpawn(), 0, 0);
        $waitroompos = [
          "x" => $x,
          "y" => $y,
          "z" => $z
        ];
        $this->arena[$this->plugin->currentArena]["waitroom"] = $waitroompos;
        $this->arena[$this->plugin->currentArena]["waitlobby"] = $this->plugin->currentLobby;
        $this->arena[$this->plugin->currentArena]["waittimer"] = 60;
        $this->arena[$this->plugin->currentArena]["gametimer"] = 120;
        $this->arena[$this->plugin->currentArena]["votetimer"] = 30;
        $this->arena[$this->plugin->currentArena]["status"] = "empty";
        array_push($this->plugin->arenas, $this->arena);
        $config->set("arenas", $this->plugin->arenas);
        $config->save();
        $this->plugin->mode++;
        $player->sendMessage($this->plugin->prefix . " §aWait room registered. Tap a sign to register it for this arena.");
      }
    }
  }

  public function onChat(PlayerChatEvent $event) {
    $player = $event->getPlayer();
    if($this->plugin->mode == 26) {
      $this->plugin->currentLobby = $event->getMessage();
      $player->sendMessage($this->plugin->prefix . " §9Tap to set waitroom coordinates.");
    }
  }

  public function arenaExists(string $arena) {
    $config = new Config($this->plugin->getDataFolder() . "arenas.json", Config::JSON);
    if(!empty($config->get("arenas")[$arena])) {
      return true;
    } else {
      return false;
    }
  }
}
