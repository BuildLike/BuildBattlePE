<?php

namespace BuildBattle;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use BuildBattle\Commands\BBCommand;

use BuildBattle\Events\CreateBuildZones;
use BuildBattle\Events\JoinSign;

//use BuildBattle\Tasks\LobbyTimerTask;
//use BuildBattle\Tasks\GameTimerTask;

class Main extends PluginBase {

  public $prefix = "§8[ §bBuild§3Battle §8]";
  public $mode = 0;
  public $arenas = [];

  public $currentArena;
  public $currentLobby;

  // TODO public $themes = ["house", "rainbow", "redstone", "village", "lake"];

  public function onEnable() {
    $this->getLogger()->info("§b[BuildBattle] Loading...");
    $this->initializeConfig();
    $this->loadArenas();
    $this->registerEvents();
    $this->registerCommands();
    //$this->registerTasks();
    $this->getLogger()->info("§a[BuildBattle] Everything loaded.");
  }

  private function initializeConfig() {
    @mkdir($this->getDataFolder());
    if(!file_exists($this->getDataFolder() . "config.yml")) {
      $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }
    if(!file_exists($this->getDataFolder() . "temp_match_data.json")) {
      $temp = new Config($this->getDataFolder() . "temp_match_data.json", Config::JSON);
    }
  }

  private function loadArenas() {
    $config = new Config($this->getDataFolder() . "arenas.json", Config::JSON);
    $arenas = $config->get("arenas");
    if($arenas !== null && !empty($arenas)) {
      foreach($arenas as $key => $arena) {
        if($arena !== null) {
          $this->getServer()->loadLevel($arena);
          $lobby = $arenas[$key]["waitlobby"];
          $this->getServer()->loadLevel($lobby);
        }
      }
    }
  }

  private function registerEvents() {
    $this->getServer()->getPluginManager()->registerEvents(new CreateBuildZones($this), $this);
    $this->getServer()->getPluginManager()->registerEvents(new JoinSign($this), $this);
  }

  private function registerCommands() {
    $this->getCommand("bb")->setExecutor(new BBCommand($this), $this);
  }

  private function registerTasks() {
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new LobbyTimerTask($this), 20);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameTimerTask($this), 20);
  }
}
