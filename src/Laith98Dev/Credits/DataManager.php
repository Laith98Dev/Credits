<?php

namespace Laith98Dev\Credits;

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
 *  Copyright (C) 2021 Laith98Dev
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

use pocketmine\player\Player;
use pocketmine\utils\Config;

class DataManager {
	
	/** @vra Main */
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function getPlayers(){
		$players = [];
		
		$path = $this->plugin->getDataFolder() . "players/";

        foreach (scandir($path) as $file) {
			if(in_array($file, [".", ".."]))
				continue;
			$name = str_replace(".json", "", $file);
			$players[] = $name;
        }
		
		return $players;
	}
	
	public function checkData(Player $player): bool{
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return false;
		
		$name = strtolower($player->getName());
		$path = $this->plugin->getDataFolder() . "players/" . $name . ".json";
		
		if(!is_file($path)){
			(new Config($path, Config::JSON, [
				"name" => $name,
				"credits" => 100,
				"lastdaily" => 0,
			]));
		}
		
		return true;
	}
	
	public function getPlayerData(Player $player): ?Config {
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return null;
		$name = strtolower($player->getName());
		$path = $this->plugin->getDataFolder() . "players/" . $name . ".json";
		if(!is_file($path))
			return null;
		return new Config($path, Config::JSON);
	}
	
	public function getPlayerDataByName(string $name): ?Config {
		$name = strtolower($name);
		$path = $this->plugin->getDataFolder() . "players/" . $name . ".json";
		if(!is_file($path))
			return null;
		return new Config($path, Config::JSON);
	}
	
	public function updateLastDaily(Player $player){
		$data = $this->getPlayerData($player);
		if($data === null || !($data instanceof Config))
			return false;
		
		$now = time();
		$day = (0 * 86400);
		$hour = (13 * 3600);
		$minute = (59 * 60);
		$lastdaily = $now + $day + $hour + $minute;
		
		$data->set("lastdaily", $lastdaily);
		$data->save();
	}
	
	public function getDailyTimeLeft(Player $player){
		$lastdaily = $this->plugin->getLastDaily($player);
		if($lastdaily !== 0){
			$now = time();
			
			$remainingTime = $lastdaily - $now;
			$day = floor($remainingTime / 86400);
			$hourSeconds = $remainingTime % 86400;
			$h = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$m = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$s = ceil($remainingSec);
			
			return ["h" => $h, "m" => $m, "s" => $s];
		}
		
		return null;
	}
	
	public function getLastDaily(Player $player){
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return null;
		$data = $this->getPlayerData($player);
		if($data === null || !($data instanceof Config))
			return null;
		
		return $data->get("lastdaily");
	}
	
	public function getCredits(Player $player){
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return null;
		$data = $this->getPlayerData($player);
		if($data === null || !($data instanceof Config))
			return null;
		
		return $data->get("credits");
	}
	
	public function getCreditsByName(string $playerName){
		$data = $this->getPlayerDataByName($playerName);
		if($data === null || !($data instanceof Config))
			return null;
		
		return $data->get("credits");
	}
	
	public function addCredits(Player $player, int $add): bool {
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return false;
		
		$now = $this->getCredits($player);
		
		if($now === null)
			return false;
		
		if($now >= PHP_INT_MAX)
			return true;
		
		$data = $this->getPlayerData($player);
		if($data === null || !($data instanceof Config))
			return false;
		
		$new = ($now + $add);
		
		$data->set("credits", $new);
		$data->save();
		
		return true;
	}
	
	public function addCreditsByName(string $playerName, int $add): bool {
		
		$now = $this->getCreditsByName($playerName);
		
		if($now === null)
			return false;
		
		if($now >= PHP_INT_MAX)
			return true;
		
		$data = $this->getPlayerDataByName($playerName);
		if($data === null || !($data instanceof Config))
			return false;
		
		$new = ($now + $add);
		
		$data->set("credits", $new);
		$data->save();
		
		return true;
	}
	
	public function reduceCredits(Player $player, int $count): bool {
		$player = $this->plugin->getPlayer($player);
		if($player === null)
			return false;
		
		$now = $this->getCredits($player);
		
		if($now === null)
			return false;
		
		if($count > $now)
			return false;
		
		$data = $this->getPlayerData($player);
		if($data === null || !($data instanceof Config))
			return false;
		
		$new = ($now - $count);
		
		$data->set("credits", $new);
		$data->save();
		
		return true;
	}
	
	public function reduceCreditsByName(string $playerName, int $count): bool {
		
		$now = $this->getCreditsByName($playerName);
		
		if($now === null)
			return false;
		
		if($count > $now)
			return false;
		
		$data = $this->getPlayerDataByName($playerName);
		if($data === null || !($data instanceof Config))
			return false;
		
		$new = ($now - $count);
		
		$data->set("credits", $new);
		$data->save();
		
		return true;
	}
	
	public function transferCredits(string $playerName, string $toName, int $count, string $reason): bool{
		$player = $this->plugin->getPlayer($playerName);
		$to = $this->plugin->getPlayer($toName);
		$pcredits = $this->getCreditsByName($playerName);
		
		if($pcredits === null)
			return false;
		
		if($count > $pcredits){
			if($player !== null)
				$player->sendMessage($this->plugin->getMessage("not.enough.credits.for.transfer"));
			return false;
		}
		
		if($this->reduceCreditsByName($playerName, $count)){
			if($this->addCreditsByName($toName, $count)){
				if($player !== null)
					$player->sendMessage(str_replace(["{count}", "{toName}"], [$count, $toName], $this->plugin->getMessage("successful.transfer.player")));
				if($to !== null)
					$to->sendMessage(str_replace(["{count}", "{from}", "{reason}"], [$count, $playerName, $reason], $this->plugin->getMessage("claim.credits")));
				return true;
			}
		}
		
		return false;
	}
}
