<?php

namespace Laith98Dev\Credits\command;

/*  
 *  A plugin for PocketMine-MP.
 *  
 *	 _           _ _   _    ___   ___  _____             
 *	| |         (_) | | |  / _ \ / _ \|  __ \            
 *	| |     __ _ _| |_| |_| (_) | (_) | |  | | _____   __
 *	| |    / _` | | __| '_ \__, |> _ <| |  | |/ _ \ \ / /
 *	| |___| (_| | | |_| | | |/ /| (_) | |__| |  __/\ V / 
 *	|______\__,_|_|\__|_| |_/_/  \___/|_____/ \___| \_/  
 *	
 *	Copyright (C) 2021 Laith98Dev
 *	
 *	Youtube: Laith Youtuber
 *	Discord: Laith98Dev#0695
 *	Gihhub: Laith98Dev
 *	Email: help@laithdev.tk
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 	
 */

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;

use Laith98Dev\Credits\Main;

/**
 * Class CreditsCommand
 * @package Credits\command
 */
class CreditsCommand extends PluginCommand
{
	/** @var Main */
	private $plugin;
	
	public function __construct(Main $plugin){
		parent::__construct("credits", $plugin);
		$this->plugin = $plugin;
		$this->setDescription("Credits Command");
		$this->setAliases(["c"]);
	}
	
	public function getDataManager(){
		return $this->plugin->getDataManager();
	}
	
	public function getPlayerByName(string $name){
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach($this->getDataManager()->getPlayers() as $player){
			if(stripos($player, $name) === 0){
				$curDelta = strlen($player) - strlen($name);
				if($curDelta < $delta){
					$found = $player;
					$delta = $curDelta;
				}
				if($curDelta === 0){
					break;
				}
			}
		}

		return $found;
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("run the command in-game only!");
			return false;
		}
		
		if(!isset($args[0])){
			$player = $this->plugin->getPlayer($sender);
			if($player === null)
				return false;
			$c = $this->plugin->getCredits($player);
			$player->sendMessage("Hey " . $sender->getName() . ", your account balance is $" . $c . ".");
			return true;
		}
		
		$to = $this->getPlayerByName($args[0]);
		
		$data = $this->getDataManager()->getPlayerDataByName($to);
		if(!isset($args[1])){
			if($data !== null){
				$c = $data->get("credits");
				$sender->sendMessage($to . " balance is $" . $c . ".");
				return true;
			} else {
				$sender->sendMessage("Player not found!");
				return false;
			}
		}
		
		if(isset($args[1])){
			if(!is_numeric($args[1]) || strpos(".", $args[1])){
				$sender->sendMessage("transfer count must be intger!");
				return false;
			}
			
			$count = $args[1];
			
			$reason = "No reason provided";
			if(isset($args[2])){
				$reason = $args[2] . " ";
				for ($i = 3; $i <= (count($args) - 1); $i++){
					$reason .= $args[$i] . " ";
				}
			}
			
			if($this->plugin->submitTransfer($sender, $to, $count, $reason)){
				return true;
			}
		}
		
		return false;
	}
}
