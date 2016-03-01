<?php
namespace  TDroidd\Infection\Tasks;
use pocketmine\Player;
use pocketmine\Server;
use TDroidd\Infection\Main;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\PluginTask;

class WaitTask extends PluginTask{

    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->waitTime = 30;
    }

    public function onRun($currentTick){

    }

}