<?php

namespace WolfDen133\Wolfies_NickUI;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\event\Listener;

use pocketmine\utils\Config;

class main extends PluginBase implements Listener {

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
        $config = new Config($this->getDataFolder() . "Player.yml", Config::YAML);
	}
    public function onJoin(PlayerJoinEvent $jevent){
        $player = $jevent->getPlayer();
		$config = new Config($this->getDataFolder() . "Player.yml", Config::YAML);
		if (!$config->exists($player->getName())){
			$config->set($player->getName(), $player->getName());
			$config->save();
			$player->setDisplayName($player->getName());
		} else {
        	$nick = $config->get($player->getName());
			$player->setDisplayName($nick);
		}
    }


    public function onCommand(CommandSender $sender, Command $cmd, String $label, Array $args) : bool {

        switch($cmd->getName()){
            case "nick":
                if($sender instanceof Player){
                    if($sender->isOp()){
                        if (isset($args[0]) && isset($args[1])){
                            if ($args[0] === "find") {
                                $players = array();
                                foreach ($this->getServer()->getOnlinePlayers() as $player) {
                                    if ($player->getDisplayName() === $args[1]) {
                                        $players[$player->getName()] = $player->getName();
                                    }
                                }
                                if (count($players) === 0) $sender->sendMessage("§a> No people have the nick $args[1]");
                                else $sender->sendMessage("§a> " . implode(",", $players) . " have the nick $args[1]");
                            } else {
                                $this->openNickUI($sender);
                            }
                        } else {
                            $this->openNickUI($sender);
                        }
                    } elseif($sender->hasPermission("nick.use")) {
                        $this->openNickUI($sender);
                    } else{
                        $sender->sendMessage("§cYou do not have permission to execute this command!");
                    }
                } else {
                    $sender->sendMessage("§cPlease use this command in-game!");
                }
                break;
        }
        return true;
    }
	
	public function openNickUI($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){
				case 0:
					$this->openCustomNick($player);
				break;
				case 1:
					$name = $player->getName();
					$player->setDisplayName($name);
					$config = new Config($this->getDataFolder() . "Player.yml", Config::YAML);
					if (!$config->exists($player->getDisplayName())){
						$config->set($player->getName(), $player->getDisplayName());
						$config->save();
					}
					$this->openNickUI($player);
				break;
				case 2:
				break;
			}


		});
		$form->setTitle("§l§bNickUI");
		$dname = $player->getDisplayName();
		$form->setContent("§eWelcome to NickUI!\n§eCurrent name §9" . $dname . "§e!");
		$form->addButton("§l§aSet\n§r§eSet you nick in a UI!", 0, "textures/ui/icon_setting");
		$form->addButton("§l§3Reset \n§r§eReset you nick with 1 button!", 0, "textures/ui/refresh_light");
		$form->addButton("§cClose", 0, "textures/ui/realms_red_x");
		$form->sendToPlayer($player);
		return $form;		
	}
	public function openCustomNick($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(function (Player $player, array $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			$player->setDisplayName($data[1]);
			$config = new Config($this->getDataFolder() . "Player.yml", Config::YAML);
			if (!$config->exists($player->getDisplayName())){
				$config->set($player->getName(), $player->getDisplayName());
				$config->save();
			}
			$this->openNickUI($player);

		});
		$form->setTitle("§l§bNickUI");
		$form->addLabel("§aWelcome to NickUI!\n§2Please input your nick!");
		$form->addInput("", "Input nick here!");
		$form->sendToPlayer($player);
		return $form;		
	}
}
