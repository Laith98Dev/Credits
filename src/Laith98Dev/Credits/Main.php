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

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\{Config, TextFormat as TF};
use pocketmine\player\Player;

use pocketmine\event\player\PlayerJoinEvent;

use Laith98Dev\Credits\command\CreditsCommand;
use Laith98Dev\Credits\command\DailyCommand;

/**
 * Class Main
 * @package Credits
 */
class Main extends PluginBase {
	
	const MAX_DAILY = 3000;
	
	const VERSION = "1.0.1";
	
	public static $instance = null;
	
	/** @var array */
	public $acceptTransfer = [];
	
	/** @var DataManager */
	private $dataManager;
	
	public function onLoad(): void{
		self::$instance = $this;
	}
	
	public function onEnable(): void{
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "players");
		
		$this->saveResource("lang.yml");
		
		$lang = new Config($this->getDataFolder() . "lang.yml", Config::YAML);
		if($lang->get("lang.version") !== self::VERSION)
			$this->updateVersion();
		
		$this->dataManager = new DataManager($this);
		
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		
		$map = $this->getServer()->getCommandMap();
		
		$cc = new CreditsCommand("credits", "Credits Command", null, ["c"]);
		$cc->init($this);
		
		$dc = new DailyCommand("daily", "Daily Command", null, ["d"]);
		$dc->init($this);
		
		$map->register($this->getName(), $cc);
		$map->register($this->getName(), $dc);
	}
	
	public function updateVersion(){
		$path = $this->getDataFolder() . "lang.yml";
		
		@unlink($path);
		
		$this->saveResource("lang.yml");
	}
	
	public static function getInstance(){
		return self::$instance;
	}
	
	public function getPlayer($player){
		$server = $this->getServer();
		$p = null;
		
		if(is_string($player)){
			$p = $server->getPlayerByPrefix($player);
			if($p == null){
				$p = $server->getPlayerExact($player);
			}
		} elseif ($player instanceof Player){
			$p = $server->getPlayerByPrefix($player->getName());
			if($p == null){
				$p = $server->getPlayerExact($player->getName());
			}
		}
		
		return $p;
	}
	
	public function getMessage(string $msg){
		$lang = new Config($this->getDataFolder() . "lang.yml", Config::YAML);
		return str_replace("&", TF::ESCAPE, $lang->get($msg));
	}
	
	public function getDataManager(){
		return $this->dataManager;
	}
	
	public function getCredits(Player $player){
		return $this->getDataManager()->getCredits($player);
	}
	
	public function addCredits(Player $player, int $new){
		return $this->getDataManager()->addCredits($player, $new);
	}
	
	public function reduceCredits(Player $player, int $count){
		return $this->getDataManager()->reduceCredits($player, $count);
	}
	
	public function transferCredits(Player $player, string $toName, int $count, string $reason){
		return $this->getDataManager()->transferCredits($player->getName(), $toName, $count, $reason);
	}
	
	public function getLastDaily(Player $player){
		return $this->getDataManager()->getLastDaily($player);
	}
	
	public function submitTransfer(Player $player, string $to, int $count, string $reason){
		$player = $this->getPlayer($player);
		if($player === null)
			return false;
		
		foreach ($this->acceptTransfer as $t){
			if($t->getPlayer() == null)
				continue;
			if($t->getPlayer()->getName() == $player->getName()){
				$player->sendMessage($this->getMessage("another.transfer.process"));
				return false;
			}
		}
		
		$code = $this->generateRandomCode();
		$task = new TypeTransferCodeTask($this, $player, $to, $count, $reason, $code);
		$this->acceptTransfer[] = $task;
		$this->getScheduler()->scheduleDelayedTask($task, 20 * 15);
		$player->sendMessage("Type: '" . $code . "' to confirm the transfer.");
		return true;
	}
	
	public function cliamDaily(Player $player): bool{
		$player = $this->getPlayer($player);
		if($player === null)
			return false;
		
		$now = time();
		if($now >= $this->getLastDaily($player)){
			$data = $this->getDataManager()->getPlayerData($player);
			if($data === null || !($data instanceof Config))
				return false;
			
			$data->set("lastdaily", 0);
			$data->save();
		}
		
		$lastdaily = $this->getLastDaily($player);
		if($lastdaily !== 0){
			$left = $this->getDataManager()->getDailyTimeLeft($player);
			if($left === null)
				return false;
			$h = $left["h"];
			$m = $left["m"];
			$s = $left["s"];
			$player->sendMessage(str_replace(["{h}", "{m}", "{s}"], [$h, $m, $s], $this->getMessage("cannot.claim.daily")));
			return true;
		}
		
		//$add = 2000 + (($n = mt_rand(2000, self::MAX_DAILY)) > 2500 ? $n + ($n % 10) : 2500 + mt_rand(0, 500));
		$add = (($n = mt_rand(2000, self::MAX_DAILY)) > 2500 ? $n + ($n % mt_rand(1, 2)) : 2400 + mt_rand(0, 600));
		if($this->addCredits($player, $add)){
			$this->getDataManager()->updateLastDaily($player);
			$left = $this->getDataManager()->getDailyTimeLeft($player);
			if($left === null)
				return true;
			$h = $left["h"];
			$m = $left["m"];
			$s = $left["s"];
			$player->sendMessage(str_replace(["{h}", "{m}", "{s}", "{count}"], [$h, $m, $s, $add], $this->getMessage("successful.claimed.daily")));
			return true;
		}
		
		return false;
	}
	
	public function generateRandomCode(){
		$chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
		srand((double)microtime() * 1000000); 
		$i = 0; 
		$code = ''; 

		while ($i < 5) { 
			$num = rand() % 33; 
			$tmp = substr($chars, $num, 1); 
			$code = $code . $tmp; 
			$i++; 
		}
		
		return $code;
	}
}
