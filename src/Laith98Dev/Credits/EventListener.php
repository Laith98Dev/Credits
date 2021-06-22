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

use pocketmine\event\Listener;

use pocketmine\utils\{Config, TextFormat as TF};
use pocketmine\Player;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;

/**
 * Class EventListener
 * @package Credits
 */
class EventListener implements Listener {
	
	/** @var Main */
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function getDataManager(){
		return $this->plugin->getDataManager();
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$pp = $event->getPlayer();
		$player = $this->plugin->getPlayer($pp);
		
		if($player === null || !($player instanceof Player))
			return false;
		
		if($this->getDataManager()->checkData($player)){
			$data = $this->getDataManager()->getPlayerData($player);
			$lastdaily = $data->get("lastdaily");
			$now = time();
			
			if($lastdaily !== 0){
				if($now >= $lastdaily){
					$data->set("lastdaily", 0);
					$data->save();
				}
			}
		}
	}
	
	public function onChat(PlayerChatEvent $event){
		$player = $event->getPlayer();
		$msg = $event->getMessage();
		
		foreach ($this->plugin->acceptTransfer as $key => $task){
			if($task->getPlayer() == null)
				continue;
			if($task->getPlayer()->getName() == $player->getName()){
				if($msg == $task->getCode()){
					$to = $task->getTo();
					$count = $task->getCount();
					$reason = $task->getReason();
					
					if(!$task->isCanceled()){
						$this->plugin->transferCredits($player, $to, $count, $reason);
						$task->cancelTask();
					}
					
					unset($this->plugin->acceptTransfer[$key]);
				} else {
					$player->sendMessage(TF::RED . "wrong code try again");
				}
				
				$event->setCancelled(true);
				break;
			}
		}
	}
}
