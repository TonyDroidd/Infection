<?php
namespace TDroidd\Infection;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use TDroidd\Infection\EventListener;
use TDroidd\Infection\Tasks\WaitTask;
use pocketmine\utils\Config;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener{

    public $infected = [];
    public $healthy_payers = [];
    public $inGame = [];
    public $config;

    public $game_status = self::GAME_WAITING;
    const GAME_WAITING = 0;
    const GAME_STARTED = 1;


    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this),$this);
        //$this->getCommand("infection")->setExecutor(new Commands($this));
        $this->getLogger()->info(Main::COLOR("&bInfection plugin &aEnabled!"));
        $this->registerEvents();
        @mkdir($this->getDataFolder());
        $this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function registerEvents(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public static function COLOR($message){
        $message = str_replace("&0", TextFormat::BLACK, $message);
        $message = str_replace("&1", TextFormat::DARK_BLUE, $message);
        $message = str_replace("&2", TextFormat::DARK_GREEN, $message);
        $message = str_replace("&3", TextFormat::DARK_AQUA, $message);
        $message = str_replace("&4", TextFormat::DARK_RED, $message);
        $message = str_replace("&5", TextFormat::DARK_PURPLE, $message);
        $message = str_replace("&6", TextFormat::GOLD, $message);
        $message = str_replace("&7", TextFormat::GRAY, $message);
        $message = str_replace("&8", TextFormat::DARK_GRAY, $message);
        $message = str_replace("&9", TextFormat::BLUE, $message);
        $message = str_replace("&a", TextFormat::GREEN, $message);
        $message = str_replace("&b", TextFormat::AQUA, $message);
        $message = str_replace("&c", TextFormat::RED, $message);
        $message = str_replace("&d", TextFormat::LIGHT_PURPLE, $message);
        $message = str_replace("&e", TextFormat::YELLOW, $message);
        $message = str_replace("&f", TextFormat::WHITE, $message);

        $message = str_replace("&k", TextFormat::OBFUSCATED, $message);
        $message = str_replace("&l", TextFormat::BOLD, $message);
        $message = str_replace("&m", TextFormat::STRIKETHROUGH, $message);
        $message = str_replace("&n", TextFormat::UNDERLINE, $message);
        $message = str_replace("&o", TextFormat::ITALIC, $message);
        $message = str_replace("&r", TextFormat::RESET, $message);
        return $message;
    }

    public function getPlayersInGame(){
        return $this->inGame;
    }

    public function addGamePlayer(Player $player){
        $this->inGame[$player->getName()] = $player->getName();
    }

    public function setInfected(Player $player){
        $this->infected[$player->getName()] = $player->getName();
        $player->sendMessage(Main::COLOR("&eHas entrado al equipo de los Zombies!"));
    }

    public function setHealthy(Player $player){
        $this->healthy_payers[$player->getName()] = $player->getName();
        $player->sendMessage(Main::COLOR("&aHas entrado al equipo de los Humanos!"));
        }

    public function getInfected(){
        if(isset($this->infected)){
                return $this->infected;
        }else{
            $this->getLogger()->error(Main::COLOR("&cNo hay jugadores infectados"));
            return false;
        }
    }

    public function getHealthy(){
        if(isset($this->healthy_payers)){
            return $this->healthy_payers;
        }else{
            $this->getLogger()->error(Main::COLOR("&cNo hay jugadores sanos"));
            return false;
        }
    }

    public function isInfected(Player $player){
        return in_array($player->getName(), $this->infected);
    }

    public function isHealthy(Player $player){
        return in_array($player->getName(), $this->healthy_payers);
    }

    public function isInGame(Player $player){
        return in_array($player->getName(), $this->inGame);
    }

    public function removePlayer(Player $player){
        if (in_array($player->getName(), $this->healthy_payers)) {
            $player->sendMessage(Main::COLOR("&eHas sido eliminado de la partida."));
            unset($this->healthy_payers[$player->getName()]);
            unset($this->inGame[$player->getName()]);
        }elseif(in_array($player->getName(), $this->infected)){
            unset($this->infected[$player->getName()]);
            unset($this->inGame[$player->getName()]);
        }
    }

    public function pickTeam(Player $player) {
        if(count($this->healthy_payers) === 0 && count($this->infected) === 0){
            $this->setInfected($player);
            $this->addGamePlayer($player);
            return true;
        }
        if(count($this->healthy_payers) < count($this->infected)){
            $this->setHealthy($player);
            $this->addGamePlayer($player);
        } else {
            if(count($this->infected) < count($this->healthy_payers)) {
                $this->setInfected($player);
                $this->addGamePlayer($player);
            }
        }
        if(count($this->infected) === 8 && count($this->healthy_payers) === 8){
            $player->sendMessage("Los equipos están llenos!!");
            return false;
        }
        return true;
    }

    public function startGame(){
        $this->game_status = Main::GAME_STARTED;
    }

    public function endGame(){
        $this->game_status = Main::GAME_WAITING;
    }

    public function startTask($time){
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new WaitTask($this), $time * 20);
        $time--;
        $players = $this->getPlayersInGame();
        if($players instanceof Player){
            $players->sendPopup(Main::COLOR("&aIniciando en &e" . $time . "&a segundos"));
        }
        if(count($this->getPlayersInGame()) === 16){
            $this->getServer()->getScheduler()->cancelTasks($this);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) == "infection") {
            if ($sender instanceof Player) {
                switch ($args) {
                    case "join":
                        if ($this->game_status === Main::GAME_WAITING) {
                            $this->pickTeam($sender);
                        } else {
                            $sender->sendMessage(Main::COLOR("&cEl juego ya ha comenzado!"));
                            return false;
                        }
                        break;
                    case "setlobby":
                        $x = $sender->getFloorX();
                        $y = $sender->getFloorY();
                        $z = $sender->getFloorZ();
                        $level = $sender->getLevel()->getName();
                        //$configlobb = $this->getConfig()->get("Game-Lobby");
                        //$this->getConfig()->set("Game-Lobby", [$x, $y, $z, $level]);
                        $sender->sendMessage(Main::COLOR("&aOk."));
                        $this->getConfig()->save();
                        break;
                    case "setinfpos":
                        break;
                    case "sethealpos":
                        break;
                }
            }
        }
        return true;
    }

}