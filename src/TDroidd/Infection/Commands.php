<?php

namespace TDroidd\Infection;

use pocketmine\permission\Permission;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use TDroidd\Infection\Main;

class Commands implements CommandExecutor {

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        $cmd = strtolower($cmd->getName());
        switch($cmd){
            case "infect":
                if($sender instanceof Player){
                    $this->plugin->setInfected($sender);
                    $sender->sendTip(Main::COLOR("&eDebug!"));
                }
                break;
            case "gi":
                $sender->sendMessage("in:" . $this->plugin->getInfected());
                break;
        }
    }
}