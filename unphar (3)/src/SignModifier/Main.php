<?php

namespace SignModifier;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class Main extends PluginBase implements Listener{
	private $changing;
	private $msg;
	private $erase;

    public function onEnable(){
		$this->getServer()->getLogger()->info(TextFormat::GREEN."SignModifier Enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function playerBlockTouch(PlayerInteractEvent $event){
		$name = $event->getPlayer()->getName();
		$b = $event->getBlock();
        if(($b->getID() == 63 || $b->getID() == 68) && isset($this->changing[$name])){
            $sign = $event->getPlayer()->getLevel()->getTile($b);
            if(!($sign instanceof Sign)){
                return;
            }
            $signt = $sign->getText();
			$msg = $this->msg[$name];
			if ($msg == "\"\"") $msg = "";
			$x = $b->x;
			$y = $b->y;
			$z = $b->z;
			$l = $event->getPlayer()->getLevel()->getName();
			$line = $this->changing[$name];
			$this->getServer()->getLogger()->info(TextFormat::GREEN.$name." changed line $line of sign($x $y $z $l) from \"".$signt[$line-1]."\" to \"$msg\"");
			switch($line){
				case 1:
				$sign->setText($msg,$signt[1],$signt[2],$signt[3]);
				break;
				case 2:
				$sign->setText($signt[0],$msg,$signt[2],$signt[3]);
				break;
				case 3:
				$sign->setText($signt[0],$signt[1],$msg,$signt[3]);
				break;
				case 4:
				$sign->setText($signt[0],$signt[1],$signt[2],$msg);
				break;
			}
			$event->setCancelled();
			if ($this->erase[$name] === 1){
				unset ($this->changing[$name]);
				unset ($this->msg[$name]);
				unset ($this->erase[$name]);
				$event->getPlayer()->sendMessage(TextFormat::GREEN."[SignModifier] Sign modification stopped");
			} 
        }
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if ($command->getName() === "changesign"){
			if($sender instanceof Player){
			$name = $sender->getName();
			if (isset ($args[0]) && isset ($args[2])){
			switch($args[0]){
				case 1:
				case 2:
				case 3:
				case 4:
				$this->changing[$name] = $args[0];
				$this->msg[$name] = $args[2];
				$sender->sendMessage(TextFormat::YELLOW."[SignModifier] Tap sign(s) to change. " . TextFormat::GREEN."Mode: ". TextFormat::AQUA.(($args[1] === "b" or $args[1] === "B") ? "Bulk" : "Single sign"));
				break;
				default:
				$sender->sendMessage(TextFormat::RED."[SignModifier] Line number must be 1-4");
				return;
				break;
			}
			if ($args[1] === "b" or $args[1] === "B"){
				$this->erase[$name] = 0;
			}else{
				$this->erase[$name] = 1;
			}
			return true;
			}elseif (isset($args[0])){
				if ($args[0] === "c" or $args[0] === "C"){
				unset ($this->changing[$name]);
				unset ($this->msg[$name]);
				unset ($this->erase[$name]);
				$sender->sendMessage(TextFormat::GREEN."[SignModifier] Sign modification stopped");
				return true;
				}
			}
			}else{
				$sender->sendMessage(TextFormat::RED."Sorry, this command can only be used in-game :)");
				return true;
			}
		}
    }
	
	public function onDisable(){
		$this->getServer()->getLogger()->info(TextFormat::RED."SignModifier Disabled!");
    }
}
