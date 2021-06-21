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

use pocketmine\scheduler\Task;
use pocketmine\Player;

/**
 * Class TypeTransferCodeTask
 * @package Credits
 */
class TypeTransferCodeTask extends Task {
	
	/** @var Main */
	private $plugin;
	
	/** @var Player */
	public $player;
	
	/** @var string */
	public $to;
	
	/** @var int */
	public $count;
	
	/** @var string */
	public $reason;
	
	/** @var string */
	public $code;
	
	public function __construct(Main $plugin, Player $player, string $to, int $count, string $reason, string $code){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->to = $to;
		$this->count = $count;
		$this->reason = $reason;
		$this->code = $code;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getTo(){
		return $this->to;
	}
	
	public function getCount(){
		return $this->count;
	}
	
	public function getReason(){
		return $this->reason;
	}
	
	public function getCode(){
		return $this->code;
	}
	
	public function onRun(int $tick){
		$plugin = $this->plugin;
		$player = $this->player;
		$to = $this->to;
		$count = $this->count;
		$reason = $this->reason;
		
		$give = true;
		foreach ($plugin->acceptTransfer as $t){
			if($t->getPlayer() == null)
				continue;
			if($t->getPlayer()->getName() == $player->getName()){
				unset($plugin->acceptTransfer[$player->getName()]);
				$player->sendMessage($plugin->getMessage("transfer.time.ended"));
				$give = false;
			}
		}
		
		
		if($give){
			$this->plugin->transferCredits($player, $to, $count, $reason);
		}
		
		$this->getHandler()->cancel();
	}
}
