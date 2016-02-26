<?php
namespace TDroidd\Infection;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Position;
use pocketmine\tile\Sign;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use TDroidd\Infection\Main;

class EventListener implements Listener{

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getEntity();
        if($this->getPlugin()->isInGame($player)){
            $this->getPlugin()->removePlayer($player);
        }
    }

    public function onSignTap(PlayerInteractEvent $event){
        if ($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68) {
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            $player = $event->getPlayer();
            if (!($sign instanceof Sign)) {
                return false;
            }
            $sign = $sign->getText();
            if ($sign[0] == TextFormat::YELLOW . "[Infected]") {
                if($this->getPlugin()->game_status === Main::GAME_WAITING){
                    $this->getPlugin()->pickTeam($player);
                }else{
                    $player->sendMessage(Main::COLOR("&cEl juego ya ha comenzado!"));
                    return false;
                }
            }
        }
        return true;
    }

    public function getPlugin(){
        return $this->plugin;
    }

    public function getServer(){
        return $this->plugin->getServer();
    }
}