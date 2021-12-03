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
use pocketmine\command\Command;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;

use Laith98Dev\Credits\Main;

/**
 * Class DailyCommand
 * @package Credits\command
 */
final class DailyCommand extends Command implements PluginOwned
{
	/** @var Main */
	private Main $plugin;
	
	public function init(Main $plugin) : void{
		$this->plugin = $plugin;
	}
	
	public function getOwningPlugin() : Plugin{
		return $this->plugin;
	}
	
	/* public function __construct(Main $plugin){
		parent::__construct("daily", $plugin);
		$this->plugin = $plugin;
		$this->setDescription("Daily Command");
		$this->setAliases(["d"]);
	} */
	
	public function getDataManager(){
		return $this->plugin->getDataManager();
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("run the command in-game only!");
			return false;
		}
		
		if($this->plugin->cliamDaily($sender)){
			return true;
		}
		
		return false;
	}
}
